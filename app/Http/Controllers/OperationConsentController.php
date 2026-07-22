<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OperationConsent;

class OperationConsentController extends Controller
{
    // Search / List admissions (to pick a patient)
    public function search(Request $request)
    {
        $query = DB::table('patient_admission as pa')
            ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
            ->select(
                'pa.id as admission_id',
                'pa.admissionid',
                'pa.doctor_name',
                'pa.created_at as admission_date',
                'p.patientname',
                'p.patientcode',
                'p.age',
                'p.gender',
                'p.mobile_no'
            )
            ->orderByDesc('pa.created_at');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('p.patientname', 'like', '%' . $request->q . '%')
                  ->orWhere('pa.admissionid', 'like', '%' . $request->q . '%')
                  ->orWhere('p.patientcode', 'like', '%' . $request->q . '%')
                  ->orWhere('p.mobile_no', 'like', '%' . $request->q . '%');
            });
        }

        $admissions = $query->limit(30)->get();

        return view('operation_consent.search', compact('admissions'));
    }

    // Consent form (create / edit) - auto-fill from patient + admission
    public function create($admission_id)
    {
        $admission = DB::table('patient_admission as pa')
            ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
            ->where('pa.id', $admission_id)
            ->select(
                'pa.id as admission_id',
                'pa.admissionid',
                'pa.doctor_name',
                'pa.doctor_reg_no',
                'pa.department',
                'pa.discharge_date',
                'pa.created_at as admission_date',
                'p.id as patient_id',
                'p.patientcode',
                'p.patientname',
                'p.patientfather',
                'p.patienthusband',
                'p.age',
                'p.gender',
                'p.blood_group',
                'p.mobile_no',
                'p.address',
                'p.village',
                'p.union',
                'p.upozila',
                'p.district'
            )
            ->first();

        if (!$admission) {
            return redirect()->route('operation.consent.search')
                ->with('error', 'Admission তথ্য পাওয়া যায়নি।');
        }

        $existing = OperationConsent::where('admission_id', $admission_id)->first();

        return view('operation_consent.create', compact('admission', 'existing'));
    }

    // Save consent
    public function store(Request $request)
    {
        $request->validate([
            'admission_id'   => 'required|integer',
            'patient_id'     => 'required|integer',
            'operation_name' => 'required|string|max:255',
            'operation_date' => 'required|date',
            'signatory_name' => 'nullable|string|max:255',
        ]);

        OperationConsent::where('admission_id', $request->admission_id)->delete();

        $consent = OperationConsent::create([
            'admission_id'          => $request->admission_id,
            'patient_id'            => $request->patient_id,
            'ward_no'               => $request->ward_no,
            'post_office'           => $request->post_office,
            'disease_name'          => $request->disease_name,
            'operation_name'        => $request->operation_name,
            'operation_date'        => $request->operation_date,
            'operation_time'        => $request->operation_time,
            'bed_no'                => $request->bed_no,
            'surgeon_name'          => $request->surgeon_name,
            'assistant_surgeon'     => $request->assistant_surgeon,
            'anesthesiologist'      => $request->anesthesiologist,
            'operation_note'        => $request->operation_note,
            'signatory_name'        => $request->signatory_name,
            'signatory_relation'    => $request->signatory_relation,
            'doctor_signature_name' => $request->doctor_signature_name,
            'created_by'            => auth()->id() ?? 1,
        ]);

        return redirect()->route('operation.consent.print', $consent->id)
            ->with('success', 'সম্মতি ফর্ম সংরক্ষিত হয়েছে।');
    }

    // Print
    public function print($id)
    {
        $consent = OperationConsent::findOrFail($id);

        $admission = DB::table('patient_admission as pa')
            ->join('patients as p', 'p.patientcode', '=', 'pa.patientcode')
            ->where('pa.id', $consent->admission_id)
            ->select(
                'pa.admissionid',
                'pa.doctor_name',
                'pa.doctor_reg_no',
                'pa.department',
                'pa.discharge_date',
                'pa.created_at as admission_date',
                'p.patientcode',
                'p.patientname',
                'p.patientfather',
                'p.patienthusband',
                'p.age',
                'p.gender',
                'p.blood_group',
                'p.mobile_no',
                'p.address',
                'p.village',
                'p.union',
                'p.upozila',
                'p.district'
            )
            ->first();

        return view('operation_consent.print', compact('consent', 'admission'));
    }

    // List all consents
    public function index()
    {
        $consents = DB::table('operation_consents as oc')
            ->join('patient_admission as pa', 'pa.id', '=', 'oc.admission_id')
            ->join('patients as p', 'p.id', '=', 'oc.patient_id')
            ->select(
                'oc.id',
                'oc.operation_name',
                'oc.operation_date',
                'oc.created_at',
                'pa.admissionid',
                'p.patientname',
                'p.patientcode',
                'p.age',
                'p.gender',
                'p.mobile_no'
            )
            ->orderByDesc('oc.created_at')
            ->paginate(20);

        return view('operation_consent.index', compact('consents'));
    }
}