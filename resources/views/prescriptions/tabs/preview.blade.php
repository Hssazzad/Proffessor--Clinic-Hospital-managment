{{-- resources/views/prescriptions/preview.blade.php --}}
@extends('adminlte::page')

@section('title', 'Prescription Preview')

@section('content_header')
    {{-- Intentionally empty – we use our own modern toolbar --}}
@endsection

@section('content')
@php
    $pid       = $pid ?? request('id');
    $patientId = $patientId ?? request('patient');
    $wz        = $wz ?? 'rx';
    $validTabs = ['patients','complain','oe','investigations','medicine','preview','finish'];
    $backTab   = request('back_tab', 'medicine');
    $backTab   = in_array($backTab, $validTabs, true) ? $backTab : 'medicine';
@endphp

<div class="preview-app">

    {{-- Modern Toolbar --}}
    <div class="preview-toolbar no-print">
        <div class="toolbar-left">
            <a href="{{ route($wz.'.wizard', ['id' => $pid, 'patient' => $patientId, 'tab' => $backTab, 'back_tab' => $backTab]) }}"
               class="btn-toolbar btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>

        <div class="toolbar-center">
            <span class="preview-title">
                <i class="fas fa-file-medical"></i>
                Prescription Preview
            </span>
            <span class="preview-subtitle">Exact print layout · A4</span>
        </div>

        <div class="toolbar-right">
            {{-- EDIT BUTTON – medicine info ভুল হলে এখান থেকে edit করুন --}}
            <a href="{{ route($wz.'.wizard', ['id' => $pid, 'patient' => $patientId, 'tab' => $backTab, 'back_tab' => $backTab]) }}"
               class="btn-toolbar btn-edit">
                <i class="fas fa-edit"></i>
                <span>Edit Medicine</span>
            </a>

            <button type="button" class="btn-toolbar btn-print" id="btnPrintPrescription">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    {{-- Optional quick-edit notice --}}
    <div class="preview-notice no-print">
        <i class="fas fa-info-circle"></i>
        Medicine information ভুল থাকলে <strong>Edit Medicine</strong> বাটনে ক্লিক করে ঠিক করুন, তারপর আবার Preview দেখুন।
    </div>

    {{-- A4 Live Preview Stage — canonical partials/rx_page (migrated) --}}
    <div class="preview-stage">
        <div class="a4-shadow-wrapper">
            @php
                // Data mapping mirrors prescriptions/print.blade.php (outdoor reference).
                $rxPatientCode   = trim((string) ($patient->patientcode ?? ''));
                $rxPatientName   = trim((string) ($patient->patientname ?? ''));
                $rxPatientMobile = trim((string) ($patient->mobile_no ?? ''));
                $rxPatientAge    = trim((string) ($patient->age ?? ''));

                $rxDate = \Carbon\Carbon::parse($rx->prescribed_on ?? $rx->created_at ?? now())->format('d-m-Y');

                // C/C — chief_complaint first, complaints rows as fallback
                $rxCC = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($rx->chief_complaint ?? '')));
                if ($rxCC === '') {
                    $rxCC = collect($complaints ?? [])
                        ->map(fn($c) => trim((string) ($c->note ?? '')) ?: trim((string) ($c->name ?? $c->complaint ?? '')))
                        ->filter()
                        ->map(fn($v) => '• ' . $v)
                        ->implode("\n");
                }

                // O/E — built by the wizard (buildWizardOeText), rx_page shows default when empty
                $rxOE = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($oeText ?? '')));

                // Dx — diagnosis first, rows as fallback, D/D appended at the end
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
        </div>
    </div>

</div>
@endsection

@push('css')
<style>
/* ========== Modern Preview Shell ========== */
.preview-app {
    min-height: calc(100vh - 80px);
    background: #f1f5f9;
    margin: -15px -15px 0 -15px;
    padding-bottom: 40px;
}

/* Toolbar */
.preview-toolbar {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 24px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.toolbar-left,
.toolbar-right {
    flex: 0 0 auto;
    min-width: 140px;
}
.toolbar-right {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.toolbar-center {
    flex: 1 1 auto;
    text-align: center;
}
.preview-title {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
}
.preview-title i {
    color: #0ea5e9;
    margin-right: 6px;
}
.preview-subtitle {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

/* Toolbar buttons */
.btn-toolbar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    line-height: 1.3;
}
.btn-back {
    background: #fff;
    color: #334155;
    border-color: #cbd5e1;
}
.btn-back:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
    text-decoration: none;
}
.btn-edit {
    background: #fff;
    color: #b45309;
    border-color: #fbbf24;
}
.btn-edit:hover {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
    text-decoration: none;
}
.btn-print {
    background: #0ea5e9;
    color: #fff;
    border-color: #0ea5e9;
    box-shadow: 0 1px 2px rgba(14, 165, 233, 0.25);
}
.btn-print:hover {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff;
}

/* Notice bar */
.preview-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 24px 0;
    padding: 12px 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
}
.preview-notice i {
    color: #f59e0b;
    font-size: 15px;
}

/* A4 Stage */
.preview-stage {
    display: flex;
    justify-content: center;
    padding: 24px 16px 48px;
    overflow-x: auto;
}
.a4-shadow-wrapper {
    background: #fff;
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.08),
        0 10px 25px -5px rgba(0, 0, 0, 0.12),
        0 0 0 1px rgba(0, 0, 0, 0.04);
    border-radius: 2px;
}
.a4-shadow-wrapper .print-page {
    margin: 0 !important;
    box-shadow: none !important;
}

/* ========== Print ========== */
@page {
    size: A4 portrait;
    margin: 0;
}
@media print {
    html, body {
        width: 210mm !important;
        height: 297mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .navbar, .main-sidebar, .content-header, .main-footer,
    .control-sidebar, .no-print,
    .preview-toolbar, .preview-notice {
        display: none !important;
    }
    .wrapper, .content-wrapper, .content, .container, .container-fluid,
    .card, .card-body, .box, .box-body, section.content,
    .preview-app {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        min-height: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
    }
    .preview-stage {
        display: block !important;
        overflow: visible !important;
        padding: 0 !important;
    }
    .a4-shadow-wrapper {
        box-shadow: none !important;
        background: #fff !important;
        border-radius: 0 !important;
    }
    .print-page {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        width: 210mm !important;
        height: 297mm !important;
        padding: 52mm 8mm 26mm 8mm !important;
        overflow: visible !important;
        transform: none !important;
        zoom: 1 !important;
    }
    .med-table th {
        background-color: #e8e8e8 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const printBtn = document.getElementById('btnPrintPrescription');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }
});
</script>
@endpush
