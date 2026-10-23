{{--
    Admission medicine prescription — canonical A4 sheet (partials/rx_page).

    Data source: immutable snapshots from PrescriptionPrintService::generate()
    ($snapshots), falling back to live $patient / $medicines. The session
    print selection (admission_print_selection_...) still overrides the
    snapshot C/C · Inv · O/E values exactly as before.

    Shared by: admedicinePrint, premedicinePrint, postmedicinePrint,
    roundmedicinePrint, freshmedicinePrint (all pass the same data shape).
--}}
@php
    // ── Snapshot preference (immutable record wins) ──
    $medSnap  = $snapshots['medicines_snapshot'] ?? [];
    $patSnap  = $snapshots['patient_snapshot']   ?? [];
    $docSnap  = $snapshots['doctor_snapshot']    ?? [];
    $clinSnap = $snapshots['clinical_snapshot']  ?? [];

    // Patient values
    $patName   = $patSnap['patientname'] ?? ($patient->patientname ?? '');
    $patCode   = $patSnap['patientcode'] ?? ($patient->patientcode ?? '');
    $patMobile = $patSnap['mobile_no']   ?? ($patient->mobile_no ?? '');
    $patAge    = $patSnap['age']         ?? ($patient->age ?? '');
    $patGender = $patSnap['gender']      ?? ($patient->gender ?? '');

    $admitDate = $admission->admission_date ?? $admission->created_at ?? now();
    $dateStr = '';
    if (!empty($admitDate)) {
        try { $dateStr = \Carbon\Carbon::parse($admitDate)->format('d-m-Y'); }
        catch (\Exception $e) { $dateStr = $admitDate; }
    }
    if ($dateStr === '') { $dateStr = now()->format('d-m-Y'); }

    // Doctor values
    $docName    = $docSnap['doctor_name'] ?? ($admission->doctor_name ?? '');
    $docSpec    = $docSnap['speciality']  ?? ($admission->department ?? '');
    $docReg     = $docSnap['reg_no']      ?? ($admission->doctor_reg_no ?? '');
    $docContact = $docSnap['contact']     ?? ($admission->doctor_mobile ?? '');

    $docMeta = [];
    if (trim((string) $docReg) !== '')     $docMeta[] = 'Reg: ' . trim((string) $docReg);
    if (trim((string) $docContact) !== '') $docMeta[] = 'Mobile: ' . trim((string) $docContact);

    // Clinical values
    $chiefComplaints = $clinSnap['chief_complaints'] ?? [];
    $investigations  = $clinSnap['investigations']   ?? [];
    $onExamination   = $clinSnap['on_examination']   ?? [];

    // User-picked selection (same session) overrides the snapshot for this patient.
    $printSel = session('admission_print_selection_' . $patCode);
    if (is_array($printSel)) {
        if (!empty($printSel['cc']) && is_array($printSel['cc'])) {
            $chiefComplaints = array_map(function ($c) {
                return (object) ['complaint' => $c, 'note' => ''];
            }, array_filter(array_map('trim', $printSel['cc'])));
        }
        if (!empty($printSel['inv']) && is_array($printSel['inv'])) {
            $investigations = array_map(function ($i) {
                return (object) ['name' => $i];
            }, array_filter(array_map('trim', $printSel['inv'])));
        }
        if (!empty($printSel['oe']) && is_array($printSel['oe'])) {
            $onExamination = array_filter($printSel['oe'], function ($v) {
                return trim((string) $v) !== '';
            });
        }
    }

    // Clinical text (newline separated — rx_page renders them as <br> lines).
    $ccText = collect($chiefComplaints)->map(function ($c) {
        $text = $c->complaint ?? $c->complain ?? $c->name ?? '';
        $note = $c->note ?? '';
        return trim((string) $text) !== '' ? '• ' . trim((string) $text) . ($note ? ' (' . trim((string) $note) . ')' : '') : '';
    })->filter()->implode("\n");

    $oeText = collect($onExamination)->map(function ($v, $k) {
        $val = is_object($v) ? (array) $v : $v;
        if (is_array($val)) { $val = implode(', ', array_filter(array_map('strval', $val))); }
        return trim((string) $val) !== '' ? $k . ': ' . $val : '';
    })->filter()->implode("\n");

    $invText = collect($investigations)->map(function ($i) {
        $text = $i->name ?? $i->investigation_name ?? $i->test ?? '';
        $note = $i->note ?? '';
        return trim((string) $text) !== '' ? '• ' . trim((string) $text) . ($note ? ' (' . trim((string) $note) . ')' : '') : '';
    })->filter()->implode("\n");

    // Medicines: snapshot rows, else the live collection.
    $medicineRows = collect($medSnap);
    if ($medicineRows->isEmpty() && !empty($medicines)) {
        $medicineRows = $medicines instanceof \Illuminate\Support\Collection
            ? $medicines
            : collect($medicines);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('partials.rx_styles')
    <style>
        .rx-audit-bar {
            position: sticky;
            top: 0;
            z-index: 50;
            max-width: 210mm;
            margin: 12px auto 0;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            background: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            color: #444;
        }
        .rx-audit-bar .btn-print {
            margin-left: auto;
            background: #fff;
            color: #111;
            border: 1.5px solid #222;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,.2);
        }
        .rx-audit-bar .btn-print:hover { background: #222; color: #fff; }
    </style>
</head>
<body class="rx-standalone">
    <div class="no-print rx-audit-bar">
        <span><strong>{{ $title }}</strong></span>
        <span>Print ID: {{ $printId }}</span>
        <span>Order: {{ $orderType }}</span>
        <span>Hash: {{ $printHash }}</span>
        <button type="button" class="btn-print" onclick="window.print()">Print Prescription</button>
    </div>

    @include('partials.rx_page', [
        'rxDoctorName'    => $docName ?: 'Professor Clinic',
        'rxDoctorLine'    => $docSpec ?: $title,
        'rxDoctorSub'     => implode(' | ', $docMeta),
        'rxPatientCode'   => $patCode,
        'rxPatientName'   => $patName,
        'rxPatientMobile' => $patMobile,
        'rxPatientAge'    => trim(trim((string) $patAge) . ' / ' . trim((string) $patGender), ' /'),
        'rxDate'          => $dateStr,
        'rxCC'            => $ccText,
        'rxOE'            => $oeText,
        'rxDx'            => '',
        'rxInv'           => $invText,
        'rxMedicines'     => $medicineRows,
        'rxNotes'         => [],
        'rxMinRows'       => 0,
    ])

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 500);
        });
    </script>
</body>
</html>
