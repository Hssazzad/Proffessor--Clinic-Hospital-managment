@extends('adminlte::page')

@section('title', 'Investigation Accounting Report')

@push('css')
<style>
    .sum-card          { border-radius: 10px; padding: 16px 18px; color: #fff; margin-bottom: 12px; }
    .sum-card .lbl     { font-size: 12px; opacity: .82; margin-bottom: 3px; }
    .sum-card .amt     { font-size: 24px; font-weight: 700; }
    .sum-card .sub     { font-size: 11px; opacity: .72; margin-top: 3px; }
    .card-blue         { background: linear-gradient(135deg,#1a73e8,#0d47a1); }
    .card-red          { background: linear-gradient(135deg,#e53935,#b71c1c); }
    .card-orange       { background: linear-gradient(135deg,#fb8c00,#e65100); }
    .card-green        { background: linear-gradient(135deg,#2e7d32,#1b5e20); }
    .money             { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .ok                { color:#2e7d32; font-weight:700; }
    .bad               { color:#c62828; font-weight:700; }
    .tbl th            { background:#37474f; color:#fff; font-size:13px; white-space:nowrap; }
    .tbl td            { font-size:13px; vertical-align:middle; }
    tfoot td           { font-weight:700; background:#eceff1; }
    @media print {
        .main-header,.main-sidebar,.content-header,.no-print{ display:none!important; }
        .content-wrapper{ margin-left:0!important; padding:0!important; }
    }
</style>
@endpush

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <h1 class="m-0" style="font-size:18px;font-weight:600;">
        <i class="fas fa-balance-scale text-primary mr-2"></i>
        Investigation Accounting Report
    </h1>
    <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Investigation Report</li>
    </ol>
</div>
@stop

@section('content')

{{-- Filter --}}
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('accounting.investigation.reconciliation') }}"
              class="row gx-2 gy-2 align-items-end">
            <div class="col-md-3">
                <label class="small mb-1">From Date</label>
                <input type="date" name="date_from"
                       value="{{ $dateFrom ?? now()->startOfMonth()->toDateString() }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="small mb-1">To Date</label>
                <input type="date" name="date_to"
                       value="{{ $dateTo ?? now()->toDateString() }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('accounting.investigation.reconciliation') }}"
                   class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
            <div class="col-md-2">
                <button type="button" onclick="window.print()"
                        class="btn btn-outline-dark btn-sm w-100">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Summary Cards ══════════════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-blue">
            <div class="lbl"><i class="fas fa-flask mr-1"></i> Gross Income</div>
            <div class="amt">৳ {{ number_format($grossIncome, 2) }}</div>
            <div class="sub">{{ $totalInvoices }} invoices
                @if($dateFrom || $dateTo)
                    · {{ $dateFrom ?? '—' }} → {{ $dateTo ?? '—' }}
                @else · All time @endif
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-red">
            <div class="lbl"><i class="fas fa-tags mr-1"></i> Total Discount</div>
            <div class="amt">৳ {{ number_format($totalDiscount, 2) }}</div>
            <div class="sub">Deducted from gross</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-orange">
            <div class="lbl"><i class="fas fa-hourglass-half mr-1"></i> Total Due (Receivable)</div>
            <div class="amt">৳ {{ number_format($totalDue, 2) }}</div>
            <div class="sub">Not yet collected</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-green">
            <div class="lbl"><i class="fas fa-money-bill-wave mr-1"></i> Net Cash Received</div>
            <div class="amt">৳ {{ number_format($totalPaid, 2) }}</div>
            <div class="sub">Gross − Discount − Due</div>
        </div>
    </div>
</div>

{{-- ══ Reconciliation Table ════════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-balance-scale-left text-primary mr-2"></i>
            Billing ↔ Accounting (GL) Reconciliation
        </h3>
        <small class="text-muted">GL = all-time authorized transactions</small>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered tbl mb-0">
            <thead>
                <tr>
                    <th style="width:38%">Item</th>
                    <th class="money">Billing Side (৳)</th>
                    <th class="money">GL Accounting (৳)</th>
                    <th class="money">Difference (৳)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>

                {{-- Row 1: Gross Income --}}
                <tr>
                    <td>
                        <b>Investigation Income</b>
                        <div class="text-muted" style="font-size:11px;">
                            Billing: SUM(TotalBill) &nbsp;|&nbsp; GL A/C: 40601101
                        </div>
                    </td>
                    <td class="money">{{ number_format($grossIncome, 2) }}</td>
                    <td class="money">{{ number_format($glIncome, 2) }}</td>
                    <td class="money {{ abs($diffIncome) < 0.01 ? 'ok' : 'bad' }}">
                        {{ number_format($diffIncome, 2) }}
                    </td>
                    <td class="text-center">
                        @if(abs($diffIncome) < 0.01)
                            <span class="badge badge-success">✓ Match</span>
                        @else
                            <span class="badge badge-danger">✗ Gap</span>
                        @endif
                    </td>
                </tr>

                {{-- Row 2: Discount --}}
                <tr>
                    <td>
                        <b>Discount Given</b>
                        <div class="text-muted" style="font-size:11px;">
                            Billing: SUM(Discount) &nbsp;|&nbsp; GL A/C: 51501101
                        </div>
                    </td>
                    <td class="money">{{ number_format($totalDiscount, 2) }}</td>
                    <td class="money">{{ number_format($glDiscount, 2) }}</td>
                    <td class="money {{ abs($diffDiscount) < 0.01 ? 'ok' : 'bad' }}">
                        {{ number_format($diffDiscount, 2) }}
                    </td>
                    <td class="text-center">
                        @if(abs($diffDiscount) < 0.01)
                            <span class="badge badge-success">✓ Match</span>
                        @else
                            <span class="badge badge-danger">✗ Gap</span>
                        @endif
                    </td>
                </tr>

                {{-- Row 3: Due / Receivable --}}
                <tr>
                    <td>
                        <b>Due / Receivable</b>
                        <div class="text-muted" style="font-size:11px;">
                            Billing: SUM(DueAmount) &nbsp;|&nbsp; GL A/C: 20701101
                        </div>
                    </td>
                    <td class="money">{{ number_format($totalDue, 2) }}</td>
                    <td class="money">{{ number_format($glReceivable, 2) }}</td>
                    <td class="money {{ abs($diffReceiv) < 0.01 ? 'ok' : 'bad' }}">
                        {{ number_format($diffReceiv, 2) }}
                    </td>
                    <td class="text-center">
                        @if(abs($diffReceiv) < 0.01)
                            <span class="badge badge-success">✓ Match</span>
                        @else
                            <span class="badge badge-danger">✗ Gap</span>
                        @endif
                    </td>
                </tr>

                {{-- Row 4: Net Cash (main reconciliation line) --}}
                <tr style="background:#e8f5e9;">
                    <td>
                        <b>Net Cash from Investigations</b>
                        <div class="text-muted" style="font-size:11px;">
                            Billing: Gross − Discount − Due = PaidAmount
                            &nbsp;|&nbsp; GL: Income − Discount − Receivable
                        </div>
                    </td>
                    <td class="money"><b>{{ number_format($billingNetCash, 2) }}</b></td>
                    <td class="money"><b>{{ number_format($glNetCash, 2) }}</b></td>
                    <td class="money {{ abs($diffNetCash) < 0.01 ? 'ok' : 'bad' }}">
                        <b>{{ number_format($diffNetCash, 2) }}</b>
                    </td>
                    <td class="text-center">
                        @if(abs($diffNetCash) < 0.01)
                            <span class="badge badge-success font-weight-bold">✓ Balanced</span>
                        @else
                            <span class="badge badge-danger font-weight-bold">✗ Gap</span>
                        @endif
                    </td>
                </tr>

                {{-- Row 5: GL Cash balance (informational) --}}
                <tr style="background:#fff8e1;">
                    <td>
                        <b>GL Cash Account Balance</b>
                        <div class="text-muted" style="font-size:11px;">
                            GL A/C: 205101101 &nbsp;|&nbsp;
                            Includes investigation cash + opening balance &amp; other entries
                        </div>
                    </td>
                    <td class="money text-muted">—</td>
                    <td class="money"><b>{{ number_format($glCash, 2) }}</b></td>
                    <td class="money text-muted">
                        @if(abs($glCashNonInv) > 0.01)
                            Non-inv: {{ number_format($glCashNonInv, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">Info only</span>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- Gap alert --}}
    @php $anyGap = abs($diffIncome)>=0.01 || abs($diffDiscount)>=0.01 || abs($diffReceiv)>=0.01 || abs($diffNetCash)>=0.01; @endphp
    @if($anyGap)
    <div class="card-footer" style="background:#fff3e0;">
        <small class="text-danger">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <b>Gap detected.</b>
            কিছু invoice এখনো
            <a href="{{ route('transaction.authorizereview') }}">Transaction → Authorize</a>
            করা হয়নি। Authorize করলেই numbers মিলে যাবে।
        </small>
    </div>
    @else
    <div class="card-footer" style="background:#e8f5e9;">
        <small class="text-success">
            <i class="fas fa-check-circle mr-1"></i>
            <b>Fully reconciled.</b> Billing records match GL accounting — সব মিলেছে।
        </small>
    </div>
    @endif
</div>

{{-- ══ Net Profit / Loss Box ══════════════════════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header py-2">
        <h3 class="card-title mb-0">
            <i class="fas fa-chart-line text-success mr-2"></i>
            Income vs Expense — Net Profit / Loss
            <small class="text-muted ml-2">(GL accounting, all-time)</small>
        </h3>
    </div>
    <div class="card-body py-3">
        <div class="row text-center">
            <div class="col-md-4">
                <div style="background:#e3f2fd;border-radius:8px;padding:14px;">
                    <div class="text-muted small mb-1">Investigation Income</div>
                    <div style="font-size:22px;font-weight:700;color:#1565c0;">
                        ৳ {{ number_format($glIncome, 2) }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">GL A/C 40601101</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:#fce4ec;border-radius:8px;padding:14px;">
                    <div class="text-muted small mb-1">Total Expenditure (GL)</div>
                    <div style="font-size:22px;font-weight:700;color:#b71c1c;">
                        ৳ {{ number_format($totalGlExpenditure, 2) }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">Discount + সব খরচ</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:{{ $glNetProfit>=0?'#e8f5e9':'#fff3e0' }};border-radius:8px;padding:14px;">
                    <div class="text-muted small mb-1">Net (Income − Expenditure)</div>
                    <div style="font-size:22px;font-weight:700;color:{{ $glNetProfit>=0?'#2e7d32':'#e65100' }};">
                        ৳ {{ number_format($glNetProfit, 2) }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        {{ $glNetProfit>=0 ? '✓ Lab Profit' : '✗ Lab Loss' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Daily Breakdown ════════════════════════════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-calendar-alt text-primary mr-2"></i>
            Daily Breakdown
            <small class="text-muted">(billing side)</small>
        </h3>
        <small class="text-muted">{{ $dailyRows->count() }} day(s)</small>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover tbl mb-0">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th class="text-center">Invoice</th>
                    <th class="money">Gross Income (৳)</th>
                    <th class="money">Discount (৳)</th>
                    <th class="money">Due (৳)</th>
                    <th class="money">Cash Received (৳)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyRows as $r)
                <tr>
                    <td><b>{{ \Carbon\Carbon::parse($r->pay_date)->format('d M Y, D') }}</b></td>
                    <td class="text-center">{{ $r->invoices }}</td>
                    <td class="money">{{ number_format($r->gross, 2) }}</td>
                    <td class="money text-danger">
                        {{ $r->discount > 0 ? '− '.number_format($r->discount, 2) : '—' }}
                    </td>
                    <td class="money text-warning">
                        {{ $r->due > 0 ? '− '.number_format($r->due, 2) : '—' }}
                    </td>
                    <td class="money text-success font-weight-bold">
                        {{ number_format($r->paid, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        এই সময়ের মধ্যে কোনো payment পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($dailyRows->count() > 0)
            <tfoot>
                <tr>
                    <td><b>মোট</b></td>
                    <td class="text-center"><b>{{ $totalInvoices }}</b></td>
                    <td class="money"><b>{{ number_format($grossIncome, 2) }}</b></td>
                    <td class="money text-danger"><b>− {{ number_format($totalDiscount, 2) }}</b></td>
                    <td class="money text-warning"><b>− {{ number_format($totalDue, 2) }}</b></td>
                    <td class="money text-success"><b>{{ number_format($totalPaid, 2) }}</b></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@stop
