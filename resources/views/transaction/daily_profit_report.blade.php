@extends('adminlte::page')

@section('title', 'Daily Profit Report')

@section('content')
@php
    $fmt = fn($n) => number_format((float) $n, 2);
    $displayDate = \Carbon\Carbon::parse($date)->format('d F Y');
@endphp

<style>
    /* ===== Core table style (same as monthly_profit) ===== */
    .cl3-pl { width: 94%; border-collapse: collapse; margin: 30px auto; }
    .cl3-pl td, .cl3-pl th { padding: 4px 8px; border: 1px solid #ccc; text-align: left; font-size: 17px; }
    .cl3-pl .center { text-align: center; }
    .cl3-pl .amount { text-align: right; }

    /* ===== Section header (blue) ===== */
    .cl3-pl .sec-head td {
        background: #3498db;
        color: white;
        font-weight: bold;
        font-size: 17px;
        text-align: center;
    }

    /* ===== Side-by-side wrapper ===== */
    .cl3-pl .side-cell {
        width: 50%;
        vertical-align: top;
        padding: 0;
        border: none;
    }
    .cl3-pl .side-cell table {
        width: 100%;
        border-collapse: collapse;
    }
    .cl3-pl .side-cell table td {
        padding: 3px 8px;
        font-size: 17px;
        border: 1px solid #ccc;
    }
    .cl3-pl .side-cell table td.amount { text-align: right; }
    .cl3-pl .side-cell table tr.total-row td {
        border-top: 2px solid #555;
        font-weight: bold;
    }

    /* ===== Net summary row ===== */
    .cl3-pl .net-row td {
        font-size: 19px;
        font-weight: bold;
        border-top: 3px double #000;
    }

    /* ===== Auditor signature section ===== */
    .signature-section {
        width: 94%;
        margin: 40px auto 20px;
        border-top: 2px solid #555;
        padding-top: 30px;
    }
    .signature-grid {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }
    .sig-box {
        flex: 1;
        text-align: center;
    }
    .sig-line {
        border-top: 1px solid #333;
        margin-top: 60px;
        padding-top: 6px;
        font-size: 14px;
        color: #444;
    }
    .sig-title {
        font-weight: bold;
        font-size: 15px;
        margin-bottom: 4px;
        color: #222;
    }

    /* ===== Print ===== */
    @media print {
        .no-print,
        .main-header, .main-sidebar, .main-footer,
        .content-header { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        body { font-size: 14px; }
        .signature-section { page-break-inside: avoid; }
    }
</style>

{{-- ============ Filter bar (not printed) ============ --}}
<div class="card card-outline card-primary no-print">
    <div class="card-body py-2">
        <form method="GET" action="{{ url()->current() }}" class="form-inline">
            <label for="date" class="mr-2 font-weight-bold">Date</label>
            <input type="date" id="date" name="date" value="{{ $date }}"
                   class="form-control form-control-sm mr-3">
            <button type="submit" class="btn btn-sm btn-primary mr-2">
                <i class="fas fa-search"></i> Show
            </button>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary">
                <i class="fas fa-print"></i> Print
            </button>
        </form>
    </div>
</div>

{{-- ============ Report Title ============ --}}
<table class="cl3-pl" style="margin-bottom:0;">
    <tr>
        <td class="center" colspan="2" style="font-size:20px; font-weight:bold; border-bottom:0;">
            {{ config('app.name') }}
        </td>
    </tr>
    <tr>
        <td class="center" colspan="2" style="font-size:17px; border-top:0;">
            Daily Profit &amp; Loss Report &mdash; {{ $displayDate }}
            &nbsp;&nbsp;|&nbsp;&nbsp; Print date: {{ \Carbon\Carbon::now()->format('d-M-Y') }}
        </td>
    </tr>
</table>

{{-- ============ Side-by-side Income vs Expenditure ============ --}}
<table class="cl3-pl" style="margin-top:0;">

    {{-- Section Headers --}}
    <tr class="sec-head">
        <td style="width:50%; text-align:center;">Income</td>
        <td style="width:50%; text-align:center;">Expenditure</td>
    </tr>

    {{-- Column sub-headers --}}
    <tr>
        <td style="padding:0; border-right: 2px solid #aaa;">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc;">#</td>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc;">Account</td>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc; text-align:right;">Amount</td>
                </tr>
            </table>
        </td>
        <td style="padding:0;">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc;">#</td>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc;">Account</td>
                    <td style="padding:3px 8px; font-size:15px; color:#555; border-bottom:1px solid #ccc; text-align:right;">Amount</td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Data rows --}}
    <tr>
        {{-- Income column --}}
        <td class="side-cell" style="border-right: 2px solid #aaa;">
            <table>
                @forelse ($income as $i => $r)
                    <tr>
                        <td style="width:32px;">{{ $i + 1 }}</td>
                        <td>{{ $r->label }}</td>
                        <td class="amount" style="white-space:nowrap;">{{ $fmt($r->amount) }}/-</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#999;">No income records.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2"><b>Total Income</b></td>
                    <td class="amount" style="white-space:nowrap;"><b>{{ $fmt($totalIncome) }}/-</b></td>
                </tr>
            </table>
        </td>

        {{-- Expenditure column --}}
        <td class="side-cell">
            <table>
                @forelse ($expense as $i => $r)
                    <tr>
                        <td style="width:32px;">{{ $i + 1 }}</td>
                        <td>{{ $r->label }}</td>
                        <td class="amount" style="white-space:nowrap;">{{ $fmt($r->amount) }}/-</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#999;">No expenditure records.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2"><b>Total Expenditure</b></td>
                    <td class="amount" style="white-space:nowrap;"><b>{{ $fmt($totalExpense) }}/-</b></td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Net Profit / Loss row --}}
    <tr class="net-row">
        <td style="text-align:right; border-right: 2px solid #aaa;">
            Total Income: <b>{{ $fmt($totalIncome) }}/-</b>
            &nbsp;&nbsp;(&minus;)&nbsp;&nbsp;
            Total Expenditure: <b>{{ $fmt($totalExpense) }}/-</b>
        </td>
        <td style="color: {{ $net >= 0 ? 'green' : 'red' }};">
            {{ $net >= 0 ? 'Net Profit' : 'Net Loss' }}:
            <b>{{ $fmt($net) }}/-</b>
        </td>
    </tr>
</table>

{{-- ============ Auditor Signature Section ============ --}}
<div class="signature-section">
    <div class="signature-grid">
        <div class="sig-box">
            <div class="sig-title">Prepared By</div>
            <div class="sig-line">Name &amp; Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Checked By</div>
            <div class="sig-line">Name &amp; Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Auditor</div>
            <div class="sig-line">Name &amp; Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Authorized By</div>
            <div class="sig-line">Name &amp; Signature</div>
        </div>
    </div>
</div>

@stop
