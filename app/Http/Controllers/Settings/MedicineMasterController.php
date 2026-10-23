<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuickAddMedicineRequest;
use App\Http\Requests\StoreMedicineRequest;
use App\Models\CommonMedicine;
use App\Models\Company;
use App\Models\DosageForm;
use App\Models\MedicineSubtype;
use App\Models\MedicineType;
use App\Services\MedicineMasterService;
use Illuminate\Http\Request;

/**
 * Medicine Master (Settings → Add Medicine).
 * Extracted from MasterDataController; route names/URLs unchanged.
 */
class MedicineMasterController extends Controller
{
    public function __construct(private MedicineMasterService $medicines)
    {
    }

    public function medicinesIndex(Request $request)
    {
        $data = $this->medicines->paginatedList($request);

        $medicineTypes = MedicineType::where('active', 1)->orderBy('type_name')->get();
        $companies     = Company::where('active', 1)->orderBy('name')->get();
        $dosageForms   = DosageForm::where('active', 1)->orderBy('dosage_name')->get();

        return view(
            'settings.medicines.index',
            $data + compact('medicineTypes', 'companies', 'dosageForms')
        );
    }

    /**
     * AJAX: re-render the medicine list table (+ pagination) after saves,
     * so Quick Add can refresh without a full page reload.
     */
    public function medicinesListHtml(Request $request)
    {
        $data = $this->medicines->paginatedList($request);

        return response()->json([
            'ok'   => true,
            'html' => view('settings.medicines.partials.list-table', $data)->render(),
            'total' => $data['medicines']->total(),
        ]);
    }

    public function medicinesStore(StoreMedicineRequest $request)
    {
        $validated = $request->validated();

        $duplicate = $this->medicines->findExactDuplicate($validated['medicine_name']);
        if ($duplicate) {
            return back()->withInput()->withErrors([
                'medicine_name' => "\"{$duplicate->name}\" already exists — duplicate medicine is not allowed.",
            ]);
        }

        $data = $this->medicines->buildPayload($validated);
        $data['GroupName'] = 'N/A';

        CommonMedicine::create($data);

        $page = $this->medicines->pageByName($data['name']);

        return redirect()
            ->route('settings.medicines.index', ['page' => $page > 1 ? $page : null])
            ->with('success', 'Medicine added successfully.');
    }

    public function medicinesUpdate(StoreMedicineRequest $request, CommonMedicine $medicine)
    {
        $validated = $request->validated();

        $duplicate = $this->medicines->findExactDuplicate($validated['medicine_name'], $medicine->id);
        if ($duplicate) {
            return back()->withInput()->withErrors([
                'medicine_name' => "\"{$duplicate->name}\" already exists — duplicate medicine is not allowed.",
            ]);
        }

        $medicine->update($this->medicines->buildPayload($validated));

        $page = $this->medicines->pageByName(trim($validated['medicine_name']));

        return redirect()
            ->route('settings.medicines.index', ['page' => $page > 1 ? $page : null])
            ->with('success', 'Medicine updated successfully.');
    }

    public function medicinesDestroy(CommonMedicine $medicine)
    {
        $medicine->delete();
        return back()->with('success', 'Medicine deleted successfully.');
    }

    /**
     * Quick Add: Type + Company + Name + Dosage Form required. Strength optional.
     * Same table: common_medicine. Returns JSON for AJAX (no page reload).
     */
    public function medicinesQuickAdd(QuickAddMedicineRequest $request)
    {
        $data = $request->validated();

        $name = trim($data['medicine_name']);

        $existing = $this->medicines->findExactDuplicate($name);
        if ($existing) {
            return response()->json([
                'ok'        => true,
                'duplicate' => true,
                'message'   => 'Already exists.',
                'page'      => $this->medicines->pageByName($name),
                'medicine'  => ['id' => $existing->id, 'name' => $existing->name],
            ]);
        }

        $payload = $this->medicines->buildPayload($data + ['status' => 1]);
        $payload['GroupName'] = 'N/A';

        $medicine = CommonMedicine::create($payload);

        return response()->json([
            'ok'        => true,
            'duplicate' => false,
            'message'   => 'Medicine added.',
            'page'      => $this->medicines->pageByName($medicine->name),
            'medicine'  => ['id' => $medicine->id, 'name' => $medicine->name],
        ]);
    }

    /* ////////////////////////*/
    /* CSV Export / Import      */
    /* ////////////////////////*/

    public function medicinesCsvDownload()
    {
        return $this->medicines->csvDownload();
    }

    public function medicinesCsvImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $result = $this->medicines->csvImport($request);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['csv_file' => $e->getMessage()]);
        }

        return back()->with('success', sprintf(
            'CSV import done: %d added, %d duplicate skipped, %d rows skipped (empty/invalid).',
            $result['inserted'],
            $result['duplicates'],
            $result['skipped']
        ));
    }

    /* ////////////////////////*/
    /* AJAX master quick-adds   */
    /* ////////////////////////*/

    public function companyAjaxStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:companies,name',
        ]);

        $company = Company::create([
            'name'   => $request->name,
            'active' => 1,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $company->id,
            'name'    => $company->name,
            'message' => 'Company added successfully.',
        ]);
    }

    public function medicineTypeAjaxStore(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:150|unique:medicine_types,type_name',
        ]);

        $type = MedicineType::create([
            'type_name' => $request->type_name,
            'active'    => 1,
        ]);

        return response()->json([
            'success'   => true,
            'id'        => $type->id,
            'type_name' => $type->type_name,
            'message'   => 'Medicine Type added successfully.',
        ]);
    }

    public function dosageFormAjaxStore(Request $request)
    {
        $request->validate([
            'dosage_name' => 'required|string|max:150|unique:dosage_forms,dosage_name',
        ]);

        $form = DosageForm::create([
            'dosage_name' => $request->dosage_name,
            'active'      => 1,
        ]);

        return response()->json([
            'success'     => true,
            'id'          => $form->id,
            'dosage_name' => $form->dosage_name,
            'message'     => 'Dosage Form added successfully.',
        ]);
    }

    public function subMedicineTypeAjaxList(Request $request)
    {
        $typeId = $request->get('medicine_type_id');

        $query = MedicineSubtype::where('active', 1)->orderBy('subtype_name');
        if ($typeId) {
            $query->where('medicine_type_id', $typeId);
        }

        return response()->json([
            'success'   => true,
            'sub_types' => $query->get(['id', 'subtype_name', 'medicine_type_id']),
        ]);
    }

    public function subMedicineTypeAjaxStore(Request $request)
    {
        $request->validate([
            'subtype_name'     => 'required|string|max:150',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'active'           => 'nullable|in:0,1',
        ]);

        $exists = MedicineSubtype::where('medicine_type_id', $request->medicine_type_id)
            ->where('subtype_name', $request->subtype_name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This Sub Medicine Type already exists under the selected Medicine Type.',
            ], 422);
        }

        $subtype = MedicineSubtype::create([
            'medicine_type_id' => $request->medicine_type_id,
            'subtype_name'     => $request->subtype_name,
            'active'           => $request->input('active', 1),
        ]);

        return response()->json([
            'success'      => true,
            'id'           => $subtype->id,
            'subtype_name' => $subtype->subtype_name,
            'message'      => 'Sub Medicine Type added successfully.',
        ]);
    }

    /**
     * Real-time duplicate check while typing the Medicine Name.
     */
    public function medicinesCheckDuplicate(Request $request)
    {
        $name = trim((string) $request->get('name'));
        if ($name === '' || strlen($name) < 2) {
            return response()->json(['matches' => []]);
        }

        return response()->json([
            'matches' => $this->medicines->partialMatches($name),
        ]);
    }
}
