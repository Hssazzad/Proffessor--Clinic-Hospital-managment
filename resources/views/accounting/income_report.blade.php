@extends('adminlte::page')

@section('title', 'Hospital Income Report')

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
    .card-purple       { background: linear-gradient(135deg,#8e24aa,#4a148c); }
    .card-teal         { background: linear-gradient(135deg,#00897b,#004d40); }
    .money             { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .ok                { color:#2e7d32; font-weight:700; }
    .bad               { color:#c62828; font-weight:700; }
    .tbl th            { background:#37474f; color:#fff; font-size:13px; white-space:nowrap; }
    .tbl td            { font-size:13px; vertical-align:middle; }
    tfoot td           { font-weight:700; background:#eceff1; }
    .sec-title         { font-size:15px; font-weight:600; border-left:4px solid #1a73e8; padding-left:8px; margin:22px 0 10px; }
    .drill-note        { font-size:12px; background:#fff8e1; border:1px solid #ffe082; border-radius:6px; padding:8px 12px; margin-bottom:10px; }
    @media print {
        .main-header,.main-sidebar,.content-header,.no-print{ display:none!important; }
        .content-wrapper{ margin-left:0!important; padding:0!important; }
        .card{ box-shadow:none!important; border:1px solid #ddd!important; }
    }
</style>
@endpush

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <h1 class="m-0" style="font-size:18px;font-weight:600;">
        <i class="fas fa-money-bill-wave text-primary mr-2"></i>
        Hospital Income Report
    </h1>
    <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Income Report</li>
    </ol>
</div>
@stop

@section('content')

{{-- Filter --}}
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('accounting.income.report') }}" class="row gx-2 gy-2 align-items-end">
            <div class="col-md-2">
                <label class="small mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" min="{{ $minDate }}" max="{{ $maxDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" min="{{ $minDate }}" max="{{ $maxDate }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="small mb-1">Income Category</label>
                <select name="category" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    @foreach($categories->pluck('category')->unique() as $c)
                        <option value="{{ $c }}" {{ $filterCategory === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1">Income Account</label>
                <select name="account" class="form-control form-control-sm">
                    <option value="">All Income A/C</option>
                    @foreach($incomeAccounts as $a)
                        <option value="{{ $a->AccountNo }}" {{ $filterAccount === $a->AccountNo ? 'selected' : '' }}>
                            {{ $a->AccountNo }} – {{ $a->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1">Bill No / Ref</label>
                <input type="text" name="bill" value="{{ $filterBill }}" placeholder="INV-00001" class="form-control form-control-sm">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter mr-1"></i> Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('accounting.income.report') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-undo mr-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- REVENUE SUMMARY --}}
<div class="sec-title">Revenue Summary</div>
<div class="row mb-1">
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-blue">
            <div class="lbl"><i class="fas fa-layer-group mr-1"></i> Gross Revenue</div>
            <div class="amt">৳ {{ number_format($grossRevenue, 2) }}</div>
            <div class="sub">Σ investigation_payments.TotalBill</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-red">
            <div class="lbl"><i class="fas fa-tags mr-1"></i> Discount</div>
            <div class="amt">৳ {{ number_format($totalDiscount, 2) }}</div>
            <div class="sub">Contra reduction (separate)</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-green">
            <div class="lbl"><i class="fas fa-balance-scale mr-1"></i> Net Billable Revenue</div>
            <div class="amt">৳ {{ number_format($netBillableRevenue, 2) }}</div>
            <div class="sub">Gross − Discount − Refund</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="sum-card card-orange">
            <div class="lbl"><i class="fas fa-undo mr-1"></i> Refund</div>
            <div class="amt">৳ {{ number_format($totalRefund, 2) }}</div>
            <div class="sub">Returned to patient</div>
        </div>
    </div>
</div>

{{-- COLLECTION SUMMARY --}}
<div class="sec-title">Collection Summary</div>
<div class="row mb-1">
    <div class="col-md-4 col-sm-6">
        <div class="sum-card card-teal">
            <div class="lbl"><i class="fas fa-cash-register mr-1"></i> Cash Collected</div>
            <div class="amt">৳ {{ number_format($cashCollected, 2) }}</div>
            <div class="sub">Σ PaidAmount</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="sum-card card-purple">
            <div class="lbl"><i class="fas fa-file-invoice-dollar mr-1"></i> Patient Accounts Receivable</div>
            <div class="amt">৳ {{ number_format($patientReceivable, 2) }}</div>
            <div class="sub">Σ DueAmount</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="sum-card card-blue">
            <div class="lbl"><i class="fas fa-check-circle mr-1"></i> Total Net Receivable / Collected</div>
            <div class="amt">৳ {{ number_format($totalNetReceivable, 2) }}</div>
            <div class="sub">Cash + Patient A/R</div>
        </div>
    </div>
</div>
<div class="small text-muted mb-2">
    Collection check: Cash (৳{{ number_format($cashCollected, 2) }}) + Patient A/R (৳{{ number_format($patientReceivable, 2) }})
    = ৳{{ number_format($totalNetReceivable, 2) }}
    = Net Billable Revenue (৳{{ number_format($netBillableRevenue, 2) }})
    @if(abs($totalNetReceivable - $netBillableRevenue) < 0.01)
        <span class="badge badge-success">BALANCED</span>
    @else
        <span class="badge badge-danger">OFF BY ৳{{ number_format(abs($totalNetReceivable - $netBillableRevenue), 2) }}</span>
    @endif
</div>

{{-- Income by Category --}}
<div class="sec-title">Income by Category <span class="small font-weight-normal text-muted">(bill line-item amount, not discount-allocated)</span></div>
<div class="card">
    <div class="card-body p-0">
        <table class="table tbl table-sm mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="money">Amount</th>
                    <th class="text-center">% of Gross</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    @php $pct = $grossRevenue > 0 ? ($cat->amount / $grossRevenue * 100) : 0; @endphp
                    <tr>
                        <td>{{ $cat->category }}</td>
                        <td class="money">৳ {{ number_format($cat->amount, 2) }}</td>
                        <td class="text-center">{{ number_format($pct, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No income recorded in this range.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Gross Revenue (by category)</td>
                    <td class="money">৳ {{ number_format($categoryTotal, 2) }}</td>
                    <td class="text-center">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Accounting Reconciliation --}}
<div class="sec-title">Accounting Reconciliation</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table tbl table-sm mb-0">
            <thead>
                <tr>
                    <th>Measure</th>
                    <th class="money">Billing Gross Revenue</th>
                    <th class="money">Accounting Income Credit</th>
                    <th class="money">Difference</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Recognized Revenue</td>
                    <td class="money">৳ {{ number_format($grossRevenue, 2) }}</td>
                    <td class="money">৳ {{ number_format($accountingIncome, 2) }}</td>
                    <td class="money {{ abs($diffIncome) < 0.01 ? 'ok' : 'bad' }}">{{ number_format($diffIncome, 2) }}</td>
                    <td class="text-center">
                        @if($reconStatus === 'MATCHED')
                            <span class="badge badge-success font-weight-bold">MATCHED</span>
                        @else
                            <span class="badge badge-danger font-weight-bold">MISMATCH</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="px-3 py-2 small text-muted">
            Accounting Revenue = credit to Income-type GL accounts (gl_type = 4), joined to billing via
            <code>RefNumber = BillNo</code>. Cash and Patient A/R are asset movements and are <b>not</b> counted as revenue.
            Discount is a separate contra account and is <b>not</b> subtracted from revenue a second time.
        </div>
    </div>
</div>

{{-- Detailed Transactions --}}
<div class="sec-title">Detailed Transactions (Invoice-wise)</div>
@if($isCategoryDrillDown)
    <div class="drill-note">
        <i class="fas fa-info-circle mr-1"></i>
        <b>Category Drill-down:</b> showing only invoices that contain
        <b>{{ $filterCategory }}</b>. Accounting reconciliation is performed at the
        <b>whole-invoice</b> level because the current GL does not store billing category —
        each invoice's full TotalBill is compared against its full GL income credit, never the
        single category's line amount.
        @if($filterCategory !== '')
            <br>The <i>Category</i> column shows the category line amount (for reference only); the reconciliation uses <i>Whole Bill Amount</i>.
        @endif
    </div>
@endif
<div class="card">
    <div class="card-body p-0">
        <table class="table tbl table-sm mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bill No</th>
                    <th>Category</th>
                    @if($isCategoryDrillDown)<th class="money">Category Amount</th>@endif
                    <th class="money">Whole Bill Amount</th>
                    <th class="money">Accounting Income</th>
                    <th class="money">Difference</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $d)
                    <tr>
                        <td>{{ $d['date'] }}</td>
                        <td>{{ $d['bill'] }}</td>
                        <td>{{ $d['category'] }}</td>
                        @if($isCategoryDrillDown)<td class="money">৳ {{ number_format($d['categoryBilling'], 2) }}</td>@endif
                        <td class="money">৳ {{ number_format($d['billing'], 2) }}</td>
                        <td class="money">৳ {{ number_format($d['acct'], 2) }}</td>
                        <td class="money {{ abs($d['diff']) < 0.01 ? 'ok' : 'bad' }}">{{ number_format($d['diff'], 2) }}</td>
                        <td class="text-center">
                            @if($d['status'] === 'MATCHED')
                                <span class="badge badge-success">MATCHED</span>
                            @else
                                <span class="badge badge-danger">MISMATCH</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isCategoryDrillDown ? 8 : 7 }}" class="text-center text-muted py-3">No transactions in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Action buttons --}}
<div class="no-print mb-4">
    <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm">
        <i class="fas fa-print mr-1"></i> Print
    </button>
    <a href="{{ route('accounting.income.report.pdf', request()->query()) }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> PDF
    </a>
</div>

<div class="text-center small text-muted no-print">
    Date Range: {{ $dateFrom }} → {{ $dateTo }}
    &nbsp;·&nbsp; {{ $totalInvoices }} invoices
    &nbsp;·&nbsp; Income A/C auto-detected via gl_type = 4
</div>
@stop
