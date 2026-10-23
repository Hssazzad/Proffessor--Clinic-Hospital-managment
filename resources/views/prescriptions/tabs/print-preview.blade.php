@php
    $stageLabel = $stageLabel ?? '';

    // Doctor display text.
    $docLine = '';
    $docSub  = '';

    if (!empty($rxDoctors) && collect($rxDoctors)->count()) {
        $firstDoctor = collect($rxDoctors)->first();

        $docLine = collect($rxDoctors)->map(function ($d) {
            $name = $d->doctor_name ?? $d->name ?? '';
            $role = $d->role ?? '';
            return trim($name . ($role ? " ({$role})" : ''));
        })->filter()->values()->join(', ');

        $docMeta = [];
        if (!empty($firstDoctor->reg_no))  $docMeta[] = 'Reg: ' . $firstDoctor->reg_no;
        if (!empty($firstDoctor->contact)) $docMeta[] = 'Mobile: ' . $firstDoctor->contact;
        $docSub = implode(' | ', $docMeta);
    }

    if ($docLine === '') {
        $docLine = $rx->doctor_name ?? $rx->surgeon_name ?? '';
    }

    // Advice can arrive on the rx row or as a separate collection.
    $adviceText = trim((string) ($rx->advice ?? $rx->instructions ?? ''));
    if ($adviceText === '' && !empty($advices)) {
        $adviceText = collect($advices)->pluck('advice')->filter()->implode("\n");
    }

    $bullets = function ($items, array $keys) {
        return collect($items ?? [])->map(function ($item) use ($keys) {
            $text = '';
            foreach ($keys as $k) {
                if (trim((string) ($item->{$k} ?? '')) !== '') { $text = trim($item->{$k}); break; }
            }
            if ($text === '') return '';
            $note = trim((string) ($item->note ?? $item->notes ?? $item->remarks ?? $item->comment ?? ''));
            return '• ' . $text . ($note !== '' ? ' (' . $note . ')' : '');
        })->filter()->implode("\n");
    };

    $ccText  = trim((string) ($rx->chief_complaint ?? '')) ?: $bullets($complaints ?? [], ['complaint', 'name', 'complain']);
    $dxText  = trim((string) ($rx->diagnosis ?? '')) ?: $bullets($diagnoses ?? [], ['name', 'diagnosis_name', 'diagnosis']);
    $invText = $bullets($investigations ?? [], ['name', 'investigation_name', 'test']);

    // Stage name shown under the doctor.
    $stageMap = [
        'regular'      => 'Regular Rx',
        'on_admission' => 'On Admission',
        'pre_surgery'  => 'Pre-Surgery',
        'post_surgery' => 'Post-Surgery',
        'round'        => 'Round',
        'fresh'        => 'Fresh Rx',
        'discharge'    => 'Discharge',
        'surgery'      => 'Surgery',
    ];
    $stageTitle = $stageMap[$stageLabel] ?? ($stageLabel ?: 'Prescription');

    $notes = [];
    if ($adviceText !== '') {
        $notes[] = ['title' => 'Advice', 'body' => $adviceText];
    }
    if (!empty($rx->surgeon_name)) {
        $notes[] = ['title' => 'Surgeon', 'body' => $rx->surgeon_name];
    }

    $displayDate = $rx->prescribed_on ?? $rx->display_date ?? ($rx->surgery_date ?? $rx->created_at ?? now());
    $ageGender   = trim(trim((string) ($patient->age ?? '')) . ' / ' . trim((string) ($patient->gender ?? '')), ' /');

    // Dynamic O/E values entered in this prescription's O/E tab (prescription_oe + oe_master)
    $rxOE = '';
    if ($rx && !empty($rx->id)
        && \Illuminate\Support\Facades\Schema::hasTable('prescription_oe')
        && \Illuminate\Support\Facades\Schema::hasTable('oe_master')) {
        $oeRows = \Illuminate\Support\Facades\DB::table('prescription_oe')
            ->join('oe_master', 'oe_master.id', '=', 'prescription_oe.oe_master_id')
            ->where('prescription_oe.prescription_id', $rx->id)
            ->whereNotNull('prescription_oe.value')
            ->where('prescription_oe.value', '<>', '')
            ->orderBy('oe_master.sort_order')
            ->orderBy('oe_master.id')
            ->get(['oe_master.name', 'oe_master.short_name', 'oe_master.unit', 'prescription_oe.value']);
        $parts = [];
        foreach ($oeRows as $row) {
            $label = trim((string) ($row->short_name !== '' ? $row->short_name : $row->name));
            $val   = trim((string) $row->value);
            $unit  = trim((string) $row->unit);
            $parts[] = ($label !== '' ? $label . ': ' : '') . $val . ($unit !== '' ? ' ' . $unit : '');
        }
        $rxOE = implode("\n", $parts);
    }
    if (!empty($patient)) {
        $oeLines = $rxOE !== '' ? explode("\n", $rxOE) : [];
        $oeSeen = [];
        foreach ($oeLines as $l) {
            $k = mb_strtolower(trim((string) strstr($l, ':', true)));
            if ($k !== '') { $oeSeen[$k] = true; }
        }

        // Shared Pre-Con Assessment reader: per-prescription snapshot → patient latest
        $readerText = app(\App\Services\PreconAssessmentReader::class)
            ->textFor($rx->id ?? null, $patient->patientcode ?? '', $patient->id ?? null);
        if ($readerText !== '') {
            foreach (explode("\n", $readerText) as $l) {
                $l = trim($l);
                if ($l === '') { continue; }
                $k = mb_strtolower(trim((string) strstr($l, ':', true)));
                if (!isset($oeSeen[$k]) || $k === '') {
                    $oeLines[] = $l;
                    $oeSeen[$k] = true;
                }
            }
        }

        // Prescription/admission row's own oe_* columns (core four)
        $rxCore = [
            'Pulse'  => $rx->oe_pulse ?? null,
            'BP'     => $rx->oe_bp ?? null,
            'Temp'   => $rx->oe_temp ?? null,
            'Weight' => $rx->oe_weight ?? null,
        ];
        foreach ($rxCore as $label => $val) {
            $val = trim((string) $val);
            if ($val !== '' && !isset($oeSeen[mb_strtolower($label)])) {
                $oeLines[] = $label . ': ' . $val;
                $oeSeen[mb_strtolower($label)] = true;
            }
        }
        $rxOE = implode("\n", $oeLines);
    }
@endphp

@include('partials.rx_styles')
<style>
    .rx-actions { display: flex; gap: .5rem; justify-content: flex-end; margin-top: .75rem; flex-wrap: wrap; }
    .rx-btn { padding: .5rem .9rem; border-radius: .6rem; border: 1px solid #cfe1f4; background: #0ea5e9; color: #fff; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
</style>

@include('partials.rx_page', [
    'rxDoctorName'    => $docLine,
    'rxDoctorLine'    => $stageTitle,
    'rxDoctorSub'     => $docSub,
    'rxPatientCode'   => $patient->patientcode ?? $patient->id ?? '',
    'rxPatientName'   => $patient->patientname ?? $patient->patient_name ?? '',
    'rxPatientMobile' => $patient->mobile_no ?? $patient->patient_phone ?? '',
    'rxPatientAge'    => $ageGender,
    'rxDate'          => \Carbon\Carbon::parse($displayDate)->format('d-m-Y'),
    'rxCC'            => $ccText,
    'rxOE'            => $rxOE,
    'rxDx'            => $dxText,
    'rxInv'           => $invText,
    'rxMedicines'     => $medicines ?? [],
    'rxNotes'         => $notes,
])

{{-- Screen-only --}}
<div class="rx-actions rx-no-print">
    <button type="button" class="rx-btn" onclick="window.print();return false;">
        <i class="fas fa-print"></i> Print Prescription
    </button>
</div>
