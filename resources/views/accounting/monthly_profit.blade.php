@extends('adminlte::page')

@section('title', 'Monthly Profit')

@section('content')
@php
    [$yy, $mm] = explode('-', $month);
    $monthNames = [
        '01' => 'January', '02' => 'February', '03' => 'March',
        '04' => 'April',   '05' => 'May',       '06' => 'June',
        '07' => 'July',    '08' => 'August',     '09' => 'September',
        '10' => 'October', '11' => 'November',   '12' => 'December',
    ];
    $fmt = fn($n) => number_format((float) $n, 2);
    $maxRows = max(count($income), count($expense));
@endphp

<style>
    /* ===== Core table style (same as income_statement) ===== */
    .cl3-pl { width: 94%; border-collapse: collapse; margin: 30px auto; }
    .cl3-pl td, .cl3-pl th { padding: 4px 8px; border: 1px solid #ccc; text-align: left; font-size: 17px; }
    .cl3-pl .center { text-align: center; }
    .cl3-pl .amount { text-align: right; }

    /* ===== Section header (blue, like income_statement th) ===== */
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

    /* ===== Month Picker ===== */
    .mp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
    .mp-month-btn {
        border: 1px solid #dee2e6; background: #fff; border-radius: 4px;
        padding: 6px 4px; font-size: 13px; cursor: pointer;
        transition: background .15s, color .15s; width: 100%;
    }
    .mp-month-btn:hover  { background: #e9f0ff; border-color: #4d8bff; color: #0056b3; }
    .mp-month-btn.mp-active { background: #007bff; color: #fff; border-color: #007bff; font-weight: 600; }

    /* ===== Print ===== */
    @media print {
        .no-print,
        .main-header, .main-sidebar, .main-footer,
        .content-header { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        body { font-size: 14px; }
    }
</style>

{{-- ============ Filter bar (not printed) ============ --}}
<div class="card card-outline card-primary no-print">
    <div class="card-body py-2">
        <form method="GET" action="{{ url()->current() }}" class="form-inline">
            <label for="month" class="mr-2 font-weight-bold">Month</label>
            <input type="hidden" id="month" name="month" value="{{ $month }}">
            <div class="dropdown mr-3" id="monthPickerWrap">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" id="monthPickerBtn"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:140px">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <span id="monthPickerLabel">{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</span>
                </button>
                <div class="dropdown-menu p-2" id="monthPickerMenu" style="min-width:260px" aria-labelledby="monthPickerBtn">
                    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                        <button type="button" class="btn btn-sm btn-light" id="mpPrevYear"><i class="fas fa-chevron-left"></i></button>
                        <strong id="mpYear">{{ $yy }}</strong>
                        <button type="button" class="btn btn-sm btn-light" id="mpNextYear"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="mp-grid">
                        @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $mi => $mn)
                            <button type="button" class="mp-month-btn {{ ((int)$mm === $mi+1) ? 'mp-active' : '' }}"
                                    data-m="{{ str_pad($mi+1, 2, '0', STR_PAD_LEFT) }}">{{ $mn }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <label for="admitted" class="mr-2">Admitted Patients</label>
            <input type="number" min="0" id="admitted" name="admitted" value="{{ $admitted }}"
                   class="form-control form-control-sm mr-3" style="width:90px">

            <label for="operations" class="mr-2">Operations</label>
            <input type="number" min="0" id="operations" name="operations" value="{{ $operations }}"
                   class="form-control form-control-sm mr-3" style="width:90px">

            <button type="submit" class="btn btn-sm btn-primary mr-2"><i class="fas fa-search"></i> Show</button>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Print</button>
        </form>
    </div>
</div>

{{-- ============ Report ============ --}}

{{-- Report Title --}}
<table class="cl3-pl" style="margin-bottom:0;">
    <tr>
        <td class="center" colspan="2" style="font-size:20px; font-weight:bold; border-bottom:0;">
            {{ config('app.name') }}
        </td>
    </tr>
    <tr>
        <td class="center" colspan="2" style="font-size:17px; border-top:0;">
            Monthly Profit &amp; Loss Report &mdash; {{ $monthNames[$mm] ?? $mm }} {{ $yy }}
            &nbsp;&nbsp;|&nbsp;&nbsp; Print date: {{ \Carbon\Carbon::now()->format('d-M-Y') }}
            @if (($admitted !== null && $admitted !== '') || ($operations !== null && $operations !== ''))
                &nbsp;&nbsp;|&nbsp;&nbsp; Admitted: {{ $admitted }} &nbsp; Operations: {{ $operations }}
            @endif
        </td>
    </tr>
</table>

{{-- Side-by-side Income vs Expenditure --}}
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
                    <tr><td colspan="3" style="text-align:center; color:#999;">No records.</td></tr>
                @endforelse
                {{-- Spacer rows to equalise height if needed --}}
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
                    <tr><td colspan="3" style="text-align:center; color:#999;">No records.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2"><b>Total Expenditure</b></td>
                    <td class="amount" style="white-space:nowrap;"><b>{{ $fmt($totalExpense) }}/-</b></td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Net Profit / Loss row spanning both columns --}}
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

@stop

@section('js')
<script>
(function () {
    const monthNames = ['January','February','March','April','May','June',
                        'July','August','September','October','November','December'];
    const hiddenInput = document.getElementById('month');
    const yearLabel   = document.getElementById('mpYear');
    const pickerLabel = document.getElementById('monthPickerLabel');
    const menu        = document.getElementById('monthPickerMenu');

    let currentYear  = parseInt(yearLabel.textContent);

    menu.addEventListener('click', function (e) { e.stopPropagation(); });

    document.getElementById('mpPrevYear').addEventListener('click', function () {
        currentYear--;
        yearLabel.textContent = currentYear;
    });
    document.getElementById('mpNextYear').addEventListener('click', function () {
        currentYear++;
        yearLabel.textContent = currentYear;
    });

    document.querySelectorAll('.mp-month-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const m = parseInt(btn.dataset.m);
            document.querySelectorAll('.mp-month-btn').forEach(b => b.classList.remove('mp-active'));
            btn.classList.add('mp-active');
            const mm = String(m).padStart(2, '0');
            hiddenInput.value = String(currentYear) + '-' + mm;
            pickerLabel.textContent = monthNames[m - 1] + ' ' + String(currentYear);
            $('#monthPickerBtn').dropdown('hide');
        });
    });
})();
</script>
@stop