<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreConAssessment;
use App\Models\Patient;
use App\Models\OeMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PreConAssessmentController extends Controller
{
    /**
     * Show Pre-Con Assessment Form
     */
    public function create(Request $request)
    {
        $q = $request->get('q'); 
        $patientcode = $request->get('patientcode');

        $patients = Patient::query()
            ->when($q, function ($query) use ($q) {
                $query->where('patientname', 'like', "%{$q}%")
                      ->orWhere('patientcode', 'like', "%{$q}%")
                      ->orWhere('mobile_no', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $selectedPatient = null;
        $assessmentHistory = collect();

        // Every O/E field, enriched with its latest saved value (if any)
        $oeFields = collect();
        if (Schema::hasTable('oe_master')) {
            $oeFields = OeMaster::orderBy('sort_order')->orderBy('name')->get();
        }

        if ($patientcode) {
            $selectedPatient = Patient::where('patientcode', $patientcode)->first();

            // Latest 10 records only
            $assessments = PreConAssessment::where('patientcode', $patientcode)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Load all values for those assessments joined with oe_master (field_code)
            $valueRows = collect();
            if ($assessments->isNotEmpty() && Schema::hasTable('preconassessment_values')) {
                $valueRows = DB::table('preconassessment_values as pv')
                    ->leftJoin('oe_master as om', 'om.id', '=', 'pv.oe_master_id')
                    ->whereIn('pv.assessment_id', $assessments->pluck('id')->all())
                    ->select('pv.assessment_id', 'pv.value', 'pv.oe_master_id', 'om.name', 'om.short_name')
                    ->get()
                    ->groupBy('assessment_id');
            }

            // Convert every assessment into an object with mapped values.
            // Values are keyed by oe_master_id (and/or field name); explicit DB
            // columns (weight/height/bp...) are used as fallback.
            $assessmentHistory = $assessments->map(function ($record) use ($valueRows) {
                $byId   = [];
                $byName = [];

                foreach ($valueRows->get($record->id, collect()) as $row) {
                    if ($row->oe_master_id !== null) {
                        $byId[(int) $row->oe_master_id] = $row->value;
                    }
                    $nm = mb_strtolower(trim((string) ($row->name ?? $row->short_name ?? '')));
                    if ($nm !== '') {
                        $byName[$nm] = $row->value;
                    }
                }

                $defs = [
                    'pulse'  => ['ids' => [1, 0],     'names' => ['pulse'],                  'col' => 'pulse'],
                    'weight' => ['ids' => [4],        'names' => ['weight', 'wt'],           'col' => 'weight'],
                    'height' => ['ids' => [5],        'names' => ['height', 'ht'],           'col' => 'height'],
                    'spo2'   => ['ids' => [6],        'names' => ['o2', 'spo2', 'oxygen'],   'col' => 'spo2'],
                    'temp'   => ['ids' => [3],        'names' => ['temp', 'temperature'],    'col' => 'temp'],
                    'rr'     => ['ids' => [7],        'names' => ['rr', 'resp'],             'col' => 'rr'],
                    'bp'     => ['ids' => [2],        'names' => ['bp', 'blood pressure'],   'col' => null],
                ];

                $res = [];
                foreach ($defs as $key => $d) {
                    $v = null;
                    foreach ($d['ids'] as $id) {
                        if (isset($byId[(int) $id]) && $byId[(int) $id] !== '') { $v = $byId[(int) $id]; break; }
                    }
                    if ($v === null) {
                        foreach ($d['names'] as $nm) {
                            if (isset($byName[$nm]) && $byName[$nm] !== '') { $v = $byName[$nm]; break; }
                        }
                    }
                    if ($v === null && $d['col'] && isset($record->{$d['col']}) && $record->{$d['col']} !== '') {
                        $v = $record->{$d['col']};
                    }
                    $res[$key] = $v;
                }

                if ($res['bp'] === null && isset($record->bp_sys) && $record->bp_sys !== '') {
                    $res['bp'] = trim(trim((string) $record->bp_sys, '/') . '/' . trim((string) ($record->bp_dia ?? ''), '/'), '/');
                }

                $bpParts = $res['bp'] ? explode('/', (string) $res['bp']) : [];

                $record->weight = $res['weight'];
                $record->height = $res['height'];
                $record->pulse  = $res['pulse'];
                $record->spo2   = $res['spo2'];
                $record->temp   = $res['temp'];
                $record->rr     = $res['rr'];
                $record->bp_sys = $bpParts[0] ?? null;
                $record->bp_dia = $bpParts[1] ?? null;

                return $record;
            });

            // Pull latest saved O/E values for this patient (if any)
            if ($selectedPatient && Schema::hasTable('preconassessment_values')) {
                $latestAssessment = PreConAssessment::where('patient_id', $selectedPatient->id)
                    ->orderByDesc('id')
                    ->first();

                if ($latestAssessment) {
                    $latestValues = DB::table('preconassessment_values')
                        ->where('assessment_id', $latestAssessment->id)
                        ->pluck('value', 'oe_master_id');
                } else {
                    $latestValues = collect();
                }

                if ($oeFields->isNotEmpty() && isset($latestValues)) {
                    foreach ($oeFields as $oe) {
                        $oe->value = $latestValues->get($oe->id);
                    }
                }
            }
        }

        return view('preconassessment.create', compact(
            'patients', 'q', 'assessmentHistory', 'patientcode',
            'selectedPatient', 'oeFields'
        ));
    }

    /**
     * Store Assessment Record
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patientcode' => ['required', 'string'],
            'weight'      => ['nullable', 'numeric', 'min:0'],
            'height'      => ['nullable', 'numeric', 'min:0'],
            'temp'        => ['nullable', 'numeric', 'min:0'],
            'bp_sys'      => ['nullable', 'integer', 'min:0'],
            'bp_dia'      => ['nullable', 'integer', 'min:0'],
            'pulse'       => ['nullable', 'integer', 'min:0'],
            'spo2'        => ['nullable', 'integer', 'min:0', 'max:100'],
            'rr'          => ['nullable', 'integer', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'oe_values'   => ['nullable', 'array'],
            'oe_values.*' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            // Get patient ID
            $patient = Patient::where('patientcode', $validatedData['patientcode'])->first();

            $validatedData['patient_id'] = $patient ? $patient->id : null;
            $validatedData['status']     = 1;

            $assessment = PreConAssessment::create($validatedData);

            if (!$assessment) {
                throw new \Exception("Failed to save assessment record");
            }

            // Save dynamic O/E values
            $oeValues = $request->input('oe_values', []);

            foreach ($oeValues as $oeMasterId => $value) {
                if ($value !== null && $value !== '') {
                DB::table('preconassessment_values')->insert([
                    'assessment_id' => $assessment->id,
                    'oe_master_id' => $oeMasterIdInt,
                    'value' => $rawValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Bind this assessment to the patient's active draft prescription so
            // that ONLY that prescription's print shows THESE vitals. Finished
            // prescriptions keep their own snapshot (no cross-contamination).
            $draftId = DB::table('prescriptions')
                ->where('patient_id', $patient->id)
                ->where(function ($q) {
                    $q->whereNull('status')
                      ->orWhere('status', 0);
                })
                ->orderByDesc('id')
                ->value('id');
            if ($draftId) {
                DB::table('prescriptions')
                    ->where('id', $draftId)
                    ->whereNull('preconassessment_id')
                    ->update([
                        'preconassessment_id' => $assessment->getKey(),
                        'updated_at' => now(),
                    ]);
            }
            }

            DB::commit();

            return redirect()
                ->route('prescriptions.preconassessment', ['patientcode' => $validatedData['patientcode']])
                ->with('success', 'Vitals recorded successfully for Patient: ' . $validatedData['patientcode']);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PreConAssessment Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get Assessment History via AJAX
     */
    public function getHistory($patientcode)
    {
        try {
            $history = PreConAssessment::where('patientcode', $patientcode)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(function($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->created_at->format('d M Y'),
                        'time' => $record->created_at->format('h:i A'),
                        'datetime' => $record->created_at->format('d M Y, h:i A'),
                        'weight' => $record->weight ?? '--',
                        'height' => $record->height ?? '--',
                        'bmi' => $record->weight && $record->height 
                            ? number_format(($record->weight / (($record->height/100) ** 2)), 1)
                            : '--',
                        'temp' => $record->temp ?? '--',
                        'bp' => ($record->bp_sys ?? '--') . '/' . ($record->bp_dia ?? '--'),
                        'bp_sys' => $record->bp_sys ?? '--',
                        'bp_dia' => $record->bp_dia ?? '--',
                        'pulse' => $record->pulse ?? '--',
                        'spo2' => $record->spo2 ?? '--',
                        'rr' => $record->rr ?? '--',
                        'notes' => $record->notes ?? 'No notes',
                    ];
                });

            return response()->json([
                'success' => true,
                'total' => $history->count(),
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
?>