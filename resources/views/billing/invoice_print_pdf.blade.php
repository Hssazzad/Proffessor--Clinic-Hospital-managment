<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: notobengali, sans-serif;
            color: #000000;
        }
        .invoice-wrapper {
            position: relative;
            width: 148mm;
            height: 210mm;
            padding: 10mm 10mm 8mm 10mm;
            box-sizing: border-box;
            font-size: 12px;
            color: #000000;
        }
        .invoice-body {
            position: relative;
        }
        .invoice-footer {
            position: absolute;
            left: 10mm;
            right: 10mm;
            bottom: 8mm;
        }
        .brand-name {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: .3px;
            color: #000000;
            text-transform: uppercase;
        }
        .footer-brand-name {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: .3px;
            color: #000000;
            text-transform: uppercase;
        }
        .label-muted {
            color: #222222;
            font-weight: 700;
        }
        .val-strong {
            font-weight: 900;
            color: #000000;
        }
    </style>
</head>
<body>
 
@php
    $billNo = data_get($payment, 'BillNo') ?? data_get($payment, 'bill_no', '');
    $statusVal = data_get($payment, 'Status') ?? data_get($payment, 'status', 'due');
    $statusKey = strtolower((string) $statusVal);
    $stampWord = $statusKey !== '' ? strtoupper($statusKey) : 'DUE';
 
    $totalBill = (float)(data_get($payment, 'TotalBill') ?? data_get($payment, 'total_bill', 0));
    $disc = (float)(data_get($payment, 'Discount') ?? data_get($payment, 'discount', 0));
    $extraDisc = (float)(data_get($payment, 'ExtraDiscount') ?? data_get($payment, 'extra_discount', 0));
    $net = $totalBill - $disc - $extraDisc;
 
    // Discount type/percent — flat discounts still show an equivalent %
    // so the printed invoice is consistent regardless of how it was entered.
    $discType = data_get($payment, 'discount_type') ?? data_get($payment, 'DiscountType') ?? 'percent';
    $discPercent = data_get($payment, 'discount_percent') ?? data_get($payment, 'DiscountPercent');
    if ($discPercent === null || $discPercent === '') {
        $discPercent = $totalBill > 0 ? round(($disc / $totalBill) * 100, 2) : 0;
    } else {
        $discPercent = round((float) $discPercent, 2);
    }
 
    $clinicAddress = data_get($clinic, 'Address') ?? data_get($clinic, 'address') ?? config('clinic.address');
    $clinicPhone = data_get($clinic, 'Phone') ?? data_get($clinic, 'phone') ?? config('clinic.phone');
    $deliveryNote = data_get($clinic, 'DeliveryNote') ?? data_get($clinic, 'delivery_note') ?? config('clinic.delivery_note');
@endphp
 
<div class="invoice-wrapper">
<div class="invoice-body">
 
    <!-- Header: Logo + Hospital Name -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 2px solid #000000; padding-bottom: 6px;">
        <tr>
            <td width="55px" valign="top" align="left">
                @if(file_exists(public_path('images/PC.png')))
                    <img src="{{ str_replace('\\', '/', public_path('images/PC.png')) }}" alt="" width="45px" />
                @endif
            </td>
            <td valign="top" align="left" style="padding-left: 10px;">
                <div class="brand-name">{{ config('clinic.name') }}</div>
                <div style="font-size: 14px; font-weight: 900; letter-spacing: .3px; color: #000000; margin-top: 2px;">{{ config('clinic.name_bn') }}</div>
                @if(!empty($clinicAddress) || !empty($clinicPhone))
                <div style="font-size: 11.5px; font-weight: 800; color: #000000; line-height: 1.6; margin-top: 3px;">
                    @if(!empty($clinicAddress)){{ $clinicAddress }}<br/>@endif
                    @if(!empty($clinicPhone))Phone: {{ $clinicPhone }}@endif
                </div>
                @endif
            </td>
        </tr>
    </table>
 
    <!-- Invoice No & Date -->
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="right" style="padding: 6px 0 6px 0; font-size: 13px; font-weight: 700;">
                @if(!empty($billNo))
                Invoice No: <strong>{{ $billNo }}</strong><br/>
                @endif
                @php
                    $pdfInvoiceDate = data_get($payment, 'PaymentDate') ?? data_get($payment, 'payment_date') ?? data_get($payment, 'created_at');
                @endphp
                Date: <strong>{{ $pdfInvoiceDate ? \Carbon\Carbon::parse($pdfInvoiceDate)->format('d M Y') : '' }}</strong>
            </td>
        </tr>
    </table>
 
    <!-- Patient Info Box -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px;">
        <tr>
            <td style="border: 2px solid #000000; border-radius: 3px; padding: 7px 10px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="58%" style="padding: 3px 0; font-size: 13px;">
                            <span class="label-muted">ID No.</span> <span class="val-strong">: {{ data_get($payment, 'PatientCode') ?? data_get($payment, 'patient_code') ?? data_get($payment, 'PatientID') ?? data_get($payment, 'patient_id') ?? $billNo }}</span>
                        </td>
                        <td width="42%" style="padding: 3px 0; font-size: 13px;">
                            @if(!empty(data_get($payment, 'MobileNo') ?? data_get($payment, 'mobile_no')))
                            <span class="label-muted">Mobile</span>
                            <span class="val-strong">: {{ data_get($payment, 'MobileNo') ?? data_get($payment, 'mobile_no') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="58%" style="padding: 3px 0; font-size: 13px;">
                            <span class="label-muted">Name</span>
                            <span class="val-strong">: {{ strtoupper((string) (data_get($payment, 'PatientName') ?? data_get($payment, 'patient_name'))) }}</span>
                        </td>
                        <td width="42%" style="padding: 3px 0; font-size: 13px;">
                            <span class="label-muted">Age</span>
                            <span class="val-strong">: {{ data_get($payment, 'PatientAge') ?? data_get($payment, 'patient_age') }}</span>
                            <div style="margin-top: 12px;">
                                <span class="label-muted">Gender</span>
                                <span class="val-strong">: {{ data_get($patient, 'gender') ?: '—' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 3px 0; font-size: 13px;">
                            <span class="label-muted">Refd by</span>
                            <span class="val-strong">: {{ strtoupper((string) (data_get($payment, 'RefDoctor') ?? '—')) }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
 
    <!-- Items Table -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 8px;">
        <thead>
            <tr>
                <th width="8%" align="left" style="padding: 6px; font-size: 12px; font-weight: 900; text-transform: uppercase; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">SL</th>
                <th align="left" style="padding: 6px; font-size: 12px; font-weight: 900; text-transform: uppercase; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">Test/Service</th>
                <th width="10%" align="center" style="padding: 6px; font-size: 12px; font-weight: 900; text-transform: uppercase; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">Qty</th>
                <th width="18%" align="right" style="padding: 6px; font-size: 12px; font-weight: 900; text-transform: uppercase; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">Amount</th>
                <th width="28%" align="right" style="padding: 6px; font-size: 12px; font-weight: 900; text-transform: uppercase; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">Delivery Date/Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
            <tr>
                <td align="left" style="padding: 4px 6px; border-bottom: 1px solid #000000; font-size: 12px; font-weight: 700;">{{ $index + 1 }}</td>
                <td align="left" style="padding: 4px 6px; border-bottom: 1px solid #000000; font-size: 12px; font-weight: 700;">
                    {{ data_get($item, 'ServiceName') ?? data_get($item, 'service_name') }}
                    @php
                        $itemRemarks = data_get($item, 'Remarks') ?? data_get($item, 'remarks');
                    @endphp
                    @if(!empty($itemRemarks))
                        <div style="font-size: 10.5px; color: #222222; margin-top: 1px;">{{ $itemRemarks }}</div>
                    @endif
                </td>
                <td align="center" style="padding: 4px 6px; border-bottom: 1px solid #000000; font-size: 12px; font-weight: 700;">{{ data_get($item, 'Quantity') ?? data_get($item, 'quantity') }}</td>
                <td align="right" style="padding: 4px 6px; border-bottom: 1px solid #000000; font-size: 12px; font-weight: 700;">{{ number_format((float)(data_get($item, 'Amount') ?? data_get($item, 'amount', 0)), 2) }}</td>
                <td align="right" style="padding: 4px 6px; border-bottom: 1px solid #000000; font-size: 11px; font-weight: 700;">
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
                <td colspan="5" align="center" style="padding: 10px 6px; border-bottom: 1px solid #000000; font-size: 12px; font-weight: 700; color: #222222;">No items found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
 
    <!-- Financial Section -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 12px; margin-bottom: 8px;">
        <tr>
            <td width="52%" valign="top">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border: 3px solid #000000; border-radius: 4px; font-size: 22px; font-weight: 900; letter-spacing: 1.5px; padding: 4px 18px; text-transform: uppercase;">{{ $stampWord }}</td>
                    </tr>
                </table>
            </td>
            <td width="48%" valign="top">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 5px 0; font-size: 13px; color: #222222; font-weight: 700;">Total amount</td>
                        <td align="right" style="padding: 5px 0; font-size: 13px; font-weight: 700;">{{ number_format($totalBill, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-size: 13px; color: #222222; font-weight: 700;">Discount</td>
                        <td align="right" style="padding: 5px 0; font-size: 13px; font-weight: 700;">
                            {{ number_format($disc, 2) }}
                            @if($disc > 0)
                                <span style="font-size: 11px; color: #444444; font-weight: 700;">({{ $discPercent }}%)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-size: 13px; color: #222222; font-weight: 700;">Extra Discount</td>
                        <td align="right" style="padding: 5px 0; font-size: 13px; font-weight: 700;">{{ number_format($extraDisc, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 6px; border-top: 2px solid #000000; font-size: 16px; font-weight: 900;">Payable</td>
                        <td align="right" style="padding-top: 6px; border-top: 2px solid #000000; font-size: 16px; font-weight: 900;">{{ number_format($net, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-size: 14px; font-weight: 900;">Received</td>
                        <td align="right" style="padding: 5px 0; font-size: 14px; font-weight: 900;">{{ number_format((float)(data_get($payment, 'PaidAmount') ?? data_get($payment, 'paid_amount', 0)), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-size: 16px; font-weight: 900;">Due</td>
                        <td align="right" style="padding: 5px 0; font-size: 16px; font-weight: 900;">{{ number_format((float)(data_get($payment, 'DueAmount') ?? data_get($payment, 'due_amount', 0)), 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
 
</div>
 
<div class="invoice-footer">
 
    <!-- Footer Note -->
    @if(!empty($deliveryNote))
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 6px;">
        <tr>
            <td align="center" style="border-top: 2px solid #000000; padding-top: 10px;">
                <div style="font-size: 19px; color: #000000; line-height: 1.5; font-weight: 900; letter-spacing: .2px;">
                    {{ $deliveryNote }}
                </div>
            </td>
        </tr>
    </table>
    @endif
 
    <!-- Signature Section -->
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="170px" valign="bottom">
                <table width="170px" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-top: 2px solid #000000; padding-top: 5px; font-size: 13px; font-weight: 800;">
                            {{ data_get($payment, 'CollectedBy') ?? data_get($payment, 'collected_by') }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size: 11px; color: #222222; padding-top: 2px; font-weight: 800;">(Signature)</td>
                    </tr>
                </table>
            </td>
            <td valign="bottom" align="right">
                @php
                    $copyLabel = match ($copyType ?? 'patient') {
                        'office'  => 'OFFICE COPY',
                        'reprint' => 'REPRINTED COPY',
                        default   => 'PATIENT COPY',
                    };
                @endphp
                <div style="font-size: 22px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">{{ $copyLabel }}</div>
                <div style="font-size: 11px; color: #222222; font-weight: 700;">
                    Printed on {{ data_get($payment, 'PrintTime') ?? data_get($payment, 'print_time') ?? now()->format('d/m/y h:i:sA') }}, * Discount not applicable
                </div>
            </td>
        </tr>
    </table>
 
</div>
</div>
 
</body>
</html>