<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Centralized reader for Pre-Con Assessment vitals.
 *
 * Produces a plain "label: value unit" text block from either:
 *   1. the assessment linked to a specific prescription (preconassessment_id),
 *   2. or the latest assessment of the patient (patient_id, then patientcode).
 *
 * All dynamic fields are kept (no hardcoded 4-keyword filter, no silent drop),
 * sorted by oe_master.sort_order, id. When the assessment has no dynamic
 * values, the legacy explicit columns (bp_sys/bp_dia/pulse/weight/...) are
 * used as a fallback.
 */
class PreconAssessmentReader
{
    /**
     * @param  int|null    $prescriptionId preferred source: prescriptions.preconassessment_id
     * @param  string|null $patientcode    legacy fallback key
     * @param  int|null    $patientId      fallback key (checked before patientcode)
     * @return string newline-separated "label: value unit" lines, or '' when nothing found
     */
    public function textFor(?int $prescriptionId, ?string $patientcode, ?int $patientId = null): string
    {
        $assessment = $this->resolveAssessment($prescriptionId, $patientcode, $patientId);
        if (!$assessment) {
            return '';
        }

        // 1) Dynamic values: preconassessment_values JOIN oe_master — ALL fields
        if (Schema::hasTable('preconassessment_values') && Schema::hasTable('oe_master')) {
            $rows = DB::table('preconassessment_values as pv')
                ->join('oe_master as om', 'om.id', '=', 'pv.oe_master_id')
                ->where('pv.assessment_id', $assessment->id)
                ->whereNotNull('pv.value')
                ->where('pv.value', '<>', '')
                ->orderBy('om.sort_order')
                ->orderBy('om.id')
                ->get(['om.name', 'om.short_name', 'om.unit', 'pv.value']);

            if ($rows->isNotEmpty()) {
                $lines = [];
                foreach ($rows as $row) {
                    $label = trim((string) (($row->short_name !== null && $row->short_name !== '') ? $row->short_name : $row->name));
                    $full  = trim((string) $row->value) . ($row->unit !== '' ? ' ' . $row->unit : '');
                    $lines[] = ($label !== '' ? $label . ': ' : '') . $full;
                }
                return implode("\n", array_filter($lines, fn ($l) => $l !== ''));
            }
        }

        // 2) Legacy explicit columns fallback (old records)
        return $this->legacyText($assessment);
    }

    /**
     * Resolve only the assessment id (same chain as textFor) — useful for
     * per-field prefills (e.g. wizard O/E tab auto-fill).
     */
    public function latestAssessmentId(?int $prescriptionId, ?string $patientcode, ?int $patientId = null): ?int
    {
        $assessment = $this->resolveAssessment($prescriptionId, $patientcode, $patientId);
        return $assessment ? (int) $assessment->id : null;
    }

    /**
     * Resolve the assessment: prescription link → patient_id latest → patientcode latest.
     */
    private function resolveAssessment(?int $prescriptionId, ?string $patientcode, ?int $patientId): ?object
    {
        if (!Schema::hasTable('preconassessment')) {
            return null;
        }

        // 1) Per-prescription snapshot
        if ($prescriptionId && Schema::hasColumn('prescriptions', 'preconassessment_id')) {
            $linkedId = DB::table('prescriptions')
                ->where('id', $prescriptionId)
                ->value('preconassessment_id');
            if ($linkedId) {
                $row = DB::table('preconassessment')->where('id', $linkedId)->first();
                if ($row) {
                    return $row;
                }
            }
        }

        // 2) Latest by patient_id
        if ($patientId) {
            $row = DB::table('preconassessment')
                ->where('patient_id', $patientId)
                ->orderByDesc('id')
                ->first();
            if ($row) {
                return $row;
            }
        }

        // 3) Latest by patientcode (legacy)
        if ($patientcode !== null && $patientcode !== '') {
            return DB::table('preconassessment')
                ->where('patientcode', $patientcode)
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }

    /**
     * Build text from explicit legacy vitals columns.
     */
    private function legacyText(object $assessment): string
    {
        $lines = [];

        $bpSys = trim((string) ($assessment->bp_sys ?? ''));
        $bpDia = trim((string) ($assessment->bp_dia ?? ''));
        if ($bpSys !== '' && $bpDia !== '') {
            $lines[] = 'BP: ' . $bpSys . '/' . $bpDia;
        } elseif ($bpSys !== '') {
            $lines[] = 'BP: ' . $bpSys;
        }

        foreach (['pulse' => 'Pulse', 'weight' => 'Weight', 'temp' => 'Temp', 'height' => 'Height', 'spo2' => 'SpO2', 'rr' => 'RR'] as $col => $label) {
            $v = trim((string) ($assessment->{$col} ?? ''));
            if ($v !== '' && $v !== '0') {
                $lines[] = $label . ': ' . $v;
            }
        }

        return implode("\n", $lines);
    }
}
