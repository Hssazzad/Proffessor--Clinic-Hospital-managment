<?php

namespace App\Services;

use App\Models\CommonMedicine;
use App\Models\Company;
use App\Models\DosageForm;
use App\Models\MedicineSubtype;
use App\Models\MedicineType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Business logic for the Medicine Master module.
 * Extracted from MasterDataController — behavior preserved 1:1.
 */
class MedicineMasterService
{
    public const PER_PAGE = 20;

    public function hasGenericName(): bool
    {
        return Schema::hasColumn('common_medicine', 'generic_name');
    }

    public function normalizeName(?string $name): string
    {
        $name = trim($name ?? '');
        $name = preg_replace('/\s+/', ' ', $name);
        return Str::lower($name);
    }

    /**
     * Merged common + template medicine list, sorted by name, paginated.
     * Mirrors the original medicinesIndex logic exactly.
     */
    public function paginatedList(Request $request): array
    {
        $hasGenericName = $this->hasGenericName();

        $commonlyUsedFilter = $request->get('commonly_used_filter');
        $search             = trim((string) $request->get('search', ''));

        $commonQuery = CommonMedicine::with(['medicineType', 'medicineSubType', 'companyRecord', 'dosageForm']);
        if ($commonlyUsedFilter === 'yes') {
            $commonQuery->where('commonly_used', 1);
        } elseif ($commonlyUsedFilter === 'no') {
            $commonQuery->where('commonly_used', 0);
        }
        if ($search !== '') {
            $commonQuery->where(function ($q) use ($search, $hasGenericName) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%');
                if ($hasGenericName) {
                    $q->orWhere('generic_name', 'like', '%' . $search . '%');
                }
            });
        }
        $commonRows = $commonQuery->orderBy('name')->get();

        // Template medicines (used in admit/preorder/postorder/fresh templates) are shown
        // alongside the common medicine list. They are read-only rows (no edit/delete).
        $templateQuery = DB::table('template_medicine')
            ->select('name', 'strength', 'company', 'company_id', 'active', DB::raw('0 as commonly_used'), DB::raw('1 as is_template'));
        if ($hasGenericName) {
            $templateQuery->addSelect('generic_name');
        }
        $templateQuery->where('active', 1);
        if ($search !== '') {
            $templateQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%');
            });
        }
        $templateRows = $templateQuery->orderBy('name')->get();

        $allRows = $commonRows
            ->toBase()
            ->map(function ($m) {
                $m->is_template = 0;
                return $m;
            })
            ->merge($templateRows)
            ->sortBy(fn ($r) => strtolower($r->name))
            ->values();

        $page = max(1, (int) $request->get('page', 1));
        $medicines = new LengthAwarePaginator(
            $allRows->forPage($page, self::PER_PAGE)->values(),
            $allRows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'medicines'      => $medicines,
            'hasGenericName' => $hasGenericName,
            'selectedFilter' => $commonlyUsedFilter ?: 'all',
            'search'         => $search,
        ];
    }

    /**
     * Create/update payload from validated input.
     * Strength empty hole null noy — kichu DB-te column NOT NULL, tai ''.
     */
    public function buildPayload(array $validated): array
    {
        $company = Company::find($validated['company_id']);

        $data = [
            'medicine_type_id'     => $validated['medicine_type_id'],
            'medicine_sub_type_id' => ($validated['medicine_subtype_id'] ?? null) ?: null,
            'company_id'           => $validated['company_id'],
            'company'              => $company?->name,
            'name'                 => trim($validated['medicine_name']),
            'strength'             => trim($validated['strength'] ?? '') ?: '',
            'dosage_form_id'       => $validated['dosage_form_id'],
            'active'               => $validated['status'],
            'name_normalized'      => $this->normalizeName($validated['medicine_name']),
        ];

        if ($this->hasGenericName()) {
            $data['generic_name'] = $validated['generic_name'] ?? null;
        }

        $data['note'] = isset($validated['note']) ? trim((string) $validated['note']) ?: null : null;

        $data['commonly_used'] = !empty($validated['commonly_used']) ? 1 : 0;

        return $data;
    }

    /**
     * Exact duplicate lookup by normalized name.
     * Pass $excludeId to ignore the row being updated.
     */
    public function findExactDuplicate(string $name, ?int $excludeId = null): ?CommonMedicine
    {
        $query = CommonMedicine::where('name_normalized', $this->normalizeName($name));
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->first();
    }

    /**
     * Real-time duplicate suggestions while typing the Medicine Name.
     */
    public function partialMatches(string $name, int $limit = 5): array
    {
        $normalized = $this->normalizeName($name);

        return CommonMedicine::with(['companyRecord', 'dosageForm'])
            ->where('name_normalized', 'like', '%' . $normalized . '%')
            ->orderByRaw('name_normalized = ? DESC', [$normalized])
            ->limit($limit)
            ->get(['id', 'name', 'strength', 'company_id', 'company', 'dosage_form_id', 'name_normalized'])
            ->map(function ($m) use ($normalized) {
                return [
                    'id'       => $m->id,
                    'name'     => $m->name,
                    'strength' => $m->strength,
                    'company'  => $m->companyRecord?->name ?? $m->company,
                    'exact'    => $m->name_normalized === $normalized,
                ];
            })
            ->all();
    }

    /**
     * Compute the alphabetical list page on which a medicine name appears,
     * mirroring the merge/sort/pagination logic of paginatedList().
     */
    public function pageByName(string $name, int $perPage = self::PER_PAGE): int
    {
        $names = DB::table('common_medicine')->orderBy('name')->pluck('name')
            ->merge(DB::table('template_medicine')->where('active', 1)->orderBy('name')->pluck('name'))
            ->map(fn ($n) => strtolower((string) $n))
            ->sort()
            ->values();

        $needle  = strtolower(trim($name));
        $lastIdx = null;
        foreach ($names as $i => $n) {
            if ($n === $needle) {
                $lastIdx = $i;
            }
        }

        return (int) floor(($lastIdx ?? 0) / $perPage) + 1;
    }

    /* ////////////////////////*/
    /* CSV Export / Import      */
    /* ////////////////////////*/

    /**
     * Sob common_medicine download korbe CSV akare (Excel-e khulte UTF-8 BOM soh).
     */
    public function csvDownload(): StreamedResponse
    {
        $hasGeneric = $this->hasGenericName();

        $rows = CommonMedicine::with(['medicineType', 'medicineSubType', 'companyRecord', 'dosageForm'])
            ->orderBy('name')
            ->get();

        $headers = [
            'name', 'strength', 'medicine_type', 'sub_type', 'company',
            'dosage_form', 'GroupName', 'commonly_used', 'active',
        ];
        if ($hasGeneric) {
            array_splice($headers, 2, 0, ['generic_name']);
        }

        $filename = 'medicines_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $headers, $hasGeneric) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);

            foreach ($rows as $m) {
                $line = [
                    $m->name,
                    $m->strength ?? '',
                    $m->medicineType?->type_name ?? '',
                    $m->medicineSubType?->subtype_name ?? '',
                    $m->companyRecord?->name ?? $m->company ?? '',
                    $m->dosageForm?->dosage_name ?? '',
                    $m->GroupName ?? '',
                    (string) ($m->commonly_used ? 1 : 0),
                    (string) ($m->active ? 1 : 0),
                ];
                if ($hasGeneric) {
                    array_splice($line, 2, 0, [$m->generic_name ?? '']);
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * CSV file theke sob medicine import. type/company/dosage form named
     * match na pele auto-create hoy. name_normalized duplicate hole skip.
     *
     * @return array{inserted:int, duplicates:int, skipped:int}
     */
    public function csvImport(Request $request): array
    {
        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        if (!$handle) {
            throw new \RuntimeException('CSV file could not be read.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('CSV file is empty.');
        }

        // Excel-export kora UTF-8 BOM prothom header cell theke sorai
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $col = fn (string $key) => array_search($key, $header, true);

        $nameIdx = $col('name');
        if ($nameIdx === false) {
            fclose($handle);
            throw new \RuntimeException('CSV-te oboshshoi "name" column thakte hobe.');
        }

        $idx = [
            'strength' => $col('strength'),
            'generic'  => $col('generic_name'),
            'type'     => $col('medicine_type'),
            'sub'      => $col('sub_type'),
            'company'  => $col('company'),
            'form'     => $col('dosage_form'),
            'group'    => $col('groupname'),
            'used'     => $col('commonly_used'),
            'active'   => $col('active'),
        ];

        $inserted   = 0;
        $duplicates = 0;
        $skipped    = 0;
        $rowNo      = 1;

        DB::transaction(function () use ($handle, $idx, $nameIdx, &$inserted, &$duplicates, &$skipped, &$rowNo) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNo++;
                $name = trim((string) ($row[$nameIdx] ?? ''));
                if ($name === '') {
                    $skipped++;
                    continue;
                }

                $normalized = $this->normalizeName($name);

                if (CommonMedicine::where('name_normalized', $normalized)->exists()) {
                    $duplicates++;
                    continue;
                }

                $get = function (?int $i) use ($row) {
                    return $i !== false && isset($row[$i]) ? trim((string) $row[$i]) : '';
                };

                // Medicine Type (na thakle create)
                $typeId = null;
                $typeName = $get($idx['type']);
                if ($typeName !== '') {
                    $type = MedicineType::firstOrCreate(
                        ['type_name' => $typeName],
                        ['type_name' => $typeName, 'active' => 1]
                    );
                    $typeId = $type->id;
                }

                // Sub Type (parent type-er aotay; na pele create)
                $subId = null;
                $subName = $get($idx['sub']);
                if ($subName !== '' && $typeId) {
                    $sub = MedicineSubtype::firstOrCreate(
                        ['medicine_type_id' => $typeId, 'subtype_name' => $subName],
                        ['medicine_type_id' => $typeId, 'subtype_name' => $subName, 'active' => 1]
                    );
                    $subId = $sub->id;
                }

                // Company (na thakle create)
                $companyId = null;
                $companyName = $get($idx['company']);
                if ($companyName !== '') {
                    $company = Company::firstOrCreate(
                        ['name' => $companyName],
                        ['name' => $companyName, 'active' => 1]
                    );
                    $companyId = $company->id;
                }

                // Dosage Form (na thakle create)
                $formId = null;
                $formName = $get($idx['form']);
                if ($formName !== '') {
                    $form = DosageForm::firstOrCreate(
                        ['dosage_name' => $formName],
                        ['dosage_name' => $formName, 'active' => 1]
                    );
                    $formId = $form->id;
                }

                $payload = [
                    'name'                 => $name,
                    'name_normalized'      => $normalized,
                    'strength'             => $get($idx['strength']) ?: '',
                    'medicine_type_id'     => $typeId,
                    'medicine_sub_type_id' => $subId,
                    'company_id'           => $companyId,
                    'company'              => $companyName ?: null,
                    'dosage_form_id'       => $formId,
                    'GroupName'            => $get($idx['group']) ?: 'N/A',
                    'active'               => in_array($get($idx['active']), ['0', 'false'], true) ? 0 : 1,
                    'commonly_used'        => in_array($get($idx['used']), ['1', 'true', 'yes'], true) ? 1 : 0,
                ];

                if ($this->hasGenericName()) {
                    $payload['generic_name'] = $get($idx['generic']) ?: null;
                }

                CommonMedicine::create($payload);
                $inserted++;
            }
            fclose($handle);
        });

        return compact('inserted', 'duplicates', 'skipped');
    }
}
