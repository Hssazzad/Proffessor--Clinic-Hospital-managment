<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends Controller
{
    // 1) SEARCH
    public function search(Request $request)
    {
        $patientId = $request->query('patient');
        $patients = DB::table('patients')
            ->orderBy('patientname')
            ->get();
        $selectedPatient = null;
        $prescriptions = collect();
        if ($patientId) {
            $selectedPatient = DB::table('patients')
                ->where('id', $patientId)
                ->first();
            $prescriptions = $this->getPatientPrescriptions($patientId, $selectedPatient);
        }
        return view('prescriptions.search', compact(
            'patients',
            'selectedPatient',
            'prescriptions',
            'patientId'
        ));
    }

    private function getPatientPrescriptions($patientId, $selectedPatient)
    {
        $prescriptions = collect();
        $prescriptions = DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                $item->source = 'prescription';
                $item->display_date = $item->prescribed_on ?? $item->created_at;
                $item->label = $item->prescription_no ?? ('RX #' . $item->id);
                return $item;
            });

        if ($selectedPatient && !empty($selectedPatient->patientcode)) {
            $pc = $selectedPatient->patientcode;
            $admissionTables = [
                'patient_admission_medicines' => ['label' => 'On Admission', 'stage' => 'on_admission'],
                'patient_presurgery_medicines' => ['label' => 'Pre-Surgery', 'stage' => 'pre_surgery'],
                'patient_postsurgery_medicines' => ['label' => 'Post-Surgery', 'stage' => 'post_surgery'],
                'patient_round_medicines' => ['label' => 'Round', 'stage' => 'round'],
                'patient_instant_medicines' => ['label' => 'Fresh Rx', 'stage' => 'fresh'],
                'patient_fresh_medicines' => ['label' => 'Discharge', 'stage' => 'discharge'],
            ];
            foreach ($admissionTables as $table => $info) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                $col = Schema::hasColumn($table, 'patientcode') ? 'patientcode' : 'patientid';
                $query = DB::table($table)->where($col, $pc);
                if (Schema::hasColumn($table, 'active')) {
                    $query->where('active', 1);
                }
                $admCol = Schema::hasColumn($table, 'admissionid') ? 'admissionid' : 'patient_admission_id';
                $hasCreatedAt = Schema::hasColumn($table, 'created_at');
                $selectCols = [$admCol . ' as admissionid'];
                if ($hasCreatedAt) {
                    $selectCols[] = 'created_at';
                }
                $rows = $query->select($selectCols)->distinct()->get();
                foreach ($rows as $r) {
                    if (empty($r->admissionid)) {
                        continue;
                    }
                    $prescriptions->push((object) [
                        'id' => 'adm-' . $table . '-' . $r->admissionid,
                        'source' => 'admission',
                        'stage' => $info['stage'],
                        'label' => $info['label'],
                        'display_date' => $r->created_at ?? now(),
                        'admissionid' => $r->admissionid,
                        'table_name' => $table,
                        'doctor_name' => $info['label'],
                    ]);
                }
            }
            if (Schema::hasTable('surgery_prescriptions')) {
                $surgeryRx = DB::table('surgery_prescriptions')
                    ->where('patient_id', $patientId)
                    ->orderByDesc('id')
                    ->get()
                    ->map(function ($item) {
                        $item->source = 'surgery';
                        $item->display_date = $item->created_at;
                        $item->label = $item->prescription_no ?? ('SUR #' . $item->id);
                        return $item;
                    });
                $prescriptions = $prescriptions->concat($surgeryRx);
            }
        }

        return $prescriptions->sortByDesc(function ($p) {
            return $p->display_date ?? now();
        })->values();
    }

    // 2) AJAX PREVIEW
    public function previewAjax($id)
    {
        $rx = DB::table('prescriptions')->where('id', $id)->first();
        if (!$rx) {
            return response('Prescription not found', 404);
        }
        $patient = DB::table('patients')->where('id', $rx->patient_id)->first();
        $complaints = DB::table('prescriptions_complain')->where('prescription_id', $id)->get();
        $diagnoses = DB::table('prescriptions_diagnosis')->where('prescription_id', $id)->get();
        $investigations = DB::table('prescriptions_investigations')->where('prescription_id', $id)->get();
        $medicines = DB::table('prescriptions_medicine')->where('prescription_id', $id)->get();
        $rxDoctors = collect();
        if (Schema::hasTable('prescription_doctors')) {
            $rxDoctors = DB::table('prescription_doctors as pd')
                ->leftJoin('doctors as d', 'd.id', '=', 'pd.doctor_id')
                ->where('pd.prescription_id', $id)
                ->select('pd.*', 'd.doctor_name', 'd.name', 'd.speciality')
                ->get();
        }
        $clinicAddr = 'Shah Amanat (R.) Road, G.E.C. Circle, Chattagram';
        $clinicPhone = 'Phone: 880-18-19-433633';
        $html = view('prescriptions.tabs.print-preview', [
            'rx' => $rx,
            'rxDoctors' => $rxDoctors,
            'patient' => $patient,
            'complaints' => $complaints,
            'diagnoses' => $diagnoses,
            'investigations' => $investigations,
            'medicines' => $medicines,
            'clinicAddr' => $clinicAddr,
            'clinicPhone' => $clinicPhone,
            'stageLabel' => 'regular',
        ])->render();
        return response($html);
    }

    // 3) PDF GENERATION
    public function pdf($id)
    {
        $rx = DB::table('prescriptions')->where('id', $id)->first();
        if (!$rx) {
            abort(404);
        }
        $patient = DB::table('patients')->where('id', $rx->patient_id)->first();
        $complaints = DB::table('prescriptions_complain')->where('prescription_id', $id)->get();
        $diagnoses = DB::table('prescriptions_diagnosis')->where('prescription_id', $id)->get();
        $investigations = DB::table('prescriptions_investigations')->where('prescription_id', $id)->get();
        $medicines = DB::table('prescriptions_medicine')->where('prescription_id', $id)->get();
        $rxDoctors = collect();
        if (Schema::hasTable('prescription_doctors')) {
            $rxDoctors = DB::table('prescription_doctors as pd')
                ->leftJoin('doctors as d', 'd.id', '=', 'pd.doctor_id')
                ->where('pd.prescription_id', $id)
                ->select('pd.*', 'd.doctor_name', 'd.name', 'd.speciality', 'd.reg_no', 'd.contact')
                ->get();
        }
        $pdf = Pdf::loadView('prescriptions.pdf', [
            'rx' => $rx,
            'rxDoctors' => $rxDoctors,
            'patient' => $patient,
            'complaints' => $complaints,
            'diagnoses' => $diagnoses,
            'investigations' => $investigations,
            'medicines' => $medicines,
        ])->setPaper('A4', 'portrait');
        return $pdf->stream("prescription-$id.pdf");
    }

    // 4) BROWSER PRINT
    public function printView($id, Request $request)
    {
        $rx = DB::table('prescriptions')->where('id', $id)->first();
        if (!$rx) {
            abort(404, 'Prescription not found');
        }

        $patientId = $request->query('patient', $rx->patient_id);
        $patient = DB::table('patients')->where('id', $patientId)->first();

        $complaints = DB::table('prescriptions_complain')
            ->where('prescription_id', $id)
            ->get();

        $diagnoses = DB::table('prescriptions_diagnosis')
            ->where('prescription_id', $id)
            ->get();

        $investigations = DB::table('prescriptions_investigations')
            ->where('prescription_id', $id)
            ->get();

        $medicines = DB::table('prescriptions_medicine')
            ->where('prescription_id', $id)
            ->get();

        $rxDoctors = collect();
        if (Schema::hasTable('prescription_doctors')) {
            $rxDoctors = DB::table('prescription_doctors as pd')
                ->leftJoin('doctors as d', 'd.id', '=', 'pd.doctor_id')
                ->where('pd.prescription_id', $id)
                ->select('pd.*', 'd.doctor_name', 'd.name', 'd.speciality', 'd.reg_no', 'd.contact')
                ->get();
        }

        $oeText = $this->buildPrintOeText($patient->patientcode ?? '', $rx);

        $data = [
            'rx'             => $rx,
            'patient'        => $patient,
            'patientId'      => $patientId,
            'rxDoctors'      => $rxDoctors,
            'complaints'     => $complaints,
            'diagnoses'      => $diagnoses,
            'investigations' => $investigations,
            'medicines'      => $medicines,
            'oeText'         => $oeText,
            'clinicName'     => config('app.name', 'Professor Clinic'),
            'clinicAddr'     => 'Your clinic address here',
            'clinicPhone'    => '01XXXXXXXXX',
        ];

        return view('prescriptions.print', $data);
    }

    // ============ SURGERY PRESCRIPTION ============
    public function storeSurgeryPrescription(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|integer',
                'surgery_name' => 'required|string',
                'surgery_date' => 'required|date',
                'medicines' => 'required|array',
                'medicines.*.name' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $prescriptionId = DB::table('surgery_prescriptions')->insertGetId([
                'patient_id' => $request->patient_id,
                'surgery_name' => $request->surgery_name,
                'surgery_date' => $request->surgery_date,
                'anesthesia_type' => $request->anesthesia_type,
                'ward_bed' => $request->ward_bed,
                'bp' => $request->bp,
                'pulse' => $request->pulse,
                'temperature' => $request->temperature,
                'weight' => $request->weight,
                'instructions' => $request->instructions,
                'template_id' => $request->template_id,
                'created_by' => auth()->id(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->has('medicines')) {
                foreach ($request->medicines as $medicine) {
                    DB::table('surgery_prescription_medicines')->insert([
                        'prescription_id' => $prescriptionId,
                        'medicine_id' => $medicine['template_medicine_id'] ?? null,
                        'name' => $medicine['name'],
                        'strength' => $medicine['strength'] ?? null,
                        'brand' => $medicine['brand'] ?? 'Generic',
                        'dosage' => $medicine['dosage'],
                        'duration' => $medicine['duration'],
                        'order_type' => $medicine['order_type'],
                        'route' => $medicine['route'] ?? 'Oral',
                        'frequency' => $medicine['frequency'] ?? null,
                        'medicine_type' => $medicine['medicine_type'] ?? 'Tablet',
                        'instructions' => $medicine['instructions'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            if ($request->has('diagnoses')) {
                foreach ($request->diagnoses as $diagnosis) {
                    DB::table('surgery_prescription_diagnoses')->insert([
                        'prescription_id' => $prescriptionId,
                        'name' => $diagnosis['name'],
                        'notes' => $diagnosis['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            if ($request->has('investigations')) {
                foreach ($request->investigations as $investigation) {
                    DB::table('surgery_prescription_investigations')->insert([
                        'prescription_id' => $prescriptionId,
                        'name' => $investigation['name'],
                        'notes' => $investigation['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            if ($request->has('advices')) {
                foreach ($request->advices as $advice) {
                    DB::table('surgery_prescription_advices')->insert([
                        'prescription_id' => $prescriptionId,
                        'advice' => $advice['advice'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            if ($request->has('discharge')) {
                DB::table('surgery_prescription_discharges')->insert([
                    'prescription_id' => $prescriptionId,
                    'treatment' => $request->discharge['treatment'] ?? null,
                    'condition' => $request->discharge['condition'] ?? null,
                    'follow_up' => $request->discharge['follow_up'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Surgery prescription saved successfully',
                'data' => ['id' => $prescriptionId],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Surgery prescription store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving prescription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function surgeryPreview($id)
    {
        $rx = DB::table('surgery_prescriptions')->where('id', $id)->first();
        if (!$rx) {
            return response('Surgery prescription not found', 404);
        }
        $patient = DB::table('patients')->where('id', $rx->patient_id)->first();
        $medicines = DB::table('surgery_prescription_medicines')
            ->where('prescription_id', $id)->get();
        $diagnoses = DB::table('surgery_prescription_diagnoses')
            ->where('surgery_prescription_id', $id)->get();
        $investigations = DB::table('surgery_investigations')
            ->where('prescription_id', $id)->get();
        $complaints = collect();
        $advices = collect();
        if (Schema::hasTable('surgery_prescription_advices')) {
            $advices = DB::table('surgery_prescription_advices')
                ->where('prescription_id', $id)->get();
        }
        $clinicAddr = 'Shah Amanat (R.) Road, G.E.C. Circle, Chattagram';
        $clinicPhone = 'Phone: 880-18-19-433633';
        $html = view('prescriptions.tabs.print-preview', [
            'rx' => $rx,
            'patient' => $patient,
            'complaints' => $complaints,
            'diagnoses' => $diagnoses,
            'investigations' => $investigations,
            'medicines' => $medicines,
            'advices' => $advices,
            'clinicAddr' => $clinicAddr,
            'clinicPhone' => $clinicPhone,
            'stageLabel' => 'surgery',
        ])->render();
        return response($html);
    }

    public function printData($id)
    {
        $rx = DB::table('prescriptions')->where('id', $id)->first();
        if (!$rx) {
            return response()->json(['error' => 'Prescription not found'], 404);
        }
        $patient = DB::table('patients')->where('id', $rx->patient_id)->first();
        $doctor = null;
        if (Schema::hasTable('prescription_doctors')) {
            $doctor = DB::table('prescription_doctors as pd')
                ->leftJoin('doctors as d', 'd.id', '=', 'pd.doctor_id')
                ->where('pd.prescription_id', $id)
                ->select('d.doctor_name', 'd.name', 'd.speciality', 'd.reg_no', 'd.contact')
                ->first();
        }
        $medicines = DB::table('prescriptions_medicine')->where('prescription_id', $id)->get();
        $complaints = DB::table('prescriptions_complain')->where('prescription_id', $id)->get();
        $investigations = DB::table('prescriptions_investigations')->where('prescription_id', $id)->get();
        $cc = $this->buildPrintCcText($complaints);
        if ($cc === '' && !empty($rx->chief_complaint)) {
            $cc = '• ' . $rx->chief_complaint;
        }
        return response()->json([
            'title' => 'Prescription',
            'stageTitle' => 'Prescription',
            'patientCode' => $patient->patientcode ?? '',
            'patientName' => $patient->patientname ?? '',
            'mobile' => $patient->mobile_no ?? $patient->mobileno ?? '',
            'admissionDate' => date('d-m-Y', strtotime($rx->prescribed_on ?? $rx->created_at)),
            'doctorName' => $doctor->doctor_name ?? $doctor->name ?? $rx->doctor_name ?? '',
            'doctorSpeciality' => $doctor->speciality ?? '',
            'doctorRegNo' => $doctor->reg_no ?? '',
            'doctorContact' => $doctor->contact ?? '',
            'cc' => $cc,
            'oe' => $this->buildPrintOeText($patient->patientcode ?? '', $rx),
            'inv' => $this->buildPrintInvText($investigations),
            'medicines' => $this->formatMedicinesForPrint($medicines),
        ]);
    }

    public function admissionPrintData(Request $request)
    {
        $admissionId = $request->query('admissionid');
        $table = $request->query('table');
        $stage = $request->query('stage', '');
        $patientCode = $request->query('patientcode', '');
        $stageLabels = [
            'on_admission' => 'On Admission Medicine Prescription',
            'pre_surgery' => 'Pre-Surgery Medicine Prescription',
            'post_surgery' => 'Post-Surgery Medicine Prescription',
            'round' => 'Round Patient Medicine Prescription',
            'fresh' => 'Fresh Medicine Prescription',
            'discharge' => 'Discharge Medicine Prescription',
        ];
        $stageTitle = $stageLabels[$stage] ?? 'Admission Medicine Prescription';
        if (!$table || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Medicine table not found'], 404);
        }
        $admission = null;
        foreach (['tbl_patient_admission', 'patient_admission'] as $tbl) {
            if (!Schema::hasTable($tbl)) {
                continue;
            }
            $admission = DB::table($tbl)
                ->where('admissionid', $admissionId)
                ->orWhere('id', $admissionId)
                ->first();
            if ($admission) {
                break;
            }
        }
        $patient = null;
        if ($patientCode) {
            $patient = DB::table('patients')->where('patientcode', $patientCode)->first();
        }
        if (!$patient && $admission && !empty($admission->patientcode)) {
            $patient = DB::table('patients')->where('patientcode', $admission->patientcode)->first();
        }
        $pcCol = Schema::hasColumn($table, 'patientcode') ? 'patientcode' : 'patientid';
        $admCol = Schema::hasColumn($table, 'admissionid') ? 'admissionid' : 'patient_admission_id';
        $query = DB::table($table);
        if ($admissionId && Schema::hasColumn($table, $admCol)) {
            $query->where($admCol, $admissionId);
        } elseif ($patientCode && Schema::hasColumn($table, $pcCol)) {
            $query->where($pcCol, $patientCode);
        }
        if (Schema::hasColumn($table, 'active')) {
            $query->where('active', 1);
        }
        $medicines = $query->orderBy('id')->get();
        $doctor = $this->resolveAdmissionPrintDoctor($admission);
        $templateId = $admission->templeteid ?? '';
        return response()->json([
            'title' => $stageTitle,
            'stageTitle' => $stageTitle,
            'patientCode' => $patient->patientcode ?? $patientCode,
            'patientName' => $patient->patientname ?? '',
            'mobile' => $patient->mobile_no ?? $patient->mobileno ?? '',
            'admissionDate' => isset($admission->admission_date)
                ? date('d-m-Y', strtotime($admission->admission_date))
                : date('d-m-Y'),
            'doctorName' => $doctor->doctor_name ?? $doctor->name ?? '',
            'doctorSpeciality' => $doctor->speciality ?? $doctor->Posting ?? '',
            'doctorRegNo' => $doctor->reg_no ?? '',
            'doctorContact' => $doctor->contact ?? '',
            'cc' => $this->buildTemplateCcText($templateId) ?: $stageTitle,
            'oe' => $this->buildPrintOeText($patient->patientcode ?? $patientCode),
            'inv' => $this->buildTemplateInvText($templateId),
            'medicines' => $this->formatMedicinesForPrint($medicines),
        ]);
    }

    private function parseMedicineSchedule($m): array
    {
        $morning = $m->morning ?? $m->sokal ?? '';
        $noon = $m->noon ?? $m->afternoon ?? $m->dupure ?? $m->dupur ?? '';
        $night = $m->night ?? $m->raat ?? '';
        if ($morning === '' && $noon === '' && $night === '') {
            $schedule = $m->frequency ?? $m->dose ?? '';
            if (preg_match('/[\d\/\+]/', $schedule)) {
                $parts = preg_split('/[\+\-]/', str_replace(' ', '', $schedule));
                $morning = $parts[0] ?? '';
                $noon = $parts[1] ?? '';
                $night = $parts[2] ?? '';
            }
        }
        return [
            'name' => $m->name ?? $m->medicine_name ?? '',
            'morning' => $morning,
            'noon' => $noon,
            'night' => $night,
            'strength' => $m->strength ?? '',
            'route' => $m->route ?? '',
            'dose' => $m->dose ?? '',
            'timing' => $m->timing ?? $m->meal_timing ?? '',
            'duration' => $m->duration ?? '',
            'instruction' => $m->note ?? $m->instruction ?? '',
        ];
    }

    private function formatMedicinesForPrint($medicines): array
    {
        return collect($medicines)
            ->map(fn ($m) => $this->parseMedicineSchedule($m))
            ->values()
            ->all();
    }

    private function buildPrintCcText($complaints): string
    {
        return collect($complaints)->map(function ($c) {
            $text = $c->complaint ?? $c->complain ?? $c->name ?? '';
            $note = $c->note ?? '';
            return trim($text) !== '' ? '• ' . $text . ($note ? ' (' . $note . ')' : '') : '';
        })->filter()->implode("\n");
    }

    private function buildPrintInvText($investigations): string
    {
        return collect($investigations)->map(function ($i) {
            $text = $i->name ?? $i->investigation_name ?? $i->test ?? '';
            $note = $i->note ?? $i->notes ?? '';
            return trim($text) !== '' ? '• ' . $text . ($note ? ' (' . $note . ')' : '') : '';
        })->filter()->implode("\n");
    }

    private function buildTemplateCcText($templateId): string
    {
        if (!$templateId || !Schema::hasTable('template_complain')) {
            return '';
        }
        $rows = DB::table('template_complain')->where('templateid', $templateId)->get();
        return $rows->map(fn ($c) => '• ' . $c->complain . ($c->note ? ' (' . $c->note . ')' : ''))->implode("\n");
    }

    private function buildTemplateInvText($templateId): string
    {
        if (!$templateId || !Schema::hasTable('template_investigations')) {
            return '';
        }
        $rows = DB::table('template_investigations')->where('templateid', $templateId)->get();
        return $rows->map(fn ($i) => '• ' . $i->name . ($i->note ? ' (' . $i->note . ')' : ''))->implode("\n");
    }

    private function resolveAdmissionPrintDoctor($admission)
    {
        if (!$admission) {
            return (object) [];
        }
        if (!empty($admission->doctor_id) && Schema::hasTable('doctors')) {
            $doctor = DB::table('doctors')->where('id', $admission->doctor_id)->first();
            if ($doctor) {
                return $doctor;
            }
        }
        if (!empty($admission->doctor_name) || !empty($admission->doctor_reg_no) || !empty($admission->department)) {
            return (object) [
                'doctor_name' => $admission->doctor_name ?? null,
                'name' => $admission->doctor_name ?? null,
                'speciality' => $admission->department ?? null,
                'reg_no' => $admission->doctor_reg_no ?? null,
                'contact' => $admission->doctor_mobile ?? null,
                'Posting' => $admission->department ?? null,
            ];
        }
        return (object) [];
    }

    private function buildPrintOeText($patientcode, $rx = null): string
    {
        $always = ['Pulse', 'BP', 'Temp', 'Weight'];
        $optional = ['Height', 'SpO2', 'RR', 'Notes'];

        // 0) Wizard O/E tab
        if ($rx && !empty($rx->id)
            && Schema::hasTable('prescription_oe')
            && Schema::hasTable('oe_master')) {
            $oeRows = DB::table('prescription_oe')
                ->join('oe_master', 'oe_master.id', '=', 'prescription_oe.oe_master_id')
                ->where('prescription_oe.prescription_id', $rx->id)
                ->whereNotNull('prescription_oe.value')
                ->where('prescription_oe.value', '<>', '')
                ->orderBy('oe_master.sort_order')
                ->orderBy('oe_master.id')
                ->get(['oe_master.name', 'oe_master.short_name', 'oe_master.unit', 'prescription_oe.value']);

            if ($oeRows->isNotEmpty()) {
                $parts = [];
                foreach ($oeRows as $row) {
                    $label = trim((string) ($row->short_name !== '' ? $row->short_name : $row->name));
                    $val = trim((string) $row->value);
                    $unit = trim((string) $row->unit);
                    $parts[] = ($label !== '' ? $label . ': ' : '') . $val . ($unit !== '' ? ' ' . $unit : '');
                }
                return implode("\n", $parts);
            }
        }

        // 1) Pre-Con Assessment via shared reader (per-prescription snapshot → patient latest).
        //    All fields preserved: Pulse/BP/Temp/Weight matched into $values, rest into $extraOE.
        $extraOE = [];
        $values = [
            'Pulse' => '',
            'BP' => '',
            'Temp' => '',
            'Weight' => '',
            'Height' => '',
            'SpO2' => '',
            'RR' => '',
            'Notes' => '',
        ];
        $oeText = app(\App\Services\PreconAssessmentReader::class)
            ->textFor($rx->id ?? null, $patientcode !== '' ? $patientcode : null);
        if ($oeText !== '') {
            foreach (explode("\n", $oeText) as $oeLine) {
                $oeLine = trim($oeLine);
                if ($oeLine === '') {
                    continue;
                }
                $colon = strpos($oeLine, ':');
                if ($colon === false) {
                    $extraOE[] = $oeLine;
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
                    $extraOE[] = $oeLine;
                }
            }
        }

        $record = null;
        if ($rx && !empty($rx->oe_nursing_id)) {
            foreach (['preconassessment', 'nursing_admissions'] as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                $found = DB::table($table)->where('id', $rx->oe_nursing_id)->first();
                if ($found) {
                    $record = $found;
                    break;
                }
            }
        }
        if (!$record && $patientcode && Schema::hasTable('nursing_admissions')) {
            $record = DB::table('nursing_admissions')
                ->where('patient_code', $patientcode)
                ->orderByDesc('id')
                ->first();
        }

        if ($record) {
            $bp = trim(($record->bp_sys ?? '') . '/' . ($record->bp_dia ?? ''), '/');
            if ($bp === '' && !empty($record->bp)) {
                $bp = $record->bp;
            }
            $values = array_merge($values, [
                'Pulse' => $record->pulse ?? $values['Pulse'],
                'BP' => $bp ?: $values['BP'],
                'Temp' => $record->temp ?? $record->temperature ?? $values['Temp'],
                'Weight' => $record->weight ?? $values['Weight'],
                'Height' => $record->height ?? $values['Height'],
                'SpO2' => $record->spo2 ?? $values['SpO2'],
                'RR' => $record->rr ?? $values['RR'],
                'Notes' => $record->notes ?? $values['Notes'],
            ]);
        }

        // 2b) Prescription's own oe_* columns — always gap-fill the core four
        if ($rx) {
            $rxCore = [
                'Pulse'  => $rx->oe_pulse ?? null,
                'BP'     => $rx->oe_bp ?? null,
                'Temp'   => $rx->oe_temp ?? null,
                'Weight' => $rx->oe_weight ?? null,
            ];
            foreach ($rxCore as $label => $val) {
                if (trim((string) ($values[$label] ?? '')) === '' && trim((string) $val) !== '') {
                    $values[$label] = trim((string) $val);
                }
            }
        }

        $lines = [];
        foreach ($always as $label) {
            $lines[] = $label . ': ' . trim((string) ($values[$label] ?? ''));
        }
        foreach ($optional as $label) {
            $v = trim((string) ($values[$label] ?? ''));
            if ($v !== '') {
                $lines[] = $label . ': ' . $v;
            }
        }
        foreach ($extraOE as $extraLine) {
            if (!in_array($extraLine, $lines, true)) {
                $lines[] = $extraLine;
            }
        }
        return implode("\n", $lines);
    }

    public function searchSurgeryMedicines(Request $request)
    {
        try {
            $query = $request->get('q') ?? $request->get('search');
            if (empty($query) || strlen($query) < 2) {
                return response()->json(['success' => true, 'data' => []]);
            }
            if (DB::getSchemaBuilder()->hasTable('common_medicine')) {
                $medicines = DB::table('common_medicine')
                    ->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('strength', 'LIKE', "%{$query}%")
                    ->orWhere('GroupName', 'LIKE', "%{$query}%")
                    ->limit(20)
                    ->get();
                $formatted = $medicines->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'strength' => $item->strength ?? '',
                        'brand' => $item->GroupName ?? 'Generic',
                        'medicine_type' => $item->medicine_type ?? 'Tablet',
                        'text' => $item->name . ' ' . ($item->strength ?? '') . ' (' . ($item->GroupName ?? 'Generic') . ')',
                    ];
                });
                return response()->json(['success' => true, 'data' => $formatted]);
            }
            return response()->json(['success' => true, 'data' => []]);
        } catch (\Exception $e) {
            Log::error('Surgery medicine search error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error', 'data' => []], 500);
        }
    }

    public function getSurgeryTemplates()
    {
        try {
            $templates = [];
            if (DB::getSchemaBuilder()->hasTable('surgery_prescription_templates')) {
                $templates = DB::table('surgery_prescription_templates')
                    ->where('status', 'active')
                    ->orWhereNull('status')
                    ->get();
            }
            return response()->json(['success' => true, 'data' => $templates]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function getSurgeryTemplateData($id)
    {
        try {
            $template = null;
            if (DB::getSchemaBuilder()->hasTable('surgery_prescription_templates')) {
                $template = DB::table('surgery_prescription_templates')->where('id', $id)->first();
            }
            if (!$template) {
                return response()->json(['success' => false, 'message' => 'Template not found'], 404);
            }
            $medicines = DB::table('template_medicines')->where('template_id', $id)->get() ?? [];
            $diagnoses = DB::table('template_diagnoses')->where('template_id', $id)->get() ?? [];
            $investigations = DB::table('template_investigations')->where('template_id', $id)->get() ?? [];
            $advices = DB::table('template_advices')->where('template_id', $id)->get() ?? [];
            return response()->json([
                'success' => true,
                'template' => $template,
                'medicines' => $medicines,
                'diagnoses' => $diagnoses,
                'investigations' => $investigations,
                'advices' => $advices,
                'discharge' => null,
                'counts' => [
                    'medicines' => count($medicines),
                    'diagnoses' => count($diagnoses),
                    'investigations' => count($investigations),
                    'advices' => count($advices),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading template data'], 500);
        }
    }

    public function searchSurgeryPatients(Request $request)
    {
        try {
            $query = $request->get('q');
            $patients = [];
            if (DB::getSchemaBuilder()->hasTable('patients')) {
                $patientsQuery = DB::table('patients');
                if (!empty($query)) {
                    $patientsQuery->where(function ($q) use ($query) {
                        $q->where('patientname', 'LIKE', "%{$query}%")
                            ->orWhere('patientcode', 'LIKE', "%{$query}%")
                            ->orWhere('mobile_no', 'LIKE', "%{$query}%")
                            ->orWhere('phone', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%");
                    });
                }
                $patients = $patientsQuery->limit(50)->get();
            }
            return response()->json(['success' => true, 'data' => $patients]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error', 'data' => []], 500);
        }
    }

    public function searchSurgeryDiagnosis(Request $request)
    {
        try {
            $query = $request->get('search') ?? $request->get('q');
            $diagnoses = [];
            $table = Schema::hasTable('template_diagnosis') ? 'template_diagnosis' : (Schema::hasTable('template_diagnoses') ? 'template_diagnoses' : null);
            if ($table) {
                $q = DB::table($table);
                if (!empty($query)) {
                    $q->where('name', 'LIKE', "%{$query}%");
                }
                $diagnoses = $q->limit(20)->get();
            }
            return response()->json(['success' => true, 'data' => $diagnoses]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function searchSurgeryInvestigations(Request $request)
    {
        try {
            $query = $request->get('search') ?? $request->get('q');
            $investigations = [];
            if (Schema::hasTable('template_investigations')) {
                $q = DB::table('template_investigations');
                if (!empty($query)) {
                    $q->where('name', 'LIKE', "%{$query}%");
                }
                $investigations = $q->limit(20)->get();
            }
            return response()->json(['success' => true, 'data' => $investigations]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function searchSurgeryAdvice(Request $request)
    {
        try {
            $query = $request->get('search') ?? $request->get('q');
            $advices = [];
            $table = Schema::hasTable('template_advice') ? 'template_advice' : (Schema::hasTable('template_advices') ? 'template_advices' : null);
            if ($table) {
                $q = DB::table($table);
                if (!empty($query)) {
                    $q->where('name', 'LIKE', "%{$query}%")->orWhere('advice', 'LIKE', "%{$query}%");
                }
                $advices = $q->limit(20)->get();
            }
            return response()->json(['success' => true, 'data' => $advices]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function searchSurgeryDischarge(Request $request)
    {
        try {
            $query = $request->get('search') ?? $request->get('q');
            $discharges = [];
            if (Schema::hasTable('template_discharge')) {
                $q = DB::table('template_discharge');
                if (!empty($query)) {
                    $q->where('name', 'LIKE', "%{$query}%");
                }
                $discharges = $q->limit(20)->get();
            }
            return response()->json(['success' => true, 'data' => $discharges]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function searchSurgeryComplain(Request $request)
    {
        try {
            $query = $request->get('search') ?? $request->get('q');
            $complains = [];
            if (Schema::hasTable('template_complain')) {
                $q = DB::table('template_complain');
                if (!empty($query)) {
                    $q->where('name', 'LIKE', "%{$query}%")->orWhere('complain', 'LIKE', "%{$query}%");
                }
                $complains = $q->limit(20)->get();
            }
            return response()->json(['success' => true, 'data' => $complains]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    public function admissionStagePreview(Request $request, $admissionId)
    {
        $stage = $request->query('stage');
        $table = $request->query('table');
        $admission = null;
        foreach (['tbl_patient_admission', 'patient_admission'] as $tbl) {
            if (Schema::hasTable($tbl)) {
                $admission = DB::table($tbl)->where('id', $admissionId)
                    ->orWhere('admissionid', $admissionId)
                    ->first();
                if ($admission) {
                    break;
                }
            }
        }
        $patient = null;
        if ($admission) {
            $patient = DB::table('patients')->where('id', $admission->patient_id ?? 0)->first();
            if (!$patient && !empty($admission->patientcode)) {
                $patient = DB::table('patients')->where('patientcode', $admission->patientcode)->first();
            }
        }
        $medicines = collect();
        if ($table && Schema::hasTable($table)) {
            $pcCol = Schema::hasColumn($table, 'patientcode') ? 'patientcode' : 'patientid';
            $admCol = Schema::hasColumn($table, 'admissionid') ? 'admissionid' : 'patient_admission_id';
            $query = DB::table($table);
            if (Schema::hasColumn($table, $admCol)) {
                $query->where($admCol, $admissionId);
            } elseif ($patient && !empty($patient->patientcode) && Schema::hasColumn($table, $pcCol)) {
                $query->where($pcCol, $patient->patientcode);
            }
            if (Schema::hasColumn($table, 'active')) {
                $query->where('active', 1);
            }
            $medicines = $query->orderBy('id')->get();
        }
        $clinicAddr = 'Shah Amanat (R.) Road, G.E.C. Circle, Chattagram';
        $clinicPhone = 'Phone: 880-18-19-433633';
        $html = view('prescriptions.tabs.print-preview', [
            'rx' => $admission,
            'patient' => $patient,
            'complaints' => collect(),
            'diagnoses' => collect(),
            'investigations' => collect(),
            'medicines' => $medicines,
            'clinicAddr' => $clinicAddr,
            'clinicPhone' => $clinicPhone,
            'stageLabel' => $stage,
        ])->render();
        return response($html);
    }
}