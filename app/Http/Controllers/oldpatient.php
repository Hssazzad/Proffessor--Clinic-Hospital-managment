<?php

namespace App\Http\Controllers;

use App\Models\Anesthesiologist;
use App\Models\AssistantSurgeon;
use App\Models\Doctor;
use App\Models\OperationName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class oldpatient extends Controller
{
    public function oldpatient(Request $request, PatientAdmitController $dischargeController)
    {
        return $dischargeController->dischargeHistory($request);
    }

    public function view(Request $request, int $admission)
    {
        $admission = DB::table('patient_admission')->where('id', $admission)->where('status', 6)->first();
        abort_unless($admission, 404, 'Discharge record not found.');

        $patient = DB::table('patients')->where('patientcode', $admission->patientcode)->first();
        $discharge = DB::table('discharges')->where('admission_id', $admission->id)->orderByDesc('id')->first();
        $savedMedicines = collect();

        if (Schema::hasTable('discharge_medicines')) {
            $medicineQuery = DB::table('discharge_medicines');
            $columns = Schema::getColumnListing('discharge_medicines');
            in_array('admission_id', $columns, true)
                ? $medicineQuery->where('admission_id', $admission->id)
                : $medicineQuery->where('patientcode', $admission->patientcode);
            $savedMedicines = $medicineQuery->orderBy('id')->get();
        }

        $operationNames = OperationName::where('active', 1)->orderBy('name')->get();
        $surgeons = Doctor::where('active', 1)->orderBy('doctor_name')->get();
        $anesthesiologists = Anesthesiologist::where('active', 1)->orderBy('name')->get();
        $assistantSurgeons = AssistantSurgeon::where('active', 1)->orderBy('name')->get();

        return view('oldpatient.discharge_show', compact(
            'patient', 'admission', 'discharge', 'savedMedicines',
            'operationNames', 'surgeons', 'anesthesiologists', 'assistantSurgeons'
        ));
    }

    /**
     * Admission patient list audit report.
     * Lists all patient admissions with patient name, bed, doctor, department
     * and status. Left joins patients and beds so an admission is never dropped
     * if its master/bed row is transiently missing.
     */
    public function admissionList(Request $request)
    {
        $stageNames = [
            1 => 'Admit Medicine',
            2 => 'Pre Surgery',
            3 => 'Post Surgery',
            4 => 'Round Patient',
            5 => 'Fresh Prescription',
            6 => 'Release Patient',
        ];

        $query = DB::table('patient_admission as pa')
            ->leftJoin('patients as p', 'p.patientcode', '=', 'pa.patientcode')
            ->leftJoin('beds as b', 'b.current_admission_id', '=', 'pa.id')
            ->select(
                'pa.id',
                'pa.patientcode',
                'p.patientname as patientname',
                'b.bed_number as bed_no',
                'pa.doctor_name',
                'pa.department',
                'pa.status',
                'pa.admissionid',
                'pa.created_at'
            );

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('p.patientname', 'like', "%{$search}%")
                  ->orWhere('pa.patientcode', 'like', "%{$search}%")
                  ->orWhere('pa.admissionid', 'like', "%{$search}%")
                  ->orWhere('pa.doctor_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('pa.status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('pa.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('pa.created_at', '<=', $request->date_to);
        }

        $admissions = $query->orderByDesc('pa.id')
            ->paginate(25)
            ->withQueryString();

        return view('oldpatient.admissionlist', compact('admissions', 'stageNames'));
    }
}
