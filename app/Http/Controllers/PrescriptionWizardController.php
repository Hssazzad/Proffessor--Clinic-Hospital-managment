<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PrescriptionWizardController extends Controller
{



public function show(Request $request)
{
    // Handle draft cancellation
    if ($request->query('cancel') == 'y' && $request->query('patient')) {
        $this->cancelDraft((int) $request->query('patient'));
        return redirect()->to('prescriptions/wizard?tab=patients');
    }

    // Extract request parameters
    $tab = $request->query('tab', 'patients');
    $prescriptionId = $request->query('id');
    $patientId = $request->query('patient');

    // Get patient and prescription data
    $patient = $this->getPatient($patientId, $prescriptionId);
    $prescription = $this->getPrescription($prescriptionId);
    $vitals = $this->getVitals($patientId);
    $masterLists = $this->getMasterLists();
    
    // Check for existing draft
    $hasDraft = $this->hasIncompletePrescription($patientId);
    
    // Get prescription-related data
    $prescriptionData = $this->getPrescriptionData($prescriptionId);
    
    // Get patients list for dropdown
    $patients = $this->getPatientsList();
	
	
	// ========== O/E Master data coming  ==========
// ========== O/E Master Options ==========
$masterOptions = [];

if (Schema::hasTable('oe_master')) {
    $query = DB::table('oe_master');

    // active কলাম থাকলে ফিল্টার করো, না থাকলে সব নাও
    if (Schema::hasColumn('oe_master', 'active')) {
        $query->where(function($q) {
            $q->where('active', 1)->orWhereNull('active');
        });
    }

    // sort_order থাকলে সেই অনুযায়ী সাজাও
    if (Schema::hasColumn('oe_master', 'sort_order')) {
        $query->orderBy('sort_order');
    }

    $query->orderBy('id');

    $rows = $query->get();

    $masterOptions = $rows->map(function ($m) {
        return [
            'id'         => $m->id,
            'name'       => $m->name ?? '',
            'short_name' => $m->short_name ?? '',
            'unit'       => $m->unit ?? '',
            'input_type' => $m->input_type ?? 'text',
        ];
    })->toArray();
}



   
    $viewData = [
        'tab' => $tab,
		'active' => $tab,
        'pid' => $prescriptionId,
        'patientId' => $patientId,
        'patient' => $patient,
        'rx' => $prescription,
        'vitals' => $vitals,
        'hasDraft' => $hasDraft,
        'patients' => $patients,       
        'complaints' => $prescriptionData['complaints'],
        'investigations' => $prescriptionData['investigations'],
        'diagnoses' => $prescriptionData['diagnoses'],
        'medicines' => $prescriptionData['medicines'],
		'rxDoctors' => $prescriptionData['rxDoctors'] ?? collect(),
		
		'commoncomplain' => $masterLists['commonComplaints'],
        'commonInvestigations' => $masterLists['commonInvestigations'],
        'commonDiagnoses' => $masterLists['commonDiagnoses'],
        'commonMedicines' => $masterLists['commonMedicines'],
        'commonDoctors' => $masterLists['commonDoctors'],
        'companies' => $this->getCompanies(),
        'medicineTypes' => $this->getMedicineTypes(),
        'dosageForms' => $this->getDosageForms(),
		
		'masterOptions'         => $masterOptions,
		
		'oeText' => $this->buildWizardOeText((int) ($prescriptionId ?? 0), $patient, $prescription),
    ];

    return view('prescriptions.wizard-master', $viewData);
}

    // ------- Private Helper Methods -------
    
    /**
     * Cancel incomplete prescription draft
     */
    private function cancelDraft(int $patientId): void
    {
        if (Schema::hasTable('prescriptions')) {
            DB::table('prescriptions')
                ->where('patient_id', $patientId)
                ->where('status', 0)
                ->update(['status' => 9]);
        }
    }
    
    /**
     * Check if patient has incomplete prescription
     */
    private function hasIncompletePrescription(?int $patientId): bool
    {
        if (!$patientId || !Schema::hasTable('prescriptions')) {
            return false;
        }
        
        return DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->where('status', 0)
            ->exists();
    }
    
    /**
     * Get patient data by ID or through prescription
     */
    private function getPatient(?int $patientId, ?int $prescriptionId): ?object
    {
        if ($patientId && Schema::hasTable('patients')) {
            return DB::table('patients')->where('id', $patientId)->first();
        }
        
        if ($prescriptionId && Schema::hasTable('patients') && Schema::hasTable('prescriptions')) {
            return DB::table('patients')
                ->join('prescriptions', 'prescriptions.patient_id', '=', 'patients.id')
                ->where('prescriptions.id', $prescriptionId)
                ->select('patients.*')
                ->first();
        }
        
        return null;
    }
    
    /**
     * Get prescription by ID
     */
    private function getPrescription(?int $prescriptionId): ?object
    {
        if (!$prescriptionId || !Schema::hasTable('prescriptions')) {
            return null;
        }
        
        return DB::table('prescriptions')->find($prescriptionId);
    }
    
    /**
     * Get latest patient vitals from pre-assessments
     */
    private function getVitals(?int $patientId): ?object
    {
        // Debug: Log the patientId being passed
        \Log::info('getVitals called with patientId: ' . $patientId);
        
        if (!$patientId || !Schema::hasTable('preconassessment')) {
            \Log::warning('getVitals: No patientId or table missing', [
                'patientId' => $patientId,
                'tableExists' => Schema::hasTable('preconassessment')
            ]);
            return null;
        }
        
        // Debug: Check if patient exists in patients table
        $patientExists = DB::table('patients')->where('id', $patientId)->exists();
        \Log::info('Patient exists in patients table: ' . ($patientExists ? 'YES' : 'NO'));
        
        // Try multiple approaches to find the correct foreign key
        $vitalsByPatientId = DB::table('preconassessment')
            ->where('patientcode', $patientId)  // Try patient_id first
            ->latest()
            ->first();
            
        $vitalsByPatientCode = DB::table('preconassessment')
            ->where('patientcode', $patientId)  // Then try patientcode
            ->latest()
            ->first();
            
        // Debug: Log results
        \Log::info('Vitals by patientcode: ' . ($vitalsByPatientId ? 'FOUND' : 'NOT FOUND'));
        \Log::info('Vitals by patientcode: ' . ($vitalsByPatientCode ? 'FOUND' : 'NOT FOUND'));
        
        // Check if patient has a patientcode that should be used instead
        $patient = DB::table('patients')->where('id', $patientId)->first();
        if ($patient && isset($patient->patientcode)) {
            $vitalsByPatientPatientCode = DB::table('preconassessment')
                ->where('patientcode', $patient->patientcode)
                ->latest()
                ->first();
            \Log::info('Patient code: ' . $patient->patientcode);
            \Log::info('Vitals by patient.patientcode: ' . ($vitalsByPatientPatientCode ? 'FOUND' : 'NOT FOUND'));
            
            if ($vitalsByPatientPatientCode) {
                return $vitalsByPatientPatientCode;
            }
        }
        
        // Return whichever one works
        return $vitalsByPatientId ?? $vitalsByPatientCode;
    }
    
    /**
     * Get all patients for dropdown
     */
    private function getPatientsList(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('patients')) {
            return collect();
        }
        
        return DB::table('patients')
        ->orderBy('patientname')
        ->get([
            'id',
            'patientname',
            'mobile_no',
            'patientcode',
            'gender',
            'age',
            'blood_group',
            'nid_number'
        ]);

    }
    
    /**
     * Get prescription-related data (complaints, investigations, etc.)
     */
    private function getPrescriptionData(?int $prescriptionId): array
    {
        $data = [
            'complaints' => collect(),
            'investigations' => collect(),
            'diagnoses' => collect(),
            'medicines' => collect(),
            'doctors' => collect(),
        ];
        
        if (!$prescriptionId) {
            return $data;
        }
        
        // Get complaints
        if (Schema::hasTable('prescriptions_complain')) {
            $data['complaints'] = DB::table('prescriptions_complain')
                ->where('prescription_id', $prescriptionId)
                ->orderBy('id')
                ->get() ?? collect();
        }
        
        // Get investigations
        if (Schema::hasTable('prescriptions_investigations')) {
            $data['investigations'] = DB::table('prescriptions_investigations')
                ->where('prescription_id', $prescriptionId)
                ->orderBy('id')
                ->get() ?? collect();
        }
        
        // Get diagnoses
        if (Schema::hasTable('prescriptions_diagnosis')) {
            $data['diagnoses'] = DB::table('prescriptions_diagnosis')
                ->where('prescription_id', $prescriptionId)
                ->orderBy('id')
                ->get() ?? collect();
        }
        
        // Get medicines
        if (Schema::hasTable('prescriptions_medicine')) {
            $data['medicines'] = DB::table('prescriptions_medicine')
                ->where('prescription_id', $prescriptionId)
                ->orderBy('id')
                ->get() ?? collect();
        }
        
		// Doctor list (same like medicines)
		// Doctor list (same like medicines)
	if (Schema::hasTable('prescription_doctors') && Schema::hasTable('doctors')) {
		$q = DB::table('prescription_doctors as pd')
			->join('doctors as d', 'd.id', '=', 'pd.doctor_id')
			->where('pd.prescription_id', $prescriptionId)
			->orderBy('pd.id')
			->select([
				'pd.id',
				'pd.prescription_id',
				'pd.doctor_id',
				'pd.role',
				'pd.note',
				'pd.active',
				'd.doctor_name as doctor_name',      // ✅ FIXED (was doctor_name)
				'd.speciality',               // optional
			]);

		// optional: only active doctors
		if (Schema::hasColumn('prescription_doctors', 'active')) {
			$q->where('pd.active', 1);
		}

		// optional: only active doctor master
		if (Schema::hasColumn('doctors', 'active')) {
			$q->where('d.active', 1);
		}

		$data['rxDoctors'] = $q->get() ?? collect();
	}

        return $data;
    }
    
    // ------- Filtered Medicines (AJAX) -------
    public function filteredMedicines(Request $request)
    {
        if (!Schema::hasTable('common_medicine')) {
            return response()->json([]);
        }

        $query = DB::table('common_medicine')->where('active', 1);

        if ($request->filled('company')) {
            $query->where('company', $request->company);
        }
        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', (int) $request->medicine_type_id);
        }
        if ($request->filled('medicine_sub_type_id')) {
            $query->where('medicine_sub_type_id', (int) $request->medicine_sub_type_id);
        }
        if ($request->filled('dosage_form_id')) {
            $query->where('dosage_form_id', (int) $request->dosage_form_id);
        }

        return $query->orderBy('name')->get(['id', 'name', 'strength', 'GroupName', 'company', 'note']);
    }

    // ------- Commonly Used Medicines (AJAX) -------
    // Returns medicines flagged as commonly_used with pagination for lazy loading.
    public function commonlyUsedMedicines(Request $request)
    {
        if (!Schema::hasTable('common_medicine')) {
            return response()->json(['medicines' => [], 'total' => 0, 'loaded' => 0]);
        }

        $query = DB::table('common_medicine')
            ->leftJoin('dosage_forms', 'dosage_forms.id', '=', 'common_medicine.dosage_form_id')
            ->where('common_medicine.commonly_used', 1)
            ->where('common_medicine.active', 1)
            ->select(
                'common_medicine.id',
                'common_medicine.name',
                'common_medicine.strength',
                'common_medicine.company',
                'common_medicine.company_id',
                'common_medicine.note',
                'dosage_forms.dosage_name'
            )
            ->orderBy('common_medicine.name');

        $total = $query->count();

        $offset = max(0, (int) $request->get('offset', 0));
        $limit  = 12;

        $medicines = $query->skip($offset)->take($limit)->get();

        return response()->json([
            'medicines' => $medicines,
            'total'     => $total,
            'loaded'    => $offset + $medicines->count(),
        ]);
    }

    // ------- Quick-Add Medicine (AJAX) -------
    // Lightweight quick-add — only 3 fields from the doctor.
    // Defaults: active=1, commonly_used=1, medicine_type="Unknown", company="General".
    public function quickAddMedicine(Request $request)
    {
        $request->validate([
            'medicine_name'  => 'required|string|max:255',
            'strength'       => 'required|string|max:50',
            'dosage_form_id' => 'required|exists:dosage_forms,id',
        ]);

        $name     = trim($request->medicine_name);
        $strength = trim($request->strength);
        $formId   = $request->dosage_form_id;

        // Duplicate check: same name + strength + dosage form
        $existing = DB::table('common_medicine')
            ->where('name', $name)
            ->where('strength', $strength)
            ->where('dosage_form_id', $formId)
            ->first();

        if ($existing) {
            return response()->json([
                'ok'        => true,
                'duplicate' => true,
                'medicine'  => [
                    'id'        => $existing->id,
                    'name'      => $existing->name,
                    'strength'  => $existing->strength,
                    'GroupName' => $existing->GroupName ?? 'N/A',
                    'company'   => $existing->company,
                ],
            ]);
        }

        // System defaults
        $company = DB::table('companies')->where('name', 'General')->orWhere('id', 1)->first();
        $type    = DB::table('medicine_types')->where('type_name', 'Unknown')->orWhere('id', 1)->first();

        $id = DB::table('common_medicine')->insertGetId([
            'name'                 => $name,
            'strength'             => $strength,
            'medicine_type_id'     => $type?->id ?? 1,
            'medicine_sub_type_id' => null,
            'company_id'           => $company?->id ?? 1,
            'company'              => $company?->name ?? 'General',
            'dosage_form_id'       => $formId,
            'commonly_used'        => 1,
            'active'               => 1,
            'name_normalized'      => $this->normalizeName($name),
            'GroupName'            => 'N/A',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return response()->json([
            'ok'        => true,
            'duplicate' => false,
            'medicine'  => [
                'id'        => $id,
                'name'      => $name,
                'strength'  => $strength,
                'GroupName' => 'N/A',
                'company'   => $company?->name ?? 'General',
            ],
        ]);
    }

    private function normalizeName($name)
    {
        $name = trim($name ?? '');
        $name = preg_replace('/\s+/', ' ', $name);
        return mb_strtolower($name);
    }

    // ------- Sub Medicine Types for the filter cascade (AJAX) -------
    // Returns the ACTIVE sub types of one medicine type: [{id, subtype_name}].
    // Types without sub types return [] and the UI keeps the dropdown hidden.
    public function subTypes(Request $request)
    {
        if (!Schema::hasTable('medicine_subtypes')) {
            return response()->json([]);
        }

        $query = DB::table('medicine_subtypes')->where('active', 1);

        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', (int) $request->medicine_type_id);
        }

        return $query->orderBy('subtype_name')->get(['id', 'subtype_name']);
    }

    private function getCompanies(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('companies')) return collect();
        return DB::table('companies')->where('active', 1)->orderBy('name')->get(['id', 'name']);
    }

    private function getMedicineTypes(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('medicine_types')) return collect();
        return DB::table('medicine_types')->where('active', 1)->orderBy('type_name')->get(['id', 'type_name']);
    }

    private function getDosageForms(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('dosage_forms')) return collect();
        return DB::table('dosage_forms')->where('active', 1)->orderBy('dosage_name')->get(['id', 'dosage_name']);
    }

    /**
     * Get master lists for auto-suggestions
     */
    private function getMasterLists(): array
    {
        return [
            'commonInvestigations' => $this->getMasterList('common_investigation'),
            'commonDiagnoses' => $this->getMasterList('common_diagnosis'),
            'commonMedicines' => $this->getMasterList('common_medicine'),
            'commonComplaints' => $this->getMasterList('common_complain'),
            'commonDoctors' => $this->getMasterList('doctors')
        ];
    }
    
    /**
     * Get individual master list with active filter
     */
    private function getMasterList(string $tableName): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable($tableName)) {
            return collect();
        }
        
        $query = DB::table($tableName)->orderBy('name');
        
        // Filter by active column if it exists
        if (Schema::hasColumn($tableName, 'active')) {
            $query->where('active', 1);
        }
        
        return $query->get() ?? collect();
    }


    // ------- Helpers -------
  private function nextTab(string $current): string
{
    $order = [
        'patients',
        'complain',
        'oe',
        'investigations',
        'medicine',
        'preview',
        'finish'
    ];

    $index = array_search($current, $order, true);

    if ($index === false || $index >= count($order) - 1) {
        return $current;
    }

    return $order[$index + 1];
}

    private function makePatientCode(): string
    {
        $prefix = 'PT-'.now()->format('Ym').'-';
        $last = DB::table('patients')
            ->where('patientcode','like',$prefix.'%')
            ->orderByDesc('patientcode')
            ->value('patientcode');
        $n = 1;
        if ($last && preg_match('/-(\d{4})$/',$last,$m)) $n = intval($m[1])+1;
        return $prefix . str_pad((string)$n,4,'0',STR_PAD_LEFT);
    }

private function go(Request $r, string $fromTab, int $pid, ?int $patientId = null)
{
    $to = $r->input('next') ?: $this->nextTab($fromTab);

    $allowed = [
        'patients', 'complain', 'oe', 'investigations',
        'medicine', 'preview', 'finish'
    ];

    if (!in_array($to, $allowed, true)) {
        $to = $this->nextTab($fromTab);
    }

    return redirect()->route('rx.wizard', [
        'id'      => $pid,
        'patient' => $patientId,
        'tab'     => $to,
    ])->with('success', 'Saved successfully.');
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
            'gender'          => ['nullable', Rule::in(['Male','Female','Other'])],
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
            DB::table('patients')->where('id',$patientId)->update([
                'patientcode'=>$code,'patientname'=>$v['patientname'],
                'patientfather'=>$v['patientfather']??null,'patienthusband'=>$v['patienthusband']??null,
                'address'=>$v['address']??null,'age'=>$v['age']??null,'date_of_birth'=>$v['date_of_birth']??null,
                'mobile_no'=>$v['mobile_no']??null,'contact_no'=>$v['contact_no']??null,'nid_number'=>$v['nid_number']??null,
                'email'=>$v['email']??null,'gender'=>$v['gender']??null,'blood_group'=>$v['blood_group']??null,
                'notes'=>$v['notes']??null,'updated_at'=>$now,
            ]);
        } else {
            $patientId = DB::table('patients')->insertGetId([
                'patientcode'=>$code,'patientname'=>$v['patientname'],
                'patientfather'=>$v['patientfather']??null,'patienthusband'=>$v['patienthusband']??null,
                'address'=>$v['address']??null,'age'=>$v['age']??null,'date_of_birth'=>$v['date_of_birth']??null,
                'mobile_no'=>$v['mobile_no']??null,'contact_no'=>$v['contact_no']??null,'nid_number'=>$v['nid_number']??null,
                'email'=>$v['email']??null,'gender'=>$v['gender']??null,'blood_group'=>$v['blood_group']??null,
                'notes'=>$v['notes']??null,'created_at'=>$now,'updated_at'=>$now,
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
            DB::table('prescriptions')->where('id',$pid)->update([
                'patient_id'=>$patientId,'updated_at'=>$now
            ]);
        }

        return $this->go($r, 'patients', $pid, $patientId);
    }
    
public function savePatient(Request $r)
{
    $v = $r->validate([
        'prescription_id'           => 'nullable|integer|exists:prescriptions,id',
        'patient_id'                => 'required|integer|exists:patients,id',
        'previous_prescription_id'  => 'nullable|integer|exists:prescriptions,id',
        'next'                      => 'nullable|string|in:patients,complain,investigations,diagnoses,medicines,preview',
    ]);

    $now        = now();
    $patientId  = (int) $v['patient_id'];
    $pid        = $v['prescription_id'] ?? null;
    $prevRxId   = $v['previous_prescription_id'] ?? null;

    $patientCode = DB::table('patients')
        ->where('id', $patientId)
        ->value('patientcode');

    // ------------------------------------------------------------
    // CASE A: previous Rx selected -> CREATE NEW prescription ALWAYS
    // ------------------------------------------------------------
    if ($prevRxId) {

        $newPid = null;

        DB::transaction(function() use ($patientId, $prevRxId, $patientCode, $now, &$newPid){

            // 1) Create a brand-new prescription
            $tmpNo = 'TMP-' . $now->timestamp;
            $newPid = DB::table('prescriptions')->insertGetId([
                'patient_id'      => $patientId,
                'prescription_no' => $tmpNo,
                'advices'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // 2) Fetch previous prescription
            $prevRx = DB::table('prescriptions')->where('id', $prevRxId)->first();

            // Copy advices directly into new one if exists
            if ($prevRx && !empty($prevRx->advices)) {
                DB::table('prescriptions')
                    ->where('id', $newPid)
                    ->update(['advices' => $prevRx->advices]);
            }

            // 3) Copy COMPLAINS
            $prevComplains = DB::table('prescriptions_complain')
                ->where('prescription_id', $prevRxId)
                ->where('active', 1)
                ->get();

            foreach ($prevComplains as $c) {
                DB::table('prescriptions_complain')->insert([
                    'patientcode'     => $patientCode ?? $c->patientcode,
                    'prescription_id' => $newPid,
                    'complaint'       => $c->complaint,
                    'note'            => $c->note,
                    'name'            => $c->name,
                    'active'          => 1,
                    'name_normalized' => $c->name_normalized,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            // 4) Copy DIAGNOSIS
            $prevDiagnosis = DB::table('prescriptions_diagnosis')
                ->where('prescription_id', $prevRxId)
                ->where('active', 1)
                ->get();

            foreach ($prevDiagnosis as $d) {
                DB::table('prescriptions_diagnosis')->insert([
                    'patientcode'     => $patientCode ?? $d->patientcode,
                    'prescription_id' => $newPid,
                    'name'            => $d->name,
                    'note'            => $d->note,
                    'active'          => 1,
                    'name_normalized' => $d->name_normalized,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            // 5) Copy INVESTIGATIONS (prescription_id is varchar in your table)
            $prevInv = DB::table('prescriptions_investigations')
                ->where('prescription_id', (string)$prevRxId)
                ->get();

            foreach ($prevInv as $i) {
                DB::table('prescriptions_investigations')->insert([
                    'prescription_id'  => (string)$newPid,
                    'investigation_id' => $i->investigation_id,
                    'name'             => $i->name,
                    'note'             => $i->note,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            // 6) Copy MEDICINES
            $prevMeds = DB::table('prescriptions_medicine')
                ->where('prescription_id', $prevRxId)
                ->get();

            foreach ($prevMeds as $m) {
                DB::table('prescriptions_medicine')->insert([
                    'prescription_id' => $newPid,
                    'medicine_name'   => $m->medicine_name,
                    'name'            => $m->name,
                    'strength'        => $m->strength,
                    'dose'            => $m->dose,
                    'route'           => $m->route,
                    'frequency'       => $m->frequency,
                    'duration'        => $m->duration,
                    'timing'          => $m->timing,
                    'note'            => $m->note,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            // OPTIONAL (if you really want to mark old one inactive):
            DB::table('prescriptions')->where('id', $prevRxId)->update(['status' => 0]);

        });

        $nextTab = $r->input('next', 'complain');

        // Redirect wizard to NEW prescription
        return $this->go($r, $nextTab, $newPid, $patientId)
            ->with('success', "Previous prescription loaded into NEW RX #{$newPid}. Now edit/add/delete as needed.");
    }

    // ------------------------------------------------------------
    // CASE B: no previous Rx -> old behavior (update/create normal)
    // ------------------------------------------------------------
    if ($pid) {
        DB::table('prescriptions')->where('id', $pid)->update([
            'patient_id' => $patientId,
            'updated_at' => $now,
        ]);
    } else {
        $tmpNo = 'TMP-' . $now->timestamp;
        $pid = DB::table('prescriptions')->insertGetId([
            'patient_id'      => $patientId,
            'prescription_no' => $tmpNo,
            'created_at'      => $now,
            'updated_at'      => $now,
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

    if (Schema::hasColumn('prescriptions','chief_complaint')) {
        DB::table('prescriptions')->where('id',$pid)->update([
            'chief_complaint' => $v['chief_complaint'] ?? null,
            'updated_at'      => $now,
        ]);
    } elseif (Schema::hasTable('prescriptions_complain')) {
        $exists = DB::table('prescriptions_complain')->where('prescription_id',$pid)->exists();
        if ($exists) {
            DB::table('prescriptions_complain')->where('prescription_id',$pid)->update([
                'complaint'  => $v['chief_complaint'] ?? null,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('prescriptions_complain')->insert([
                'prescription_id' => $pid,
                'complaint'       => $v['chief_complaint'] ?? null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    $nextTab = $r->input('next','investigations'); // go to investigations tab

    return $this->go($r, $nextTab, $pid, $patientId);
}

public function saveDoctor(Request $r)
{
    $r->validate([
        'id' => ['required','integer'],
        'doctor_id' => ['required','integer'],
        'doctor_note' => ['nullable','string','max:255'],
    ]);

    DB::table('prescriptions')
        ->where('id', (int)$r->id)
        ->update([
            'doctor_id' => (int)$r->doctor_id,
            'doctor_note' => $r->doctor_note,
            'updated_at' => now(),
        ]);

    return redirect()->route('rx.wizard', [
        'id' => $r->id,
        'patient' => $r->patient,
        'tab' => 'complaint',
    ])->with('success', 'Doctor saved.');
}

    // ------- TAB 2: Investigations (single row CRUD) -------
   // Store ONE investigation (stay on the same tab)
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
                'name'            => $data['name'],
                'note'            => $data['note'] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // STAY on Investigations
            return redirect()->route('rx.wizard', [
                'id'      => $data['prescription_id'],
                'patient' => $data['patient_id'] ?? null,
                'tab'     => 'investigations',
            ])->with('success','Investigation added.');
        }

        // Update ONE investigation (stay on the same tab)
        public function updateInvestigation(Request $r, int $invId)
        {
            $data = $r->validate([
                'prescription_id' => 'required|integer|exists:prescriptions,id',
                'patient_id'      => 'nullable|integer|exists:patients,id',
                'name'            => 'required|string|max:150',
                'note'            => 'nullable|string|max:255',
            ]);

            $ok = DB::table('prescriptions_investigations')
                ->where('id',$invId)
                ->where('prescription_id',$data['prescription_id'])
                ->exists();
            abort_unless($ok, 404);

            DB::table('prescriptions_investigations')->where('id',$invId)->update([
                'name'       => $data['name'],
                'note'       => $data['note'] ?? null,
                'updated_at' => now(),
            ]);

            // STAY on Investigations
            return redirect()->route('rx.wizard', [
                'id'      => $data['prescription_id'],
                'patient' => $data['patient_id'] ?? null,
                'tab'     => 'investigations',
            ])->with('success','Investigation updated.');
        }

        // Delete ONE investigation (stay on the same tab)
        public function destroyInvestigation(Request $r, int $invId)
        {
            $data = $r->validate([
                'prescription_id' => 'required|integer|exists:prescriptions,id',
                'patient_id'      => 'nullable|integer|exists:patients,id',
            ]);

            $ok = DB::table('prescriptions_investigations')
                ->where('id',$invId)
                ->where('prescription_id',$data['prescription_id'])
                ->exists();
            abort_unless($ok, 404);

            DB::table('prescriptions_investigations')->where('id',$invId)->delete();

            // STAY on Investigations
            return redirect()->route('rx.wizard', [
                'id'      => $data['prescription_id'],
                'patient' => $data['patient_id'] ?? null,
                'tab'     => 'investigations',
            ])->with('success','Investigation deleted.');
        }


    // ------- TAB 3: Diagnosis -------
    public function storeDiagnosis(Request $r)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
            'name'            => 'required|string|max:150',
            'note'            => 'nullable|string|max:255',
            'next'            => 'nullable|string',
        ]);

        DB::table('prescription_diagnoses')->insert([
            'prescription_id'=>$data['prescription_id'],
            'name'=>$data['name'],
            'note'=>$data['note']??null,
            'created_at'=>now(),'updated_at'=>now(),
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

        $ok = DB::table('prescription_diagnoses')
            ->where('id',$diag)->where('prescription_id',$data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescription_diagnoses')->where('id',$diag)->update([
            'name'=>$data['name'],'note'=>$data['note']??null,'updated_at'=>now(),
        ]);

        return $this->go($r, 'diagnosis', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function destroyDiagnosis(Request $r, int $diag)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
        ]);

        $ok = DB::table('prescription_diagnoses')
            ->where('id',$diag)->where('prescription_id',$data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescription_diagnoses')->where('id',$diag)->delete();

        return redirect()->route('rx.wizard', [
            'id'=>$data['prescription_id'],'patient'=>$data['patient_id']??null,'tab'=>'diagnosis'
        ])->with('success','Deleted.');
    }

    // ------- TAB 4: Medicine -------
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

        $pid = (int) $data['prescription_id'];

        // Check if patient has been properly assessed before allowing prescription
        $hasComplaints = false;
        $hasDiagnoses = false;

        if (Schema::hasTable('prescriptions_complain')) {
            $hasComplaints = DB::table('prescriptions_complain')
                ->where('prescription_id', $pid)
                ->where('active', 1)
                ->exists();
        }

        if (Schema::hasTable('prescriptions_diagnosis')) {
            $hasDiagnoses = DB::table('prescriptions_diagnosis')
                ->where('prescription_id', $pid)
                ->where('active', 1)
                ->exists();
        }

        // Prevent prescribing if assessment is incomplete
        if (!$hasComplaints || !$hasDiagnoses) {
            $message = '';
            if (!$hasComplaints && !$hasDiagnoses) {
                $message = 'ঔষধ প্রেসক্রাইব করার আগে রোগীর সমস্যা (complaints) এবং রোগ নির্ণয় (diagnosis) উভয়ই করতে হবে।';
            } elseif (!$hasComplaints) {
                $message = 'ঔষধ প্রেসক্রাইব করার আগে রোগীর সমস্যা (complaints) লিপিবদ্ধ করতে হবে।';
            } else {
                $message = 'ঔষধ প্রেসক্রাইব করার আগে রোগ নির্ণয় (diagnosis) করতে হবে।';
            }

            return redirect()->route('rx.wizard', [
                'id' => $pid,
                'patient' => $data['patient_id'] ?? null,
                'tab' => 'medicine'
            ])->with('error', $message);
        }

        DB::table('prescription_medicines')->insert([
            'prescription_id'=>$data['prescription_id'],
            'name'=>$data['name'],
            'strength'=>$data['strength']??null,'dose'=>$data['dose']??null,
            'route'=>$data['route']??null,'frequency'=>$data['frequency']??null,
            'duration'=>$data['duration']??null,'timing'=>$data['timing']??null,
            'note'=>$data['note']??null,
            'created_at'=>now(),'updated_at'=>now(),
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

        $pid = (int) $data['prescription_id'];

        // Check if patient has been properly assessed before allowing prescription update
        $hasComplaints = false;
        $hasDiagnoses = false;

        if (Schema::hasTable('prescriptions_complain')) {
            $hasComplaints = DB::table('prescriptions_complain')
                ->where('prescription_id', $pid)
                ->where('active', 1)
                ->exists();
        }

        if (Schema::hasTable('prescriptions_diagnosis')) {
            $hasDiagnoses = DB::table('prescriptions_diagnosis')
                ->where('prescription_id', $pid)
                ->where('active', 1)
                ->exists();
        }

        // Prevent prescribing if assessment is incomplete
        if (!$hasComplaints || !$hasDiagnoses) {
            $message = '';
            if (!$hasComplaints && !$hasDiagnoses) {
                $message = 'ঔষধ আপডেট করার আগে রোগীর সমস্যা (complaints) এবং রোগ নির্ণয় (diagnosis) উভয়ই করতে হবে।';
            } elseif (!$hasComplaints) {
                $message = 'ঔষধ আপডেট করার আগে রোগীর সমস্যা (complaints) লিপিবদ্ধ করতে হবে।';
            } else {
                $message = 'ঔষধ আপডেট করার আগে রোগ নির্ণয় (diagnosis) করতে হবে।';
            }

            return redirect()->route('rx.wizard', [
                'id' => $pid,
                'patient' => $data['patient_id'] ?? null,
                'tab' => 'medicine'
            ])->with('error', $message);
        }

        $ok = DB::table('prescription_medicines')
            ->where('id',$med)->where('prescription_id',$data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescription_medicines')->where('id',$med)->update([
            'name'=>$data['name'],
            'strength'=>$data['strength']??null,'dose'=>$data['dose']??null,
            'route'=>$data['route']??null,'frequency'=>$data['frequency']??null,
            'duration'=>$data['duration']??null,'timing'=>$data['timing']??null,
            'note'=>$data['note']??null,
            'updated_at'=>now(),
        ]);

        return $this->go($r, 'medicine', (int)$data['prescription_id'], $data['patient_id'] ?? null);
    }

    public function destroyMedicine(Request $r, int $med)
    {
        $data = $r->validate([
            'prescription_id' => 'required|integer|exists:prescriptions,id',
            'patient_id'      => 'nullable|integer|exists:patients,id',
        ]);

        $ok = DB::table('prescription_medicines')
            ->where('id',$med)->where('prescription_id',$data['prescription_id'])->exists();
        abort_unless($ok, 404);

        DB::table('prescription_medicines')->where('id',$med)->delete();

        return redirect()->route('rx.wizard', [
            'id'=>$data['prescription_id'],'patient'=>$data['patient_id']??null,'tab'=>'medicine'
        ])->with('success','Deleted.');
    }
    
	public function storeDoctor(Request $r)
{
    $data = $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
        'patient_id'      => 'nullable|integer|exists:patients,id',
        'doctor_id'       => 'required|integer|exists:doctors,id',
        'role'            => 'nullable|string|max:80',
        'note'            => 'nullable|string|max:255',
        'next'            => 'nullable|string',
    ]);

    $pid = (int) $data['prescription_id'];

    // prevent duplicate doctor in same prescription (because of UNIQUE)
    try {
        DB::table('prescription_doctors')->insert([
            'prescription_id' => $pid,
            'doctor_id'       => (int)$data['doctor_id'],
            'role'            => $data['role'] ?? null,
            'note'            => $data['note'] ?? null,
            'active'          => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    } catch (\Throwable $e) {
        return redirect()->route('rx.wizard', [
            'id' => $pid,
            'patient' => $data['patient_id'] ?? null,
            'tab' => 'doctor'
        ])->with('error', 'এই Doctor আগেই Add করা আছে।');
    }

    return $this->go($r, 'doctor', $pid, $data['patient_id'] ?? null);
}

public function updateDoctor(Request $r, $doc)
{
    $data = $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
        'patient_id'      => 'nullable|integer|exists:patients,id',
        'role'            => 'nullable|string|max:80',
        'note'            => 'nullable|string|max:255',
        'active'          => 'nullable|in:0,1',
        'next'            => 'nullable|string',
    ]);

    $pid = (int)$data['prescription_id'];

    DB::table('prescription_doctors')
        ->where('id', (int)$doc)
        ->where('prescription_id', $pid) // safety
        ->update([
            'role'       => $data['role'] ?? null,
            'note'       => $data['note'] ?? null,
            'active'     => isset($data['active']) ? (int)$data['active'] : 1,
            'updated_at' => now(),
        ]);

    return $this->go($r, 'doctor', $pid, $data['patient_id'] ?? null);
}
	public function destroyDoctor(Request $r, $doc)
{
    $data = $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
        'patient_id'      => 'nullable|integer|exists:patients,id',
        'next'            => 'nullable|string',
    ]);

    $pid = (int)$data['prescription_id'];

    DB::table('prescription_doctors')
        ->where('id', (int)$doc)
        ->where('prescription_id', $pid)
        ->delete();

    return $this->go($r, 'doctor', $pid, $data['patient_id'] ?? null);
}

   public function ajaxInvestigation(Request $r)
{
    // ensure table exists
    if (!Schema::hasTable('prescriptions_investigations')) {
        return response()->json(['ok'=>false,'error'=>'Table prescriptions_investigations not found'], 500);
    }

    $action = $r->string('action')->toString();
    $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
    ]);
    $pid = (int) $r->prescription_id;

    try {
        switch ($action) {
            case 'add':
                $r->validate([
                    'name' => 'required|string|max:150',
                    'note' => 'nullable|string|max:255',
                ]);
                $id = DB::table('prescriptions_investigations')->insertGetId([
                    'prescription_id' => $pid,
                    'name'            => $r->name,
                    'note'            => $r->note ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                return response()->json(['ok'=>true,'id'=>$id]);

            case 'update':
                $r->validate([
                    'id'   => 'required|integer',
                    'name' => 'required|string|max:150',
                    'note' => 'nullable|string|max:255',
                ]);
                $exists = DB::table('prescriptions_investigations')
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table('prescriptions_investigations')->where('id', $r->id)->update([
                    'name'       => $r->name,
                    'note'       => $r->note ?? null,
                    'updated_at' => now(),
                ]);
                return response()->json(['ok'=>true]);

            case 'delete':
                $r->validate([
                    'id' => 'required|integer',
                ]);
                $exists = DB::table('prescriptions_investigations')
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table('prescriptions_investigations')->where('id', $r->id)->delete();
                return response()->json(['ok'=>true]);

            case 'list':
            default:
                $rows = DB::table('prescriptions_investigations')
                    ->select('id','name','note')
                    ->where('prescription_id', $pid)
                    ->orderByDesc('id')->get();
                return response()->json(['ok'=>true,'rows'=>$rows]);
        }
    } catch (\Throwable $e) {
        return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
}

private function diagTable(): ?string
{
    // Try plural first (recommended), then legacy singular
    if (Schema::hasTable('prescriptions_diagnosis')) return 'prescriptions_diagnosis';
    if (Schema::hasTable('prescriptions_diagnosis'))  return 'prescriptions_diagnosis';
    return null;
}

public function ajaxDiagnosis(Request $r)
{
    $tbl = $this->diagTable();
    if (!$tbl) {
        return response()->json(['ok'=>false,'error'=>'Diagnosis table not found (expected prescriptions_diagnoses or prescription_diagnoses)'], 500);
    }

    $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
    ]);
    $pid    = (int) $r->prescription_id;
    $action = (string) $r->input('action', 'list');

    try {
        switch ($action) {
            case 'add':
                $r->validate([
                    'name' => 'required|string|max:150',
                    'note' => 'nullable|string|max:255',
                ]);
                $id = DB::table($tbl)->insertGetId([
                    'prescription_id' => $pid,
                    'name'            => $r->name,
                    'note'            => $r->note ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                return response()->json(['ok'=>true,'id'=>$id]);

            case 'update':
                $r->validate([
                    'id'   => 'required|integer',
                    'name' => 'required|string|max:150',
                    'note' => 'nullable|string|max:255',
                ]);
                $exists = DB::table($tbl)
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table($tbl)->where('id', $r->id)->update([
                    'name'       => $r->name,
                    'note'       => $r->note ?? null,
                    'updated_at' => now(),
                ]);
                return response()->json(['ok'=>true]);

            case 'delete':
                $r->validate(['id' => 'required|integer']);
                $exists = DB::table($tbl)
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table($tbl)->where('id', $r->id)->delete();
                return response()->json(['ok'=>true]);

            case 'list':
            default:
                $rows = DB::table($tbl)
                    ->select('id','name','note')
                    ->where('prescription_id', $pid)
                    ->orderByDesc('id')
                    ->get();
                return response()->json(['ok'=>true,'rows'=>$rows]);
        }
    } catch (\Throwable $e) {
        return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
}

private function medTable(): ?string
{
    // Prefer plural; fall back to legacy singular if your DB uses it
    if (Schema::hasTable('prescriptions_medicine')) return 'prescriptions_medicine';
    if (Schema::hasTable('prescriptions_medicine'))  return 'prescriptions_medicine';
    return null;
}

public function ajaxMedicine(Request $r)
{
    $tbl = $this->medTable();
    if (!$tbl) {
        return response()->json(['ok'=>false,'error'=>'Medicine table not found (expected prescriptions_medicines or prescription_medicines)'], 500);
    }

    $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
    ]);
    $pid    = (int) $r->prescription_id;
    $action = (string) $r->input('action', 'list');

    try {
        switch ($action) {
            case 'add':
                $r->validate([
                    'name'      => 'required|string|max:150',
                    'strength'  => 'nullable|string|max:80',
                    'dose'      => 'nullable|string|max:80',
                    'route'     => 'nullable|string|max:80',
                    'frequency' => 'nullable|string|max:80',
                    'duration'  => 'nullable|string|max:80',
                    'timing'    => 'nullable|string|max:80',
                    'note'      => 'nullable|string|max:255',
                ]);
                $id = DB::table($tbl)->insertGetId([
                    'prescription_id' => $pid,
                    'name'            => $r->name,
                    'strength'        => $r->strength ?: null,
                    'dose'            => $r->dose ?: null,
                    'route'           => $r->route ?: null,
                    'frequency'       => $r->frequency ?: null,
                    'duration'        => $r->duration ?: null,
                    'timing'          => $r->timing ?: null,
                    'note'            => $r->note ?: null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                return response()->json(['ok'=>true,'id'=>$id]);

            case 'update':
                $r->validate([
                    'id'        => 'required|integer',
                    'name'      => 'required|string|max:150',
                    'strength'  => 'nullable|string|max:80',
                    'dose'      => 'nullable|string|max:80',
                    'route'     => 'nullable|string|max:80',
                    'frequency' => 'nullable|string|max:80',
                    'duration'  => 'nullable|string|max:80',
                    'timing'    => 'nullable|string|max:80',
                    'note'      => 'nullable|string|max:255',
                ]);
                $exists = DB::table($tbl)
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table($tbl)->where('id', $r->id)->update([
                    'name'       => $r->name,
                    'strength'   => $r->strength ?: null,
                    'dose'       => $r->dose ?: null,
                    'route'      => $r->route ?: null,
                    'frequency'  => $r->frequency ?: null,
                    'duration'   => $r->duration ?: null,
                    'timing'     => $r->timing ?: null,
                    'note'       => $r->note ?: null,
                    'updated_at' => now(),
                ]);
                return response()->json(['ok'=>true]);

            case 'delete':
                $r->validate(['id' => 'required|integer']);
                $exists = DB::table($tbl)
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table($tbl)->where('id', $r->id)->delete();
                return response()->json(['ok'=>true]);

            case 'list':
            default:
                $rows = DB::table($tbl . ' as pm')
                    ->leftJoin('common_medicine as cm', 'cm.name', '=', 'pm.name')
                    ->leftJoin('medicine_types as mt', 'mt.id', '=', 'cm.medicine_type_id')
                    ->leftJoin('dosage_forms as df', 'df.id', '=', 'cm.dosage_form_id')
                    ->select(
                        'pm.id',
                        'pm.name',
                        'pm.strength',
                        'pm.dose',
                        'pm.route',
                        'pm.frequency',
                        'pm.duration',
                        'pm.timing',
                        'pm.note',
                        'cm.company',
                        'mt.type_name',
                        'df.dosage_name as form_name'
                    )
                    ->where('pm.prescription_id', $pid)
                    ->orderByDesc('pm.id')
                    ->get();
                return response()->json(['ok'=>true,'rows'=>$rows]);
        }
    } catch (\Throwable $e) {
        return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
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

    // ✅ First close (complete) the active prescription if exists
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

        $draftId = (int) DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 0);
            })
            ->orderByDesc('id')
            ->value('id');

        DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 0);
            })
            ->update([
                'status'          => 1,
                'prescribed_on'   => now()->toDateString(),
                'prescription_no' => $prescriptionNo,
                'updated_at'      => $now,
            ]);

        // Freeze the vitals snapshot: if the draft has no assessment yet, link
        // the patient's latest Pre-Con Assessment so the print always shows a
        // per-prescription snapshot (old prescriptions are never affected).
        if ($draftId && Schema::hasColumn('prescriptions', 'preconassessment_id')) {
            $latestAssessmentId = DB::table('preconassessment')
                ->where('patient_id', $patientId)
                ->orderByDesc('id')
                ->value('id');
            if ($latestAssessmentId) {
                DB::table('prescriptions')
                    ->where('id', $draftId)
                    ->whereNull('preconassessment_id')
                    ->update([
                        'preconassessment_id' => $latestAssessmentId,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    $tab = $patientId ? 'patients' : 'patients';

    return redirect()->route('rx.wizard', [
       'tab'     => $tab,
    ])->with('success', 'Prescription completed. New prescription started.');
}


public function patientsSearchAjax(Request $request)
{
    $term = trim($request->get('term', ''));
    if ($term === '') return response()->json([]);

    return Patient::where('patientname','like',"%{$term}%")
        ->orWhere('mobile_no','like',"%{$term}%")
        ->orWhere('patientcode','like',"%{$term}%")
        ->orderBy('patientname')
        ->limit(15)
        ->get(['id','patientname','mobile_no','patientcode']);
}
public function ajaxComplain(Request $r)
{
    // table: prescription_complaints (same one you use in saveComplain())
    if (!Schema::hasTable('prescriptions_complain')) {
        return response()->json(['ok'=>false,'error'=>'Table prescription_complaints not found'], 500);
    }

    $action = (string) $r->input('action', 'list');

    $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
    ]);
    $pid = (int) $r->prescription_id;

    try {
        switch ($action) {
            case 'add':
                $r->validate([
                    'complaint' => 'required|string|max:255',
                    'note'      => 'nullable|string|max:255',
                ]);
                $id = DB::table('prescriptions_complain')->insertGetId([
                    'prescription_id' => $pid,
                    'complaint'       => $r->complaint,
                    'note'            => $r->note ?: null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                return response()->json(['ok'=>true,'id'=>$id]);

            case 'update':
                $r->validate([
                    'id'        => 'required|integer',
                    'complaint' => 'required|string|max:255',
                    'note'      => 'nullable|string|max:255',
                ]);
                $exists = DB::table('prescriptions_complain')
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table('prescriptions_complain')->where('id', $r->id)->update([
                    'complaint'  => $r->complaint,
                    'note'       => $r->note ?: null,
                    'updated_at' => now(),
                ]);
                return response()->json(['ok'=>true]);

            case 'delete':
                $r->validate(['id' => 'required|integer']);
                $exists = DB::table('prescriptions_complain')
                    ->where('id', $r->id)->where('prescription_id', $pid)->exists();
                if (!$exists) return response()->json(['ok'=>false,'error'=>'Row not found'], 404);

                DB::table('prescriptions_complain')->where('id', $r->id)->delete();
                return response()->json(['ok'=>true]);

            case 'list':
            default:
                $rows = DB::table('prescriptions_complain')
                    ->select('id','complaint','note')
                    ->where('prescription_id', $pid)
                    ->orderByDesc('id')
                    ->get();
                return response()->json(['ok'=>true,'rows'=>$rows]);
        }
    } catch (\Throwable $e) {
        return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
    }
}

public function previousPrescriptions($patientId)
{
    $list = DB::table('prescriptions')
        ->where('patient_id', $patientId)
        ->orderByDesc('id')
        ->get(['id', 'created_at']);

    return response()->json([
        'ok'   => true,
        'data' => $list
    ]);
}

public function previousPrescriptionDetails($rxId)
{
    $rx = DB::table('prescriptions')->where('id', $rxId)->first();

    if (!$rx) {
        return response()->json([
            'ok' => false,
            'message' => 'Prescription not found'
        ], 404);
    }

    $complains = DB::table('prescriptions_complain')
        ->where('prescription_id', $rxId)
        ->where('active', 1)
        ->orderBy('id')
        ->get(['id', 'complaint', 'note', 'name']);

    $diagnosis = DB::table('prescriptions_diagnosis')
        ->where('prescription_id', $rxId)
        ->where('active', 1)
        ->orderBy('id')
        ->get(['id', 'name', 'note']);

    $investigations = DB::table('prescriptions_investigations')
        ->where('prescription_id', $rxId)
        ->orderBy('id')
        ->get(['id', 'name', 'note']);

    $medicines = DB::table('prescriptions_medicine')
        ->where('prescription_id', $rxId)
        ->orderBy('id')
        ->get([
            'id',
            'medicine_name',
            'strength',
            'dose',
            'route',
            'frequency',
            'duration',
            'timing',
            'note'
        ]);

    return response()->json([
        'ok' => true,
        'data' => [
            'rx'             => $rx,
            'complains'      => $complains,
            'diagnosis'      => $diagnosis,
            'investigations' => $investigations,
            'medicines'      => $medicines,
        ]
    ]);
}

public function pdf($id, Request $r)
{
    $pid = (int) $id;

    $rx = DB::table('prescriptions')->where('id', $pid)->first();
    if (!$rx) abort(404, 'Prescription not found');

    $patient = DB::table('patients')->where('id', $rx->patient_id)->first();

    $complaints = DB::table('prescriptions_complain')
        ->where('prescription_id', $pid)
        ->where('active', 1)
        ->orderBy('id')
        ->get();

    $diagnoses = DB::table('prescriptions_diagnosis')
        ->where('prescription_id', $pid)
        ->where('active', 1)
        ->orderBy('id')
        ->get();

    $investigations = DB::table('prescriptions_investigations')
        ->where('prescription_id', (string)$pid) // varchar column
        ->orderBy('id')
        ->get();

    $medicines = DB::table('prescriptions_medicine')
        ->where('prescription_id', $pid)
        ->orderBy('id')
        ->get();

    // ✅ Doctors for this prescription (from prescription_doctors + doctors)
    $rxDoctors = collect();
    if (Schema::hasTable('prescription_doctors')) {
        $rxDoctors = DB::table('prescription_doctors as pd')
            ->leftJoin('doctors as d', 'd.id', '=', 'pd.doctor_id')
            ->where('pd.prescription_id', $pid)
            ->orderBy('pd.id')
            ->select('pd.*', 'd.doctor_name', 'd.name', 'd.speciality', 'd.reg_no', 'd.contact')
            ->get();
    }

    // ---- OPTIONAL: clean weird private-use chars from DB (recommended) ----
    $cleanBangla = function ($text) {
        if (!$text) return $text;
        // remove private-use unicode (causes    etc.)
        $text = preg_replace('/[\x{E000}-\x{F8FF}]/u', '', $text);
        // remove zero-width chars
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        return trim($text);
    };

    $rx->chief_complaint = $cleanBangla($rx->chief_complaint ?? '');
    $rx->diagnosis       = $cleanBangla($rx->diagnosis ?? '');
    $rx->advice          = $cleanBangla($rx->advice ?? '');

    foreach ($complaints as $c) {
        $c->complaint = $cleanBangla($c->complaint ?? '');
        $c->note      = $cleanBangla($c->note ?? '');
    }
    foreach ($diagnoses as $d) {
        $d->name = $cleanBangla($d->name ?? '');
        $d->note = $cleanBangla($d->note ?? '');
    }
    foreach ($investigations as $i) {
        $i->name = $cleanBangla($i->name ?? '');
        $i->note = $cleanBangla($i->note ?? '');
    }

    // (optional) clean doctor fields too
    foreach ($rxDoctors as $doc) {
        $doc->doctor_name = $cleanBangla($doc->doctor_name ?? '');
        $doc->speciality  = $cleanBangla($doc->speciality ?? '');
        $doc->role        = $cleanBangla($doc->role ?? '');
        $doc->note        = $cleanBangla($doc->note ?? '');
    }

    $data = compact('pid','rx','patient','complaints','diagnoses','investigations','medicines','rxDoctors');

    $html = view('prescriptions.pdf', $data)->render();

    // --- mPDF font setup ---
    $defaultConfig = (new ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 8,
        'margin_right' => 8,
        'margin_top' => 8,
        'margin_bottom' => 8,

        // add your public/fonts folder
        'fontDir' => array_merge($fontDirs, [
            base_path('public/fonts'),
        ]),

        // register NotoSansBengali
        'fontdata' => $fontData + [
            'notobengali' => [
                'R'  => 'NotoSansBengali-Regular.ttf',
                'B'  => 'NotoSansBengali-Bold.ttf',
                'I'  => 'NotoSansBengali-Regular.ttf',
                'BI' => 'NotoSansBengali-Bold.ttf',
            ],
        ],

        'default_font' => 'notobengali',

        // Bangla shaping / ligatures ON
        'useOTL' => 1,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output("prescription-{$pid}.pdf", 'S'))
        ->header('Content-Type', 'application/pdf');
}
public function showCaesarean(Request $r)
{
    $tab       = $r->query('tab', 'patients');
    $pid       = $r->query('id');           // prescriptions.id
    $patientId = $r->query('patient');      // optional

    // Common data
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

    // Preload ALL patients for the dropdown
    $patients = collect();
    if (Schema::hasTable('patients')) {
        $patients = DB::table('patients')
            ->orderBy('patientname')
            ->get(['id', 'patientname', 'mobile_no', 'patientcode', 'gender', 'age', 'blood_group']);
    }

    // Load caesarean-specific data if prescription exists
    $complaints = collect();
    if ($pid && Schema::hasTable('prescriptions_complain')) {
        $complaints = DB::table('prescriptions_complain')
            ->where('prescription_id', $pid)
            ->where('active', 1)
            ->orderBy('id')
            ->get(['id','complaint','note']);
    }

    $diagnoses = collect();
    if ($pid && Schema::hasTable('prescriptions_diagnosis')) {
        $diagnoses = DB::table('prescriptions_diagnosis')
            ->where('prescription_id', $pid)
            ->where('active', 1)
            ->orderBy('id')
            ->get(['id','name','note']);
    }

    $medicines = collect();
    if ($pid && Schema::hasTable('prescriptions_medicine')) {
        $medicines = DB::table('prescriptions_medicine')
            ->where('prescription_id', $pid)
            ->orderBy('id')
            ->get(['id','name','strength','dose','route','frequency','duration','timing','note']);
    }

    $investigations = collect();
    if ($pid && Schema::hasTable('prescriptions_investigations')) {
        $investigations = DB::table('prescriptions_investigations')
            ->where('prescription_id', (string)$pid)
            ->orderBy('id')
            ->get(['id','name','note']);
    }

    // Common master lists
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
        if (Schema::hasColumn('common_complain','active')) $q->where('active',1);
        $commoncomplain = $q->get();
    }

    // Get ONLY prescriptions that are confirmed as Caesarean delivery
    $caesareanPrescriptions = collect();
    
    // Simple approach: First check if column exists, add if needed
    if (Schema::hasTable('prescriptions')) {
        if (!Schema::hasColumn('prescriptions', 'delivery_type')) {
            DB::statement("ALTER TABLE prescriptions ADD COLUMN delivery_type VARCHAR(50) DEFAULT NULL AFTER patient_id");
        }
        
        // Check if any Caesarean prescriptions exist
        $caesareanCount = DB::table('prescriptions')
            ->where('status', '!=', 9)
            ->where('delivery_type', 'Caesarean')
            ->count();
            
        // Only get prescriptions if they exist
        if ($caesareanCount > 0) {
            $caesareanPrescriptions = DB::table('prescriptions')
                ->join('patients', 'patients.id', '=', 'prescriptions.patient_id')
                ->where('prescriptions.status', '!=', 9) // Exclude cancelled
                ->where('prescriptions.delivery_type', 'Caesarean') // Only exact match
                ->orderByDesc('prescriptions.id')
                ->select([
                    'prescriptions.id',
                    'prescriptions.patient_id',
                    'prescriptions.created_at',
                    'prescriptions.delivery_type',
                    'prescriptions.status',
                    'patients.patientname',
                    'patients.mobile_no',
                    'patients.patientcode',
                    'patients.age',
                    'patients.gender'
                ])
                ->get();
        }
    }

    return view('prescriptions.wizard-caesarean', compact(
        'tab','pid','patientId','patient','rx',
        'patients','commoncomplain',
        'complaints','investigations','diagnoses','medicines',
        'commonInvestigations','commonDiagnoses','commonMedicines',
        'caesareanPrescriptions'
    ));
}

public function getCaesareanPrescriptions()
{
    // API endpoint to get only Caesarean delivery confirmed prescriptions
    $caesareanPrescriptions = collect();
    if (Schema::hasTable('prescriptions')) {
        // Check if delivery_type column exists, if not add it
        if (!Schema::hasColumn('prescriptions', 'delivery_type')) {
            DB::statement("ALTER TABLE prescriptions ADD COLUMN delivery_type VARCHAR(50) DEFAULT NULL AFTER patient_id");
        }
        
        $caesareanPrescriptions = DB::table('prescriptions')
            ->join('patients', 'patients.id', '=', 'prescriptions.patient_id')
            ->where(function($query) {
                $query->where('prescriptions.delivery_type', 'Caesarean')
                      ->orWhere('prescriptions.delivery_type', 'C-section')
                      ->orWhere('prescriptions.delivery_type', 'Caesarean Section');
            })
            ->where('prescriptions.status', '!=', 9) // Exclude cancelled
            ->orderByDesc('prescriptions.id')
            ->select([
                'prescriptions.id',
                'prescriptions.patient_id',
                'prescriptions.created_at',
                'prescriptions.delivery_type',
                'prescriptions.status',
                'patients.patientname',
                'patients.mobile_no',
                'patients.patientcode',
                'patients.age',
                'patients.gender'
            ])
            ->get();
    }

    return response()->json([
        'success' => true,
        'data' => $caesareanPrescriptions
    ]);
}

public function addDeliveryTypeColumn()
{
    // Method to add delivery_type column if it doesn't exist
    if (Schema::hasTable('prescriptions') && !Schema::hasColumn('prescriptions', 'delivery_type')) {
        DB::statement("ALTER TABLE prescriptions ADD COLUMN delivery_type VARCHAR(50) DEFAULT NULL AFTER patient_id");
        return response()->json([
            'success' => true,
            'message' => 'delivery_type column added successfully'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'delivery_type column already exists or table not found'
    ]);
}

public function updateDeliveryType(Request $r)
{
    $data = $r->validate([
        'prescription_id' => 'required|integer|exists:prescriptions,id',
        'delivery_type' => 'required|string|in:Normal,Caesarean,C-section,Caesarean Section,Vaginal'
    ]);

    $updated = DB::table('prescriptions')
        ->where('id', $data['prescription_id'])
        ->update([
            'delivery_type' => $data['delivery_type'],
            'updated_at' => now()
        ]);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Delivery type updated successfully'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Failed to update delivery type'
    ]);
}

public function testCaesarean()
{
    // Simple test method to check if functionality works
    try {
        // Check if table exists
        if (!Schema::hasTable('prescriptions')) {
            return response()->json(['error' => 'prescriptions table not found']);
        }

        // Check/add column
        if (!Schema::hasColumn('prescriptions', 'delivery_type')) {
            DB::statement("ALTER TABLE prescriptions ADD COLUMN delivery_type VARCHAR(50) DEFAULT NULL AFTER patient_id");
        }

        // Get total prescriptions
        $total = DB::table('prescriptions')->count();
        
        // Get prescriptions with NULL delivery_type
        $nullDelivery = DB::table('prescriptions')->whereNull('delivery_type')->count();
        
        // Get confirmed Caesarean prescriptions
        $caesarean = DB::table('prescriptions')->whereIn('delivery_type', ['Caesarean', 'C-section', 'Caesarean Section'])->count();

        // Get all delivery types for debugging
        $allDeliveryTypes = DB::table('prescriptions')
            ->select('delivery_type', DB::raw('count(*) as count'))
            ->groupBy('delivery_type')
            ->get();

        return response()->json([
            'success' => true,
            'total_prescriptions' => $total,
            'null_delivery_type' => $nullDelivery,
            'confirmed_caesarean' => $caesarean,
            'all_delivery_types' => $allDeliveryTypes,
            'message' => 'Test completed successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

public function debugCaesarean()
{
    // Debug method to see the actual query results
    try {
        // Check if delivery_type column exists
        if (!Schema::hasColumn('prescriptions', 'delivery_type')) {
            return response()->json(['error' => 'delivery_type column does not exist']);
        }

        // Run the exact same query as showCaesarean
        $results = DB::table('prescriptions')
            ->join('patients', 'patients.id', '=', 'prescriptions.patient_id')
            ->where('prescriptions.status', '!=', 9)
            ->where('prescriptions.delivery_type', 'Caesarean') // Only exact match
            ->orderByDesc('prescriptions.id')
            ->select([
                'prescriptions.id',
                'prescriptions.patient_id',
                'prescriptions.created_at',
                'prescriptions.delivery_type',
                'prescriptions.status',
                'patients.patientname',
                'patients.mobile_no',
                'patients.patientcode',
                'patients.age',
                'patients.gender'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'count' => $results->count(),
            'data' => $results,
            'message' => 'Debug query completed'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


public function oeAjax(Request $request)
{
    $action = $request->input('action');
    $pid    = (int) $request->input('prescription_id');

    if (!$pid) {
        return response()->json(['ok' => false, 'error' => 'Prescription ID required'], 422);
    }

    $usesPrescriptionOe = in_array($action, ['list', 'add', 'update', 'delete'], true);
    if ($usesPrescriptionOe && !Schema::hasTable('prescription_oe')) {
        return response()->json(['ok' => false, 'error' => 'Table prescription_oe not found'], 500);
    }

    try {
        if ($action === 'list') {
            $rows = DB::table('prescription_oe as po')
                ->leftJoin('oe_master as om', 'om.id', '=', 'po.oe_master_id')
                ->where('po.prescription_id', $pid)
                ->orderBy('om.sort_order')
                ->orderBy('po.id')
                ->get([
                    'po.id',
                    'po.oe_master_id as field_id',
                    'po.value',
                    'om.name',
                    'om.unit',
                ]);
            return response()->json(['ok' => true, 'rows' => $rows]);
        }

        if ($action === 'add') {
            $fieldId = (int) $request->input('field_id');
            $value   = trim((string) $request->input('value'));

            if (!$fieldId || $value === '') {
                return response()->json(['ok' => false, 'error' => 'Field and Value required'], 422);
            }

            if (DB::table('prescription_oe')->where('prescription_id', $pid)->where('oe_master_id', $fieldId)->exists()) {
                return response()->json(['ok' => false, 'error' => 'This field already added'], 422);
            }

            $id = DB::table('prescription_oe')->insertGetId([
                'prescription_id' => $pid,
                'oe_master_id'    => $fieldId,
                'value'           => $value,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            return response()->json(['ok' => true, 'id' => $id]);
        }

        if ($action === 'latest') {
            $fieldId = (int) $request->input('field_id');

            if (!$fieldId) {
                return response()->json(['ok' => false, 'error' => 'Field required'], 422);
            }

            // Resolve the patient from the prescription
            $patient = DB::table('patients')
                ->join('prescriptions', 'prescriptions.patient_id', '=', 'patients.id')
                ->where('prescriptions.id', $pid)
                ->select('patients.*')
                ->first();

            $value = null;
            $foundAssessmentId = null;
            if ($patient) {
                // Shared resolver: prescription snapshot link → patient latest
                $foundAssessmentId = app(\App\Services\PreconAssessmentReader::class)
                    ->latestAssessmentId($pid, $patient->patientcode ?? null, $patient->id ?? null);

                if ($foundAssessmentId && Schema::hasTable('preconassessment_values')) {
                    $row = DB::table('preconassessment_values')
                        ->where('assessment_id', $foundAssessmentId)
                        ->where('oe_master_id', $fieldId)
                        ->first();
                    $value = $row->value ?? null;
                }
            }

            return response()->json([
                'ok'            => true,
                'assessment_id' => $foundAssessmentId,
                'oe_master_id'  => $fieldId,
                'value'         => $value,
            ]);
        }

        if ($action === 'update') {
            $id    = (int) $request->input('id');
            $value = trim((string) $request->input('value'));

            if (!$id || $value === '') {
                return response()->json(['ok' => false, 'error' => 'ID and Value required'], 422);
            }

            DB::table('prescription_oe')
                ->where('id', $id)
                ->where('prescription_id', $pid)
                ->update(['value' => $value, 'updated_at' => now()]);

            return response()->json(['ok' => true]);
        }

        if ($action === 'delete') {
            $id = (int) $request->input('id');
            if (!$id) return response()->json(['ok' => false, 'error' => 'ID required'], 422);

            DB::table('prescription_oe')
                ->where('id', $id)
                ->where('prescription_id', $pid)
                ->delete();

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'error' => 'Invalid action'], 422);

    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

public function printPreview(Request $request, $id = null)
{
    // ... আপনার কোড (ঠিক আছে) ...
}

// ========== এখানে নতুন মেথড ==========
private function buildWizardOeText(int $pid, $patient, $rx): string
{
    $always = ['Pulse', 'BP', 'Temp', 'Weight'];
    $values = ['Pulse' => '', 'BP' => '', 'Temp' => '', 'Weight' => ''];
    $extra  = [];

    // 1) Wizard O/E tab (prescription_oe + oe_master)
    if (Schema::hasTable('prescription_oe') && Schema::hasTable('oe_master')) {
        $oeRows = DB::table('prescription_oe as po')
            ->join('oe_master as om', 'om.id', '=', 'po.oe_master_id')
            ->where('po.prescription_id', $pid)
            ->whereNotNull('po.value')
            ->where('po.value', '<>', '')
            ->orderBy('om.sort_order')
            ->orderBy('om.id')
            ->get(['om.name', 'om.short_name', 'om.unit', 'po.value']);

        foreach ($oeRows as $row) {
            $label = trim((string) (($row->short_name !== '' && $row->short_name !== null) ? $row->short_name : $row->name));
            $val   = trim((string) $row->value);
            $unit  = trim((string) ($row->unit ?? ''));
            $full  = $val . ($unit !== '' ? ' ' . $unit : '');

            $lower = mb_strtolower($label);
            if (str_contains($lower, 'pulse')) {
                $values['Pulse'] = $full;
            } elseif (str_contains($lower, 'bp') || str_contains($lower, 'blood')) {
                $values['BP'] = $full;
            } elseif (str_contains($lower, 'temp')) {
                $values['Temp'] = $full;
            } elseif (str_contains($lower, 'weight') || str_contains($lower, 'wt')) {
                $values['Weight'] = $full;
            } else {
                $extra[] = ($label !== '' ? $label . ': ' : '') . $full;
            }
        }
    }

    // 1.5+2) Pre-Con Assessment via shared reader (per-prescription snapshot → patient latest).
    // Keeps ALL fields: matched ones fill Pulse/BP/Temp/Weight gaps, the rest go to $extra.
    $preconText = app(\App\Services\PreconAssessmentReader::class)
        ->textFor($pid, $patient->patientcode ?? null, $patient->id ?? null);
    if ($preconText !== '') {
        foreach (explode("\n", $preconText) as $oeLine) {
            $oeLine = trim($oeLine);
            if ($oeLine === '') {
                continue;
            }
            $colon = strpos($oeLine, ':');
            if ($colon === false) {
                $extra[] = $oeLine;
                continue;
            }
            $label = trim(substr($oeLine, 0, $colon));
            $full  = trim(substr($oeLine, $colon + 1));
            $lower = mb_strtolower($label);
            if ($values['Pulse'] === '' && str_contains($lower, 'pulse')) {
                $values['Pulse'] = $full;
            } elseif ($values['BP'] === '' && (str_contains($lower, 'bp') || str_contains($lower, 'blood'))) {
                $values['BP'] = $full;
            } elseif ($values['Temp'] === '' && str_contains($lower, 'temp')) {
                $values['Temp'] = $full;
            } elseif ($values['Weight'] === '' && (str_contains($lower, 'weight') || str_contains($lower, 'wt'))) {
                $values['Weight'] = $full;
            } else {
                $extra[] = $oeLine;
            }
        }
    }

    // 3) nursing_admissions
    $patientcode = $patient->patientcode ?? null;
    if ($patientcode && Schema::hasTable('nursing_admissions')) {
        $rec = DB::table('nursing_admissions')
            ->where('patient_code', $patientcode)
            ->orderByDesc('id')
            ->first();

        if ($rec) {
            if ($values['Pulse'] === '' && !empty($rec->pulse)) {
                $values['Pulse'] = (string) $rec->pulse;
            }
            if ($values['BP'] === '' && !empty($rec->bp)) {
                $values['BP'] = (string) $rec->bp;
            }
            if ($values['Temp'] === '') {
                $t = $rec->temp ?? $rec->temperature ?? '';
                if ($t !== '') {
                    $values['Temp'] = (string) $t;
                }
            }
            if ($values['Weight'] === '' && !empty($rec->weight)) {
                $values['Weight'] = (string) $rec->weight;
            }
        }
    }

    $lines = [];
    foreach ($always as $label) {
        $lines[] = $label . ': ' . ($values[$label] ?? '');
    }
    foreach ($extra as $line) {
        $lines[] = $line;
    }

    return implode("\n", $lines);
}

public function markAsCaesarean($prescriptionId)
{
    try {
        $updated = DB::table('prescriptions')
            ->where('id', $prescriptionId)
            ->update([
                'delivery_type' => 'Caesarean',
                'updated_at' => now()
            ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => "Prescription {$prescriptionId} marked as Caesarean"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Prescription {$prescriptionId} not found"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
}