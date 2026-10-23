@php
    $h = include resource_path('views/partials/rx_helpers.php');

    // Normalise each variable to a predictable shape.
    $rxDoctorName     = trim((string) ($doctorName ?? $rxDoctorName ?? ''));
    $rxDoctorLine     = trim((string) ($doctorLine ?? $rxDoctorLine ?? ''));
    $rxDoctorSub      = trim((string) ($doctorSub ?? $rxDoctorSub ?? ''));
    $rxPatientCode    = trim((string) ($patientCode ?? $rxPatientCode ?? $patient->patientcode ?? ''));
    $rxPatientName    = trim((string) ($patientName ?? $rxPatientName ?? $patient->patientname ?? ''));
    $rxPatientMobile  = trim((string) ($patientMobile ?? $rxPatientMobile ?? $patient->mobile_no ?? $patient->mobileno ?? ''));
    $rxPatientAge     = trim((string) ($patientAge ?? $rxPatientAge ?? $patient->age ?? ''));
    $rxDate           = trim((string) ($date ?? $rxDate ?? ''));
    $rxCC             = trim((string) ($cc ?? $rxCC ?? ''));
    $rxOE             = trim((string) ($oe ?? $rxOE ?? ''));
    $rxDx             = trim((string) ($dx ?? $rxDx ?? ''));
    $rxInv            = trim((string) ($inv ?? $rxInv ?? ''));
    $rxAdvice         = trim((string) ($advice ?? $rxAdvice ?? ''));
    $rxFollowUp       = trim((string) ($followUp ?? $rxFollowUp ?? ''));
    $rxSignatureLabel = trim((string) ($signatureLabel ?? $rxSignatureLabel ?? ''));
    $rxFooterLeft     = trim((string) ($footerLeft ?? $rxFooterLeft ?? ''));
    $rxFooterRight    = trim((string) ($footerRight ?? $rxFooterRight ?? ''));
    $rxMedicines      = $medicines ?? $rxMedicines ?? [];
    $rxMinRows        = (int) ($minRows ?? $rxMinRows ?? 8);
    $rxLogoUrl        = trim((string) ($logoUrl ?? $rxLogoUrl ?? ''));
    $rxNotes          = $notes ?? $rxNotes ?? [];

    // ── Build doctor info from rxDoctors if not already provided ──
    if ($rxDoctorName === '' && !empty($rxDoctors)) {
        $docs = collect($rxDoctors);
        $rxDoctorName = $docs->map(fn($d) => trim($d->doctor_name ?? $d->name ?? ''))
            ->filter()->values()->join(', ');
        $rxDoctorLine = $docs->pluck('speciality')->filter()->unique()->values()->join(', ');
        $first = $docs->first();
        $meta = [];
        if (!empty($first->reg_no))  $meta[] = 'Reg: ' . $first->reg_no;
        if (!empty($first->contact)) $meta[] = 'Mobile: ' . $first->contact;
        $rxDoctorSub = implode(' | ', $meta);
    }
    if ($rxDoctorName === '' && !empty($rx?->doctor_name)) {
        $rxDoctorName = $rx->doctor_name;
    }
    if ($rxDoctorName === '') { $rxDoctorName = 'Professor Clinic'; }

    if ($rxDate === '') {
        $rxDate = \Carbon\Carbon::parse($rx->prescribed_on ?? $rx->created_at ?? now())->format('d-m-Y');
    }

    // ── Build clinical text from raw objects if needed ──
    $bulletList = function ($items, array $textKeys, array $noteKeys = ['note', 'notes']) {
        return collect($items ?? [])->map(function ($item) use ($textKeys, $noteKeys) {
            $item = is_array($item) ? (object) $item : $item;
            $text = '';
            foreach ($textKeys as $key) { if (trim((string) ($item->{$key} ?? '')) !== '') { $text = trim($item->{$key}); break; } }
            if ($text === '') return '';
            $note = '';
            foreach ($noteKeys as $key) { if (trim((string) ($item->{$key} ?? '')) !== '') { $note = trim($item->{$key}); break; } }
            return '• ' . $text . ($note !== '' ? ' (' . $note . ')' : '');
        })->filter()->implode("\n");
    };

    if ($rxCC === '' && !empty($rx?->chief_complaint)) { $rxCC = $rx->chief_complaint; }
    if ($rxCC === '') { $rxCC = $bulletList($complaints ?? [], ['complaint', 'complain', 'name']); }

    if ($rxOE === '' && !empty($rx) && !empty($rx->id)
        && \Illuminate\Support\Facades\Schema::hasTable('prescription_oe')
        && \Illuminate\Support\Facades\Schema::hasTable('oe_master')) {
        $rxOE = rescue(function () use ($rx) {
            $rows = \Illuminate\Support\Facades\DB::table('prescription_oe')
                ->join('oe_master', 'oe_master.id', '=', 'prescription_oe.oe_master_id')
                ->where('prescription_oe.prescription_id', $rx->id)
                ->whereNotNull('prescription_oe.value')
                ->where('prescription_oe.value', '<>', '')
                ->orderBy('oe_master.sort_order')
                ->orderBy('oe_master.id')
                ->get(['oe_master.name', 'oe_master.short_name', 'oe_master.unit', 'prescription_oe.value']);
            if ($rows->isEmpty()) return '';
            $parts = [];
            foreach ($rows as $row) {
                $label = trim((string) ($row->short_name !== '' ? $row->short_name : $row->name));
                $val   = trim((string) $row->value);
                $unit  = trim((string) $row->unit);
                $parts[] = ($label !== '' ? $label . ': ' : '') . $val . ($unit !== '' ? ' ' . $unit : '');
            }
            return implode("\n", $parts);
        }, '', false);
    }

    if ($rxOE === '' && !empty($patient)) {
        $rxOE = rescue(function () use ($patient, $rx) {
            // Shared Pre-Con Assessment reader: per-prescription snapshot → patient latest
            $text = app(\App\Services\PreconAssessmentReader::class)
                ->textFor($rx->id ?? null, $patient->patientcode ?? '', $patient->id ?? null);
            if ($text !== '') { return $text; }
            // Nursing fallback (no Pre-Con Assessment exists)
            $vitals = \Illuminate\Support\Facades\DB::table('nursing_admissions')
                ->where('patient_code', $patient->patientcode ?? '')
                ->orderBy('id', 'desc')->first();
            if (!$vitals) return '';
            $parts = [];
            if (!empty($vitals->bp_sys) || !empty($vitals->bp_dia)) {
                $parts[] = 'BP: ' . ($vitals->bp_sys ?? '—') . '/' . ($vitals->bp_dia ?? '—');
            } elseif (!empty($vitals->bp)) { $parts[] = 'BP: ' . $vitals->bp; }
            if (!empty($vitals->pulse))  $parts[] = 'Pulse: ' . $vitals->pulse;
            if (!empty($vitals->weight)) $parts[] = 'Weight: ' . $vitals->weight;
            $temp = $vitals->temp ?? $vitals->temperature ?? '';
            if (trim((string) $temp) !== '') $parts[] = 'Temp: ' . $temp;
            if (!empty($vitals->height)) $parts[] = 'Height: ' . $vitals->height;
            return implode("\n", $parts);
        }, '', false);
    }

    // ── Merge: ensure the core four vitals also come from the rx row itself ──
    if (!empty($rx)) {
        $oeLines = $rxOE !== '' ? explode("\n", $rxOE) : [];
        $oeSeen = [];
        foreach ($oeLines as $l) {
            $k = mb_strtolower(trim((string) strstr($l, ':', true)));
            if ($k !== '') { $oeSeen[$k] = true; }
        }
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

    if ($rxDx === '' && !empty($rx->diagnosis)) { $rxDx = $rx->diagnosis; }
    if ($rxDx === '') { $rxDx = $bulletList($diagnoses ?? [], ['name', 'diagnosis']); }

    if ($rxInv === '') { $rxInv = $bulletList($investigations ?? [], ['name', 'investigation_name', 'test']); }

    if ($rxAdvice === '' && !empty($rx->advice)) { $rxAdvice = $rx->advice; }
    if ($rxAdvice === '' && !empty($rx->advices)) { $rxAdvice = $rx->advices; }

    // Build notes array from advice + follow-up + extra notes
    $notesArray = [];
    if ($rxAdvice !== '') { $notesArray[] = ['title' => 'Advice', 'body' => $rxAdvice]; }
    foreach ($rxNotes as $n) { $notesArray[] = $n; }

    if ($rxSignatureLabel === '') { $rxSignatureLabel = "Doctor's Signature"; }
    if ($rxFooterLeft === '') { $rxFooterLeft = 'Terms & Conditions ............................................'; }
    if ($rxFooterRight === '') { $rxFooterRight = '............... days/months later follow up.'; }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Prescription</title>
    @include('partials.rx_styles')
</head>
<body class="rx-standalone">
    @include('partials.rx_page', [
        'rxDoctorName'   => $rxDoctorName,
        'rxDoctorLine'   => $rxDoctorLine,
        'rxDoctorSub'    => $rxDoctorSub,
        'rxLogoUrl'      => $rxLogoUrl,
        'rxPatientCode'  => $rxPatientCode,
        'rxPatientName'  => $rxPatientName,
        'rxPatientMobile'=> $rxPatientMobile,
        'rxPatientAge'   => $rxPatientAge,
        'rxDate'         => $rxDate,
        'rxCC'           => $rxCC,
        'rxOE'           => $rxOE,
        'rxDx'           => $rxDx,
        'rxInv'          => $rxInv,
        'rxMedicines'    => $rxMedicines,
        'rxMinRows'      => $rxMinRows,
        'rxNotes'        => $notesArray,
        'rxSignatureLabel'=> $rxSignatureLabel,
        'rxFooterLeft'   => $rxFooterLeft,
        'rxFooterRight'  => $rxFooterRight,
    ])
    <script>window.onload=function(){setTimeout(function(){window.print()},500)};</script>
</body>
</html>
