<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Hospital Income Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
    h1 { font-size: 20px; text-align:center; margin:0 0 4px; }
    .meta { text-align:center; color:#555; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; margin-bottom:18px; }
    th { background:#37474f; color:#fff; padding:6px; text-align:left; }
    td { padding:5px 6px; border-bottom:1px solid #ddd; }
    .money { text-align:right; font-variant-numeric:tabular-nums; }
    tfoot td { font-weight:bold; background:#eceff1; }
    .ok { color:#2e7d32; font-weight:bold; }
    .bad { color:#c62828; font-weight:bold; }
    .sec { font-size:14px; font-weight:bold; border-left:4px solid #1a73e8; padding-left:8px; margin:18px 0 8px; }
    .note { font-size:10px; color:#666; margin-top:4px; }
</style>
</head>
<body>

<h1>HOSPITAL INCOME REPORT</h1>
<div class="meta">From Date: {{ $dateFrom }} &nbsp; To Date: {{ $dateTo }} &nbsp;·&nbsp; {{ $totalInvoices }} invoices</div>

<div class="sec">Revenue Summary</div>
<table>
    <tr><td>Gross Revenue</td><td class="money">৳ {{ number_format($grossRevenue, 2) }}</td></tr>
    <tr><td>Discount</td><td class="money">৳ {{ number_format($totalDiscount, 2) }}</td></tr>
    <tr><td>Refund</td><td class="money">৳ {{ number_format($totalRefund, 2) }}</td></tr>
    <tr><td><b>Net Billable Revenue</b></td><td class="money"><b>৳ {{ number_format($netBillableRevenue, 2) }}</b></td></tr>
</table>

<div class="sec">Collection Summary</div>
<table>
    <tr><td>Cash Collected</td><td class="money">৳ {{ number_format($cashCollected, 2) }}</td></tr>
    <tr><td>Patient Accounts Receivable</td><td class="money">৳ {{ number_format($patientReceivable, 2) }}</td></tr>
    <tr><td><b>Total Net Receivable / Collected</b></td><td class="money"><b>৳ {{ number_format($totalNetReceivable, 2) }}</b></td></tr>
</table>
<div class="note">Cash + Patient A/R (৳{{ number_format($totalNetReceivable, 2) }}) reconciles to Net Billable Revenue (৳{{ number_format($netBillableRevenue, 2) }}).</div>

<div class="sec">Income by Category</div>
<table>
    <thead><tr><th>Category</th><th class="money">Amount</th></tr></thead>
    <tbody>
        @forelse($categories as $cat)
            <tr><td>{{ $cat->category }}</td><td class="money">৳ {{ number_format($cat->amount, 2) }}</td></tr>
        @empty
            <tr><td colspan="2">No income recorded in this range.</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr><td>Total Gross Revenue (by category)</td><td class="money">৳ {{ number_format($categoryTotal, 2) }}</td></tr></tfoot>
</table>

<div class="sec">Accounting Reconciliation</div>
<table>
    <thead><tr><th>Measure</th><th class="money">Billing Gross</th><th class="money">Accounting Income</th><th class="money">Diff</th><th>Status</th></tr></thead>
    <tbody>
        <tr>
            <td>Recognized Revenue</td>
            <td class="money">৳ {{ number_format($grossRevenue, 2) }}</td>
            <td class="money">৳ {{ number_format($accountingIncome, 2) }}</td>
            <td class="money {{ abs($diffIncome) < 0.01 ? 'ok' : 'bad' }}">{{ number_format($diffIncome, 2) }}</td>
            <td>{{ $reconStatus }}</td>
        </tr>
    </tbody>
</table>
<div class="note">Accounting Revenue = credit to Income-type GL accounts (gl_type = 4). Cash and Patient A/R are assets and are not income. Discount is a separate contra account.</div>

<div class="sec">Detailed Transactions</div>
<table>
    <thead><tr><th>Date</th><th>Bill No</th><th>Category</th><th class="money">Whole Bill</th><th class="money">Accounting</th><th class="money">Diff</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($details as $d)
            <tr>
                <td>{{ $d['date'] }}</td><td>{{ $d['bill'] }}</td><td>{{ $d['category'] }}</td>
                <td class="money">৳ {{ number_format($d['billing'], 2) }}</td>
                <td class="money">৳ {{ number_format($d['acct'], 2) }}</td>
                <td class="money {{ abs($d['diff']) < 0.01 ? 'ok' : 'bad' }}">{{ number_format($d['diff'], 2) }}</td>
                <td>{{ $d['status'] }}</td>
            </tr>
        @empty
            <tr><td colspan="7">No transactions in this range.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="meta">Generated {{ now()->format('Y-m-d H:i') }} · Income A/C auto-detected via gl_type = 4</div>

</body>
</html>
