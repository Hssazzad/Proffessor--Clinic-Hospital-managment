<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\OperationName;
use App\Models\Doctor;
use App\Models\Anesthesiologist;
use App\Models\AssistantSurgeon;

class AdmissionController extends Controller
{
    /**
     * Show the main discharge input view.
     */
    public function showDischargeMedicine(Request $request)
    {
        $patientcode = $request->input('patientcode') ?? $request->query('patientcode');
        
        $patient = DB::table('patients')->where('patientcode', $patientcode)->first();
        
        // Fetch the active or latest admission
        $admission = DB::table('admissions')
            ->where('patientcode', $patientcode)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$admission) {
            $admission = DB::table('patient_admission')
                ->where('patientcode', $patientcode)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        $discharge = null;
        $savedMedicines = collect();
        
        if ($admission) {
            $discharge = DB::table('discharges')
                ->where('admission_id', $admission->id)
                ->first();
            
            $savedMedicines = DB::table('discharge_medicines')
                ->where('patientcode', $patientcode)
                ->get();
        }
        
        $operationNames   = OperationName::where('active', 1)->orderBy('name', 'asc')->get();
        $surgeons         = Doctor::where('active', 1)->orderBy('doctor_name', 'asc')->get();
        $anesthesiologists = Anesthesiologist::where('active', 1)->orderBy('name', 'asc')->get();
        $assistantSurgeons = AssistantSurgeon::where('active', 1)->orderBy('name', 'asc')->get();
        
        return view('usermenu.discharge_show_medicine', compact('patient', 'admission', 'discharge', 'savedMedicines', 'patientcode', 'operationNames', 'surgeons', 'anesthesiologists', 'assistantSurgeons'));
    }

    /**
     * Save the draft of discharge notes and medicines.
     */
    public function saveDischargeDraft(Request $request)
    {
        $admission_id = $request->input('admission_id');
        $patientcode = $request->input('patientcode');
        
        if (empty($admission_id) || empty($patientcode)) {
            return response()->json(['success' => false, 'message' => 'Admission ID and Patient Code are required.'], 422);
        }

        $data = [
            'operation_name'   => $request->input('operation_name'),
            'operation_method' => $request->input('operation_method'),
            'surgeon'          => $request->input('surgeon'),
            'anesthetist'      => $request->input('anesthetist'),
            'assistant_1'      => $request->input('assistant_1'),
            'assistant_2'      => $request->input('assistant_2'),
            'diagnosis'        => $request->input('diagnosis'),
            'drug_allergy'     => $request->input('drug_allergy'),
            'investigations'   => $request->input('investigations'),
            'notes'            => $request->input('notes'),
            'updated_at'       => now(),
        ];
        
        $exists = DB::table('discharges')->where('admission_id', $admission_id)->exists();
        if ($exists) {
            DB::table('discharges')->where('admission_id', $admission_id)->update($data);
        } else {
            $data['admission_id'] = $admission_id;
            $data['created_at'] = now();
            DB::table('discharges')->insert($data);
        }
        
        // Clear and insert medicines
        DB::table('discharge_medicines')->where('patientcode', $patientcode)->delete();
        
        $medicines = $request->input('medicines', []);
        if (is_array($medicines) && count($medicines) > 0) {
            $insertData = [];
            foreach ($medicines as $med) {
                if (!empty($med['name'])) {
                    $insertData[] = [
                        'patientcode'   => $patientcode,
                        'medicine_name' => $med['name'],
                        'strength'      => $med['strength'] ?? null,
                        'dose'          => $med['dose'] ?? null,
                        'morning'       => $med['morning'] ?? '-',
                        'noon'          => $med['noon'] ?? '-',
                        'night'         => $med['night'] ?? '-',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }
            if (count($insertData) > 0) {
                DB::table('discharge_medicines')->insert($insertData);
            }
        }
        
        return response()->json(['success' => true, 'message' => 'Discharge records saved successfully.']);
    }

    /**
     * Approve the discharge by a manager.
     */
    public function approveByManager(Request $request)
    {
        $admission_id = $request->input('admission_id');
        
        if (empty($admission_id)) {
            return response()->json(['success' => false, 'message' => 'Admission ID is required.'], 422);
        }

        // Update both possible tables for robustness
        DB::table('admissions')->where('id', $admission_id)->update([
            'manager_approved' => 1,
            'updated_at'       => now(),
        ]);
        
        DB::table('patient_admission')->where('id', $admission_id)->update([
            'manager_approved' => 1,
            'updated_at'       => now(),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Discharge has been approved.']);
    }

    /**
     * Mark the patient discharge as completed.
     */
    public function doFinalDischarge(Request $request)
    {
        $admission_id = $request->input('admission_id');
        
        if (empty($admission_id)) {
            return response()->json(['success' => false, 'message' => 'Admission ID is required.'], 422);
        }

        DB::table('discharges')->where('admission_id', $admission_id)->update([
            'status'     => 'completed',
            'updated_at' => now(),
        ]);
        
        DB::table('admissions')->where('id', $admission_id)->update([
            'status'     => 'Discharged',
            'updated_at' => now(),
        ]);
        
        DB::table('patient_admission')->where('id', $admission_id)->update([
            'status'     => 6, // Stage 6 is Release Patient / Discharge
            'Active'     => 0,
            'discharge_date' => now(),
            'updated_at' => now(),
        ]);
        
        // Vacate bed if assigned
        DB::table('beds')->where('current_admission_id', $admission_id)->update([
            'status'               => 'vacant',
            'current_admission_id' => null,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Patient discharge finalized successfully.']);
    }

    /**
     * Render the 4-page print preview of the discharge slips.
     */
    public function printDischargeSlips($patientcode)
    {
        $patient = DB::table('patients')->where('patientcode', $patientcode)->first();
        
        $admission = DB::table('admissions')
            ->where('patientcode', $patientcode)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$admission) {
            $admission = DB::table('patient_admission')
                ->where('patientcode', $patientcode)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        $discharge = null;
        $savedMedicines = collect();
        
        if ($admission) {
            $discharge = DB::table('discharges')
                ->where('admission_id', $admission->id)
                ->first();
            
            $savedMedicines = DB::table('discharge_medicines')
                ->where('patientcode', $patientcode)
                ->get();
        }
        
        return view('usermenu.discharge_print', compact('patient', 'admission', 'discharge', 'savedMedicines'));
    }
}
