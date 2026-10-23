<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\Patient;

class CesPresStepsController extends Controller
{
    public function show(Request $r)
    {
        $tab       = $r->query('tab', 'patients');
        $pid       = $r->query('id');
        $patientId = $r->query('patient');

        $patient = null;
        $rx = null;

        if ($patientId) {
            $patient = DB::table('patients')->where('id', $patientId)->first();
        } elseif ($pid) {
            $patient = DB::table('patients')
                ->join('prescriptions', 'prescriptions.patient_id', '=', 'patients.id')
                ->where('prescriptions.id', $pid)
                ->select('patients.*')
                ->first();
        }

        if ($pid && Schema::hasTable('prescriptions')) {
            $rx = DB::table('prescriptions')->find($pid);
        }

        $patients = collect();
        if (Schema::hasTable('patients')) {
            $patients = DB::table('patients')
                ->orderBy('patientname')
                ->get(['id', 'patientname', 'mobile_no', 'patientcode', 'gender', 'age', 'blood_group', 'address', 'nid_number']);
        }

        $investigations = collect();
        if ($pid && Schema::hasTable('prescriptions_investigations')) {
            $investigations = DB::table('prescriptions_investigations')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get(['id', 'name', 'note']);
        }

        $diagnoses = collect();
        if ($pid && Schema::hasTable('prescriptions_diagnosis')) {
            $diagnoses = DB::table('prescriptions_diagnosis')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get(['id', 'name', 'note']);
        }

        $medicines = collect();
        if ($pid && Schema::hasTable('prescriptions_medicine')) {
            $medicines = DB::table('prescriptions_medicine')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get(['id', 'name', 'strength', 'dose', 'route', 'frequency', 'duration', 'timing', 'note']);
        }

        $complaints = collect();
        if ($pid && Schema::hasTable('prescriptions_complain')) {
            $complaints = DB::table('prescriptions_complain')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get(['id', 'complaint', 'note']);
        }

        $commonInvestigations = collect();
        if (Schema::hasTable('common_investigation')) {
            $q = DB::table('common_investigation')->orderBy('name');
            if (Schema::hasColumn('common_investigation', 'active')) $q->where('active', 1);
            $commonInvestigations = $q->get();
        }

        $commonDiagnoses = collect();
        if (Schema::hasTable('common_diagnosis')) {
            $q = DB::table('common_diagnosis')->orderBy('name');
            if (Schema::hasColumn('common_diagnosis', 'active')) $q->where('active', 1);
            $commonDiagnoses = $q->get();
        }

        $commonMedicines = collect();
        if (Schema::hasTable('common_medicine')) {
            $q = DB::table('common_medicine')->orderBy('name');
            if (Schema::hasColumn('common_medicine', 'active')) $q->where('active', 1);
            $commonMedicines = $q->get();
        }

        $commoncomplain = collect();
        if (Schema::hasTable('common_complain')) {
            $q = DB::table('common_complain')->orderBy('name');
            if (Schema::hasColumn('common_complain', 'active')) $q->where('active', 1);
            $commoncomplain = $q->get();
        }

        // ===== O/E List (patient_pre_assessments থেকে) =====
   // ===== O/E List (preconassessment থেকে) =====
$oeList = collect();
if ($patient && !empty($patient->patientcode) && Schema::hasTable('preconassessment')) {
    $oeList = DB::table('preconassessment')
        ->where('patientcode', $patient->patientcode)
        ->orderByDesc('id')
        ->limit(20)
        ->get();
}

        return view('Cesprescriptions.wizard-master', compact(
            'tab', 'pid', 'patientId', 'patient', 'rx',
            'patients', 'commoncomplain',
            'complaints', 'investigations', 'diagnoses', 'medicines',
            'commonInvestigations', 'commonDiagnoses', 'commonMedicines',
            'oeList'
        ));
    }

    public function show1(Request $r)
    {
        $tab       = $r->query('tab', 'patients');
        $pid       = $r->query('id');
        $patientId = $r->query('patient');

        $patient = null;
        $rx = null;

        if ($patientId) {
            $patient = DB::table('patients')->where('id', $patientId)->first();
        } elseif ($pid) {
            $patient = DB::table('patients')
                ->join('prescriptions', 'prescriptions.patient_id', '=', 'patients.id')
                ->where('prescriptions.id', $pid)
                ->select('patients.*')
                ->first();
        }

        if ($pid && Schema::hasTable('prescriptions')) {
            $rx = DB::table('prescriptions')->find($pid);
        }

        $patients = collect();
        if (Schema::hasTable('patients')) {
            $patients = DB::table('patients')
                ->orderBy('patientname')
                ->get(['id', 'patientname', 'mobile_no', 'patientcode']);
        }

        $investigations = collect();
        if ($pid && Schema::hasTable('prescriptions_investigations')) {
            $investigations = DB::table('prescriptions_investigations')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get();
        }

        $diagnoses = collect();
        if ($pid && Schema::hasTable('prescription_diagnoses')) {
            $diagnoses = DB::table('prescription_diagnoses')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get();
        }

        $medicines = collect();
        if ($pid && Schema::hasTable('prescription_medicines')) {
            $medicines = DB::table('prescription_medicines')
                ->where('prescription_id', $pid)
                ->orderBy('id')
                ->get();
        }

        $commonInvestigations = collect();
        if (Schema::hasTable('common_investigation')) {
            $q = DB::table('common_investigation')->orderBy('name');
            if (Schema::hasColumn('common_investigation', 'active')) $q->where('active', 1);
            $commonInvestigations = $q->get();
        }

        $commonDiagnoses = collect();
        if (Schema::hasTable('common_diagnosis')) {
            $q = DB::table('common_diagnosis')->orderBy('name');
            if (Schema::hasColumn('common_diagnosis', 'active')) $q->where('active', 1);
            $commonDiagnoses = $q->get();
        }

        $commonMedicines = collect();
        if (Schema::hasTable('common_medicine')) {
            $q = DB::table('common_medicine')->orderBy('name');
            if (Schema::hasColumn('common_medicine', 'active')) $q->where('active', 1);
            $commonMedicines = $q->get();
        }

        $commoncomplain = collect();
        if (Schema::hasTable('common_complain')) {
            $q = DB::table('common_complain')->orderBy('name');
            if (Schema::hasColumn('common_complain', 'active')) $q->where('active', 1);
            $commoncomplain = $q->get();
        }

        // O/E List
        $oeList = collect();
        if ($patient && !empty($patient->patientcode) && Schema::hasTable('patient_pre_assessments')) {
            $oeList = DB::table('patient_pre_assessments')
                ->where('patientcode', $patient->patientcode)
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        return view('Cesprescriptions.wizard-master', compact(
            'tab', 'pid', 'patientId', 'patient', 'rx',
            'patients', 'commoncomplain',
            'investigations', 'diagnoses', 'medicines',
            'commonInvestigations', 'commonDiagnoses', 'commonMedicines',
            'oeList'
        ));
    }

    // ------- Helpers -------
    private function nextTab(string $current): string
    {
        $order = ['patients', 'complain', 'oe', 'investigations', 'diagnosis', 'medicine', 'preview'];
        $i = array_search($current, $order, true);
        return $order[min($i + 1, count($order) - 1)] ?? 'preview';
    }

    private function makePatientCode(): string
    {
        $prefix = 'PT-' . now()->format('Ym') . '-';
        $last = DB::table('patients')
            ->where('patientcode', 'like', $prefix . '%')
            ->orderByDesc('patientcode')
            ->value('patientcode');
        $n = 1;
        if ($last && preg_match('/-(\d{4})$/', $last, $m)) $n = intval($m[1]) + 1;
        return $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    private function go(Request $r, string $fromTab, int $pid, ?int $patientId = null)
    {
        $to = $r->input('next', $this->nextTab($fromTab));
        return redirect()->route('rx.wizard', [
            'id'      => $pid,
            'patient' => $patientId,
            'tab'     => $to,
        ])->with('success', 'Saved.');
    }

    // ------- TAB 1: Patients -------
    public function savePatient1(Request $r)
    {
        $v = $r->validate([
            'prescription_id' => 'nullable|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'patientcode'     => 'nullable|string|max:30',
            'patientname'     => 'required|string|max:150',
            'mobile_no'       => 'nullable|string|max:20',
            'nid_number'      => 'nullable|string|max:30',
            'gender'          => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'age'             => 'nullable|integer|min:0|max:200',
            'date_of_birth'   => 'nullable|date',
            'address'         => 'nullable|string|max:255',
            'blood_group'     => 'nullable|string|max:5',
            'notes'           => 'nullable|string',
            'patientfather'   => 'nullable|string|max:70',
            'patienthusband'  => 'nullable|string|max:70',
            'contact_no'      => 'nullable|string|max:20',
            'email'           => 'nullable|string|max:120',
        ]);

        $now = now();
        $patientId = $v['patient_id'] ?? null;
        $code = $v['patientcode'] ?: $this->makePatientCode();

        if ($patientId) {
            DB::table('patients')->where('id', $patientId)->update([
                'patientcode' => $code, 'patientname' => $v['patientname'],
                'patientfather' => $v['patientfather'] ?? null, 'patienthusband' => $v['patienthusband'] ?? null,
                'address' => $v['address'] ?? null, 'age' => $v['age'] ?? null, 'date_of_birth' => $v['date_of_birth'] ?? null,
                'mobile_no' => $v['mobile_no'] ?? null, 'contact_no' => $v['contact_no'] ?? null, 'nid_number' => $v['nid_number'] ?? null,
                'email' => $v['email'] ?? null, 'gender' => $v['gender'] ?? null, 'blood_group' => $v['blood_group'] ?? null,
                'notes' => $v['notes'] ?? null, 'updated_at' => $now,
            ]);
        } else {
            $patientId = DB::table('patients')->insertGetId([
                'patientcode' => $code, 'patientname' => $v['patientname'],
                'patientfather' => $v['patientfather'] ?? null, 'patienthusband' => $v['patienthusband'] ?? null,
                'address' => $v['address'] ?? null, 'age' => $v['age'] ?? null, 'date_of_birth' => $v['date_of_birth'] ?? null,
                'mobile_no' => $v['mobile_no'] ?? null, 'contact_no' => $v['contact_no'] ?? null, 'nid_number' => $v['nid_number'] ?? null,
                'email' => $v['email'] ?? null, 'gender' => $v['gender'] ?? null, 'blood_group' => $v['blood_group'] ?? null,
                'notes' => $v['notes'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $pid = $v['prescription_id'] ?? null;
        if (!$pid) {
            $tmpNo = 'TMP-' . $now->timestamp;
            $pid = DB::table('prescriptions')->insertGetId([
                'patient_id'      => $patientId,
                'prescription_no' => $tmpNo,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        } else {
            DB::table('prescriptions')->where('id', $pid)->update([
                'patient_id' => $patientId, 'updated_at' => $now
            ]);
        }

        return $this->go($r, 'patients', $pid, $patientId);
    }

    public function savePatient(Request $r)
    {
        $v = $r->validate([
            'prescription_id'          => 'nullable|integer|exists:prescriptions,id',
            'patient_id'               => 'required|integer|exists:patients,id',
            'previous_prescription_id' => 'nullable|integer|exists:prescriptions,id',
            'next'                     => 'nullable|string|in:patients,complain,oe,investigations,diagnosis,medicine,preview',
        ]);

        $now       = now();
        $patientId = (int) $v['patient_id'];
        $pid       = $v['prescription_id'] ?? null;
        $prevRxId  = $v['previous_prescription_id'] ?? null;

        $patientCode = DB::table('patients')->where('id', $patientId)->value('patientcode');

        if ($prevRxId) {
            $newPid = null;

            DB::transaction(function () use ($patientId, $prevRxId, $patientCode, $now, &$newPid) {
                $tmpNo = 'TMP-' . $now->timestamp;
                $newPid = DB::table('prescriptions')->insertGetId([
                    'patient_id'      => $patientId,
                    'prescription_no' => $tmpNo,
                    'advices'         => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                $prevRx = DB::table('prescriptions')->where('id', $prevRxId)->first();
                if ($prevRx && !empty($prevRx->advices)) {
                    DB::table('prescriptions')->where('id', $newPid)->update(['advices' => $prevRx->advices]);
                }

                $prevComplains = DB::table('prescriptions_complain')
                    ->where('prescription_id', $prevRxId)->where('active', 1)->get();
                foreach ($prevComplains as $c) {
                    DB::table('prescriptions_complain')->insert([
                        'patientcode' => $patientCode ?? $c->patientcode,
                        'prescription_id' => $newPid,
                        'complaint' => $c->complaint, 'note' => $c->note, 'name' => $c->name,
                        'active' => 1, 'name_normalized' => $c->name_normalized,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                $prevDiagnosis = DB::table('prescriptions_diagnosis')
                    ->where('prescription_id', $prevRxId)->where('active', 1)->get();
                foreach ($prevDiagnosis as $d) {
                    DB::table('prescriptions_diagnosis')->insert([
                        'patientcode' => $patientCode ?? $d->patientcode,
                        'prescription_id' => $newPid,
                        'name' => $d->name, 'note' => $d->note, 'active' => 1,
                        'name_normalized' => $d->name_normalized,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                $prevInv = DB::table('prescriptions_investigations')
                    ->where('prescription_id', (string)$prevRxId)->get();
                foreach ($prevInv as $i) {
                    DB::table('prescriptions_investigations')->insert([
                        'prescription_id' => (string)$newPid,
                        'investigation_id' => $i->investigation_id,
                        'name' => $i->name, 'note' => $i->note,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                $prevMeds = DB::table('prescriptions_medicine')->where('prescription_id', $prevRxId)->get();
                foreach ($prevMeds as $m) {
                    DB::table('prescriptions_medicine')->insert([
                        'prescription_id' => $newPid,
                        'medicine_name' => $m->medicine_name, 'name' => $m->name,
                        'strength' => $m->strength, 'dose' => $m->dose, 'route' => $m->route,
                        'frequency' => $m->frequency, 'duration' => $m->duration,
                        'timing' => $m->timing, 'note' => $m->note,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }

                DB::table('prescriptions')->where('id', $prevRxId)->update(['status' => 0]);
            });

            $nextTab = $r->input('next', 'complain');
            return $this->go($r, $nextTab, $newPid, $patientId)
                ->with('success', "Previous prescription loaded into NEW RX #{$newPid}.");
        }

        if ($pid) {
            DB::table('prescriptions')->where('id', $pid)->update([
                'patient_id' => $patientId, 'updated_at' => $now,
            ]);
        } else {
            $tmpNo = 'TMP-' . $now->timestamp;
            $pid = DB::table('prescriptions')->insertGetId([
                'patient_id' => $patientId, 'prescription_no' => $tmpNo,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $nextTab = $r->input('next', 'complain');
        return $this->go($r, $nextTab, $pid, $patientId);
    }

    public function saveComplain(Request $r)
    {
        $v = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'required|integer|exists:patients,id',
            'chief_complaint' => 'nullable|string|max:1000',
            'next'            => 'nullable|string',
        ]);

        $pid = (int) $v['prescription_id'];
        $patientId = (int) $v['patient_id'];
        $now = now();

        if (Schema::hasColumn('prescriptions', 'chief_complaint')) {
            DB::table('prescriptions')->where('id', $pid)->update([
                'chief_complaint' => $v['chief_complaint'] ?? null,
                'updated_at' => $now,
            ]);
        } elseif (Schema::hasTable('prescriptions_complain')) {
            $exists = DB::table('prescriptions_complain')->where('prescription_id', $pid)->exists();
            if ($exists) {
                DB::table('prescriptions_complain')->where('prescription_id', $pid)->update([
                    'complaint' => $v['chief_complaint'] ?? null, 'updated_at' => $now,
                ]);
            } else {
                DB::table('prescriptions_complain')->insert([
                    'prescription_id' => $pid,
                    'complaint' => $v['chief_complaint'] ?? null,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // ✅ Complain এর পর O/E ট্যাবে যাবে
        $nextTab = $r->input('next', 'oe');

        return $this->go($r, $nextTab, $pid, $patientId);
    }

    // ✅ O/E Save Method
    public function saveOe(Request $r)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'required|integer|exists:patients,id',
            'nursing_id'      => 'nullable|integer',
            'pulse'           => 'nullable|string|max:50',
            'bp'              => 'nullable|string|max:50',
            'temp'            => 'nullable|string|max:50',
            'weight'          => 'nullable|string|max:50',
            'next'            => 'nullable|string',
        ]);

        DB::table('prescriptions')->where('id', $data['prescription_id'])->update([
            'oe_pulse'      => $data['pulse'] ?? null,
            'oe_bp'         => $data['bp'] ?? null,
            'oe_temp'       => $data['temp'] ?? null,
            'oe_weight'     => $data['weight'] ?? null,
            'oe_nursing_id' => $data['nursing_id'] ?? null,
            'updated_at'    => now(),
        ]);

        return $this->go($r, 'oe', (int)$data['prescription_id'], $data['patient_id']);
    }

    // ------- Investigations -------
    public function storeInvestigation(Request $r)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'note'            => 'nullable|string|max:255',
        ]);

        DB::table('prescriptions_investigations')->insert([
            'prescription_id' => $data['prescription_id'],
            'name' => $data['name'], 'note' => $data['note'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('rx.wizard', [
            'id' => $data['prescription_id'],
            'patient' => $data['patient_id'] ?? null,
            'tab' => 'investigations',
        ])->with('success', 'Investigation added.');
    }

    public function updateInvestigation(Request $r, int $invId)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'note'            => 'nullable|string|max:255',
        ]);

        $ok = DB::table('prescriptions_investigations')
            ->where('id', $invId)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_investigations')->where('id', $invId)->update([
            'name' => $data['name'], 'note' => $data['note'] ?? null, 'updated_at' => now(),
        ]);

        return redirect()->route('rx.wizard', [
            'id' => $data['prescription_id'],
            'patient' => $data['patient_id'] ?? null,
            'tab' => 'investigations',
        ])->with('success', 'Investigation updated.');
    }

    public function destroyInvestigation(Request $r, int $invId)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
        ]);

        $ok = DB::table('prescriptions_investigations')
            ->where('id', $invId)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_investigations')->where('id', $invId)->delete();

        return redirect()->route('rx.wizard', [
            'id' => $data['prescription_id'],
            'patient' => $data['patient_id'] ?? null,
            'tab' => 'investigations',
        ])->with('success', 'Investigation deleted.');
    }

    // ------- Diagnosis -------
    public function storeDiagnosis(Request $r)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'note'            => 'nullable|string|max:255',
            'next'            => 'nullable|string',
        ]);

        DB::table('prescriptions_diagnosis')->insert([
            'prescription_id' => $data['prescription_id'],
            'name' => $data['name'], 'note' => $data['note'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $this->go($r, 'diagnosis', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function updateDiagnosis(Request $r, int $diag)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'note'            => 'nullable|string|max:255',
            'next'            => 'nullable|string',
        ]);

        $ok = DB::table('prescriptions_diagnosis')
            ->where('id', $diag)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_diagnosis')->where('id', $diag)->update([
            'name' => $data['name'], 'note' => $data['note'] ?? null, 'updated_at' => now(),
        ]);

        return $this->go($r, 'diagnosis', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function destroyDiagnosis(Request $r, int $diag)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
        ]);

        $ok = DB::table('prescriptions_diagnosis')
            ->where('id', $diag)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_diagnosis')->where('id', $diag)->delete();

        return redirect()->route('rx.wizard', [
            'id' => $data['prescription_id'], 'patient' => $data['patient_id'] ?? null, 'tab' => 'diagnosis'
        ])->with('success', 'Deleted.');
    }

    // ------- Medicine -------
    public function storeMedicine(Request $r)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'strength'        => 'nullable|string|max:80',
            'dose'            => 'nullable|string|max:80',
            'route'           => 'nullable|string|max:80',
            'frequency'       => 'nullable|string|max:80',
            'duration'        => 'nullable|string|max:80',
            'timing'          => 'nullable|string|max:80',
            'note'            => 'nullable|string|max:255',
            'next'            => 'nullable|string',
        ]);

        DB::table('prescriptions_medicine')->insert([
            'prescription_id' => $data['prescription_id'],
            'name' => $data['name'],
            'strength' => $data['strength'] ?? null, 'dose' => $data['dose'] ?? null,
            'route' => $data['route'] ?? null, 'frequency' => $data['frequency'] ?? null,
            'duration' => $data['duration'] ?? null, 'timing' => $data['timing'] ?? null,
            'note' => $data['note'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $this->go($r, 'medicine', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function updateMedicine(Request $r, int $med)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'strength'        => 'nullable|string|max:80',
            'dose'            => 'nullable|string|max:80',
            'route'           => 'nullable|string|max:80',
            'frequency'       => 'nullable|string|max:80',
            'duration'        => 'nullable|string|max:80',
            'timing'          => 'nullable|string|max:80',
            'note'            => 'nullable|string|max:255',
            'next'            => 'nullable|string',
        ]);

        $ok = DB::table('prescriptions_medicine')
            ->where('id', $med)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_medicine')->where('id', $med)->update([
            'name' => $data['name'],
            'strength' => $data['strength'] ?? null, 'dose' => $data['dose'] ?? null,
            'route' => $data['route'] ?? null, 'frequency' => $data['frequency'] ?? null,
            'duration' => $data['duration'] ?? null, 'timing' => $data['timing'] ?? null,
            'note' => $data['note'] ?? null, 'updated_at' => now(),
        ]);

        return $this->go($r, 'medicine', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function destroyMedicine(Request $r, int $med)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
        ]);

        $ok = DB::table('prescriptions_medicine')
            ->where('id', $med)->where('prescription_id', $data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescriptions_medicine')->where('id', $med)->delete();

        return redirect()->route('rx.wizard', [
            'id' => $data['prescription_id'], 'patient' => $data['patient_id'] ?? null, 'tab' => 'medicine'
        ])->with('success', 'Deleted.');
    }

    public function ajaxInvestigation(Request $r)
    {
        if (!Schema::hasTable('prescriptions_investigations')) {
            return response()->json(['ok' => false, 'error' => 'Table prescriptions_investigations not found'], 500);
        }

        $action = $r->string('action')->toString();
        $r->validate(['prescription_id' => 'required|integer|exists:prescriptions,id']);
        $pid = (int) $r->prescription_id;

        try {
            switch ($action) {
                case 'add':
                    $r->validate(['name' => 'required|string|max:150', 'note' => 'nullable|string|max:255']);
                    $id = DB::table('prescriptions_investigations')->insertGetId([
                        'prescription_id' => $pid, 'name' => $r->name, 'note' => $r->note ?? null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true, 'id' => $id]);

                case 'update':
                    $r->validate(['id' => 'required|integer', 'name' => 'required|string|max:150', 'note' => 'nullable|string|max:255']);
                    $exists = DB::table('prescriptions_investigations')->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table('prescriptions_investigations')->where('id', $r->id)->update([
                        'name' => $r->name, 'note' => $r->note ?? null, 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true]);

                case 'delete':
                    $r->validate(['id' => 'required|integer']);
                    $exists = DB::table('prescriptions_investigations')->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table('prescriptions_investigations')->where('id', $r->id)->delete();
                    return response()->json(['ok' => true]);

                case 'list':
                default:
                    $rows = DB::table('prescriptions_investigations')
                        ->select('id', 'name', 'note')
                        ->where('prescription_id', $pid)->orderByDesc('id')->get();
                    return response()->json(['ok' => true, 'rows' => $rows]);
            }
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function diagTable(): ?string
    {
        if (Schema::hasTable('prescriptions_diagnosis')) return 'prescriptions_diagnosis';
        if (Schema::hasTable('prescription_diagnoses')) return 'prescription_diagnoses';
        return null;
    }

    public function ajaxDiagnosis(Request $r)
    {
        $tbl = $this->diagTable();
        if (!$tbl) {
            return response()->json(['ok' => false, 'error' => 'Diagnosis table not found'], 500);
        }

        $r->validate(['prescription_id' => 'required|integer|exists:prescriptions,id']);
        $pid = (int) $r->prescription_id;
        $action = (string) $r->input('action', 'list');

        try {
            switch ($action) {
                case 'add':
                    $r->validate(['name' => 'required|string|max:150', 'note' => 'nullable|string|max:255']);
                    $id = DB::table($tbl)->insertGetId([
                        'prescription_id' => $pid, 'name' => $r->name, 'note' => $r->note ?? null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true, 'id' => $id]);

                case 'update':
                    $r->validate(['id' => 'required|integer', 'name' => 'required|string|max:150', 'note' => 'nullable|string|max:255']);
                    $exists = DB::table($tbl)->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table($tbl)->where('id', $r->id)->update([
                        'name' => $r->name, 'note' => $r->note ?? null, 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true]);

                case 'delete':
                    $r->validate(['id' => 'required|integer']);
                    $exists = DB::table($tbl)->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table($tbl)->where('id', $r->id)->delete();
                    return response()->json(['ok' => true]);

                case 'list':
                default:
                    $rows = DB::table($tbl)->select('id', 'name', 'note')
                        ->where('prescription_id', $pid)->orderByDesc('id')->get();
                    return response()->json(['ok' => true, 'rows' => $rows]);
            }
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function medTable(): ?string
    {
        if (Schema::hasTable('prescriptions_medicine')) return 'prescriptions_medicine';
        if (Schema::hasTable('prescription_medicines')) return 'prescription_medicines';
        return null;
    }

    public function ajaxMedicine(Request $r)
    {
        $tbl = $this->medTable();
        if (!$tbl) {
            return response()->json(['ok' => false, 'error' => 'Medicine table not found'], 500);
        }

        $r->validate(['prescription_id' => 'required|integer|exists:prescriptions,id']);
        $pid = (int) $r->prescription_id;
        $action = (string) $r->input('action', 'list');

        try {
            switch ($action) {
                case 'add':
                    $r->validate([
                        'name' => 'required|string|max:150', 'strength' => 'nullable|string|max:80',
                        'dose' => 'nullable|string|max:80', 'route' => 'nullable|string|max:80',
                        'frequency' => 'nullable|string|max:80', 'duration' => 'nullable|string|max:80',
                        'timing' => 'nullable|string|max:80', 'note' => 'nullable|string|max:255',
                    ]);
                    $id = DB::table($tbl)->insertGetId([
                        'prescription_id' => $pid, 'name' => $r->name,
                        'strength' => $r->strength ?: null, 'dose' => $r->dose ?: null,
                        'route' => $r->route ?: null, 'frequency' => $r->frequency ?: null,
                        'duration' => $r->duration ?: null, 'timing' => $r->timing ?: null,
                        'note' => $r->note ?: null, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true, 'id' => $id]);

                case 'update':
                    $r->validate([
                        'id' => 'required|integer', 'name' => 'required|string|max:150',
                        'strength' => 'nullable|string|max:80', 'dose' => 'nullable|string|max:80',
                        'route' => 'nullable|string|max:80', 'frequency' => 'nullable|string|max:80',
                        'duration' => 'nullable|string|max:80', 'timing' => 'nullable|string|max:80',
                        'note' => 'nullable|string|max:255',
                    ]);
                    $exists = DB::table($tbl)->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table($tbl)->where('id', $r->id)->update([
                        'name' => $r->name, 'strength' => $r->strength ?: null, 'dose' => $r->dose ?: null,
                        'route' => $r->route ?: null, 'frequency' => $r->frequency ?: null,
                        'duration' => $r->duration ?: null, 'timing' => $r->timing ?: null,
                        'note' => $r->note ?: null, 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true]);

                case 'delete':
                    $r->validate(['id' => 'required|integer']);
                    $exists = DB::table($tbl)->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table($tbl)->where('id', $r->id)->delete();
                    return response()->json(['ok' => true]);

                case 'list':
                default:
                    $rows = DB::table($tbl)
                        ->select('id', 'name', 'strength', 'dose', 'route', 'frequency', 'duration', 'timing', 'note')
                        ->where('prescription_id', $pid)->orderByDesc('id')->get();
                    return response()->json(['ok' => true, 'rows' => $rows]);
            }
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function finishNew(Request $r)
    {
        $data = $r->validate([
            'same_patient' => 'nullable|boolean',
            'patient_id'   => 'nullable|integer|exists:patients,id',
        ]);

        $now = now();
        $patientId = $r->patient_id;

        if (!empty($data['same_patient']) && !empty($data['patient_id'])) {
            $patientId = (int) $data['patient_id'];
        }

        if ($patientId) {
            $prefix = 'RX-' . now()->format('Ymd') . '-';
            $lastRx = DB::table('prescriptions')
                ->where('prescription_no', 'like', $prefix . '%')
                ->orderByDesc('prescription_no')
                ->value('prescription_no');
            $nextNo = 1;
            if ($lastRx && preg_match('/-(\d{4})$/', $lastRx, $m)) {
                $nextNo = (int)$m[1] + 1;
            }
            $prescriptionNo = $prefix . str_pad((string)$nextNo, 4, '0', STR_PAD_LEFT);

            DB::table('prescriptions')
                ->where('patient_id', $patientId)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 0);
                })
                ->update([
                    'status' => 1,
                    'prescribed_on' => now()->toDateString(),
                    'prescription_no' => $prescriptionNo,
                    'updated_at' => $now,
                ]);
        }

        return redirect()->route('rx.wizard', [
            'patient' => $patientId,
            'tab' => 'patients',
        ])->with('success', 'Prescription completed. New prescription started.');
    }

    public function patientsSearchAjax(Request $request)
    {
        $term = trim($request->get('term', ''));
        if ($term === '') return response()->json([]);

        return Patient::where('patientname', 'like', "%{$term}%")
            ->orWhere('mobile_no', 'like', "%{$term}%")
            ->orWhere('patientcode', 'like', "%{$term}%")
            ->orderBy('patientname')
            ->limit(15)
            ->get(['id', 'patientname', 'mobile_no', 'patientcode']);
    }

    public function ajaxComplain(Request $r)
    {
        if (!Schema::hasTable('prescriptions_complain')) {
            return response()->json(['ok' => false, 'error' => 'Table prescriptions_complain not found'], 500);
        }

        $action = (string) $r->input('action', 'list');
        $r->validate(['prescription_id' => 'required|integer|exists:prescriptions,id']);
        $pid = (int) $r->prescription_id;

        try {
            switch ($action) {
                case 'add':
                    $r->validate(['complaint' => 'required|string|max:255', 'note' => 'nullable|string|max:255']);
                    $id = DB::table('prescriptions_complain')->insertGetId([
                        'prescription_id' => $pid, 'complaint' => $r->complaint, 'note' => $r->note ?: null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true, 'id' => $id]);

                case 'update':
                    $r->validate(['id' => 'required|integer', 'complaint' => 'required|string|max:255', 'note' => 'nullable|string|max:255']);
                    $exists = DB::table('prescriptions_complain')->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table('prescriptions_complain')->where('id', $r->id)->update([
                        'complaint' => $r->complaint, 'note' => $r->note ?: null, 'updated_at' => now(),
                    ]);
                    return response()->json(['ok' => true]);

                case 'delete':
                    $r->validate(['id' => 'required|integer']);
                    $exists = DB::table('prescriptions_complain')->where('id', $r->id)->where('prescription_id', $pid)->exists();
                    if (!$exists) return response()->json(['ok' => false, 'error' => 'Row not found'], 404);
                    DB::table('prescriptions_complain')->where('id', $r->id)->delete();
                    return response()->json(['ok' => true]);

                case 'list':
                default:
                    $rows = DB::table('prescriptions_complain')
                        ->select('id', 'complaint', 'note')
                        ->where('prescription_id', $pid)->orderByDesc('id')->get();
                    return response()->json(['ok' => true, 'rows' => $rows]);
            }
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function previousPrescriptions($patientId)
    {
        $list = DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get(['id', 'created_at']);

        return response()->json(['ok' => true, 'data' => $list]);
    }

    public function previousPrescriptionDetails($rxId)
    {
        $rx = DB::table('prescriptions')->where('id', $rxId)->first();
        if (!$rx) {
            return response()->json(['ok' => false, 'message' => 'Prescription not found'], 404);
        }

        $complains = DB::table('prescriptions_complain')
            ->where('prescription_id', $rxId)->where('active', 1)->orderBy('id')
            ->get(['id', 'complaint', 'note', 'name']);

        $diagnosis = DB::table('prescriptions_diagnosis')
            ->where('prescription_id', $rxId)->where('active', 1)->orderBy('id')
            ->get(['id', 'name', 'note']);

        $investigations = DB::table('prescriptions_investigations')
            ->where('prescription_id', $rxId)->orderBy('id')
            ->get(['id', 'name', 'note']);

        $medicines = DB::table('prescriptions_medicine')
            ->where('prescription_id', $rxId)->orderBy('id')
            ->get(['id', 'medicine_name', 'strength', 'dose', 'route', 'frequency', 'duration', 'timing', 'note']);

        return response()->json([
            'ok' => true,
            'data' => [
                'rx' => $rx,
                'complains' => $complains,
                'diagnosis' => $diagnosis,
                'investigations' => $investigations,
                'medicines' => $medicines,
            ]
        ]);
    }
}