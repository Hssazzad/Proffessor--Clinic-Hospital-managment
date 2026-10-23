<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 14mm 10mm 16mm 10mm; }
  body { font-family: notobengali, 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 8.5pt; line-height: 1.45; margin: 0; }
  * { box-sizing: border-box; }

  /* ---------- Letterhead ---------- */
  .accent-bar { height: 4px; background: {{ $accent ?? '#0f2a44' }}; }
  .letterhead { width: 100%; border-collapse: collapse; margin: 8px 0 0; }
  .letterhead td { vertical-align: middle; padding: 0; }
  .logo-cell { width: 62px; }
  .logo-cell img { width: 52px; height: auto; }
  .clinic-name { font-size: 14.5pt; font-weight: bold; color: #0f2a44; letter-spacing: .2px; line-height: 1.2; }
  .clinic-name-bn { font-size: 10pt; color: #334155; margin-top: 1px; }
  .clinic-contact { font-size: 7.5pt; color: #64748b; margin-top: 3px; }
  .report-badge { text-align: right; }
  .report-badge-title { display: inline-block; background: #0f2a44; color: #ffffff; font-size: 8pt; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; padding: 6px 12px; }
  .report-badge-sub { font-size: 7.5pt; color: #64748b; margin-top: 5px; }
  .rule { border: 0; border-top: 1px solid #e2e8f0; margin: 8px 0 0; }

  /* ---------- Title banner ---------- */
  .title-banner { width: 100%; border-collapse: collapse; margin-top: 10px; }
  .title-banner td { padding: 10px 14px; vertical-align: middle; }
  .title-main { background: #0f2a44; color: #ffffff; width: 62%; }
  .title-main h1 { font-size: 15pt; margin: 0; letter-spacing: .2px; }
  .title-main p { font-size: 7.5pt; margin: 3px 0 0; color: #cbd5e1; }
  .title-side { background: {{ $accentSoft ?? '#f1f5f9' }}; border: 1px solid #e2e8f0; border-left: 0; }
  .meta-row { font-size: 7.5pt; color: #334155; margin-bottom: 4px; }
  .meta-row:last-child { margin-bottom: 0; }
  .meta-label { color: #64748b; text-transform: uppercase; letter-spacing: .4px; font-size: 6.8pt; font-weight: bold; }
  .meta-value { font-weight: bold; color: #0f2a44; }

  /* ---------- KPI cards ---------- */
  .kpi { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 10px -6px 0; }
  .kpi td { width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; border-top: 3px solid {{ $accent ?? '#0f2a44' }}; padding: 8px 10px; vertical-align: top; }
  .kpi-label { font-size: 6.8pt; color: #64748b; text-transform: uppercase; letter-spacing: .5px; font-weight: bold; }
  .kpi-value { font-size: 12pt; font-weight: bold; color: #0f2a44; margin-top: 3px; }
  .kpi-sub { font-size: 7pt; color: #64748b; margin-top: 2px; }

  /* ---------- Table ---------- */
  .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
  .grid thead { display: table-header-group; }
  .grid th { background: #0f2a44; color: #ffffff; font-size: 7.3pt; text-transform: uppercase; letter-spacing: .4px; padding: 8px 6px; text-align: left; border: 1px solid #0f2a44; }
  .grid th.num, .grid td.num { text-align: right; }
  .grid th.ctr, .grid td.ctr { text-align: center; }
  .grid td { border: 1px solid #e2e8f0; padding: 6px; font-size: 8pt; vertical-align: top; }
  .grid tbody tr:nth-child(even) td { background: #f8fafc; }
  .sl { color: #64748b; text-align: center; width: 30px; }
  .date-cell { white-space: nowrap; }
  .amount { text-align: right; font-weight: bold; color: #0f2a44; white-space: nowrap; }
  .pill { display: inline-block; font-size: 6.8pt; font-weight: bold; letter-spacing: .4px; padding: 2px 8px; border: 1px solid; white-space: nowrap; }
  .pill-posted { background: #dcfce7; color: #166534; border-color: #86efac; }
  .pill-pending { background: #fef9c3; color: #854d0e; border-color: #fde047; }
  .grid tfoot td { background: #0f2a44; color: #ffffff; font-weight: bold; font-size: 8.5pt; padding: 8px 6px; border: 1px solid #0f2a44; }
  .grid tfoot .amount { color: #ffffff; }
  .empty-row td { text-align: center; color: #64748b; padding: 22px 6px; font-size: 8.5pt; }

  /* ---------- Signatures ---------- */
  .sign { width: 100%; border-collapse: collapse; margin-top: 26px; }
  .sign td { width: 33.33%; text-align: center; font-size: 7.5pt; color: #475569; vertical-align: bottom; padding: 0 14px; }
  .sign .line { border-top: 1px solid #0f2a44; padding-top: 5px; font-weight: bold; color: #0f2a44; margin-top: 34px; }
  .sign .role { color: #64748b; font-size: 7pt; }
  .note { margin-top: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid {{ $accent ?? '#0f2a44' }}; padding: 7px 10px; font-size: 7.3pt; color: #475569; }

  /* ---------- Footer ---------- */
  .page-footer { position: fixed; bottom: -11mm; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; font-size: 7pt; color: #64748b; }
  .page-footer table { width: 100%; border-collapse: collapse; }
  .page-footer td { padding: 0; vertical-align: middle; }
  .page-no { text-align: right; }
</style>
</head>
<body>
@php
  $rows = $records instanceof \Illuminate\Contracts\Pagination\Paginator
      ? collect($records->items())
      : collect($records);
  $totalAmount = (float) $rows->sum(fn ($r) => (float) ($r->amount ?? 0));
  $postedRows = $rows->where('status', 'Posted');
  $pendingRows = $rows->where('status', 'Pending');
  $postedTotal = (float) $postedRows->sum(fn ($r) => (float) ($r->amount ?? 0));
  $pendingTotal = (float) $pendingRows->sum(fn ($r) => (float) ($r->amount ?? 0));
  $period = ($fromDate ?? null ? date('d M Y', strtotime($fromDate)) : 'Beginning') . '  —  ' . ($toDate ?? null ? date('d M Y', strtotime($toDate)) : 'Today');
  $generatedAt = isset($generatedAt) ? \Carbon\Carbon::parse($generatedAt) : now();
  $generatedBy = $generatedBy ?? (auth()->user()->name ?? '');
  $logoPath = public_path('images/PC.png');
  $logoData = (extension_loaded('gd') && is_file($logoPath)) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
  $clinicName = config('clinic.name', config('app.name', 'Professor Clinic'));
  $clinicBn = config('clinic.name_bn', '');
  $clinicAddr = config('clinic.address', '');
  $clinicPhone = config('clinic.phone', '');
@endphp

<div class="accent-bar"></div>

<table class="letterhead">
  <tr>
    <td class="logo-cell">@if($logoData)<img src="{{ $logoData }}" alt="logo">@endif</td>
    <td>
      <div class="clinic-name">{{ strtoupper($clinicName) }}</div>
      @if($clinicBn)<div class="clinic-name-bn">{{ $clinicBn }}</div>@endif
      <div class="clinic-contact">
        @if($clinicAddr){{ $clinicAddr }}@endif
        @if($clinicAddr && $clinicPhone) &nbsp;|&nbsp; @endif
        @if($clinicPhone)Phone: {{ $clinicPhone }}@endif
      </div>
    </td>
    <td class="report-badge" style="width: 210px;">
      <div class="report-badge-title">Supplementary Report</div>
      <div class="report-badge-sub">Accounting &amp; Finance</div>
    </td>
  </tr>
</table>
<hr class="rule">

<table class="title-banner">
  <tr>
    <td class="title-main">
      <h1>{{ $title ?? 'Cash Report' }}</h1>
      <p>{{ $reportSubtitle ?? 'Posted and pending transactions for the selected period' }}</p>
    </td>
    <td class="title-side">
      <div class="meta-row"><span class="meta-label">Reporting period &nbsp;</span><span class="meta-value">{{ $period }}</span></div>
      <div class="meta-row"><span class="meta-label">Generated on &nbsp;</span><span class="meta-value">{{ $generatedAt->format('d M Y, h:i A') }}</span></div>
      <div class="meta-row"><span class="meta-label">Generated by &nbsp;</span><span class="meta-value">{{ $generatedBy ?: '—' }}</span></div>
      <div class="meta-row"><span class="meta-label">Records &nbsp;</span><span class="meta-value">{{ $rows->count() }} transaction(s)</span></div>
    </td>
  </tr>
</table>

<table class="kpi">
  <tr>
    <td>
      <div class="kpi-label">Total transactions</div>
      <div class="kpi-value">{{ $rows->count() }}</div>
      <div class="kpi-sub">{{ $postedRows->count() }} posted · {{ $pendingRows->count() }} pending</div>
    </td>
    <td>
      <div class="kpi-label">Total amount (BDT)</div>
      <div class="kpi-value">{{ number_format($totalAmount, 2) }}</div>
      <div class="kpi-sub">Grand total of this report</div>
    </td>
    <td>
      <div class="kpi-label">Posted (BDT)</div>
      <div class="kpi-value">{{ number_format($postedTotal, 2) }}</div>
      <div class="kpi-sub">{{ $postedRows->count() }} voucher(s) authorized</div>
    </td>
    <td>
      <div class="kpi-label">Pending (BDT)</div>
      <div class="kpi-value">{{ number_format($pendingTotal, 2) }}</div>
      <div class="kpi-sub">{{ $pendingRows->count() }} voucher(s) awaiting approval</div>
    </td>
  </tr>
</table>

<table class="grid">
  <thead>
    <tr>
      <th class="ctr" style="width:30px;">SL</th>
      <th style="width:70px;">Date</th>
      <th style="width:80px;">{{ $numberLabel ?? 'Voucher No.' }}</th>
      <th style="width:60px;" class="ctr">Ref No.</th>
      <th>{{ $accountLabel ?? 'Account' }}</th>
      <th style="width:28%;">Description</th>
      <th class="num" style="width:90px;">Amount (BDT)</th>
      <th class="ctr" style="width:70px;">Status</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $i => $record)
      <tr>
        <td class="sl">{{ $i + 1 }}</td>
        <td class="date-cell">{{ !empty($record->posting_date) ? date('d M Y', strtotime($record->posting_date)) : '—' }}</td>
        <td>{{ $record->receipt_number ?: '—' }}</td>
        <td class="ctr">{{ $record->ref_number }}</td>
        <td>{{ $record->account_title ?: '—' }}</td>
        <td>{{ $record->particular ?: '—' }}</td>
        <td class="amount">{{ number_format((float) $record->amount, 2) }}</td>
        <td class="ctr"><span class="pill {{ ($record->status ?? '') === 'Posted' ? 'pill-posted' : 'pill-pending' }}">{{ strtoupper($record->status ?? '') }}</span></td>
      </tr>
    @empty
      <tr class="empty-row"><td colspan="8">No record found for this report period.</td></tr>
    @endforelse
  </tbody>
  @if($rows->count())
  <tfoot>
    <tr>
      <td colspan="6" style="text-align:right;">GRAND TOTAL (BDT)</td>
      <td class="amount">{{ number_format($totalAmount, 2) }}</td>
      <td></td>
    </tr>
  </tfoot>
  @endif
</table>

<div class="note">
  <strong>Note:</strong> “Posted” rows are authorized vouchers from the general ledger; “Pending” rows are unauthenticated vouchers awaiting approval and are not yet part of the ledger balance. Amounts are shown in Bangladeshi Taka (BDT).
</div>

<table class="sign">
  <tr>
    <td><div class="line">Prepared by</div><div class="role">Signature &amp; date</div></td>
    <td><div class="line">Checked by</div><div class="role">Signature &amp; date</div></td>
    <td><div class="line">Approved by</div><div class="role">Signature &amp; date</div></td>
  </tr>
</table>

<div class="page-footer">
  <table>
    <tr>
      <td>{{ $clinicName }} · Accounting Supplementary Report · {{ $title ?? '' }}</td>
      <td class="page-no">Page <span class="pagenum"></span></td>
    </tr>
  </table>
</div>

<script type="text/php">
  if (isset($pdf)) {
    $font = $fontMetrics->get_font('notobengali', 'normal');
    if (!$font) { $font = $fontMetrics->get_font('DejaVu Sans', 'normal'); }
    $pdf->page_text($pdf->get_width() - 90, $pdf->get_height() - 38, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7, array(100, 116, 139));
  }
</script>
</body>
</html>
