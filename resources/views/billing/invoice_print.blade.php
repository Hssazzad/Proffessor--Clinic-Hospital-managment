<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $payment->PatientName }} - {{ $payment->BillNo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:        #15324f;
            --navy-deep:   #0e2438;
            --ink:         #1c2530;
            --muted:       #7a8694;
            --hairline:    #dfe4e9;
            --paid:        #1f9d55;
            --paid-tint:   #e8f7ee;
            --partial:     #c9871a;
            --partial-tint:#fdf2e0;
            --due:         #d6433f;
            --due-tint:    #fbe9e8;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: var(--ink);
            background: #eef1f4;
        }

        .invoice-wrapper {
            position: relative;
            max-width: 760px;
            margin: 30px auto;
            background: #fff;
            padding: 40px 44px;
            border-radius: 6px;
            box-shadow: 0 4px 22px rgba(15,40,60,.10);
            overflow: hidden;
        }

        /* ---------- watermark (full-bleed, sits behind everything) ---------- */
        .watermark {
            position: absolute;
            top: 54%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-26deg);
            font-size: clamp(70px, 15vw, 140px);
            font-weight: 900;
            letter-spacing: 6px;
            white-space: nowrap;
            opacity: .10;
            z-index: 0;
            pointer-events: none;
            user-select: none;
        }
        .watermark.wm-paid    { color: var(--paid); }
        .watermark.wm-partial { color: var(--partial); }
        .watermark.wm-due     { color: var(--due); }

        .inv-body { position: relative; z-index: 1; }

        /* ---------- header ---------- */
        .inv-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }
        .brand-name {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--navy);
            line-height: 1.1;
        }
        .brand-meta {
            margin-top: 8px;
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.7;
        }
        .doc-title {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--navy);
            text-align: right;
            line-height: 1.1;
        }
        .doc-meta {
            margin-top: 8px;
            font-size: 12px;
            color: var(--muted);
            text-align: right;
            line-height: 1.7;
        }
        .doc-meta strong { color: var(--ink); font-weight: 700; }

        .inv-subhead {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            font-size: 12px;
            color: var(--muted);
        }
        .inv-subhead strong { color: var(--ink); font-weight: 700; }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .status-paid    { background: var(--paid-tint);    color: var(--paid); }
        .status-partial { background: var(--partial-tint); color: var(--partial); }
        .status-due     { background: var(--due-tint);     color: var(--due); }

        .hairline {
            border-top: 1px solid var(--hairline);
            margin: 18px 0;
        }

        /* ---------- bill to ---------- */
        .eyebrow {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 10px;
        }
        .bill-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px 20px;
            margin-bottom: 22px;
        }
        .bill-grid .f label {
            display: block;
            font-size: 10.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 2px;
        }
        .bill-grid .f span {
            font-weight: 700;
            font-size: 13.5px;
            color: var(--ink);
        }

        /* ---------- items table ---------- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 12.5px;
        }
        .items-table thead tr { background: var(--navy); }
        .items-table thead th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: .4px;
            text-transform: uppercase;
            color: #fff;
        }
        .items-table thead th.text-right  { text-align: right; }
        .items-table thead th.text-center { text-align: center; }
        .items-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--hairline);
            vertical-align: middle;
            background: transparent;
        }
        .items-table tbody td.text-right  { text-align: right; font-variant-numeric: tabular-nums; }
        .items-table tbody td.text-center { text-align: center; }

        .print-inv-badge,
        .print-doc-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            white-space: nowrap;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .print-inv-badge { background: var(--paid-tint); color: var(--paid); }
        .print-doc-badge { background: #e7edf5; color: var(--navy); }

        .no-items td {
            padding: 20px;
            text-align: center;
            color: var(--muted);
            font-style: italic;
            background: transparent;
        }

        /* ---------- totals + stamp row ---------- */
        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            margin-bottom: 26px;
        }
        .stamp-mark {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            transform: rotate(-6deg);
            border: 3px solid currentColor;
            border-radius: 8px;
            padding: 4px 18px;
            opacity: .9;
        }
        .stamp-paid    { color: var(--paid); }
        .stamp-partial { color: var(--partial); }
        .stamp-due     { color: var(--due); }

        .totals-table { min-width: 260px; border-collapse: collapse; }
        .totals-table td {
            padding: 5px 0 5px 24px;
            font-size: 13px;
            color: var(--ink);
        }
        .totals-table td.text-right { text-align: right; font-variant-numeric: tabular-nums; }
        .totals-table .discount-row td:last-child { color: var(--due); }
        .totals-table .net-row td {
            padding-top: 12px;
            border-top: 2px solid var(--ink);
            font-size: 16px;
            font-weight: 800;
        }
        .totals-table .paid-row td { color: var(--paid); font-weight: 700; }
        .totals-table .due-row  td { color: var(--due);  font-weight: 700; }

        /* ---------- terms ---------- */
        .terms {
            margin-bottom: 8px;
        }
        .terms h4 {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 8px;
        }
        .terms ol {
            margin-left: 18px;
            color: var(--muted);
            font-size: 11.5px;
            line-height: 1.7;
        }
        .thank-you {
            margin-top: 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
        }

        /* ---------- signatures ---------- */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 46px;
            padding-top: 18px;
            border-top: 1px solid var(--hairline);
        }
        .sig-line {
            border-top: 1px solid #888;
            padding-top: 6px;
            margin-top: 38px;
            text-align: center;
            font-size: 11.5px;
            color: var(--muted);
        }

        .invoice-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10.5px;
            color: #b0b8c0;
            border-top: 1px dashed var(--hairline);
            padding-top: 10px;
        }

        /* ---------- on-screen print controls ---------- */
        .print-actions { text-align: center; margin: 20px 0 10px; }
        .btn-print {
            background: var(--navy);
            color: #fff;
            border: none;
            padding: 10px 36px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 8px;
        }
        .btn-print:hover { background: var(--navy-deep); }
        .btn-back {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover { background: #5a6268; color: #fff; text-decoration: none; }

        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: #fff; }
            .invoice-wrapper {
                margin: 0;
                padding: 20px 26px;
                border-radius: 0;
                box-shadow: none;
                max-width: 100%;
            }
            .print-actions { display: none !important; }
            .watermark,
            .stamp-mark,
            .status-badge,
            .print-inv-badge,
            .print-doc-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="print-actions">
    <button class="btn-print" onclick="window.print()">
        &#128438; Print Invoice
    </button>
    <a href="{{ route('billing.payment.index') }}" class="btn-back">
        &#8592; Back to Billing
    </a>
</div>

<div class="invoice-wrapper">

    @php
        $statusClass = match(strtolower($payment->Status)) {
            'paid'    => 'status-paid',
            'partial' => 'status-partial',
            default   => 'status-due',
        };
        $statusKey = str_replace('status-', '', $statusClass);
        $stampWord = match($statusKey) {
            'paid'    => 'PAID',
            'partial' => 'PARTIAL',
            default   => 'DUE',
        };
    @endphp

    {{-- WATERMARK (decorative, driven by existing status logic) --}}
    <div class="watermark wm-{{ $statusKey }}">{{ $stampWord }}</div>

    <div class="inv-body">

        {{-- HEADER --}}
        <div class="inv-head">
            <div>
                <div class="brand-name">{{ config('app.name', 'Professor Clinic') }}</div>
                <div class="brand-meta">
                    Majhira, Shahjahanpur, Bogura<br>
                    01713740680 &middot; 01715098131 &middot; 017200390058
                </div>
            </div>
            <div>
                <div class="doc-title">Invoice</div>
                <div class="doc-meta">
                    Invoice No <strong>{{ $payment->BillNo }}</strong><br>
                    Date <strong>{{ $payment->PaymentDate }}</strong>
                </div>
            </div>
        </div>

        <div class="inv-subhead">
            <div>
                Method <strong>{{ ucwords(str_replace('_', ' ', $payment->PaymentMethod)) }}</strong>
                @if($payment->CollectedBy)
                    &nbsp;&nbsp;|&nbsp;&nbsp; Collected by <strong>{{ $payment->CollectedBy }}</strong>
                @endif
            </div>
            <div>
                <span class="status-badge {{ $statusClass }}">{{ strtoupper($payment->Status) }}</span>
            </div>
        </div>

        <div class="hairline"></div>

        {{-- PATIENT INFO --}}
        <div class="eyebrow">Bill To</div>
        <div class="bill-grid">
            <div class="f">
                <label>Patient Name</label>
                <span>{{ $payment->PatientName }}</span>
            </div>
            <div class="f">
                <label>Patient Code</label>
                <span>{{ $payment->PatientCode ?? '' }}</span>
            </div>
            <div class="f">
                <label>Age</label>
                <span>{{ $payment->PatientAge ?? '' }}</span>
            </div>
            <div class="f">
                <label>Mobile</label>
                <span>{{ $payment->MobileNo ?? '' }}</span>
            </div>
            <div class="f">
                <label>Admission ID</label>
                <span>{{ $payment->AdmissionId ? '#'.$payment->AdmissionId : '' }}</span>
            </div>
            <div class="f">
                <label>Invoice Date</label>
                <span>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y, h:i A') : '' }}</span>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:18%">Category</th>
                    <th>Service / Item</th>
                    <th class="text-right" style="width:13%">Unit Price</th>
                    <th class="text-center" style="width:7%">Qty</th>
                    <th class="text-right" style="width:13%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @php
                            $isDoctor = ($item->CategoryName == 'Doctor Consultation');
                            $badgeClass = $isDoctor ? 'print-doc-badge' : 'print-inv-badge';
                        @endphp
                        <span class="{{ $badgeClass }}">
                            {{ $item->CategoryName ?? $item->CategoryCode ?? '' }}
                        </span>
                    </td>
                    <td>
                        {{ $item->ServiceName }}
                        @if($item->Remarks)
                            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ $item->Remarks }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->UnitPrice, 2) }}</td>
                    <td class="text-center">{{ $item->Quantity }}</td>
                    <td class="text-right"><strong>{{ number_format($item->Amount, 2) }}</strong></td>
                </tr>
                @empty
                <tr class="no-items">
                    <td colspan="6"> General payment (no itemised breakdown) </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTALS + STAMP --}}
        @php
            $net = (float)$payment->TotalBill - (float)($payment->Discount ?? 0);
        @endphp
        <div class="totals-row">
            <div class="stamp-mark stamp-{{ $statusKey }}">{{ $stampWord }}</div>
            <table class="totals-table">
                <tr>
                    <td>Gross Amount</td>
                    <td class="text-right">{{ number_format($payment->TotalBill, 2) }}</td>
                </tr>
                <tr class="discount-row">
                    <td>Discount</td>
                    <td class="text-right">&minus; {{ number_format($payment->Discount ?? 0, 2) }}</td>
                </tr>
                <tr class="net-row">
                    <td>Net Payable</td>
                    <td class="text-right">{{ number_format($net, 2) }}</td>
                </tr>
                <tr class="paid-row">
                    <td>Paid Amount</td>
                    <td class="text-right">{{ number_format($payment->PaidAmount, 2) }}</td>
                </tr>
                <tr class="due-row">
                    <td>Due Amount</td>
                    <td class="text-right">{{ number_format($payment->DueAmount, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- TERMS --}}
        <div class="terms">
            <h4>Terms &amp; Conditions</h4>
            <ol>
                <li>This is a system-generated invoice and is valid without a physical signature.</li>
                <li>Please retain this invoice for insurance claims or future reference.</li>
                <li>For any billing discrepancy, contact the billing counter within 7 days.</li>
            </ol>
        </div>
        <div class="thank-you">Thank you for trusting {{ config('app.name', 'Professor Clinic') }}!</div>

        {{-- SIGNATURES --}}
        <div class="signatures">
            <div>
                <div class="sig-line">Patient / Guardian Signature</div>
            </div>
            <div style="text-align:right;">
                <div class="sig-line">Authorized Signature &amp; Seal</div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="invoice-footer">
            This is a computer-generated invoice &mdash; {{ config('app.name', 'Professor Clinic') }}
            &nbsp;|&nbsp; Printed: {{ now()->format('d M Y, h:i A') }}
        </div>

    </div>
</div>

</body>
</html>