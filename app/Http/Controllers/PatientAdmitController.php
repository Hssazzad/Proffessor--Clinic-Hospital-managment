<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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

	public function admitpatient()
{
	$patients = DB::table('patients')
    ->whereNotIn('patientcode', function ($query) {

        $query->select('patientcode')
              ->from('patient_admission')
              ->where('status', '1')
              ->where('Active', '1');

    })
    ->orderBy('patientname')
    ->get();
	
	$templates = DB::table('tbl_template')->orderBy('title')->get();
    $availableBeds = Schema::hasTable('beds')
        ? DB::table('beds')->where('status', 'vacant')->orderBy('bed_number')->get()
        : collect();

    // Added: fetch doctors list for the "Select Doctor" dropdown (was missing before)
    $doctors = Schema::hasTable('doctors')
        ? DB::table('doctors')->orderBy('doctor_name')->get()
        : collect();

    return view('admission.admitpatient',compact('patients','templates', 'availableBeds', 'doctors'));
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
            'patientcode'     => $patientcode,
            'admissionid'     => $admissionid,
            'templeteid'      => $templeteid,
            'admission_type'  => $order_type,
            'status'          => 1,
            'remark'          => $request->remark,
            'order_type'      => $order_type,
            'active'          => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
            'created_by'      => Auth::id(),
        ]);
    } else {
        // আগে থেকে থাকলে id বের করো
        $patientAdmissionPK = DB::table('patient_admission')
            ->where('patientcode', $patientcode)
            ->where('admissionid', $admissionid)
            ->where('templeteid', $templeteid)
            ->where('order_type', $order_type)
            ->value('id');
    }

    // Added: persist the selected consultant doctor on this admission record (used by the print slip)
    if (Schema::hasColumn('patient_admission', 'doctor_id')) {
        DB::table('patient_admission')
            ->where('id', $patientAdmissionPK)
            ->update(['doctor_id' => $request->doctor_id]);
    }

    if (!empty($bedId) && Schema::hasTable('beds')) {
        $bedColumns = Schema::getColumnListing('beds');
        $bedUpdate = [];

        if (in_array('status', $bedColumns, true)) {
            $bedUpdate['status'] = 'occupied';
        }

        if (in_array('current_admission_id', $bedColumns, true)) {
            // Fix: আগে $admissionid (varchar custom code) save হত — "No Patient Found" এর কারণ ছিল
            // এখন patient_admission.id (bigint primary key) save হচ্ছে
            $bedUpdate['current_admission_id'] = $patientAdmissionPK;
        }

        if (in_array('updated_at', $bedColumns, true)) {
            $bedUpdate['updated_at'] = now();
        }

        if (!empty($bedUpdate)) {
            DB::table('beds')->where('id', $bedId)->update($bedUpdate);
        }
    }

    $patientmedicines = DB::table('patient_instant_medicines')
        ->where('templeteid', $templeteid)
        ->where('order_type', $order_type)
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->orderBy('id', 'asc')
        ->get();

    if ($patientmedicines->isEmpty()) {

        $templateMedicines = DB::table('template_medicine')
            ->where('templeteid', $templeteid)
            ->where('order_type', $order_type)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($templateMedicines as $row) {

            DB::table('patient_instant_medicines')->insert([
                'patientcode'  => $patientcode,
                'admissionid'  => $admissionid,
                'templeteid'   => $templeteid,
                'name'         => $row->name,
                'strength'     => $row->strength,
                'dose'         => $row->dose,
                'morning'      => $row->morning,
                'noon'         => $row->noon,
                'night'        => $row->night,
                'route'        => $row->route,
                'duration'     => $row->duration,
                'timing'       => $row->timing,
                'instruction'  => $row->instruction,
                'order_type'   => $order_type,
                'active'       => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

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

    return view('admission.admissioninfo.show_admission_slip', compact(
        'activeMenu',
        'admission',
        'patient',
        'medicines',
        'patientcode',
        'medicine',
        'assignedBed',
        'printDoctor'
    ));
}

///////////////////////////////////
public function admedicine(Request $request)
{
	$patients = DB::table('patient_admission as pa')
    ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
    ->where('pa.status', 1)
    ->where('pa.active', 1)
    ->orderBy('pa.id')
    ->get([
        'pa.patientcode',
        'p.patientname',
        'p.mobile_no'
    ]);
	
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

    $patientcode = $request->patientcode;
	$data = $this->getActiveAdmission($patientcode);

	$TempleteID = $data->templeteid ?? '';
	$admissionid = $data->admissionid ?? '';
	$order_type = $data->order_type ?? '';
	
    $medicine = DB::table('common_medicine')->orderBy('name')->get();  
    $order_type = "admit";

	$patientmedicines = DB::table('patient_admission_medicines')
        ->where('templeteid', $TempleteID)
        ->where('order_type', $order_type)
        ->where('patientcode', $patientcode)
        ->orderBy('id', 'asc')
        ->get();

	if ($patientmedicines->isNotEmpty()) {
        $medicines = $patientmedicines;
	} else {		
        $medicines = DB::table('template_medicine')
            ->where('templeteid', $TempleteID)
            ->where('order_type', $order_type)
            ->orderBy('id', 'asc')
            ->get();

        foreach($medicines as $row) {
            $exists = DB::table('patient_admission_medicines')
                ->where('patientcode', $request->patientcode)
                ->where('admissionid', $admissionid)
                ->where('name', $row->name)
                ->where('strength', $row->strength)
                ->where('dose', $row->dose)
                ->exists();

            if (!$exists) {
                DB::table('patient_admission_medicines')->insert([
                    'patientcode'  => $request->patientcode,
                    'admissionid'  => $admissionid,
                    'templeteid'   => $row->templeteid,
                    'name'         => $row->name,
                    'strength'     => $row->strength,
                    'dose'         => $row->dose,
                    'morning'      => $row->morning,
                    'noon'         => $row->noon,
                    'night'        => $row->night,
                    'route'        => $row->route,
                    'duration'     => $row->duration,
                    'timing'       => $row->timing,
                    'instruction'  => $row->instruction,
                    'order_type'   => $order_type,
                    'active'       => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
	}

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

public function admedicineSave(Request $request)
{
    $request->validate([
        'patientcode' => 'required',
        'name' => 'required|string|max:255',
    ]);
	$patientcode = $request->patientcode;
	$data = $this->getActiveAdmission($patientcode);

	$TempleteID = $data->templeteid ?? '';
	$admissionid = $data->admissionid ?? '';
	$order_type_admit = 'admit';

    $data = [
        'patientcode'   => $request->patientcode,
        'admissionid'   => $admissionid,
        'templeteid'    => $TempleteID,
        'name'          => $request->name,
        'strength'      => $request->strength,
        'dose'          => $request->dose,
        'morning'       => $request->morning,
        'noon'          => $request->noon,
        'night'         => $request->night,
        'route'         => $request->route,
        'duration'      => $request->duration,
        'timing'        => $request->timing,
        'instruction'   => $request->instruction,
        'order_type'    => $order_type_admit,
        'active'        => 1,
        'updated_at'    => now(),
    ];

    if ($request->id) {
        DB::table('patient_admission_medicines')
            ->where('id', $request->id)
            ->update($data);
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    $exists = DB::table('patient_admission_medicines')
        ->where('patientcode', $request->patientcode)
        ->where('admissionid', $admissionid)
        ->where('name', $request->name)
        ->where('order_type', $order_type_admit)
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
    }

    $data['created_at'] = now();
    DB::table('patient_admission_medicines')->insert($data);
    return response()->json(['success' => true, 'message' => 'Medicine added successfully']);
}

public function admedicineSaveAllAdmit(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);

    $patientcode = $request->patientcode;
    $admission = $this->getActiveAdmission($patientcode);

    if (!$admission || empty($admission->admissionid)) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $admissionid = $admission->admissionid;

    $updated = DB::table('patient_admission_medicines')
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->update([
            'order_type' => 'admit',
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message'   => 'Medicines saved (order type: admit).',
        'updated'   => $updated,
    ]);
}

public function admedicineList(Request $request)
{
    $patientcode = $request->patientcode;
	$data = $this->getActiveAdmission($patientcode);

	$TempleteID = $data->templeteid ?? '';
	$admissionid = $data->admissionid ?? '';

    $medicines = DB::table('patient_admission_medicines')
        ->where('patientcode', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->orderBy('id', 'asc')
        ->get();

    return view('admission.admitmedicine.medicine_rows', compact('medicines'))->render();
}

public function admedicineDelete(Request $request)
{
    $id = $request->id;
    $deleted = DB::table('patient_admission_medicines')->where('id', $id)->delete();

    if ($deleted) {
        return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Medicine not found']);
}

public function storeAdmissionMedicine(Request $request)
{
    $request->validate(['name' => 'required']);
    DB::table('template_medicine')->insert([
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'order_type'  => 'admit',
        'active'      => 1,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
    return redirect()->back()->with('success', 'Medicine added successfully');
}

public function updateAdmissionMedicine(Request $request, $id)
{
    DB::table('template_medicine')->where('id', $id)->update([
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'updated_at'  => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
}

public function destroyAdmissionMedicine($id)
{
    DB::table('template_medicine')->where('id', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
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

	$admission = $this->getActiveAdmission($patientcode);
	$TempleteID = $admission->templeteid ?? 'TPL-000001';
	$admissionid = $admission->admissionid ?? 59;

    $order_type = "preorder";
	
	$patientmedicines = DB::table('patient_presurgery_medicines')
        ->where('templeteid', $TempleteID)
        ->where('order_type', $order_type)
        ->where('patientid', $patientcode)
        ->orderBy('id', 'asc')
        ->get();

	if ($patientmedicines->isNotEmpty()) {
        $medicines = $patientmedicines;
	} else {		
        $medicines = DB::table('template_medicine')
            ->where('templeteid', $TempleteID)
            ->where('order_type', $order_type)
            ->orderBy('id', 'asc')
            ->get();

        foreach($medicines as $row) {
            $exists = DB::table('patient_presurgery_medicines')
                ->where('patientid', $request->patientcode)
                ->where('admissionid', $admissionid)
                ->where('name', $row->name)
                ->where('strength', $row->strength)
                ->where('dose', $row->dose)
                ->exists();

            if (!$exists) {
                DB::table('patient_presurgery_medicines')->insert([
                    'patientid'   => $request->patientcode,
                    'admissionid' => $admissionid,
                    'templeteid'  => $row->templeteid,
                    'name'        => $row->name,
                    'strength'    => $row->strength,
                    'dose'        => $row->dose,
                    'morning'     => $row->morning,
                    'noon'        => $row->noon,
                    'night'       => $row->night,
                    'route'       => $row->route,
                    'duration'    => $row->duration,
                    'timing'      => $row->timing,
                    'instruction' => $row->instruction,
                    'order_type'  => $order_type,
                    'active'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
	}

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
    $request->validate(['patientcode' => 'required', 'name' => 'required|string|max:255']);

	$admission = $this->getActiveAdmission($request->patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $data = [
        'patientid'   => $request->patientcode,
        'admissionid' => $admissionid,
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'order_type'  => 'preorder',
        'active'      => 1,
        'updated_at'  => now(),
    ];

    if ($request->id) {
        DB::table('patient_presurgery_medicines')->where('id', $request->id)->update($data);
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    $exists = DB::table('patient_presurgery_medicines')
        ->where('patientid', $request->patientcode)
        ->where('admissionid', $admissionid)
        ->where('name', $request->name)
        ->where('order_type', 'preorder')
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
    }

    $data['created_at'] = now();
    DB::table('patient_presurgery_medicines')->insert($data);
    return response()->json(['success' => true, 'message' => 'Medicine added successfully']);
}

public function premedicineSaveAllPreorder(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $updated = DB::table('patient_presurgery_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->update([
            'order_type' => 'preorder',
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Medicines saved (order type: preorder).',
        'updated' => $updated,
    ]);
}

public function premedicineList(Request $request)
{
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $medicines = DB::table('patient_presurgery_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->orderBy('id', 'asc')
        ->get();
    return view('admission.premitmedicine.premedicine_rows', compact('medicines'))->render();
}

public function premedicineDelete(Request $request)
{
    $id = $request->id;
    $deleted = DB::table('patient_presurgery_medicines')->where('id', $id)->delete();
    if ($deleted) {
        return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Medicine not found']);
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

	$admission = $this->getActiveAdmission($patientcode);
	$TempleteID = $admission->templeteid ?? 'TPL-000001';
	$admissionid = $admission->admissionid ?? 59;

    $order_type = "postorder";
	
	$patientmedicines = DB::table('patient_postsurgery_medicines')
        ->where('templeteid', $TempleteID)
        ->where('order_type', $order_type)
        ->where('patientid', $patientcode)
        ->orderBy('id', 'asc')
        ->get();

	if ($patientmedicines->isNotEmpty()) {
        $medicines = $patientmedicines;
	} else {		
        $medicines = DB::table('template_medicine')
            ->where('templeteid', $TempleteID)
            ->where('order_type', $order_type)
            ->orderBy('id', 'asc')
            ->get();

        foreach($medicines as $row) {
            $exists = DB::table('patient_postsurgery_medicines')
                ->where('patientid', $request->patientcode)
                ->where('admissionid', $admissionid)
                ->where('name', $row->name)
                ->where('strength', $row->strength)
                ->where('dose', $row->dose)
                ->exists();

            if (!$exists) {
                DB::table('patient_postsurgery_medicines')->insert([
                    'patientid'   => $request->patientcode,
                    'admissionid' => $admissionid,
                    'templeteid'  => $row->templeteid,
                    'name'        => $row->name,
                    'strength'    => $row->strength,
                    'dose'        => $row->dose,
                    'morning'     => $row->morning,
                    'noon'        => $row->noon,
                    'night'       => $row->night,
                    'route'       => $row->route,
                    'duration'    => $row->duration,
                    'timing'      => $row->timing,
                    'instruction' => $row->instruction,
                    'order_type'  => $order_type,
                    'active'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
	}

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
    $request->validate(['patientcode' => 'required', 'name' => 'required|string|max:255']);

	$admission = $this->getActiveAdmission($request->patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $data = [
        'patientid'   => $request->patientcode,
        'admissionid' => $admissionid,
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'order_type'  => 'postorder',
        'active'      => 1,
        'updated_at'  => now(),
    ];

    if ($request->id) {
        DB::table('patient_postsurgery_medicines')->where('id', $request->id)->update($data);
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    $exists = DB::table('patient_postsurgery_medicines')
        ->where('patientid', $request->patientcode)
        ->where('admissionid', $admissionid)
        ->where('name', $request->name)
        ->where('order_type', 'postorder')
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
    }

    $data['created_at'] = now();
    DB::table('patient_postsurgery_medicines')->insert($data);
    return response()->json(['success' => true, 'message' => 'Medicine added successfully']);
}

public function postmedicineSaveAllPostorder(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $updated = DB::table('patient_postsurgery_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->update([
            'order_type' => 'postorder',
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Medicines saved (order type: postorder).',
        'updated' => $updated,
    ]);
}

public function postmedicineList(Request $request)
{
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $medicines = DB::table('patient_postsurgery_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->orderBy('id', 'asc')
        ->get();
    return view('admission.postmedicine.postmedicine_rows', compact('medicines'))->render();
}

public function postmedicineDelete(Request $request)
{
    $id = $request->id;
    $deleted = DB::table('patient_postsurgery_medicines')->where('id', $id)->delete();
    if ($deleted) {
        return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Medicine not found']);
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

	$admission = $this->getActiveAdmission($patientcode);
	$TempleteID = $admission->templeteid ?? 'TPL-000001';
	$admissionid = $admission->admissionid ?? 59;

    $order_type = "postorder";
	
	$medicines = DB::table('patient_round_medicines')
        ->where('templeteid', $TempleteID)
        ->where('order_type', $order_type)
        ->where('patientid', $patientcode)
        ->orderBy('id', 'asc')
        ->get();

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
    $request->validate(['patientcode' => 'required', 'name' => 'required|string|max:255']);

	$admission = $this->getActiveAdmission($request->patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $data = [
        'patientid'   => $request->patientcode,
        'admissionid' => $admissionid,
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'order_type'  => $request->order_type ?? 'postorder',
        'active'      => 1,
        'updated_at'  => now(),
    ];

    if ($request->id) {
        DB::table('patient_round_medicines')->where('id', $request->id)->update($data);
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    $exists = DB::table('patient_round_medicines')
        ->where('patientid', $request->patientcode)
        ->where('admissionid', $admissionid)
        ->where('name', $request->name)
        ->where('order_type', $request->order_type ?? 'postorder')
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
    }

    $data['created_at'] = now();
    DB::table('patient_round_medicines')->insert($data);
    return response()->json(['success' => true, 'message' => 'Medicine added successfully']);
}

public function roundmedicineList(Request $request)
{
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $medicines = DB::table('patient_round_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->orderBy('id', 'asc')
        ->get();
    return view('admission.roundpatient.roundmedicine_rows', compact('medicines'))->render();
}

public function roundmedicineSaveAll(Request $request)
{
    $request->validate(['patientcode' => 'required|string']);

    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $updated = DB::table('patient_round_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->update([
            'order_type' => 'round',
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Medicines saved (order type: round).',
        'updated' => $updated,
    ]);
}

public function roundmedicineDelete(Request $request)
{
    $id = $request->id;
    $deleted = DB::table('patient_round_medicines')->where('id', $id)->delete();
    if ($deleted) {
        return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Medicine not found']);
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

	$admission = $this->getActiveAdmission($patientcode);
	$TempleteID = $admission->templeteid ?? 'TPL-000001';
	$admissionid = $admission->admissionid ?? 59;

    $order_type = "fresh";
	
	$patientmedicines = DB::table('patient_fresh_medicines')
        ->where('templeteid', $TempleteID)
        ->where('order_type', $order_type)
        ->where('patientid', $patientcode)
        ->orderBy('id', 'asc')
        ->get();

	if ($patientmedicines->isNotEmpty()) {
        $medicines = $patientmedicines;
	} else {		
        $medicines = DB::table('template_medicine')
            ->where('templeteid', $TempleteID)
            ->where('order_type', $order_type)
            ->orderBy('id', 'asc')
            ->get();

        foreach($medicines as $row) {
            $exists = DB::table('patient_fresh_medicines')
                ->where('patientid', $request->patientcode)
                ->where('admissionid', $admissionid)
                ->where('name', $row->name)
                ->where('strength', $row->strength)
                ->where('dose', $row->dose)
                ->exists();

            if (!$exists) {
                DB::table('patient_fresh_medicines')->insert([
                    'patientid'   => $request->patientcode,
                    'admissionid' => $admissionid,
                    'templeteid'  => $row->templeteid,
                    'name'        => $row->name,
                    'strength'    => $row->strength,
                    'dose'        => $row->dose,
                    'morning'     => $row->morning,
                    'noon'        => $row->noon,
                    'night'       => $row->night,
                    'route'       => $row->route,
                    'duration'    => $row->duration,
                    'timing'      => $row->timing,
                    'instruction' => $row->instruction,
                    'order_type'  => $order_type,
                    'active'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
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
    $request->validate(['patientcode' => 'required', 'name' => 'required|string|max:255']);

	$admission = $this->getActiveAdmission($request->patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $data = [
        'patientid'   => $request->patientcode,
        'admissionid' => $admissionid,
        'templeteid'  => $request->templeteid,
        'name'        => $request->name,
        'strength'    => $request->strength,
        'dose'        => $request->dose,
        'morning'     => $request->morning,
        'noon'        => $request->noon,
        'night'       => $request->night,
        'route'       => $request->route,
        'duration'    => $request->duration,
        'timing'      => $request->timing,
        'instruction' => $request->instruction,
        'order_type'  => $request->order_type ?? 'fresh',
        'active'      => 1,
        'updated_at'  => now(),
    ];

    if ($request->id) {
        DB::table('patient_fresh_medicines')->where('id', $request->id)->update($data);
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    $exists = DB::table('patient_fresh_medicines')
        ->where('patientid', $request->patientcode)
        ->where('admissionid', $admissionid)
        ->where('name', $request->name)
        ->where('order_type', $request->order_type ?? 'fresh')
        ->exists();

    if ($exists) {
        return response()->json(['success' => false, 'message' => 'This medicine already exists.']);
    }

    $data['created_at'] = now();
    DB::table('patient_fresh_medicines')->insert($data);
    return response()->json(['success' => true, 'message' => 'Medicine added successfully']);
}

public function freshmedicineList(Request $request)
{
    $patientcode = $request->patientcode;

	$admission = $this->getActiveAdmission($patientcode);
	$admissionid = $admission->admissionid ?? 59;

    $medicines = DB::table('patient_fresh_medicines')
        ->where('patientid', $patientcode)
        ->where('admissionid', $admissionid)
        ->where('active', 1)
        ->orderBy('id', 'asc')
        ->get();
    return view('admission.freshmedicine.freshmedicine_rows', compact('medicines'))->render();
}

public function freshmedicineDelete(Request $request)
{
    $id = $request->id;
    $deleted = DB::table('patient_fresh_medicines')->where('id', $id)->delete();
    if ($deleted) {
        return response()->json(['success' => true, 'message' => 'Medicine deleted successfully']);
    }
    return response()->json(['success' => false, 'message' => 'Medicine not found']);
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
    $request->validate(['patientcode' => 'required|string']);

    $admission = $this->getActiveAdmission($request->patientcode);

    if (!$admission) {
        return response()->json(['success' => false, 'message' => 'Active admission not found.'], 422);
    }

    $currentStatus = (int)$admission->status;
    $nextStatus = $currentStatus + 1;

    if ($nextStatus > 6) {
        return response()->json(['success' => false, 'message' => 'Already at final stage (Release).']);
    }

    DB::table('patient_admission')
        ->where('id', $admission->id)
        ->update(['status' => $nextStatus, 'updated_at' => now()]);

    $stageNames = [
        1 => 'Admit Medicine',
        2 => 'Pre Surgery',
        3 => 'Post Surgery',
        4 => 'Round Patient',
        5 => 'Fresh Prescription',
        6 => 'Release Patient',
    ];

    return response()->json([
        'success' => true,
        'message' => 'Moved to stage: ' . ($stageNames[$nextStatus] ?? $nextStatus),
        'current_status' => $nextStatus,
        'stage_name' => $stageNames[$nextStatus] ?? $nextStatus,
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

    $patientcode = $request->query('patientcode') ?? $request->input('patientcode');

    // Default to the first patient if none specified
    if (empty($patientcode) && $patients->isNotEmpty()) {
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
    $savedMedicines = collect();
    $commonMedicines = Schema::hasTable('common_medicine')
        ? DB::table('common_medicine')->orderBy('name')->get()
        : collect();

    if ($patientcode) {
        $admission = DB::table('patient_admission')
            ->where('patientcode', $patientcode)
            ->where('status', 6)
            ->where('Active', 1)
            ->orderByDesc('id')
            ->first();

        if ($admission) {
            $patient = DB::table('patients')->where('patientcode', $patientcode)->first();
			
			$dischargeDoctor = null;
			if(schema ::hastable('doctors')&& !empty($admission->doctor_id)){
				$dischargeDoctor =DB::table('doctors')
				->where('id',$admission->doctor_id)
			->first();
			}
			
			$dischargeBed =null;
			if(schema ::hastable ('beds')&& !empty($admission->id)){
				$dischargeBed =DB::table('beds')
				->where('current_admission_id',$admission->id)
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
    }

    return view('admission.discharge.discharge_list', compact(
        'activeMenu', 'patients', 'patientcode', 'patient', 'admission',
        'admitMedicines', 'preMedicines', 'postMedicines', 'roundMedicines', 'freshMedicines',
        'templateDischarge', 'discharge', 'savedMedicines', 'commonMedicines','dischargeDoctor',
		'dischargeBed'
    ));
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
        DB::table('discharges')->updateOrInsert(
            ['admission_id' => $admission->id],
            [
                'patient_id'        => $patient->id,
                'discharge_date'    => $request->discharge_date,
                'diagnosis'         => $request->diagnosis,
                'doctor_name'       => $request->doctor_name,
                'ward'              => $request->ward,
                'reg_no'            => $request->reg_no,
                'operation_name'    => $request->operation_name,
                'operation_method'  => $request->operation_method,
                'surgeon'           => $request->surgeon,
                'anesthetist'       => $request->anesthetist,
                'assistant_1'       => $request->assistant_1,
                'assistant_2'       => $request->assistant_2,
                'investigations'    => $request->investigations,
                'blood_group'       => $request->blood_group,
                'drug_allergy'      => $request->drug_allergy,
                'notes'             => $request->notes,
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
            'patientcode' => $patient->patientcode,
            'patient_id' => $patient->id,
            'admission_id' => $admission->id,
            'medicine_name' => $name,
            'name' => $name,
            'strength' => $medicine['strength'] ?? null,
            'dose' => $medicine['dose'] ?? null,
            'morning' => $medicine['morning'] ?? null,
            'noon' => $medicine['noon'] ?? null,
            'night' => $medicine['night'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
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

}