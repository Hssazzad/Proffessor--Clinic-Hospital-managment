@extends('adminlte::page')

@section('content_header')
    <h1>Prescription Print Preview</h1>
@endsection

@section('content')
    @php
        $patientId = $patientId ?? request('patient') ?? ($rx->patient_id ?? null);
        $backTab = request('back_tab', 'medicine');

        // ── Outdoor prescription sheet — canonical partials/rx_page ──
        // Data mapping mirrors print/prescription.blade.php (master reference).

        // Patient bar (3rd field = Mobile when present, else Age — rx_page default)
        $rxPatientCode   = trim((string) ($patient->patientcode ?? ''));
        $rxPatientName   = trim((string) ($patient->patientname ?? ''));
        $rxPatientMobile = trim((string) ($patient->mobile_no ?? ''));
        $rxPatientAge    = trim((string) ($patient->age ?? ''));

        // Date
        $rxDate = \Carbon\Carbon::parse($rx->prescribed_on ?? $rx->created_at ?? now())->format('d-m-Y');

        // C/C — chief_complaint first (reference pattern), complaints rows as fallback
        $rxCC = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($rx->chief_complaint ?? '')));
        if ($rxCC === '') {
            $rxCC = collect($complaints ?? [])
                ->map(fn($c) => trim((string) ($c->note ?? '')) ?: trim((string) ($c->name ?? $c->complaint ?? '')))
                ->filter()
                ->map(fn($v) => '• ' . $v)
                ->implode("\n");
        }

        // O/E — already built by PrescriptionController::buildPrintOeText()
        $rxOE = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($oeText ?? '')));

        // Dx — diagnosis first, diagnoses rows as fallback, D/D appended at the end
        $rxDx = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($rx->diagnosis ?? '')));
        if ($rxDx === '') {
            $rxDx = collect($diagnoses ?? [])
                ->map(fn($d) => trim((string) ($d->note ?? '')) ?: trim((string) ($d->name ?? '')))
                ->filter()
                ->map(fn($v) => '• ' . $v)
                ->implode("\n");
        }
        if (trim((string) ($rx->differential_diagnosis ?? '')) !== '') {
            $rxDx = trim($rxDx . "\n" . 'D/D: ' . trim((string) $rx->differential_diagnosis));
        }

        // Inv
        $rxInv = collect($investigations ?? [])
            ->map(fn($i) => trim((string) ($i->note ?? '')) ?: trim((string) ($i->name ?? '')))
            ->filter()
            ->map(fn($v) => '• ' . $v)
            ->implode("\n");

        // Advice + Follow-up as separate note blocks
        $rxNotes = collect()
            ->push(['title' => 'Advice', 'body' => trim((string) ($rx->advice ?? ''))])
            ->push(['title' => 'Follow Up', 'body' => trim((string) ($rx->follow_up ?? ''))])
            ->filter(fn($n) => trim($n['body'] ?? '') !== '')
            ->values();

        // Signature — doctor name | speciality | reg, else default
        $rxSignatureLabel = trim(collect($rxDoctors ?? [])
            ->map(fn($d) => array_values(array_filter([$d->doctor_name ?? '', $d->name ?? '', $d->speciality ?? '', $d->reg_no ?? ''])))
            ->flatten()
            ->implode(' | ')) ?: "Doctor's Signature";
    @endphp

    <div class="rx-no-print mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('rx.wizard', ['id' => $rx->id ?? request('id'), 'patient' => $patientId, 'tab' => $backTab, 'back_tab' => $backTab]) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Prescription
        </button>
    </div>

    @include('partials.rx_styles')
    @include('partials.rx_page', [
        'rxPatientCode'    => $rxPatientCode,
        'rxPatientName'    => $rxPatientName,
        'rxPatientMobile'  => $rxPatientMobile,
        'rxPatientAge'     => $rxPatientAge,
        'rxDate'           => $rxDate,
        'rxCC'             => $rxCC,
        'rxOE'             => $rxOE,
        'rxDx'             => $rxDx,
        'rxInv'            => $rxInv,
        'rxMedicines'      => $medicines ?? collect(),
        'rxMinRows'        => 0,
        'rxNotes'          => $rxNotes,
        'rxSignatureLabel' => $rxSignatureLabel,
        'rxFooterLeft'     => '',
        'rxFooterRight'    => '',
    ])
@endsection

@section('js')
<script>
    window.addEventListener('load', function () {
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            setTimeout(function () { window.print(); }, 500);
        }
    });
</script>
@endsection
