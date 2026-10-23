<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdmissionPrintOptionsController extends Controller
{
    public function options(Request $request)
    {
        $code = trim((string) $request->input('patientcode'));
        $patient = $code ? DB::table('patients')->where('patientcode', $code)->first() : null;

        $cc = $this->list('common_complain', ['id', 'name']);
        $inv = $this->list('common_investigation', ['id', 'name', 'category']);
        $fields = $this->list('oe_master', ['id', 'name', 'short_name', 'unit', 'input_type', 'sort_order']);

        $latest = $this->latestAssessment($patient, $code);
        $values = $latest ? $this->fieldValues($latest->id) : collect();

        $resolved = [];
        foreach (config('vitals.fields') as $key => $def) {
            $resolved[$key] = $this->value($key, $def, $latest, $values, $fields);
        }
        // Core four are ALWAYS present in the JSON (empty string when missing)
        // — same rule as the print itself, so the client never drops a label.
        foreach (['pulse' => '', 'bp' => '', 'temp' => '', 'weight' => ''] as $k => $_) {
            if (!array_key_exists($k, $resolved)) {
                $resolved[$k] = '';
            }
        }

        return response()->json([
            'ok' => true,
            'cc' => $cc,
            'inv' => $inv,
            'fields' => $fields,
            'values' => $resolved,
            'field_values' => $values->all(),
            'latest_id' => $latest->id ?? null,
            'history' => $patient && $latest ? $this->historyFor($patient) : collect(),
        ]);
    }

    public function saveSelection(Request $request)
    {
        $pc = trim((string) $request->input('patientcode'));
        if ($pc === '') {
            return response()->json(['ok' => false, 'message' => 'Patient not found.']);
        }
        session([
            'admission_print_selection_' . $pc => [
                'cc' => is_array($request->input('cc')) ? $request->input('cc') : [],
                'inv' => is_array($request->input('inv')) ? $request->input('inv') : [],
                'oe' => is_array($request->input('oe')) ? $request->input('oe') : [],
            ],
        ]);
        return response()->json(['ok' => true]);
    }

    private function list(string $table, array $cols)
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }
        $q = DB::table($table);
        if (Schema::hasColumn($table, 'active')) {
            $q->where('active', 1);
        }
        if (Schema::hasColumn($table, 'sort_order')) {
            $q->orderBy('sort_order')->orderBy('id');
        } else {
            $q->orderBy('name');
        }
        return $q->get($cols);
    }

    private function latestAssessment($patient, ?string $code)
    {
        if (!Schema::hasTable('preconassessment')) {
            return null;
        }
        $id = app(\App\Services\PreconAssessmentReader::class)
            ->latestAssessmentId(null, $code, $patient->id ?? null);
        if (!$id) {
            return null;
        }
        return DB::table('preconassessment')->where('id', $id)->first();
    }

    private function fieldValues(int $assessmentId)
    {
        if (!Schema::hasTable('preconassessment_values') || !Schema::hasTable('oe_master')) {
            return collect();
        }
        return DB::table('preconassessment_values as pv')
            ->join('oe_master as om', 'om.id', '=', 'pv.oe_master_id')
            ->where('pv.assessment_id', $assessmentId)
            ->pluck('pv.value', 'om.id');
    }

    private function value(string $key, array $def, $latest, $values, $fields): string
    {
        if ($key === 'bp' && isset($latest->bp_sys) && $latest->bp_sys !== null) {
            return trim(trim((string) $latest->bp_sys, '/') . '/' . trim((string) ($latest->bp_dia ?? ''), '/'), '/');
        }
        foreach ($fields as $f) {
            $nm = mb_strtolower(trim((string) (!empty($f->short_name) ? $f->short_name : $f->name)));
            if (!in_array($nm, $def['keys'], true)) {
                continue;
            }
            $v = $values->get($f->id);
            if ($v !== null && $v !== '') {
                return (string) $v;
            }
        }
        $col = $def['col'] ?? null;
        if ($col && $latest && isset($latest->{$col}) && $latest->{$col} !== null && $latest->{$col} !== '') {
            return (string) $latest->{$col};
        }
        return '';
    }

    private function historyFor($patient)
    {
        return DB::table('preconassessment')
            ->where('patient_id', $patient->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $bp = trim((string) ($r->bp_sys ?? '') . '/' . (string) ($r->bp_dia ?? ''), '/');
                return [
                    'id' => $r->id,
                    'datetime' => $r->created_at ? date('d/m h:i A', strtotime($r->created_at)) : '',
                    'pulse' => (string) ($r->pulse ?? ''),
                    'bp' => $bp,
                    'temp' => (string) ($r->temp ?? ''),
                    'weight' => (string) ($r->weight ?? ''),
                    'values' => $this->fieldValues($r->id)->all(),
                ];
            });
    }
}