@extends('adminlte::page')

@section('content_header')
    <h1>Prescription Print Preview</h1>
@endsection

@section('content')
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Prescription
        </button>
    </div>

    <div class="print-page">
        <!-- PATIENT INFORMATION -->
        <div class="patient-bar">
            <div class="pb-item">
                <span class="pb-label">রোগীর কোড:</span>
                <span class="pb-value">{{ $patient->patientcode ?? $rx->id ?? 'N/A' }}</span>
            </div>
            <div class="pb-sep"></div>
            <div class="pb-item pb-name">
                <span class="pb-label">নাম:</span>
                <span class="pb-value">{{ $patient->patientname ?? 'N/A' }}</span>
            </div>
            <div class="pb-sep"></div>
            <div class="pb-item">
                <span class="pb-label">মোবাইল:</span>
                <span class="pb-value">{{ $patient->mobile_no ?? $patient->mobileno ?? '—' }}</span>
            </div>
            <div class="pb-sep"></div>
            <div class="pb-item">
                <span class="pb-label">বয়স:</span>
                <span class="pb-value">{{ $patient->age ?? $patient->patient_age ?? '—' }}</span>
            </div>
            <div class="pb-sep"></div>
            <div class="pb-item">
                <span class="pb-label">লিঙ্গ:</span>
                <span class="pb-value">{{ $patient->gender ?? $patient->sex ?? '—' }}</span>
            </div>
            <div class="pb-sep"></div>
            <div class="pb-item pb-last">
                <span class="pb-label">তারিখ:</span>
                <span class="pb-value">
                    @php
                        $dateStr = '';
                        $rawDate = $rx->prescribed_on ?? $rx->created_at ?? null;
                        if (!empty($rawDate)) {
                            try {
                                $dateStr = \Carbon\Carbon::parse($rawDate)->format('d-m-Y');
                            } catch (\Exception $e) {
                                $dateStr = $rawDate;
                            }
                        } else {
                            $dateStr = now()->format('d-m-Y');
                        }
                    @endphp
                    {{ $dateStr }}
                </span>
            </div>
        </div>

        <!-- MAIN BODY -->
        <div class="prescription-body">
            <!-- LEFT PANEL -->
            <div class="left-panel">
                {{-- C/C --}}
                <div class="clinical-block">
                    <div class="section-label">C/C</div>
                    <div class="section-body">
                        @php
                            $ccLines = [];
                            if (!empty($complaints) && count($complaints) > 0) {
                                foreach ($complaints as $c) {
                                    $text = $c->complaint ?? $c->complain ?? $c->name ?? '';
                                    $note = $c->note ?? '';
                                    if (trim($text) !== '') {
                                        $ccLines[] = '• ' . $text . ($note ? ' (' . $note . ')' : '');
                                    }
                                }
                            }
                            if (empty($ccLines) && !empty($rx->chief_complaint)) {
                                $ccLines[] = '• ' . $rx->chief_complaint;
                            }
                        @endphp
                        {!! nl2br(e(implode("\n", $ccLines))) !!}
                    </div>
                </div>

                {{-- O/E --}}
                <div class="clinical-block">
                    <div class="section-label">O/E</div>
                    <div class="section-body">
                        @php
    $oeText = '';
    if (!empty($patient->patientcode) && \Illuminate\Support\Facades\Schema::hasTable('nursing_admissions')) {
        $nursing = \Illuminate\Support\Facades\DB::table('nursing_admissions')
            ->where('patient_code', $patient->patientcode)
            ->orderByDesc('id')
            ->first();
        if ($nursing) {
            $parts = [];
            $parts[] = 'Pulse: ' . ($nursing->pulse ?? '');
            $parts[] = 'BP: ' . ($nursing->bp ?? '');
            $temp = $nursing->temp ?? $nursing->temperature ?? '';
            $parts[] = 'Temp: ' . $temp;
            $parts[] = 'Weight: ' . ($nursing->weight ?? '');
            $oeText = implode("\n", $parts);
        }
    }
    if (empty($oeText)) {
        $oeText = "Pulse: \nBP: \nTemp: \nWeight: ";
    }
@endphp
{!! nl2br(e($oeText)) !!}
                    </div>
                </div>

                {{-- Investigation --}}
                <div class="clinical-block">
                    <div class="section-label">Inv</div>
                    <div class="section-body">
                        @php
                            $invLines = [];
                            if (!empty($investigations) && count($investigations) > 0) {
                                foreach ($investigations as $i) {
                                    $text = $i->name ?? $i->investigation_name ?? $i->test ?? '';
                                    $note = $i->note ?? $i->notes ?? '';
                                    if (trim($text) !== '') {
                                        $invLines[] = '• ' . $text . ($note ? ' (' . $note . ')' : '');
                                    }
                                }
                            }
                        @endphp
                        {!! nl2br(e(implode("\n", $invLines))) !!}
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="right-panel">
                <div class="rx-header">
                    <span class="rx-symbol">R<span>x</span></span>
                </div>
                <table class="med-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-name">ঔষধের নাম</th>
                            <th colspan="3" class="col-when">কখন খাবেন?</th>
                            <th colspan="2" class="col-meal">আহারের</th>
                            <th rowspan="2" class="col-duration">কতদিন</th>
                            <th rowspan="2" class="col-instruction">নির্দেশনা</th>
                        </tr>
                        <tr>
                            <th class="col-dose">সকাল</th>
                            <th class="col-dose">দুপুর</th>
                            <th class="col-dose">রাত</th>
                            <th class="col-meal-sub">আগে</th>
                            <th class="col-meal-sub">পরে</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalRows = 13;
                            $medCount = (!empty($medicines) && is_iterable($medicines)) ? count($medicines) : 0;
                            $emptyNeeded = max(0, $totalRows - $medCount);
                        @endphp

                        @if($medCount > 0)
                            @foreach($medicines as $med)
                                @php
                                    $name        = is_array($med) ? ($med['name'] ?? $med['medicine_name'] ?? '') : ($med->name ?? $med->medicine_name ?? '');
                                    $strength    = is_array($med) ? ($med['strength'] ?? '') : ($med->strength ?? '');
                                    $morning     = is_array($med) ? ($med['morning'] ?? $med['sokal'] ?? '') : ($med->morning ?? $med->sokal ?? '');
                                    $noon        = is_array($med) ? ($med['noon'] ?? $med['dupur'] ?? $med['dupure'] ?? '') : ($med->noon ?? $med->dupur ?? $med->dupure ?? '');
                                    $night       = is_array($med) ? ($med['night'] ?? $med['raat'] ?? '') : ($med->night ?? $med->raat ?? '');
                                    $duration    = is_array($med) ? ($med['duration'] ?? '') : ($med->duration ?? '');
                                    $timing      = is_array($med) ? ($med['timing'] ?? $med['meal_timing'] ?? '') : ($med->timing ?? $med->meal_timing ?? '');
                                    $instruction = is_array($med) ? ($med['instruction'] ?? $med['note'] ?? '') : ($med->instruction ?? $med->note ?? '');
                                    $timing      = strtolower(trim($timing));

                                    // frequency থেকে morning/noon/night বের করা
                                    if ($morning === '' && $noon === '' && $night === '') {
                                        $freq = is_array($med) ? ($med['frequency'] ?? $med['dose'] ?? '') : ($med->frequency ?? $med->dose ?? '');
                                        if (preg_match('/[\d\/\+]/', $freq)) {
                                            $parts = preg_split('/[\+\-]/', str_replace(' ', '', $freq));
                                            $morning = $parts[0] ?? '';
                                            $noon    = $parts[1] ?? '';
                                            $night   = $parts[2] ?? '';
                                        }
                                    }

                                    $isBefore = in_array($timing, [
                                        'before', 'before meal', 'খাবার আগে', 'empty stomach', 'at bedtime'
                                    ]);
                                    $isAfter = in_array($timing, [
                                        'after', 'after meal', 'খাবার পরে', 'with meal'
                                    ]);
                                @endphp
                                <tr>
                                    <td class="col-name">
                                        <strong>{{ $name }}</strong>
                                        @if(!empty($strength))
                                            <br><span class="strength">{{ $strength }}</span>
                                        @endif
                                    </td>
                                    <td class="col-dose">{{ $morning }}</td>
                                    <td class="col-dose">{{ $noon }}</td>
                                    <td class="col-dose">{{ $night }}</td>
                                    <td class="col-meal-sub">{{ $isBefore ? '✓' : '' }}</td>
                                    <td class="col-meal-sub">{{ $isAfter ? '✓' : '' }}</td>
                                    <td class="col-duration">{{ $duration }}</td>
                                    <td class="col-instruction">{{ $instruction }}</td>
                                </tr>
                            @endforeach
                        @endif

                        @for ($i = 0; $i < $emptyNeeded; $i++)
                            <tr class="empty-row">
                                <td class="col-name">&nbsp;</td>
                                <td class="col-dose"></td>
                                <td class="col-dose"></td>
                                <td class="col-dose"></td>
                                <td class="col-meal-sub"></td>
                                <td class="col-meal-sub"></td>
                                <td class="col-duration"></td>
                                <td class="col-instruction"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer-area">
            <div class="footer-left">
                <div class="advice-box">
                    পরামর্শ ও নির্দেশনা<br>
                    নির্ধারিত সময় ও পরিমাণ মতো ওষুধ সেবন করুন<br>
                    --- খাবার আগে খাওয়ার নির্দেশ<br>
                    ----- খাবার পরে খাওয়ার নির্দেশ<br>
                    অসুস্থতা বাড়লে অথবা পার্শ্বপ্রতিক্রিয়া হলে দ্রুত যোগাযোগ করুন
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">চিকিৎসকের স্বাক্ষর ও সিল</div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap');

body {
    background-color: #e9ecef;
}

.print-page {
    width: 210mm;
    height: 297mm;
    padding: 52mm 8mm 26mm 8mm;
    box-sizing: border-box;
    position: relative;
    background: #fff;
    margin: 15px auto;
    box-shadow: 0 0 14px rgba(0,0,0,0.12);
    color: #000;
    font-family: 'Noto Sans Bengali', sans-serif !important;
    font-size: 12.5pt;
    line-height: 1.4;
    display: flex;
    flex-direction: column;
}

/* Patient Bar */
.patient-bar {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding-bottom: 2.8mm;
    margin-bottom: 4mm;
    border-bottom: 1.5pt solid #000;
    white-space: nowrap;
    flex-shrink: 0;
    font-size: 11pt;
}
.pb-item {
    display: flex;
    align-items: baseline;
    gap: 1.2mm;
    flex-shrink: 1;
}
.pb-label { font-weight: 700; }
.pb-value { font-weight: 500; }
.pb-name {
    flex-shrink: 1;
    max-width: 52mm;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pb-name .pb-value {
    font-weight: 700;
    font-size: 12pt;
}
.pb-last { flex-shrink: 0; }
.pb-sep {
    width: 0;
    height: 3.8mm;
    border-right: 0.7pt solid #444;
    margin: 0 1.6mm;
    flex-shrink: 0;
}

/* Main Body */
.prescription-body {
    display: grid;
    grid-template-columns: 26% 1fr;
    gap: 0;
    flex: 1 1 auto;
    min-height: 0;
    margin-bottom: 2mm;
}
.left-panel {
    padding-right: 3.5mm;
    border-right: 1pt solid #000;
    display: flex;
    flex-direction: column;
    gap: 7.5mm;
    overflow: hidden;
}
.clinical-block { flex-shrink: 0; }
.section-label {
    font-weight: 700;
    font-size: 13pt;
    border-bottom: 1.3pt solid #000;
    padding-bottom: 0.5mm;
    margin-bottom: 1.5mm;
    display: inline-block;
    min-width: 18mm;
}
.section-body {
    font-size: 12pt;
    line-height: 1.6;
    min-height: 6mm;
    word-break: break-word;
}
.right-panel {
    padding-left: 3.5mm;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
.rx-header {
    flex-shrink: 0;
    margin-bottom: 2mm;
}
.rx-symbol {
    font-family: "Times New Roman", Times, serif;
    font-style: italic;
    font-weight: 700;
    font-size: 28pt;
    line-height: 1;
    letter-spacing: -1px;
}
.rx-symbol span {
    font-size: 0.72em;
    position: relative;
    top: -1px;
}

/* Medicine Table */
.med-table {
    width: 100%;
    height: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: fixed;
    flex: 1 1 auto;
    border: 0.75pt solid #000;
}
.med-table th,
.med-table td {
    border: 0.75pt solid #000;
    padding: 2.2mm 1.3mm;
    text-align: center;
    vertical-align: middle;
    font-size: 11.5pt;
    line-height: 1.35;
    box-sizing: border-box;
}
.med-table th {
    background-color: #e8e8e8;
    font-weight: 700;
    font-size: 11pt;
}
.med-table .col-name {
    width: 42%;
    text-align: left !important;
    padding-left: 2.4mm !important;
}
.med-table .col-name .strength {
    font-size: 10.5pt;
    font-weight: 400;
}
.med-table .col-when { width: 27%; }
.med-table .col-dose { width: 9%; white-space: nowrap; }
.med-table .col-meal { width: 16%; }
.med-table .col-meal-sub { width: 8%; white-space: nowrap; }
.med-table .col-duration { width: 15%; }
.med-table tbody tr { height: 7.2%; }
.med-table tbody tr.empty-row td { height: 7.2%; }

/* Footer */
.footer-area {
    position: absolute;
    left: 8mm;
    right: 8mm;
    bottom: 8mm;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 14mm;
    background: #fff;
    z-index: 20;
}
.footer-left {
    flex: 1 1 auto;
    max-width: 125mm;
}
.advice-box {
    font-size: 12pt;
    line-height: 1.6;
    font-weight: 500;
    border-top: 1pt solid #000;
    padding-top: 2.2mm;
}
.signature-box {
    flex: 0 0 52mm;
    text-align: center;
}
.signature-line {
    width: 100%;
    border-top: 1.3pt solid #000;
    margin-bottom: 1.6mm;
}
.signature-label {
    font-size: 12pt;
    font-weight: 700;
}

/* Print */
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
    .navbar, .main-sidebar, .content-header, .no-print,
    .main-footer, .control-sidebar {
        display: none !important;
    }
    .wrapper, .content-wrapper, .content, .container, .container-fluid,
    .card, .card-body, .box, .box-body, section.content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        min-height: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
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
        font-family: 'Noto Sans Bengali', sans-serif !important;
    }
    .print-page * {
        font-family: 'Noto Sans Bengali', sans-serif !important;
    }
    .med-table th {
        background-color: #e8e8e8 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endpush