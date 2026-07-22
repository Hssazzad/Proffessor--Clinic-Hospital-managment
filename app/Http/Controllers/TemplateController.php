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
        $templates    = Template::orderBy('title')->get();
        $medicine_list = $this->getDistinctList('prescriptions_medicine');
        return view('templates.medicine.create', compact('templates', 'medicine_list'));
    }

    public function ajaxAddMedicine(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'name'       => 'required|string',
                'order_type' => 'required|in:admit,preorder,postorder',
            ]);

            Log::info('Medicine Add Request:', $request->all());

            $row = TemplateMedicine::create([
                'templeteid' => $request->templateid,
                'name'       => $request->name,
                'strength'   => $request->strength,
                'dose'       => $request->dose ?? $request->dosage,
                'morning'    => $request->morning,
                'noon'       => $request->noon,
                'night'      => $request->night,
                'route'      => $request->route,
                'frequency'  => $request->frequency,
                'duration'   => $request->duration,
                'timing'     => $request->filled('timing') ? $request->timing : $request->meal_timing,
                'meal_timing'=> $request->meal_timing,
                'instruction'=> $request->instruction,
                'company'    => $request->company,
                'order_type' => $request->order_type,
                'note'       => $request->note,
                'group'      => $request->group,
                'active'     => 1,
            ]);

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
                'name'       => 'required|string|max:100',
                'order_type' => 'required|in:admit,preorder,postorder',
            ]);

            $medicine = TemplateMedicine::findOrFail($id);

            $medicine->update([
                'name'       => trim($request->name),
                'strength'   => $request->strength,
                'dose'       => $request->dose ?? $request->dosage,
                'morning'    => $request->morning,
                'noon'       => $request->noon,
                'night'      => $request->night,
                'route'      => $request->route,
                'frequency'  => $request->frequency,
                'duration'   => $request->duration,
                'timing'     => $request->filled('timing') ? $request->timing : $request->meal_timing,
                'meal_timing'=> $request->meal_timing,
                'instruction'=> $request->instruction,
                'company'    => $request->company,
                'order_type' => $request->order_type,
                'note'       => $request->note,
                'group'      => $request->group,
            ]);

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
            $rows = TemplateMedicine::where('templeteid', $request->templateid)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'ok'   => true,
                'rows' => $rows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTemplateMedicines($id)
    {
        try {
            $medicines = TemplateMedicine::where('templeteid', $id)
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success'   => true,
                'medicines' => $medicines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
     * ১০. ফ্রেশ প্রেসক্রিপশন
     */
    public function addFreshPrescription()
    {
        $templates          = Template::orderBy('title')->get();
        $medicine_list      = $this->getDistinctList('prescriptions_medicine');
        $investigation_list = $this->getDistinctList('prescriptions_investigations');
        $diagnosis_list     = $this->getDistinctList('prescriptions_diagnosis');

        return view('templates.fresh_prescription.create', compact(
            'templates', 'medicine_list', 'investigation_list', 'diagnosis_list'
        ));
    }

    public function ajaxStoreFreshPrescription(Request $request)
    {
        try {
            $request->validate([
                'templateid' => 'required',
                'order_type' => 'required|in:admit,preorder,postorder,fresh prescription',
                'name'       => 'required|string',
            ]);

            Log::info('Fresh Prescription Request:', [
                'templateid' => $request->templateid,
                'order_type' => $request->order_type,
                'name'       => $request->name,
                'dosage'     => $request->dosage,
                'duration'   => $request->duration
            ]);

            $data = [
                'templeteid' => $request->templateid,
                'name'       => $request->name,
                'dose'       => $request->dosage,
                'morning'    => $request->morning,
                'noon'       => $request->noon,
                'night'     => $request->night,
                'meal_timing'=> $request->meal_timing,
                'duration'   => $request->duration,
                'route'      => $request->route,
                'instruction'=> $request->instruction,
                'order_type' => $request->order_type,
                'active'     => 1,
            ];

            $row = TemplateMedicine::create($data);

            Log::info('Prescription saved successfully:', ['id' => $row->id, 'order_type' => $row->order_type]);

            return response()->json([
                'ok'      => true,
                'message' => 'Item Saved Successfully',
                'row'     => $row
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Prescription save error: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ১১. সেভ করা প্রেসক্রিপশনের লিস্ট দেখানো
     */
    public function listSavedPrescriptions()
    {
        $templates = Template::with(['medicines' => function ($query) {
            $query->whereNotNull('order_type')
                  ->orderBy('id', 'desc');
        }])->orderBy('created_at', 'desc')->paginate(10);

        return view('templates.fresh_prescription.list', compact('templates'));
    }

    /**
     * ১২. নির্দিষ্ট প্রেসক্রিপশন দেখানো এবং প্রিন্টের জন্য
     */
    public function showPrescription($templateid)
    {
        try {
            $template = Template::findOrFail($templateid);

            $medicines = [
                'admit' => TemplateMedicine::where('templeteid', $templateid)
                    ->where('order_type', 'admit')
                    ->orderBy('id')
                    ->get(),
                'preorder' => TemplateMedicine::where('templeteid', $templateid)
                    ->where('order_type', 'preorder')
                    ->orderBy('id')
                    ->get(),
                'postorder' => TemplateMedicine::where('templeteid', $templateid)
                    ->where('order_type', 'postorder')
                    ->orderBy('id')
                    ->get(),
            ];

            return view('templates.fresh_prescription.show', compact('template', 'medicines'));

        } catch (\Exception $e) {
            Log::error('Error in showPrescription: ' . $e->getMessage());
            return back()->with('error', 'Prescription not found: ' . $e->getMessage());
        }
    }

    /**
     * ১৩. প্রেসক্রিপশন ডিলিট করা
     */
    public function deletePrescription($templateid)
    {
        try {
            $deleted = TemplateMedicine::where('templeteid', $templateid)
                ->whereNotNull('order_type')
                ->delete();

            return response()->json([
                'ok'      => true,
                'message' => $deleted . ' prescription(s) deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage()
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
