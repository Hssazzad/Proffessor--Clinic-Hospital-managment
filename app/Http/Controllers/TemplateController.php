<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateDiagnosis;
use App\Models\TemplateInvestigation;
use App\Models\TemplateMedicine;
use App\Models\TemplateAdvice;
use App\Models\TemplateDischarge;
use App\Models\TemplateComplain;
use App\Models\Admission;
use App\Models\Doctor;
use App\Models\Company;
use App\Models\CommonMedicine;
use App\Models\DosageForm;
use App\Models\MedicineType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class TemplateController extends Controller
{
    /**
     * ১. টেমপ্লেট মেইন সেকশন
     */
    public function index()
    {
        $templates = Template::orderByDesc('id')->paginate(20);
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $last = Template::orderByDesc('id')->first();
        if ($last && $last->templateid) {
            $num = (int) str_replace('TPL-', '', $last->templateid);
            $nextTemplateId = 'TPL-' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextTemplateId = 'TPL-000001';
        }
        return view('templates.create', compact('nextTemplateId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'templateid'  => ['required', 'string'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'in:0,1'],
        ]);

        $data['status'] = $data['status'] ?? 1;
        Template::create($data);

        return redirect()->route('templates.index')->with('success', 'Template created successfully.');
    }

    /**
     * ২. অ্যাডমিশন রেকর্ড স্টোর (Ajax)
     */
    public function storeAdmission(Request $request)
    {
        try {
            $request->validate([
                'templateid'     => 'required',
                'patient_name'   => 'required|string|max:255',
                'admission_date' => 'required|date',
            ]);

            $admission = Admission::create($request->all());

            return response()->json([
                'ok'      => true,
                'message' => 'Admission Record Saved!',
                'id'      => $admission->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => 'Validation Failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Admission Save Error: " . $e->getMessage());
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ৩. পিডিএফ জেনারেশন
     */
    public function downloadPDF($id)
    {
        try {
            $admission = Admission::findOrFail($id);
            $pdf = Pdf::loadView('templates.pdf', compact('admission'))->setPaper('a4', 'portrait');
            return $pdf->stream('Admission_Record_' . $admission->reg_no . '.pdf');
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * ৪. চিফ কমপ্লেইন সেকশন
     */
    public function ajaxAddComplain(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'name'       => 'required',
            ]);

            $row = TemplateComplain::create([
                'templateid' => $request->templateid,
                'name'       => $request->name,
                'complain'   => $request->name,
                'note'       => $request->note,
                'active'     => 1,
            ]);

            return response()->json(['ok' => true, 'message' => 'Complain Added', 'row' => $row]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajaxListComplain(Request $request)
    {
        $rows = TemplateComplain::where('templateid', $request->templateid)->orderBy('id', 'desc')->get();
        return response()->json(['ok' => true, 'rows' => $rows]);
    }

    public function ajaxDeleteComplain($id)
    {
        TemplateComplain::where('id', $id)->delete();
        return response()->json(['ok' => true, 'message' => 'Deleted']);
    }

    /**
     * ৫. ডায়াগনোসিস সেকশন
     */
    public function addDiagnosis()
    {
        $templates       = Template::orderBy('title')->get();
        $diagnosis_list  = $this->getDistinctList('prescriptions_diagnosis');
        return view('templates.diagnosis.create', compact('templates', 'diagnosis_list'));
    }

    public function ajaxAddDiagnosis(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'name'       => 'required|string|max:100',
            ]);

            $name = trim($request->name);
            $norm = Str::of($name)->lower()->squish()->toString();

            $row = TemplateDiagnosis::create([
                'templateid'      => $request->templateid,
                'name'            => $name,
                'note'            => $request->note,
                'active'          => 1,
                'name_normalized' => $norm,
            ]);

            return response()->json(['ok' => true, 'message' => 'Added', 'row' => $row]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajaxListDiagnosis(Request $request)
    {
        $rows = TemplateDiagnosis::where('templateid', $request->templateid)->orderBy('id', 'desc')->get();
        return response()->json(['ok' => true, 'rows' => $rows]);
    }

    public function ajaxDeleteDiagnosis($id)
    {
        TemplateDiagnosis::where('id', $id)->delete();
        return response()->json(['ok' => true, 'message' => 'Deleted']);
    }

    /**
     * ৬. ইনভেস্টিগেশন সেকশন
     */
    public function addInvestigation()
    {
        $templates          = Template::orderBy('title')->get();
        $investigation_list = $this->getDistinctList('prescriptions_investigations');
        return view('templates.investigation.create', compact('templates', 'investigation_list'));
    }

    public function ajaxAddInvestigation(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'name'       => 'required|string|max:100',
            ]);

            $name = trim($request->name);
            $norm = Str::of($name)->lower()->squish()->toString();

            $row = TemplateInvestigation::create([
                'templateid'      => $request->templateid,
                'name'            => $name,
                'note'            => $request->note,
                'active'          => 1,
                'name_normalized' => $norm,
            ]);

            return response()->json(['ok' => true, 'message' => 'Added', 'row' => $row]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajaxListInvestigation(Request $request)
    {
        $rows = TemplateInvestigation::where('templateid', $request->templateid)->orderBy('id', 'desc')->get();
        return response()->json(['ok' => true, 'rows' => $rows]);
    }

    public function ajaxDeleteInvestigation($id)
    {
        TemplateInvestigation::where('id', $id)->delete();
        return response()->json(['ok' => true, 'message' => 'Deleted']);
    }

    /**
     * ৭. মেডিসিন সেকশন
     */
    public function addMedicine()
    {
        $medicineTypes = MedicineType::where('active', 1)->orderBy('type_name')->get();
        $companies     = Company::where('active', 1)->orderBy('name')->get();
        $dosageForms   = DosageForm::where('active', 1)->orderBy('dosage_name')->get();
        $templates     = Template::orderBy('title')->get();

        $hasGenericName = Schema::hasColumn('common_medicine', 'generic_name');

        $stages = [
            'preorder'  => 'Pre-Operation',
            'postorder' => 'Post-Operation',
            'fresh'     => 'Fresh Prescription',
        ];

        return view('templates.medicine.create', compact(
            'medicineTypes', 'companies', 'dosageForms', 'templates',
            'hasGenericName', 'stages'
        ));
    }

    /**
     * Company-wise medicine selection (Select2 AJAX).
     * Reuses the prescription page's common_medicine filter query pattern.
     */
    public function ajaxMedicineCompanies()
    {
        $companies = Company::where('active', 1)->orderBy('name')->get(['id', 'name']);
        return response()->json(['companies' => $companies]);
    }

    public function ajaxMedicineGroups(Request $request)
    {
        $query = DB::table('common_medicine')->where('active', 1);

        if ($request->filled('company_id') || $request->filled('company')) {
            $query->where(function ($q) use ($request) {
                if ($request->filled('company_id')) $q->where('company_id', $request->company_id);
                if ($request->filled('company'))     $q->orWhere('company', $request->company);
            });
        }

        $groups = $query->distinct()->pluck('GroupName')->filter()->sort()->values();

        return response()->json(['groups' => $groups]);
    }

    public function ajaxMedicineOptions(Request $request)
    {
        if (!Schema::hasTable('common_medicine')) {
            return response()->json(['medicines' => []]);
        }

        $query = DB::table('common_medicine')->where('active', 1);

        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', $request->medicine_type_id);
        }
        if ($request->filled('medicine_subtype_id')) {
            $query->where('medicine_sub_type_id', $request->medicine_subtype_id);
        }
        if ($request->filled('company_id') || $request->filled('company')) {
            $query->where(function ($q) use ($request) {
                if ($request->filled('company_id')) $q->where('company_id', $request->company_id);
                if ($request->filled('company'))     $q->orWhere('company', $request->company);
            });
        }
        if ($request->filled('group')) {
            $query->where('GroupName', $request->group);
        }
        if ($request->filled('dosage_form_id')) {
            $query->where('dosage_form_id', $request->dosage_form_id);
        }
        if ($request->filled('q')) {
            $term = trim($request->q);
            if ($term !== '') {
                $query->where(function ($s) use ($term) {
                    $s->where('name', 'like', '%' . $term . '%')
                      ->orWhere('strength', 'like', '%' . $term . '%');
                });
            }
        }

        $columns = ['id', 'name', 'strength', 'GroupName', 'company', 'company_id',
                    'medicine_type_id', 'medicine_sub_type_id', 'dosage_form_id', 'commonly_used'];
        if (Schema::hasColumn('common_medicine', 'generic_name')) {
            $columns[] = 'generic_name';
        }

        $medicines = $query->orderBy('name')->limit(500)->get($columns);

        return response()->json(['medicines' => $medicines]);
    }

    public function ajaxAddMedicine(Request $request)
    {
        try {
            $request->validate([
                'templateid'  => 'required',
                'order_type'  => 'required|in:preorder,postorder,fresh',
                'company_id'  => 'required|exists:companies,id',
                'medicine_id' => 'required|exists:common_medicine,id',
                'dose'        => 'required|string',
                'status'      => 'nullable|in:0,1',
            ], [], [
                'templateid'  => 'template',
                'order_type'  => 'stage',
                'company_id'  => 'company',
                'medicine_id' => 'medicine',
                'dose'        => 'dose',
            ]);

            Log::info('Medicine Add Request:', $request->all());

            // Duplicate check: same template + stage + medicine
            $exists = TemplateMedicine::where('templeteid', $request->templateid)
                ->where('order_type', $request->order_type)
                ->where('active', 1)
                ->where('medicine_id', $request->medicine_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'This medicine already exists in this stage.',
                ], 422);
            }

            $medicine = DB::table('common_medicine')->where('id', $request->medicine_id)->first();
            $company  = Company::find($request->company_id);
            $active   = $request->has('status') ? (int) $request->status : 1;

            $row = DB::transaction(function () use ($request, $medicine, $company, $active) {
                return TemplateMedicine::create([
                    'templeteid'  => $request->templateid,
                    'name'        => $medicine->name,
                    'medicine_id' => (int) $request->medicine_id,
                    'generic_name'=> $medicine->generic_name ?? null,
                    'strength'    => $request->filled('strength') ? $request->strength : ($medicine->strength ?? null),
                    'dose'        => $request->dose ?? $request->dosage,
                    'morning'     => $request->morning,
                    'noon'        => $request->noon,
                    'night'       => $request->night,
                    'route'       => $request->route,
                    'frequency'   => $request->frequency,
                    'duration'    => $request->duration,
                    'timing'      => $request->filled('timing') ? $request->timing : $request->meal_timing,
                    'meal_timing' => $request->meal_timing,
                    'instruction' => $request->instruction,
                    'company'     => $company?->name,
                    'company_id'  => (int) $request->company_id,
                    'group'       => $medicine->GroupName ?? $request->group,
                    'order_type'  => $request->order_type,
                    'note'        => $request->note,
                    'active'      => $active,
                ]);
            });

            return response()->json([
                'ok'      => true,
                'message' => 'Medicine Added Successfully',
                'row'     => $row
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Medicine Add Error: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ৭ক. Medicine একটি নির্দিষ্ট row এর data ফেরত দেওয়া (Edit Modal এর জন্য)
     */
    public function ajaxGetMedicine($id)
    {
        try {
            $row = TemplateMedicine::findOrFail($id);
            return response()->json([
                'ok'  => true,
                'row' => $row
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ৭খ. Medicine Update (Ajax)
     * Route: POST /templates/medicine/update/{id}
     *
     * সব editable columns update হবে:
     * name, strength, dose, route, frequency, duration, timing, order_type, note, group
     */
    public function ajaxUpdateMedicine(Request $request, $id)
    {
        try {
            $request->validate([
                'order_type'  => 'required|in:preorder,postorder,fresh',
                'company_id'  => 'required|exists:companies,id',
                'medicine_id' => 'required|exists:common_medicine,id',
                'status'      => 'nullable|in:0,1',
            ], [], [
                'order_type'  => 'stage',
                'company_id'  => 'company',
                'medicine_id' => 'medicine',
            ]);

            $medicine = TemplateMedicine::findOrFail($id);

            // Duplicate check: same template + stage + medicine (excluding self)
            $exists = TemplateMedicine::where('templeteid', $medicine->templeteid)
                ->where('order_type', $request->order_type)
                ->where('active', 1)
                ->where('id', '!=', $id)
                ->where('medicine_id', $request->medicine_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'This medicine already exists in this stage.',
                ], 422);
            }

            $cm      = DB::table('common_medicine')->where('id', $request->medicine_id)->first();
            $company = Company::find($request->company_id);

            DB::transaction(function () use ($request, $medicine, $cm, $company) {
                $medicine->update([
                    'name'        => $cm->name,
                    'medicine_id' => (int) $request->medicine_id,
                    'generic_name'=> $cm->generic_name ?? null,
                    'strength'    => $request->filled('strength') ? $request->strength : ($cm->strength ?? $medicine->strength),
                    'dose'        => $request->dose ?? $request->dosage,
                    'morning'     => $request->morning,
                    'noon'        => $request->noon,
                    'night'       => $request->night,
                    'route'       => $request->route,
                    'frequency'   => $request->frequency,
                    'duration'    => $request->duration,
                    'timing'      => $request->filled('timing') ? $request->timing : $request->meal_timing,
                    'meal_timing' => $request->meal_timing,
                    'instruction' => $request->instruction,
                    'company'     => $company?->name ?? $medicine->company,
                    'company_id'  => (int) $request->company_id,
                    'group'       => $cm->GroupName ?? $medicine->group,
                    'order_type'  => $request->order_type,
                    'note'        => $request->note,
                    'active'      => $request->has('status') ? (int) $request->status : $medicine->active,
                ]);
            });

            Log::info('Medicine Updated:', ['id' => $id, 'data' => $request->all()]);

            return response()->json([
                'ok'      => true,
                'message' => 'Medicine Updated Successfully',
                'row'     => $medicine->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Medicine Update Error: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ajaxListMedicine(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'order_type'  => 'required|in:preorder,postorder,fresh',
            ], [], [
                'templateid' => 'template',
                'order_type' => 'stage',
            ]);

            $select = [
                'tm.id', 'tm.templeteid', 'tm.name', 'tm.strength', 'tm.dose',
                'tm.morning', 'tm.noon', 'tm.night', 'tm.route', 'tm.frequency',
                'tm.duration', 'tm.timing', 'tm.meal_timing', 'tm.instruction',
                'tm.company', 'tm.company_id', 'tm.medicine_id', 'tm.group',
                'tm.order_type', 'tm.note', 'tm.active',
                'mt.type_name', 'mt.id as type_id',
                'ms.subtype_name', 'ms.id as subtype_id',
                'df.dosage_name', 'df.id as dosage_id',
                'cm.commonly_used',
            ];

            if (Schema::hasColumn('common_medicine', 'generic_name')) {
                $select[] = DB::raw('COALESCE(tm.generic_name, cm.generic_name) as generic_name');
            } else {
                $select[] = 'tm.generic_name as generic_name';
            }

            $query = DB::table('template_medicine as tm')
                ->leftJoin('common_medicine as cm', 'cm.id', '=', 'tm.medicine_id')
                ->leftJoin('medicine_types as mt', 'mt.id', '=', 'cm.medicine_type_id')
                ->leftJoin('medicine_subtypes as ms', 'ms.id', '=', 'cm.medicine_sub_type_id')
                ->leftJoin('dosage_forms as df', 'df.id', '=', 'cm.dosage_form_id')
                ->select($select)
                ->where('tm.templeteid', $request->templateid)
                ->where('tm.order_type', $request->order_type)
                ->orderBy('tm.id', 'desc');

            $commonlyUsed = $request->get('commonly_used_filter');
            if ($commonlyUsed === 'yes') {
                $query->where('cm.commonly_used', 1);
            } elseif ($commonlyUsed === 'no') {
                $query->where(function ($q) {
                    $q->where('cm.commonly_used', 0)->orWhereNull('cm.commonly_used');
                });
            }

            $rows = $query->get();

            return response()->json([
                'ok'   => true,
                'rows' => $rows
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'The stage (order_type) is required and must be one of preorder, postorder or fresh.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ajaxDeleteMedicine($id)
    {
        try {
            TemplateMedicine::where('id', $id)->delete();
            return response()->json([
                'ok'      => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ৮. অ্যাডভাইস সেকশন
     */
    public function addAdvice()
    {
        $templates = Template::orderBy('title')->get();
        return view('templates.advice.create', compact('templates'));
    }

    public function ajaxAddAdvice(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'advice'     => 'required|string',
            ]);

            $row = TemplateAdvice::create([
                'templateid' => $request->templateid,
                'advice'     => $request->advice,
                'active'     => 1,
            ]);

            return response()->json(['ok' => true, 'message' => 'Advice Added', 'row' => $row]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajaxListAdvice(Request $request)
    {
        $rows = TemplateAdvice::where('templateid', $request->templateid)->orderBy('id', 'desc')->get();
        return response()->json(['ok' => true, 'rows' => $rows]);
    }

    public function ajaxDeleteAdvice($id)
    {
        TemplateAdvice::where('id', $id)->delete();
        return response()->json(['ok' => true, 'message' => 'Deleted']);
    }

    /**
     * ৯. ডিসচার্জ সেকশন
     */
    public function addDischarge()
    {
        try {
            $templates = Template::orderBy('title')->get();
            Log::info('addDischarge: loaded ' . $templates->count() . ' templates');
            return view('templates.discharge.create', compact('templates'));
        } catch (\Exception $e) {
            Log::error('addDischarge error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function ajaxAddDischarge(Request $request)
    {
        try {
            // Step A: Request data log করো
            \Log::info('=== ajaxAddDischarge START ===');
            \Log::info('Request data:', $request->all());
            \Log::info('Request method: ' . $request->method());
            \Log::info('CSRF token present: ' . ($request->has('_token') ? 'YES' : 'NO'));

            // Step B: Validation
            $validated = $request->validate([
                'templateid' => 'required',
                'treatment'  => 'required|string',
                'condition'  => 'required|string',
            ]);

            \Log::info('Validation passed');

            // Step C: Table exists check
            if (!\Schema::hasTable('template_discharge')) {
                \Log::error('Table template_discharge does not exist!');
                return response()->json([
                    'ok'      => false,
                    'message' => 'Database table template_discharge not found!'
                ], 500);
            }

            \Log::info('Table exists: template_discharge');

            // Step D: Column check
            $columns = \Schema::getColumnListing('template_discharge');
            \Log::info('Table columns:', $columns);

            // Step E: Data prepare
            $data = [
                'templateid' => $request->templateid,
                'updated_at' => now(),
            ];

            if (in_array('treatment', $columns)) {
                $data['treatment'] = $request->treatment;
            }
            if (in_array('condition', $columns)) {
                $data['condition'] = $request->condition;
            }
            if (in_array('follow_up', $columns)) {
                $data['follow_up'] = $request->follow_up ?? null;
            }

            \Log::info('Data to save:', $data);

            // Step F: Insert or Update
            $exists = \DB::table('template_discharge')
                ->where('templateid', $request->templateid)
                ->first();

            if ($exists) {
                \DB::table('template_discharge')
                    ->where('templateid', $request->templateid)
                    ->update($data);
                \Log::info('Updated existing record');
            } else {
                if (in_array('created_at', $columns)) {
                    $data['created_at'] = now();
                }
                \DB::table('template_discharge')->insert($data);
                \Log::info('Inserted new record');
            }

            \Log::info('=== ajaxAddDischarge SUCCESS ===');

            return response()->json([
                'ok'      => true,
                'message' => 'Discharge summary saved successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error: ', $e->errors());
            return response()->json([
                'ok'      => false,
                'message' => 'Validation Error: ' . implode(', ', array_merge(...array_values($e->errors()))),
                'errors'  => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database Error: ' . $e->getMessage());
            \Log::error('SQL: ' . $e->getSql());
            return response()->json([
                'ok'      => false,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            \Log::error('General Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
            ], 500);
        }
    }

    /**
     * ১৪. ডিসপ্লে টেমপ্লেট
     */
    public function displayTemplate()
    {
        $templates = Template::with(['medicines' => function ($query) {
            $query->orderBy('order_type')->orderBy('id');
        }])->orderBy('id', 'desc')->get();

        return view('templates.display', compact('templates'));
    }

    /**
     * ১৫. ডিসপ্লে টেমপ্লেট - সিঙ্গেল
     */
    public function displaySingleTemplate($templateid)
    {
        $template = Template::with(['medicines' => function ($query) {
            $query->orderBy('order_type')->orderBy('id');
        }])->where('templateid', $templateid)->firstOrFail();

        return view('templates.display_single', compact('template'));
    }

    /**
     * ১৬. Ajax Template Details
     */
    public function ajaxTemplateDetails(Request $request)
    {
        try {
            $template = Template::where('templateid', $request->templateid)->first();

            if (!$template) {
                return response()->json(['ok' => false, 'message' => 'Template not found'], 404);
            }

            return response()->json([
                'ok'       => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ১৭. Debug Order Type
     */
    public function debugOrderType()
    {
        try {
            $data = [
                'total_medicines'  => TemplateMedicine::count(),
                'order_type_stats' => TemplateMedicine::select('order_type', DB::raw('count(*) as total'))
                    ->groupBy('order_type')
                    ->get(),
                'recent_medicines' => TemplateMedicine::whereNotNull('order_type')
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get(),
                'model_fillable'   => (new TemplateMedicine())->getFillable(),
                'table_columns'    => Schema::getColumnListing((new TemplateMedicine())->getTable()),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * ১৮. Ajax Get Discharge
     */
    public function ajaxGetDischarge(Request $request)
    {
        $discharge = TemplateDischarge::where('templateid', $request->templateid)->first();
        return response()->json(['discharge' => $discharge]);
    }

    /**
     * ১৯. Ajax List OE
     */
    public function ajaxListOE(Request $request)
    {
        return response()->json(['oe' => null]);
    }

    // ✅ NOTE: prescriptionView() method সরানো হয়েছে (removed as requested)

    /**
     * হেল্পার মেথড
     */
    private function getDistinctList($tableName)
    {
        try {
            if (Schema::hasTable($tableName)) {
                return DB::table($tableName)
                    ->select('name')
                    ->distinct()
                    ->whereNotNull('name')
                    ->orderBy('name')
                    ->get();
            }
        } catch (\Exception $e) {
            Log::warning("Table error: " . $e->getMessage());
        }
        return collect();
    }
}