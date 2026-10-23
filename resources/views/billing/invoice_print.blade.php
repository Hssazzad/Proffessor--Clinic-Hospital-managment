@extends('adminlte::page')

@section('title', 'Invoice - ' . (data_get($payment, 'PatientName') ?? data_get($payment, 'patient_name', 'Patient')) . ' - ' . (data_get($payment, 'BillNo') ?? data_get($payment, 'bill_no')))

@section('content_header')
    <h1>Invoice</h1>
@endsection

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root {
        --ink:  #000000;
        --muted:#222222;
    }

    /* A5 Exact Sheet Sizing (148mm x 210mm) */
    .invoice-wrapper {
        position: relative;
        width: 148mm;
        height: 210mm;
        max-height: 210mm;
        background: #fff;
        padding: 10mm 10mm 8mm 10mm;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        box-sizing: border-box;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        font-family: 'Inter', Arial, Verdana, Helvetica, sans-serif;
        font-variant-numeric: tabular-nums;
        font-size: 12px;
        color: var(--ink);
        margin: 15px auto;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-26deg);
        font-size: 75px;
        font-weight: 900;
        letter-spacing: 4px;
        white-space: nowrap;
        opacity: .06;
        z-index: 0;
        pointer-events: none;
        user-select: none;
        color: #000;
    }

    .inv-body {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    /* Top Header */
    .inv-head {
        text-align: center;
        border-bottom: 2px solid var(--ink);
        padding-bottom: 5px;
        margin-bottom: 8px;
    }
    .brand-name {
        font-family: 'Nirmala UI', 'Kalpurush', 'SolaimanLipi', Arial, sans-serif;
        font-size: 25px;
        font-weight: 900;
        letter-spacing: .3px;
        color: #b30000;
    }
    .brand-meta {
        font-size: 10.5px;
        color: var(--muted);
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* Sub-header Barcode & Invoice Meta */
    .bill-info-grid {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 6px;
    }
    .doc-title-right {
        text-align: right;
        font-size: 11px;
        font-weight: 700;
    }
    .doc-title-right strong { font-size: 13px; font-weight: 900; }

    /* Patient Info Box */
    .patient-box {
        border: 1.5px solid var(--ink);
        border-radius: 3px;
        padding: 7px 10px;
        margin-bottom: 8px;
        background: #fff;
    }
    .patient-grid {
        display: grid;
        grid-template-columns: 2fr 1.3fr;
        gap: 5px 12px;
    }
    .patient-grid .f {
        display: flex;
        align-items: baseline;
    }
    .patient-grid .f label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        width: 75px;
        font-weight: 700;
    }
    .patient-grid .f span {
        font-weight: 900;
        font-size: 12px;
        color: var(--ink);
    }

    /* Items Table */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: 11.5px;
    }
    .items-table thead tr { background: #fff; }
    .items-table thead th {
        padding: 6px 6px;
        text-align: left;
        font-weight: 900;
        font-size: 10.5px;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: var(--ink);
        border-top: 1.5px solid var(--ink);
        border-bottom: 1.5px solid var(--ink);
    }
    .items-table thead th.text-right  { text-align: right; }
    .items-table thead th.text-center { text-align: center; }
    .items-table tbody td {
        padding: 6px 6px;
        border-bottom: 1px solid #000;
        vertical-align: middle;
        font-weight: 700;
        color: var(--ink);
    }
    .items-table tbody td.text-right  { text-align: right; font-variant-numeric: tabular-nums; }
    .items-table tbody td.text-center { text-align: center; }

    /* Financial Section */
    .financial-section {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 15px;
        align-items: start;
        margin-top: 16px;
        margin-bottom: 8px;
    }
    .left-financial-col {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .corporate-box {
        font-size: 10.5px;
        color: var(--muted);
        line-height: 1.4;
    }
    .corporate-box strong {
        color: var(--ink);
        display: block;
        font-size: 11.5px;
        font-weight: 900;
    }
    .paid-stamp-box {
        display: inline-block;
        border: 2.5px solid var(--ink);
        color: var(--ink);
        font-size: 18px;
        font-weight: 900;
        padding: 3px 16px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        transform: rotate(-4deg);
    }

    .totals-table { width: 100%; border-collapse: collapse; }
    .totals-table td {
        padding: 4px 0;
        font-size: 12px;
        color: var(--ink);
        font-weight: 700;
    }
    .totals-table td.text-right { text-align: right; font-variant-numeric: tabular-nums; }
    .totals-table tr td:first-child { color: var(--muted); font-weight: 700; }
    .totals-table .net-row td {
        padding-top: 4px;
        border-top: 1.5px solid var(--ink);
        font-weight: 900;
        font-size: 12px;
    }
    .totals-table .paid-row td, .totals-table .due-row td { font-weight: 900; }

    /* Room Routing Box */
    .room-routing-box {
        font-family: 'Nirmala UI', 'Kalpurush', 'SolaimanLipi', Arial, sans-serif;
        border: 1.5px solid var(--ink);
        border-radius: 4px;
        padding: 11px 14px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 18px;
        background: #fff;
        font-size: 13.5px;
        font-weight: 900;
    }
    .room-boxes { display: flex; gap: 12px; }
    .room-box {
        border: 2px solid var(--ink);
        width: 46px;
        height: 38px;
        border-radius: 4px;
        background: #fff;
    }

    /* Signature Section */
    .signature-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 8px;
    }
    .sig-line {
        border-top: 1.5px solid #000;
        width: 150px;
        padding-top: 3px;
        text-align: center;
        font-size: 10px;
        color: var(--ink);
        font-weight: 800;
    }

    /* Footer Note */
    .footer-note {
        text-align: center;
        font-size: 10.5px;
        color: var(--muted);
        border-top: 1px dashed var(--ink);
        padding-top: 6px;
        line-height: 1.6;
        font-weight: 700;
    }

    /* =============== PRINT SETTINGS =============== */
    @media print {
        @page {
            size: 148mm 210mm;
            margin: 0;
        }

        /* Hide AdminLTE chrome on print */
        .main-sidebar,
        .main-header,
        .main-footer,
        .content-header,
        .breadcrumb,
        .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .wrapper {
            background: #fff !important;
        }

        html, body {
            width: 148mm !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .invoice-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 148mm !important;
            height: 210mm !important;
            max-height: 210mm !important;
            margin: 0 !important;
            padding: 8mm 10mm 6mm 10mm !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
    }
</style>
@endpush

@section('content')

<div class="no-print text-center mb-3">
    <button class="btn btn-dark" onclick="window.print()">
        <i class="fas fa-print"></i> Print Invoice
    </button>
    <a href="{{ url('Billing/CreateInvoice') }}/{{ $payment->ID }}/pdf" class="btn btn-danger">
        <i class="fas fa-file-pdf"></i> Save as PDF
    </a>
    <a href="{{ route('billing.payment.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Billing
    </a>
</div>

<div class="invoice-wrapper">

    @php
        $billNo = data_get($payment, 'BillNo') ?? data_get($payment, 'bill_no', '');
        $statusVal = data_get($payment, 'Status') ?? data_get($payment, 'status', 'due');
        $statusKey = strtolower($statusVal);
        $stampWord = strtoupper($statusKey);
    @endphp

    <div class="watermark">{{ $stampWord }}</div>

    <div class="inv-body">

        <div>
            <!-- Header: Hospital Name Top -->
            <div class="inv-head">
                <div class="brand-name">{{ config('app.name', 'Professor Clinic') }}</div>
                <div class="brand-meta"></div>
            </div>

            <!-- Date only (Invoice ID / barcode text removed) -->
            <div class="bill-info-grid">
                <div class="doc-title-right">
                    @php
                        $printInvoiceDate = data_get($payment, 'PaymentDate') ?? data_get($payment, 'payment_date') ?? data_get($payment, 'created_at');
                    @endphp
                    Date: <strong>{{ $printInvoiceDate ? \Carbon\Carbon::parse($printInvoiceDate)->format('d M Y') : '' }}</strong>
                </div>
            </div>

            <!-- Patient Info Box -->
            <div class="patient-box">
                <div class="patient-grid">
                    <div class="f">
                        <label>ID No.</label>
                        <span>: {{ data_get($payment, 'PatientCode') ?? data_get($payment, 'patient_code') ?? data_get($payment, 'PatientID') ?? data_get($payment, 'patient_id') ?? $billNo }}</span>
                    </div>
                    <div class="f">
                        @if(!empty(data_get($payment, 'MobileNo') ?? data_get($payment, 'mobile_no')))
                        <label>Mobile</label>
                        <span>: {{ data_get($payment, 'MobileNo') ?? data_get($payment, 'mobile_no') }}</span>
                        @endif
                    </div>
                    <div class="f">
                        <label>Name</label>
                        <span>: {{ strtoupper(data_get($payment, 'PatientName') ?? data_get($payment, 'patient_name', 'N/A')) }}</span>
                    </div>
                    <div class="f">
                        <label>Age</label>
                        <span>: {{ data_get($payment, 'PatientAge') ?? data_get($payment, 'patient_age', '20 year(s)') }}</span>
                    </div>
                    <div class="f" style="grid-column: span 2;">
                        <label>Refd by</label>
                        <span>: {{ strtoupper(data_get($payment, 'RefDoctor') ?? '—') }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:8%">SL</th>
                        <th>Test/Service</th>
                        <th class="text-center" style="width:10%">Qty</th>
                        <th class="text-right" style="width:16%">Amount</th>
                        <th class="text-right" style="width:12%">Disc%</th>
                        <th class="text-right" style="width:24%">Delivery Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ data_get($item, 'ServiceName') ?? data_get($item, 'service_name', '') }}
                            @php
                                $itemRemarks = data_get($item, 'Remarks') ?? data_get($item, 'remarks');
                            @endphp
                            @if(!empty($itemRemarks))
                                <div style="font-size: 9.5px; color: var(--muted); margin-top: 2px;">{{ $itemRemarks }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ data_get($item, 'Quantity') ?? data_get($item, 'quantity', 1) }}</td>
                        <td class="text-right">{{ number_format((float)(data_get($item, 'Amount') ?? data_get($item, 'amount', 0)), 2) }}</td>
                        <td class="text-right">{{ data_get($item, 'DiscountPercent') ?? data_get($item, 'discount_percent', '—') }}</td>
                        <td class="text-right" style="font-size: 10px;">
                            @php
                                $deliveryRaw = data_get($item, 'DeliveryDateTime') ?? data_get($item, 'delivery_date_time');
                            @endphp
                            @if(!empty($deliveryRaw))
                                {{ \Carbon\Carbon::parse($deliveryRaw)->format('d/m/Y h:i A') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 10px;">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @php
                $totalBill = (float)(data_get($payment, 'TotalBill') ?? data_get($payment, 'total_bill', 0));
                $disc = (float)(data_get($payment, 'Discount') ?? data_get($payment, 'discount', 0));
                $extraDisc = (float)(data_get($payment, 'ExtraDiscount') ?? data_get($payment, 'extra_discount', 0));
                $net = $totalBill - $disc - $extraDisc;
            @endphp

            <!-- Financial Section -->
            <div class="financial-section">
                <div class="left-financial-col">
                    <div class="corporate-box">
                        <strong>Corporate</strong>
                        Bogura Diabetic &amp; Swasthoseba Hospital
                    </div>
                    <div>
                        <div class="paid-stamp-box">{{ $stampWord }}</div>
                    </div>
                </div>
                <div>
                    <table class="totals-table">
                        <tr>
                            <td>Total amount</td>
                            <td class="text-right">{{ number_format($totalBill, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td class="text-right">{{ number_format($disc, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Extra Discount</td>
                            <td class="text-right">{{ number_format($extraDisc, 2) }}</td>
                        </tr>
                        <tr class="net-row">
                            <td>Payable</td>
                            <td class="text-right">{{ number_format($net, 2) }}</td>
                        </tr>
                        <tr class="paid-row">
                            <td>Received</td>
                            <td class="text-right">{{ number_format((float)(data_get($payment, 'PaidAmount') ?? data_get($payment, 'paid_amount', 0)), 2) }}</td>
                        </tr>
                        <tr class="due-row">
                            <td>Due</td>
                            <td class="text-right">{{ number_format((float)(data_get($payment, 'DueAmount') ?? data_get($payment, 'due_amount', 0)), 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <!-- Room Routing Box -->
            <div class="room-routing-box">
                <span>যে সকল রুমে যাবেন:</span>
                <div class="room-boxes">
                    <div class="room-box"></div>
                    <div class="room-box"></div>
                    <div class="room-box"></div>
                </div>
                <div style="margin-left: auto; font-size: 13px; font-weight: 900; color: var(--ink);">
                    রিপোর্ট ডেলিভারি ২য় তলায়
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div style="font-size: 9.5px; color: var(--muted); font-weight: 700;">
                    Printed on {{ data_get($payment, 'PrintTime') ?? data_get($payment, 'print_time') ?? now()->format('d/m/y h:i:sA') }} , * Discount not applicable
                </div>
                <div>
                    <div class="sig-line">{{ data_get($payment, 'CollectedBy') ?? data_get($payment, 'collected_by', 'Mahfuz') }}</div>
                    <div style="text-align: center; font-size: 9.5px; color: var(--muted); margin-top: 2px; font-weight: 800;">(Signature)</div>
                </div>
            </div>

            <!-- Footer Address & Contacts -->
            <div class="footer-note">
                House # 1103/1116, Kanosgari, Sherpur Road, Bogura Sadar, Bogura<br>
                Phone: 02589-903141-42, Mobile: 01701 560011, 01701 560012<br>
                Mobile App: Ibn Sina Doctor Appointment, Hotline: +880961-0009617<br>
                E-mail: ibnsinabogra@gmail.com, Web: www.ibnsinatrust.com
            </div>
        </div>

    </div>
</div>

@endsection