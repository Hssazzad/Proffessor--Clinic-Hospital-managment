<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\ProcedureType;
use App\Models\PatientAdmission;
use App\Models\OperationName;
use App\Models\Doctor;
use App\Models\Anesthesiologist;
use App\Models\AssistantSurgeon;
use App\Models\DischargeDiagnosis;
use App\Models\DischargeInstruction;
use App\Models\OperationMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\MedicineDraftService;
use App\Services\PrescriptionPrintService;
use RuntimeException;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientAdmitController extends Controller
{

	private function getActiveAdmission($patientcode)
	{
		return DB::table('patient_admission')
			->where('patientcode', $patientcode)
			->where('Active', 1)
			->orderByDesc('id')
			->first();
	}

	/**
	 * Resolve the active admission context (admissionid + templeteid) used by
	 * every medicine order type.
	 *
	 * @return array{admission: object|null, admissionid: string|null, templeteid: string}
	 */
	private function admissionContext(string $patientcode): array
	{
		$admission = $this->getActiveAdmission($patientcode);

		if (!$admission || empty($admission->admissionid)) {
			return ['admission' => null, 'admissionid' => null, 'templeteid' => ''];
		}

		return [
			'admission'   => $admission,
			'admissionid' => $admission->admissionid,
			'templeteid'  => $admission->templeteid ?? '',
		];
	}

	/**
	 * Add/update a medicine inside the session draft (shared by every order type).
	 */
	private function saveDraftMedicine(string $orderType, Request $request)
	{
		$request->validate([
			'patientcode' => 'required|string',
			'name'        => 'required|string|max:100',
			'strength'    => 'nullable|string|max:60',
			'dose'        => 'nullable|string|max:80',
			'morning'     => 'nullable|string|max:20',
			'noon'        => 'nullable|string|max:20',
			'night'       => 'nullable|string|max:20',
			'duration'    => 'nullable|string|max:60',
			'timing'      => 'nullable|in:before,after,with,empty',
			'instruction' => 'nullable|string|max:255',
		]);

		$patientcode = $request->patientcode;
		$context = $this->admissionContext($patientcode);

		if (!$context['admissionid']) {
			return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
		}

		$input = $request->all();
		$input['templeteid'] = $input['templeteid'] ?? $context['templeteid'];

		$result = app(MedicineDraftService::class)
			->addOrUpdate($orderType, $patientcode, $context['admissionid'], $input, $request->input('key'));

		if ($result['status'] === 'duplicate') {
			return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
		}

		return response()->json([
			'success' => true,
			'message' => $result['status'] === 'updated' ? 'Medicine updated successfully' : 'Medicine added successfully',
		]);
	}

	/**
	 * Remove a medicine from the session draft (shared by every order type).
	 * Never touches the database.
	 */
	private function deleteDraftMedicine(string $orderType, Request $request)
	{
		$request->validate(['patientcode' => 'required|string']);

		$patientcode = $request->patientcode;
		$context = $this->admissionContext($patientcode);

		if (!$context['admissionid']) {
			return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
		}

		$removed = app(MedicineDraftService::class)
			->remove($orderType, $patientcode, $context['admissionid'], (string) $request->input('key', ''));

		return $removed
			? response()->json(['success' => true, 'message' => 'Medicine deleted successfully'])
			: response()->json(['success' => false, 'message' => 'Medicine not found']);
	}

	/**
	 * Render the current session draft as table rows (shared by every order type).
	 */
	private function listDraftMedicines(string $orderType, Request $request, string $rowsView)
	{
		$patientcode = $request->patientcode;
		$context = $this->admissionContext($patientcode);

		$medicines = app(MedicineDraftService::class)
			->list($orderType, $patientcode, $context['admissionid'] ?? '');

		return view($rowsView, compact('medicines'))->render();
	}

	/**
	 * Save All — persist the session draft into the patient table and clear it
	 * (shared by every order type).
	 */
	private function saveAllDraftMedicines(string $orderType, Request $request)
	{
		$request->validate(['patientcode' => 'required|string']);

		$patientcode = $request->patientcode;
		$context = $this->admissionContext($patientcode);

		if (!$context['admissionid']) {
			return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
		}

		$service = app(MedicineDraftService::class);

		$result = $service->persist($orderType, $patientcode, $context['admissionid']);

		// Rebuild the in-page draft from the now-saved rows so the AJAX list
		// keeps showing the committed prescription without a page reload.
		$service->rebuild($orderType, $patientcode, $context['admissionid'], $context['templeteid']);

		return response()->json([
			'success'  => true,
			'message'  => 'Medicines saved successfully.',
			'inserted' => $result['inserted'],
			'updated'  => $result['updated'],
			'removed'  => $result['removed'],
		]);
	}

	/**
	 * "Load Template Medicines" — import template rows not already active into
	 * the current draft (shared by every order type).
	 */
	private function loadTemplateDraftMedicines(string $orderType, Request $request)
	{
		$request->validate([
			'order_type'  => 'required|string',
			'patientcode' => 'required|string',
		]);

		$patientcode = $request->patientcode;
		$context = $this->admissionContext($patientcode);

		if (!$context['admissionid']) {
			return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
		}

		$added = app(MedicineDraftService::class)
			->loadTemplate($orderType, $patientcode, $context['admissionid'], $request->input('templeteid', $context['templeteid']));

		return response()->json([
			'success' => true,
			'message' => $added > 0 ? "{$added} template medicine(s) loaded." : 'No new template medicines to add.',
			'added'   => $added,
		]);
	}

public function admitpatient()
{
    $patients = DB::table('patients')
        ->whereNotIn('patientcode', function ($query) {
            $query->select('patientcode')
                  ->from('patient_admission')
                  ->where('Active', 1); 
        })
        ->orderBy('patientname')
        ->get();
    
    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $availableBeds = Schema::hasTable('beds')
        ? DB::table('beds')->where('status', 'vacant')->orderBy('bed_number')->get()
        : collect();

    $doctors = Schema::hasTable('doctors')
        ? DB::table('doctors')->orderBy('doctor_name')->get()
        : collect();

    $procedureTypes = ProcedureType::where('status', 1)
        ->orderBy('procedure_name')
        ->get();

    $treatmentTypes = Schema::hasTable('treatment_types')
        ? DB::table('treatment_types')->where('status', 1)->orderBy('name')->get()
        : collect();

    return view('admission.admitpatient', compact('patients', 'templates', 'availableBeds', 'doctors', 'procedureTypes', 'treatmentTypes'));
}

public function medicineRowsHtml(Request $request)
{
    $templeteid = $request->query('templeteid');

    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'instant');

    if (!empty($templeteid)) {
        $query->where('templeteid', $templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();

    $html = view('admission.partials.admissionmedicine_rows', [
        'templateMedicines' => $templateMedicines,
    ])->render();

    return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
}

public function storeadmitpatient(Request $request)
{
    $request->validate([
        'procedure_type_id' => 'nullable|exists:procedure_types,id',
        'treatment_type_id' => 'nullable|exists:treatment_types,id',
    ]);

  $activeMenu = 'admission/admitpatient';

    $patientcode = $request->patientcode;
    $templeteid  = $request->templeteid;
    $bedId       = $request->input('bed_id');
    $order_type  = "instant";

    if (!empty($bedId) && Schema::hasTable('beds')) {
        $bed = DB::table('beds')->where('id', $bedId)->first();

        if (!$bed) {
            return redirect()->back()->withInput()->with('error', 'Selected bed not found.');
        }

        if (($bed->status ?? null) !== 'vacant') {
            return redirect()->back()->withInput()->with('error', 'Selected bed is not available.');
        }
    }

	$admissionConfig = DB::table('configadmissionid')->first();
	$currentAdmissionId = (int)$admissionConfig->admissionid;
	$newAdmissionId = $currentAdmissionId + 1;
	DB::table('configadmissionid')
		->where('id', $admissionConfig->id)
		->update([
			'admissionid' => $newAdmissionId
		]);
	$admissionid = $newAdmissionId;

    $admissionExists = DB::table('patient_admission')
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('templeteid', $templeteid)
        ->where('order_type', $order_type)
        ->exists();

    if (!$admissionExists) {
        // insertGetId() — patient_admission.id (bigint PK) return করে
        $patientAdmissionPK = DB::table('patient_admission')->insertGetId([
            'patientcode'      => $patientcode,
            'admissionid'      => $admissionid,
            'templeteid'       => $templeteid,
            'admission_type'   => $order_type,
            'status'           => 2,
            'remark'           => $request->remark,
            'order_type'       => $order_type,
            'active'           => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
            'created_by'       => Auth::id(),
        ]);
    } else {
        
        $patientAdmissionPK = DB::table('patient_admission')
            ->where('patientcode', $patientcode)
            ->where('admissionid', $admissionid)
            ->where('templeteid', $templeteid)
            ->where('order_type', $order_type)
            ->value('id');
    }

    //(used by the print slip)
    if (Schema::hasColumn('patient_admission', 'doctor_id')) {
        DB::table('patient_admission')
            ->where('id', $patientAdmissionPK)
            ->update(['doctor_id' => $request->doctor_id]);
    }

    // Pathway ta save rakhi, jate stage-skip logic (Pre-Surg/Post-Surg/Round/Fresh)
    // porer ধাপে এই admission-এর treatment_type onujayi decide korte pare.
    if (Schema::hasColumn('patient_admission', 'treatment_type_id')) {
        DB::table('patient_admission')
            ->where('id', $patientAdmissionPK)
            ->update(['treatment_type_id' => $request->treatment_type_id]);
    }

    $this->maybeCreateFollowupDue($patientAdmissionPK, $patientcode, $request->procedure_type_id);

    if (!empty($bedId) && Schema::hasTable('beds')) {
        $bedColumns = Schema::getColumnListing('beds');
        $bedUpdate = [];

        if (in_array('status', $bedColumns, true)) {
            $bedUpdate['status'] = 'occupied';
        }

        if (in_array('current_admission_id', $bedColumns, true)) {
            // NOTE: $admissionid is the custom varchar admission code.
            // beds.current_admission_id stores the patient_admission PK instead.
            $bedUpdate['current_admission_id'] = $patientAdmissionPK;
        }

        if (in_array('updated_at', $bedColumns, true)) {
            $bedUpdate['updated_at'] = now();
        }

        if (!empty($bedUpdate)) {
            DB::table('beds')->where('id', $bedId)->update($bedUpdate);
        }
    }

    // Draft-based medicine flow: nothing is copied into the patient medicine
    // tables at admission time anymore. The slip simply reads whatever was
    // saved via Save All (see MedicineDraftService).

    return redirect()
    ->route('admission.showAdmissionSlip')
    ->with('patientcode', $patientcode)
    ->with('admissionid', $admissionid)
    ->with('templeteid', $templeteid)
    ->with('success', 'Patient admission saved successfully.');
}

public function showAdmissionSlip()
{
    $activeMenu = 'admission/admitpatient';

	$patientcode  = session('patientcode');
    $admissionid  = session('admissionid');
    $templeteid   = session('templeteid');

    $admission = DB::table('patient_admission')
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->first();

    $patient = DB::table('patients')
        ->where('patientcode', $patientcode)
        ->first();

    $medicine = DB::table('common_medicine')
        ->orderBy('name')
        ->get();

    $medicines = DB::table('patient_instant_medicines')
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('templeteid', $templeteid)
        ->orderBy('id', 'asc')
        ->get();

    // Added: fetch the bed assigned to this admission (Room No / Bed No / Ward on the print slip)
    $assignedBed = null;
    if (Schema::hasTable('beds') && !empty($admission->id)) {
        $assignedBed = DB::table('beds')
            ->where('current_admission_id', $admission->id)
            ->first();
    }

    // Added: fetch the consultant doctor selected during admission (Doctor Information on the print slip)
    $printDoctor = null;
    if (Schema::hasTable('doctors') && !empty($admission->doctor_id)) {
        $printDoctor = DB::table('doctors')
            ->where('id', $admission->doctor_id)
            ->first();
    }

    $procedureTypes = ProcedureType::where('status', 1)
        ->orderBy('procedure_name')
        ->get();

    $procedureTypeId = null;
    if (Schema::hasTable('followup_dues') && !empty($admission->id)) {
        $followupDue = DB::table('followup_dues')
            ->where('admission_id', $admission->id)
            ->first();
        $procedureTypeId = $followupDue->procedure_type_id ?? null;
    }

    return view('admission.admissioninfo.show_admission_slip', compact(
        'activeMenu',
        'admission',
        'patient',
        'medicines',
        'patientcode',
        'medicine',
        'assignedBed',
        'printDoctor',
        'procedureTypes',
        'procedureTypeId'
    ));
}

public function updateProcedureType(Request $request)
{
    $request->validate([
        'patientcode'      => 'required|string',
        'procedure_type_id' => 'nullable|exists:procedure_types,id',
    ]);

    $admission = PatientAdmission::where('patientcode', $request->patientcode)
        ->where('active', 1)
        ->first();

    if (!$admission) {
        return response()->json([
            'success' => false,
            'message' => 'No active admission found for this patient.',
        ], 404);
    }

    $admission->procedure_type_id = $request->procedure_type_id;
    $admission->save();

    return response()->json([
        'success' => true,
        'message' => 'Procedure type updated successfully.',
    ]);
}

///////////////////////////////////
public function admedicine(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->leftJoin('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 1)
    ->where('pa.active', 1)
    ->select('pa.patientcode', 'p.patientname', 'p.mobile_no')
    ->orderBy('pa.id')
    ->get();
	
    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'admit');

    if ($request->templeteid) {
        $query->where('templeteid', $request->templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();

    return view('admission.admitmedicine.admedicine_table', compact('templateMedicines','patients'));
}

public function showadMedicine(Request $request)
{
	$activeMenu = 'admission/admedicine';

    $patientcode = $request->input('patientcode') ?? $request->query('patientcode');

    if (empty($patientcode)) {
        return redirect()->route('admission.admedicine')
            ->with('info', 'Please select a patient to load admission medicines.');
    }

	$data = $this->getActiveAdmission($patientcode);

	$TempleteID = $data->templeteid ?? '';
	$admissionid = $data->admissionid ?? '';
	$order_type = 'admit';

    $medicine = DB::table('common_medicine')->orderBy('name')->get();

	// Draft-based flow: the working list lives in the session. A fresh page
	// open ALWAYS rebuilds the draft from the baseline (already-saved patient
	// rows, else the template). Unsaved edits are therefore discarded on a
	// browser refresh; the in-page AJAX updates still apply to the session.
	$medicines = app(MedicineDraftService::class)
		->rebuild($order_type, $patientcode, $admissionid, $TempleteID);

	$patient = DB::table('patients')
		->where('patientcode', $patientcode)
		->first();

	$admission = DB::table('patient_admission')
		->where('patientcode', $patientcode)
		->where('Active', 1)
		->orderByDesc('id')
		->first();

    return view('admission.admitmedicine.show_medicine', compact(
		'activeMenu', 'medicines', 'patientcode', 'medicine', 'patient', 'admission'
	));
}

public function admedicinePrint(Request $request)
{
    $patientcode = $request->query('patientcode');
    $stageTitle = $request->query('stageTitle', 'Admit Medicine');
    if (!$patientcode) {
        return redirect()->route('admission.admedicine')
            ->with('error', 'Patient code is required for print.');
    }

    try {
        $data = app(PrescriptionPrintService::class)->generate('admit', $patientcode, $stageTitle);
    } catch (RuntimeException $e) {
        return redirect()->route('admission.admedicine')
            ->with('error', $e->getMessage());
    }

    $printId   = $data['print_id'];
    $printHash = $data['print_hash'];
    $snapshots = $data['snapshots'];
    $admission = $data['admission'];
    $patient   = $data['patient'];
    $medicines = $data['medicines'];
    $orderType = 'admit';
    $title     = 'Admit Medicine Prescription';

    return view('prints.prescription', compact(
        'printId', 'printHash', 'snapshots', 'admission', 'patient', 'medicines', 'orderType', 'title'
    ));
}

public function premedicinePrint(Request $request)
{
    $patientcode = $request->query('patientcode');
    $stageTitle = $request->query('stageTitle', 'Pre Surgery Medicine');
    if (!$patientcode) {
        return redirect()->route('admission.presurgery')
            ->with('error', 'Patient code is required for print.');
    }

    try {
        $data = app(PrescriptionPrintService::class)->generate('pre', $patientcode, $stageTitle);
    } catch (RuntimeException $e) {
        return redirect()->route('admission.presurgery')
            ->with('error', $e->getMessage());
    }

    $printId   = $data['print_id'];
    $printHash = $data['print_hash'];
    $snapshots = $data['snapshots'];
    $admission = $data['admission'];
    $patient   = $data['patient'];
    $medicines = $data['medicines'];
    $orderType = 'pre';
    $title     = 'Pre Surgery Prescription';

    return view('prints.prescription', compact(
        'printId', 'printHash', 'snapshots', 'admission', 'patient', 'medicines', 'orderType', 'title'
    ));
}

public function postmedicinePrint(Request $request)
{
    $patientcode = $request->query('patientcode');
    $stageTitle = $request->query('stageTitle', 'Post Surgery Medicine');
    if (!$patientcode) {
        return redirect()->route('admission.postsurgery')
            ->with('error', 'Patient code is required for print.');
    }

    try {
        $data = app(PrescriptionPrintService::class)->generate('post', $patientcode, $stageTitle);
    } catch (RuntimeException $e) {
        return redirect()->route('admission.postsurgery')
            ->with('error', $e->getMessage());
    }

    $printId   = $data['print_id'];
    $printHash = $data['print_hash'];
    $snapshots = $data['snapshots'];
    $admission = $data['admission'];
    $patient   = $data['patient'];
    $medicines = $data['medicines'];
    $orderType = 'post';
    $title     = 'Post Surgery Prescription';

    return view('prints.prescription', compact(
        'printId', 'printHash', 'snapshots', 'admission', 'patient', 'medicines', 'orderType', 'title'
    ));
}

public function roundmedicinePrint(Request $request)
{
    $patientcode = $request->query('patientcode');
    $stageTitle = $request->query('stageTitle', 'Round Patient');
    if (!$patientcode) {
        return redirect()->route('admission.roundmedicine')
            ->with('error', 'Patient code is required for print.');
    }

    try {
        $data = app(PrescriptionPrintService::class)->generate('round', $patientcode, $stageTitle);
    } catch (RuntimeException $e) {
        return redirect()->route('admission.roundmedicine')
            ->with('error', $e->getMessage());
    }

    $printId   = $data['print_id'];
    $printHash = $data['print_hash'];
    $snapshots = $data['snapshots'];
    $admission = $data['admission'];
    $patient   = $data['patient'];
    $medicines = $data['medicines'];
    $orderType = 'round';
    $title     = 'Round Medicine Prescription';

    return view('prints.prescription', compact(
        'printId', 'printHash', 'snapshots', 'admission', 'patient', 'medicines', 'orderType', 'title'
    ));
}

public function freshmedicinePrint(Request $request)
{
    $patientcode = $request->query('patientcode');
    $stageTitle = $request->query('stageTitle', 'Fresh Prescription');
    if (!$patientcode) {
        return redirect()->route('admission.freshprescription')
            ->with('error', 'Patient code is required for print.');
    }

    try {
        $data = app(PrescriptionPrintService::class)->generate('fresh', $patientcode, $stageTitle);
    } catch (RuntimeException $e) {
        return redirect()->route('admission.freshprescription')
            ->with('error', $e->getMessage());
    }

    $printId   = $data['print_id'];
    $printHash = $data['print_hash'];
    $snapshots = $data['snapshots'];
    $admission = $data['admission'];
    $patient   = $data['patient'];
    $medicines = $data['medicines'];
    $orderType = 'fresh';
    $title     = 'Fresh Prescription';

    return view('prints.prescription', compact(
        'printId', 'printHash', 'snapshots', 'admission', 'patient', 'medicines', 'orderType', 'title'
    ));
}

public function admedicineSave(Request $request)
{
    return $this->saveDraftMedicine('admit', $request);
}

public function admedicineSaveAllAdmit(Request $request)
{
    return $this->saveAllDraftMedicines('admit', $request);
}

public function admedicineList(Request $request)
{
    return $this->listDraftMedicines('admit', $request, 'admission.admitmedicine.medicine_rows');
}

public function admedicineDelete(Request $request)
{
    return $this->deleteDraftMedicine('admit', $request);
}

/////////////////// premedicine

public function premedicine(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 2)
    ->where('pa.active', 1)
    ->orderBy('pa.id')
    ->get(['pa.patientcode', 'p.patientname', 'p.mobile_no']);

    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'admit');

    if ($request->templeteid) {
        $query->where('templeteid', $request->templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();
    return view('admission.premitmedicine.premissionmedicine_table', compact('templateMedicines','patients'));
}

public function showpreMedicine(Request $request)
{
	$activeMenu = 'admission/presurgery';
    $patientcode = $request->patientcode;
    $medicine = DB::table('common_medicine')->orderBy('name')->get();

	$context = $this->admissionContext($patientcode);
	$TempleteID = $context['templeteid'] ?: 'TPL-000001';
	$admissionid = $context['admissionid'] ?? '59';

    $order_type = "preorder";

	// Draft-based flow (same architecture as every other order type).
	$medicines = app(MedicineDraftService::class)
		->rebuild($order_type, $patientcode, $admissionid, $TempleteID);

	$patient = DB::table('patients')
		->where('patientcode', $patientcode)
		->first();

	$admission = DB::table('patient_admission')
		->where('patientcode', $patientcode)
		->where('Active', 1)
		->orderByDesc('id')
		->first();

    return view('admission.premitmedicine.preshow_medicine', compact(
		'activeMenu', 'medicines', 'patientcode', 'medicine', 'patient', 'admission'
	));
}

public function premedicineSave(Request $request)
{
    return $this->saveDraftMedicine('preorder', $request);
}

public function premedicineSaveAllPreorder(Request $request)
{
    return $this->saveAllDraftMedicines('preorder', $request);
}

public function premedicineList(Request $request)
{
    return $this->listDraftMedicines('preorder', $request, 'admission.premitmedicine.premedicine_rows');
}

public function premedicineDelete(Request $request)
{
    return $this->deleteDraftMedicine('preorder', $request);
}

/////////////////// postmedicine

public function postmedicine(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 3)
    ->where('pa.active', 1)
    ->orderBy('pa.id')
    ->get(['pa.patientcode', 'p.patientname', 'p.mobile_no']);

    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'postorder');

    if ($request->templeteid) {
        $query->where('templeteid', $request->templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();
    return view('admission.postmedicine.postmedicine_table', compact('templateMedicines','patients'));
}

public function showpostMedicine(Request $request)
{
	$activeMenu = 'admission/postsurgery';
    $patientcode = $request->patientcode;
    $medicine = DB::table('common_medicine')->orderBy('name')->get();

	$context = $this->admissionContext($patientcode);
	$TempleteID = $context['templeteid'] ?: 'TPL-000001';
	$admissionid = $context['admissionid'] ?? '59';

    $order_type = "postorder";

	// Draft-based flow (same architecture as every other order type).
	$medicines = app(MedicineDraftService::class)
		->rebuild($order_type, $patientcode, $admissionid, $TempleteID);

	$patient = DB::table('patients')
		->where('patientcode', $patientcode)
		->first();

	$admission = DB::table('patient_admission')
		->where('patientcode', $patientcode)
		->where('Active', 1)
		->orderByDesc('id')
		->first();

    return view('admission.postmedicine.postshow_medicine', compact(
		'activeMenu', 'medicines', 'patientcode', 'medicine', 'patient', 'admission'
	));
}

public function postmedicineSave(Request $request)
{
    return $this->saveDraftMedicine('postorder', $request);
}

public function postmedicineSaveAllPostorder(Request $request)
{
    return $this->saveAllDraftMedicines('postorder', $request);
}

public function postmedicineList(Request $request)
{
    return $this->listDraftMedicines('postorder', $request, 'admission.postmedicine.postmedicine_rows');
}

public function postmedicineDelete(Request $request)
{
    return $this->deleteDraftMedicine('postorder', $request);
}

/////////////////// Round Medicine

public function roundmedicine(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 4)
    ->where('pa.active', 1)
    ->orderBy('pa.id')
    ->get(['pa.patientcode', 'p.patientname', 'p.mobile_no']);

    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'postorder');

    if ($request->templeteid) {
        $query->where('templeteid', $request->templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();
    return view('admission.roundpatient.roundmedicine_table', compact('templateMedicines','patients'));
}

public function showroundMedicine(Request $request)
{
	$activeMenu = 'admission/roundpatient';
    $patientcode = $request->patientcode;
    $medicine = DB::table('common_medicine')->orderBy('name')->get();

	$context = $this->admissionContext($patientcode);
	$TempleteID = $context['templeteid'] ?: 'TPL-000001';
	$admissionid = $context['admissionid'] ?? '59';

    $order_type = "round";

	// Draft-based flow (same architecture as every other order type).
	$medicines = app(MedicineDraftService::class)
		->rebuild($order_type, $patientcode, $admissionid, $TempleteID);

	$patient = DB::table('patients')
		->where('patientcode', $patientcode)
		->first();

	$admission = DB::table('patient_admission')
		->where('patientcode', $patientcode)
		->where('Active', 1)
		->orderByDesc('id')
		->first();

    return view('admission.roundpatient.roundshow_medicine', compact(
		'activeMenu', 'medicines', 'patientcode', 'medicine', 'patient', 'admission'
	));
}

public function roundmedicineSave(Request $request)
{
    return $this->saveDraftMedicine('round', $request);
}

public function roundmedicineList(Request $request)
{
    return $this->listDraftMedicines('round', $request, 'admission.roundpatient.roundmedicine_rows');
}

public function roundmedicineSaveAll(Request $request)
{
    return $this->saveAllDraftMedicines('round', $request);
}

public function roundmedicineDelete(Request $request)
{
    return $this->deleteDraftMedicine('round', $request);
}

/////////////////// freshprescription Medicine

public function freshprescription(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 5)
    ->where('pa.active', 1)
    ->orderBy('pa.id')
    ->get(['pa.patientcode', 'p.patientname', 'p.mobile_no']);

    $templates = DB::table('tbl_template')->orderBy('title')->get();
    
    $query = DB::table('template_medicine')
        ->where('active', 1)
        ->where('order_type', 'postorder');

    if ($request->templeteid) {
        $query->where('templeteid', $request->templeteid);
    }

    $templateMedicines = $query->orderBy('id')->get();
    return view('admission.freshmedicine.freshmedicine_table', compact('templateMedicines','patients'));
}

public function showfreshMedicine(Request $request)
{
	$activeMenu = 'admission/freshprescription';
    $patientcode = $request->patientcode;
    $medicine = DB::table('common_medicine')->orderBy('name')->get();

	$context = $this->admissionContext($patientcode);
	$TempleteID = $context['templeteid'] ?: 'TPL-000001';
	$admissionid = $context['admissionid'] ?? '59';

    $order_type = "fresh";

	// Draft-based flow (same architecture as every other order type).
	$service = app(MedicineDraftService::class);
	$medicines = $service->rebuild($order_type, $patientcode, $admissionid, $TempleteID);

	// Fresh Prescription: nothing saved for THIS admission yet → load the
	// fresh medicines the patient was on during previous admission(s).
	if (!$service->hasSavedRows($order_type, $patientcode, $admissionid)) {
		$medicines = $service->loadFromPreviousAdmission($order_type, $patientcode, $admissionid, $medicines);
	}

	$patient = DB::table('patients')
		->where('patientcode', $patientcode)
		->first();

	$admission = DB::table('patient_admission')
		->where('patientcode', $patientcode)
		->where('Active', 1)
		->orderByDesc('id')
		->first();

    return view('admission.freshmedicine.freshshow_medicine', compact(
		'activeMenu', 'medicines', 'patientcode', 'medicine', 'patient', 'admission'
	));
}

public function freshmedicineSave(Request $request)
{
    return $this->saveDraftMedicine('fresh', $request);
}

public function freshmedicineSaveAll(Request $request)
{
    return $this->saveAllDraftMedicines('fresh', $request);
}

public function freshmedicineList(Request $request)
{
    return $this->listDraftMedicines('fresh', $request, 'admission.freshmedicine.freshmedicine_rows');
}

public function freshmedicineDelete(Request $request)
{
    return $this->deleteDraftMedicine('fresh', $request);
}

/**
 * "Load Template Medicines" — imports template rows (that are not already
 * active) into the current session draft. Shared by every order type.
 */
public function loadTemplateMedicines(Request $request)
{
    return $this->loadTemplateDraftMedicines($request->input('order_type'), $request);
}

public function getPatientStatus(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);

    $admission = $this->getActiveAdmission($request->patientcode);

    if (!$admission) {
        return response()->json([
            'exists' => false,
            'status' => 0,
        ]);
    }

    return response()->json([
        'exists' => true,
        'status' => (int)$admission->status,
        'admissionid' => $admission->admissionid,
        'templeteid' => $admission->templeteid,
    ]);
}

/////////////////// releasepatient Medicine

public function releasepatient(Request $request)
{
    $patients = DB::table('patient_admission as pa')
        ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
        ->where('pa.status', 6)
        ->where('pa.active', 1)
        ->orderBy('pa.id')
        ->get(['pa.patientcode', 'p.patientname', 'p.mobile_no']);

    return view('admission.releasepatient.release_list', compact('patients'));
}

public function nextStage(Request $request)
{
    $request->validate([
        'patientcode' => 'required|string',
        'target_status' => 'nullable|integer|in:2,3,5,6',
    ]);

    $admission = DB::table('patient_admission')
        ->where('patientcode', $request->patientcode)
        ->where('Active', 1)
        ->where('status', '<', 6)
        ->orderByDesc('id')
        ->first();

    if (!$admission) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $currentStatus = (int)$admission->status;

    $stageNames = [
        1 => 'Admit Medicine',
        2 => 'Pre Surgery',
        3 => 'Post Surgery',
        5 => 'Fresh Prescription',
        6 => 'Release Patient',
    ];

    // Target stage -> landing page after the move. Admit (1) and
    // Round Patient (4) are excluded: 4 is removed from the flow.
    $stageRoutes = [
        2 => 'admission.presurgery',
        3 => 'admission.postsurgery',
        5 => 'admission.freshprescription',
        6 => 'admission.discharge',
    ];

    $targetStatus = $request->filled('target_status') ? (int)$request->target_status : null;

    if ($targetStatus === null) {
        // Legacy path (no dropdown selected): move to next status in sequence,
        // skipping stages marked as skip on the treatment type pathway.
        $skipColumnByStatus = [
            2 => 'skip_pre_surgery',
            3 => 'skip_post_surgery',
            5 => 'skip_fresh_prescription',
        ];

        $treatmentType = null;
        if (!empty($admission->treatment_type_id) && Schema::hasTable('treatment_types')) {
            $treatmentType = DB::table('treatment_types')->where('id', $admission->treatment_type_id)->first();
        }

        $nextStatus = $currentStatus + 1;

        // Pathway-te je stage-gula "skip" mark kora, oigula pash kore aro egiye jai,
        // jotokkhon na emon status pai ja skip na, ba 6 (Release) e pouchi.
        while ($nextStatus < 6) {
            if ($nextStatus === 4) {
                $nextStatus++;
                continue;
            }

            $column = $skipColumnByStatus[$nextStatus] ?? null;
            if ($treatmentType && $column && !empty($treatmentType->{$column})) {
                $nextStatus++;
                continue;
            }

            break;
        }

        if ($nextStatus > 6) {
            return response()->json(['success' => false, 'message' => 'Already at final stage (Release).']);
        }

        $targetStatus = $nextStatus;
    } elseif ($targetStatus === $currentStatus) {
        return response()->json(['success' => false, 'message' => 'Patient is already in the selected stage.']);
    }

    DB::table('patient_admission')
        ->where('id', $admission->id)
        ->update(['status' => $targetStatus, 'updated_at' => now()]);

    return response()->json([
        'success' => true,
        'message' => 'Moved to stage: ' . ($stageNames[$targetStatus] ?? $targetStatus),
        'current_status' => $targetStatus,
        'stage_name' => $stageNames[$targetStatus] ?? $targetStatus,
        'redirect_url' => route($stageRoutes[$targetStatus] ?? 'admission.presurgery'),
    ]);
}

/////////////////// Discharge Patient

public function dischargeList(Request $request)
{
    $activeMenu = 'admission/Discharge';
    $patients = DB::table('patient_admission as pa')
        ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
        ->where('pa.status', 6)
        ->where('pa.active', 1)
        ->orderBy('pa.id')
        ->get(['pa.id', 'pa.patientcode', 'pa.admissionid', 'pa.created_at', 'p.patientname', 'p.mobile_no']);

    $admissionId = $request->integer('admission_id') ?: null;
    $historyMode = $admissionId !== null;
    $patientcode = $request->query('patientcode') ?? $request->input('patientcode');

    // Default to the first patient if none specified
    if (!$historyMode && empty($patientcode) && $patients->isNotEmpty()) {
        $patientcode = $patients->first()->patientcode;
    }

    $patient = null;
    $admission = null;
    $templateDischarge = null;
    $admitMedicines = collect();
    $preMedicines = collect();
    $postMedicines = collect();
    $roundMedicines = collect();
    $freshMedicines = collect();
    $discharge = null;
    $dischargeDoctor = null;
    $dischargeBed = null;
    $savedMedicines = collect();
    $commonMedicines = Schema::hasTable('common_medicine')
        ? DB::table('common_medicine')->orderBy('name')->get()
        : collect();

    if ($historyMode) {
        $admission = DB::table('patient_admission')
            ->where('id', $admissionId)
            ->where('status', 6)
            ->first();
        $patientcode = $admission->patientcode ?? null;
    } elseif ($patientcode) {
        $admission = DB::table('patient_admission')
            ->where('patientcode', $patientcode)
            ->where('status', 6)
            ->where('Active', 1)
            ->orderByDesc('id')
            ->first();

    }

    if ($admission) {
            $patient = DB::table('patients')->where('patientcode', $patientcode)->first();
			
			$dischargeDoctor = null;
			if (Schema::hasTable('doctors') && !empty($admission->doctor_id)) {
				$dischargeDoctor = DB::table('doctors')
					->where('id', $admission->doctor_id)
					->first();
			}
			
			$dischargeBed = null;
			if (Schema::hasTable('beds') && !empty($admission->id)) {
				$dischargeBed = DB::table('beds')
					->where('current_admission_id', $admission->id)
					->first();
		    }
			
            $TempleteID = $admission->templeteid ?? 'TPL-000001';
            $admissionid = $admission->admissionid ?? 59;

            $templateDischarge = DB::table('template_discharge')
                ->where('templateid', $TempleteID)
                ->first();

            if (Schema::hasTable('discharges')) {
                $discharge = DB::table('discharges')
                    ->where('admission_id', $admission->id)
                    ->orderByDesc('id')
                    ->first();
            }

            if (Schema::hasTable('discharge_medicines')) {
                $medicineColumns = Schema::getColumnListing('discharge_medicines');
                $savedMedicineQuery = DB::table('discharge_medicines');

                if (in_array('admission_id', $medicineColumns, true)) {
                    $savedMedicineQuery->where('admission_id', $admission->id);
                } elseif (in_array('patientcode', $medicineColumns, true)) {
                    $savedMedicineQuery->where('patientcode', $patientcode);
                }

                $savedMedicines = $savedMedicineQuery->orderBy('id')->get();
            }

            $admitMedicines = DB::table('patient_admission_medicines')
                ->where('patientcode', $patientcode)
                ->where('admissionid', $admissionid)
                ->orderBy('id')->get();

            $preMedicines = DB::table('patient_presurgery_medicines')
                ->where('patientid', $patientcode)
                ->where('admissionid', $admissionid)
                ->orderBy('id')->get();

            $postMedicines = DB::table('patient_postsurgery_medicines')
                ->where('patientid', $patientcode)
                ->where('admissionid', $admissionid)
                ->orderBy('id')->get();

            $roundMedicines = DB::table('patient_round_medicines')
                ->where('patientid', $patientcode)
                ->where('admissionid', $admissionid)
                ->orderBy('id')->get();

            $freshMedicines = DB::table('patient_fresh_medicines')
                ->where('patientid', $patientcode)
                ->where('admissionid', $admissionid)
                ->orderBy('id')->get();
    }

    $procedureTypes = ProcedureType::where('status', 1)
        ->orderBy('procedure_name')
        ->get();

    $operationNames    = OperationName::where('active', 1)->orderBy('name', 'asc')->get();
    $surgeons          = Doctor::where('active', 1)->orderBy('doctor_name', 'asc')->get();
    $anesthesiologists = Anesthesiologist::where('active', 1)->orderBy('name', 'asc')->get();
    $assistantSurgeons = AssistantSurgeon::where('active', 1)->orderBy('name', 'asc')->get();
    $dischargeDiagnoses = DischargeDiagnosis::where('active', 1)->orderBy('name', 'asc')->get();
    $dischargeInstructions = DischargeInstruction::where('active', 1)->orderBy('name', 'asc')->get();
    $operationMethods = OperationMethod::where('active', 1)->orderBy('name', 'asc')->get();

    return view('admission.discharge.discharge_list', compact(
        'activeMenu', 'patients', 'patientcode', 'patient', 'admission',
        'admitMedicines', 'preMedicines', 'postMedicines', 'roundMedicines', 'freshMedicines',
        'templateDischarge', 'discharge', 'savedMedicines', 'commonMedicines','dischargeDoctor',
        'dischargeBed', 'procedureTypes', 'operationNames', 'surgeons', 'anesthesiologists', 'assistantSurgeons', 'dischargeDiagnoses', 'dischargeInstructions', 'operationMethods', 'historyMode'
    ));
}

public function dischargeHistory(Request $request)
{
    $query = DB::table('patient_admission as pa')
        ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
        ->leftJoin('discharges as d', 'd.admission_id', '=', 'pa.id')
        ->where('pa.status', 6)
        ->where('pa.Active', 0)
        ->select('pa.id', 'pa.patientcode', 'pa.admissionid', 'pa.created_at', 'pa.discharge_date as admission_discharge_date', 'p.patientname', 'p.mobile_no', 'd.discharge_date', 'd.doctor_name');

    if ($request->filled('search')) {
        $search = trim($request->string('search'));
        $query->where(function ($query) use ($search) {
            $query->where('p.patientname', 'like', "%{$search}%")
                ->orWhere('pa.patientcode', 'like', "%{$search}%")
                ->orWhere('pa.admissionid', 'like', "%{$search}%");
        });
    }

    if ($request->filled('date_from')) {
        $query->whereDate(DB::raw('COALESCE(d.discharge_date, pa.discharge_date)'), '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate(DB::raw('COALESCE(d.discharge_date, pa.discharge_date)'), '<=', $request->date_to);
    }

    $history = $query->orderByDesc(DB::raw('COALESCE(d.discharge_date, pa.discharge_date)'))
        ->orderByDesc('pa.id')
        ->paginate(25)
        ->withQueryString();

    $historyRoute = $request->routeIs('web-oldpatient') ? 'web-oldpatient' : 'admission.discharge.history';

    return view('admission.discharge.history', compact('history', 'historyRoute'));
}

public function showdischargeMedicine(Request $request)
{
    return redirect()->route('admission.discharge', ['patientcode' => $request->patientcode]);
}

public function submitDischargeForApproval(Request $request)
{
    $request->validate([
        'patientcode'       => 'required|string',
        'admission_id'      => 'required|integer',
        'discharge_date'    => 'nullable|date',
        'diagnosis'         => 'nullable|string|max:300',
        'doctor_name'       => 'nullable|string|max:150',
        'ward'              => 'nullable|string|max:100',
        'reg_no'            => 'nullable|string|max:100',
        'operation_name'    => 'nullable|string|max:300',
        'operation_method'  => 'nullable|string',
        'surgeon'           => 'nullable|string|max:200',
        'anesthetist'       => 'nullable|string|max:200',
        'assistant_1'       => 'nullable|string|max:200',
        'assistant_2'       => 'nullable|string|max:200',
        'investigations'    => 'nullable|string',
        'blood_group'       => 'nullable|string|max:10',
        'drug_allergy'      => 'nullable|string|max:200',
        'notes'             => 'nullable|string',
        'follow_up_date'    => 'nullable|date',
        'operation_name_id'       => 'nullable|exists:operation_names,id',
        'surgeon_id'              => 'nullable|exists:doctors,id',
        'anesthesiologist_id'     => 'nullable|exists:anesthesiologists,id',
        'assistant_surgeon_1_id'  => 'nullable|exists:assistant_surgeons,id',
        'assistant_surgeon_2_id'  => 'nullable|exists:assistant_surgeons,id',
        'discharge_diagnosis_id'  => 'nullable|exists:discharge_diagnoses,id',
        'discharge_instruction_id' => 'nullable|exists:discharge_instructions,id',
        'operation_method_id'     => 'nullable|exists:operation_methods,id',
        'operation_date'          => 'nullable|date',
        'operation_time'          => 'nullable|date_format:H:i',
        'anesthesia_type'         => 'nullable|string|max:100',
        'medicines'         => 'nullable|array',
    ]);

    if (!Schema::hasTable('discharges')) {
        return response()->json(['success' => false, 'message' => 'Discharges table not found.'], 500);
    }

    $admission = $this->getActiveAdmission($request->patientcode);
    if (!$admission || (int) $admission->id !== (int) $request->admission_id) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $patient = DB::table('patients')->where('patientcode', $request->patientcode)->first();
    if (!$patient) {
        return response()->json(['success' => false, 'message' => 'Patient not found.'], 422);
    }

    DB::transaction(function () use ($request, $admission, $patient) {
        [$operationNameId, $operationName] = $this->resolveMasterField(
            $request->input('operation_name_id', $request->input('operation_name')),
            'operation_names',
            'name'
        );

        [$surgeonId, $surgeon] = $this->resolveMasterField(
            $request->input('surgeon_id', $request->input('surgeon')),
            'doctors',
            'doctor_name'
        );

        [$anesthesiologistId, $anesthetist] = $this->resolveMasterField(
            $request->input('anesthesiologist_id', $request->input('anesthetist')),
            'anesthesiologists',
            'name'
        );

        [$assistant1Id, $assistant1] = $this->resolveMasterField(
            $request->input('assistant_surgeon_1_id', $request->input('assistant_1')),
            'assistant_surgeons',
            'name'
        );

        [$assistant2Id, $assistant2] = $this->resolveMasterField(
            $request->input('assistant_surgeon_2_id', $request->input('assistant_2')),
            'assistant_surgeons',
            'name'
        );

        [$diagnosisId, $diagnosis] = $this->resolveMasterField(
            $request->input('discharge_diagnosis_id', $request->input('diagnosis')),
            'discharge_diagnoses',
            'name'
        );

        [$instructionId, $notes] = $this->resolveMasterField(
            $request->input('discharge_instruction_id', $request->input('notes')),
            'discharge_instructions',
            'name'
        );

        [$operationMethodId, $operationMethod] = $this->resolveMasterField(
            $request->input('operation_method_id', $request->input('operation_method')),
            'operation_methods',
            'name'
        );

        DB::table('discharges')->updateOrInsert(
            ['admission_id' => $admission->id],
            [
                'patient_id'        => $patient->id,
                'discharge_date'    => $request->discharge_date,
                'diagnosis'         => $diagnosis,
                'discharge_diagnosis_id' => $diagnosisId,
                'doctor_name'       => $request->doctor_name,
                'ward'              => $request->ward,
                'reg_no'            => $request->reg_no,
                'operation_name'    => $operationName,
                'operation_method'  => $operationMethod,
                'operation_method_id' => $operationMethodId,
                'surgeon'           => $surgeon,
                'anesthetist'       => $anesthetist,
                'assistant_1'       => $assistant1,
                'assistant_2'       => $assistant2,
                'operation_name_id'      => $operationNameId,
                'surgeon_id'             => $surgeonId,
                'anesthesiologist_id'    => $anesthesiologistId,
                'assistant_surgeon_1_id' => $assistant1Id,
                'assistant_surgeon_2_id' => $assistant2Id,
                'operation_date'         => $request->input('operation_date'),
                'operation_time'         => $request->input('operation_time'),
                'anesthesia_type'        => $request->input('anesthesia_type'),
                'investigations'    => $request->investigations,
                'blood_group'       => $request->blood_group,
                'drug_allergy'      => $request->drug_allergy,
                'notes'             => $notes,
                'discharge_instruction_id' => $instructionId,
                'follow_up_date'    => $request->follow_up_date,
                'status'            => 'pending',
                'updated_at'        => now(),
                'created_at'        => now(),
            ]
        );

        $this->syncDischargeMedicines($request->input('medicines', []), $patient, $admission);
    });

    return response()->json([
        'success' => true,
        'message' => 'Discharge slip saved and sent for manager approval.',
    ]);
}

private function resolveMasterField(?string $value, string $table, string $nameColumn): array
{
    $value = trim((string) $value);

    if ($value === '') {
        return [null, null];
    }

    if (ctype_digit($value)) {
        $master = DB::table($table)->where('id', (int) $value)->first();

        if ($master) {
            return [(int) $value, $master->{$nameColumn} ?? null];
        }
    }

    return [null, $value];
}

public function approveDischarge(Request $request)
{
    $request->validate([
        'patientcode'  => 'required|string',
        'admission_id' => 'required|integer',
    ]);

    $admission = $this->getActiveAdmission($request->patientcode);
    if (!$admission || (int) $admission->id !== (int) $request->admission_id) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $updated = DB::table('discharges')
        ->where('admission_id', $admission->id)
        ->where('status', 'pending')
        ->update([
            'status' => 'completed',
            'discharge_date' => DB::raw('COALESCE(discharge_date, CURDATE())'),
            'discharged_by' => Auth::id(),
            'updated_at' => now(),
        ]);

    if (!$updated) {
        return response()->json([
            'success' => false,
            'message' => 'Pending discharge request not found or already approved.',
        ], 422);
    }

    return response()->json([
        'success' => true,
        'message' => 'Manager approval completed. You can now print and finalize discharge.',
    ]);
}

public function doDischarge(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);

    $admission = $this->getActiveAdmission($request->patientcode);
    if (!$admission) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $approved = DB::table('discharges')
        ->where('admission_id', $admission->id)
        ->where('status', 'completed')
        ->exists();

    if (!$approved) {
        return response()->json([
            'success' => false,
            'message' => 'Manager approval is required before final discharge.',
        ], 422);
    }

    $admissionUpdate = [
        'status' => 6,
        'Active' => 0,
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('patient_admission', 'discharge_date')) {
        $admissionUpdate['discharge_date'] = now();
    }

    DB::table('patient_admission')
        ->where('id', $admission->id)
        ->update($admissionUpdate);

    DB::table('discharges')
        ->where('admission_id', $admission->id)
        ->update([
            'discharge_date' => now()->toDateString(),
            'discharged_by' => Auth::id(),
            'updated_at' => now(),
        ]);

    $this->releaseAdmissionBed($admission);

    return response()->json([
        'success' => true,
        'message' => 'Patient discharged successfully.',
    ]);
}

private function syncDischargeMedicines(array $medicines, object $patient, object $admission): void
{
    if (!Schema::hasTable('discharge_medicines')) {
        return;
    }

    $columns = Schema::getColumnListing('discharge_medicines');
    $deleteQuery = DB::table('discharge_medicines');

    if (in_array('admission_id', $columns, true)) {
        $deleteQuery->where('admission_id', $admission->id);
    } elseif (in_array('patientcode', $columns, true)) {
        $deleteQuery->where('patientcode', $patient->patientcode);
    }

    $deleteQuery->delete();

    $rows = [];
    foreach ($medicines as $medicine) {
        $name = trim((string) ($medicine['medicine_name'] ?? $medicine['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $row = [
            'patientcode'  => $patient->patientcode,
            'patient_id'   => $patient->id,
            'admission_id' => $admission->id,
            'medicine_name'=> $name,
            'name'         => $name,
            'strength'     => $medicine['strength'] ?? null,
            'dose'         => $medicine['dose'] ?? null,
            'morning'      => $medicine['morning'] ?? null,
            'noon'         => $medicine['noon'] ?? null,
            'night'        => $medicine['night'] ?? null,
            'timing'       => $medicine['timing'] ?? null,
            'days'         => $medicine['days'] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $rows[] = array_intersect_key($row, array_flip($columns));

    }

    if (!empty($rows)) {
        DB::table('discharge_medicines')->insert($rows);
    }
}

private function releaseAdmissionBed(object $admission): void
{
    if (!Schema::hasTable('beds')) {
        return;
    }

    $columns = Schema::getColumnListing('beds');
    if (!in_array('current_admission_id', $columns, true)) {
        return;
    }

    $bedUpdate = ['current_admission_id' => null];

    if (in_array('status', $columns, true)) {
        $bedUpdate['status'] = 'vacant';
    }

    if (in_array('updated_at', $columns, true)) {
        $bedUpdate['updated_at'] = now();
    }

    DB::table('beds')
        ->where(function ($query) use ($admission) {
            $query->where('current_admission_id', $admission->admissionid ?? null)
                ->orWhere('current_admission_id', $admission->id);
        })
        ->update($bedUpdate);
}

private function maybeCreateFollowupDue($admissionPK, $patientcode, $procedureTypeId): void
{
    
    if (!Schema::hasTable('followup_dues')) {
        return;
    }

   
    $exists = DB::table('followup_dues')->where('admission_id', $admissionPK)->exists();
    if ($exists) {
        return;
    }

   
    
    DB::table('followup_dues')->insert([
        'admission_id'      => $admissionPK,
        'patientcode'       => $patientcode,
        'procedure_type_id' => $procedureTypeId,
        'admission_date'    => now()->toDateString(),
        'due_deadline'      => now()->addDays(30)->toDateString(),
        'amount'            => 100.00,
        'status'            => 'pending',
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);
}

}
