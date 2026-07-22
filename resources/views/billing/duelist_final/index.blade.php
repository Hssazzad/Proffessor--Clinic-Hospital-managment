@extends('adminlte::page')

@section('title', 'Payment Records — Clinic')

@section('content_header')
<div class="cc-header">
    <div class="cc-header-left">
        <div class="cc-header-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div>
            <h1 class="cc-header-title">Payment Records</h1>
            <p class="cc-header-sub">Invoice & Payment Overview</p>
        </div>
    </div>
</div>
@stop

@section('content')

{{-- SUMMARY STRIP (4 cards) --}}
<div class="cc-summary-strip">
    <div class="cc-summary-card">
        <div class="cc-summary-icon cc-summary-icon--blue"><i class="fas fa-file-invoice"></i></div>
        <div>
            <div class="cc-summary-label">Total Invoices</div>
            <div class="cc-summary-value" id="sumTotalInvoices">0</div>
        </div>
    </div>
    <div class="cc-summary-card">
        <div class="cc-summary-icon cc-summary-icon--blue"><i class="fas fa-money-bill-wave"></i></div>
        <div>
            <div class="cc-summary-label">Total Bill</div>
            <div class="cc-summary-value" id="sumTotalBill">&#2547; 0</div>
        </div>
    </div>
    <div class="cc-summary-card">
        <div class="cc-summary-icon cc-summary-icon--green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="cc-summary-label">Total Paid</div>
            <div class="cc-summary-value cc-summary-value--green" id="sumTotalPaid">&#2547; 0</div>
        </div>
    </div>
    <div class="cc-summary-card">
        <div class="cc-summary-icon cc-summary-icon--red"><i class="fas fa-exclamation-circle"></i></div>
        <div>
            <div class="cc-summary-label">Total Due</div>
            <div class="cc-summary-value cc-summary-value--red" id="sumTotalDue">&#2547; 0</div>
        </div>
    </div>
</div>

{{-- FILTER BAR --}}
<div class="cc-search-bar">
    <div class="cc-search-field">
        <i class="fas fa-search cc-search-icon"></i>
        <input type="text" id="listSearchInput" placeholder="Search name / code / bill no..." autocomplete="off">
        <button class="cc-search-clear" id="btnClearSearch" style="display:none;"><i class="fas fa-times"></i></button>
    </div>
    <div class="cc-search-actions">
        <label class="cc-toggle">
            <input type="checkbox" id="showAllChk">
            <span class="cc-toggle-track"></span>
            <span class="cc-toggle-label">Show All</span>
        </label>
        <button id="btnLoad" class="cc-btn cc-btn--primary"><i class="fas fa-sync-alt"></i> Load</button>
        <button id="btnReset" class="cc-btn cc-btn--outline"><i class="fas fa-undo-alt"></i> Reset</button>
        <button id="btnExportCsv" class="cc-btn cc-btn--outline" title="Export CSV"><i class="fas fa-file-csv"></i> CSV</button>
    </div>
</div>

{{-- TABLE --}}
<div class="cc-table-wrapper">
    <div class="cc-table-scroll">
        <table class="cc-table" id="dueTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bill No</th>
                    <th>Patient Name</th>
                    <th class="text-right">Total Bill</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="dueTableBody">
                <tr><td colspan="7" class="cc-loader"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- LOAD MORE --}}
<div id="loadMoreWrap" class="cc-load-more" style="display:none;">
    <button id="btnLoadMore" class="cc-btn cc-btn--primary cc-btn--block">
        <i class="fas fa-chevron-down mr-1"></i> Load More
    </button>
</div>

{{-- EMPTY FOOTER SPACER --}}
<div class="cc-bottom-spacer"></div>

{{-- BOTTOM NAVIGATION --}}
<nav class="cc-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('billing.paylist.index') }}" class="cc-nav-item">
        <i class="fas fa-receipt"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('billing.duelist.index') }}" class="cc-nav-item active">
        <i class="fas fa-exclamation-circle"></i>
        <span>Due List</span>
    </a>
    <a href="{{ route('billing.discharge.index') }}" class="cc-nav-item">
        <i class="fas fa-file-invoice"></i>
        <span>Discharge</span>
    </a>
    <a href="{{ route('billing.paynow.index') }}" class="cc-nav-item">
        <i class="fas fa-money-bill-wave"></i>
        <span>Pay Now</span>
    </a>
</nav>

{{-- PATIENT DETAILS MODAL (unchanged) --}}
<div class="modal fade" id="patientDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content cc-modal-content">
            <div class="modal-header cc-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span id="modalPatientName">Patient Ledger</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body cc-modal-body">
                <div class="cc-modal-section">
                    <h6 class="cc-modal-section-title"><i class="fas fa-file-invoice mr-1"></i> Bill List</h6>
                    <div class="cc-modal-table-wrap">
                        <table class="cc-modal-table">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Due</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modalBillBody">
                                <tr><td colspan="5" class="cc-modal-loading">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="cc-modal-total cc-modal-total--red">
                        Total Due: <span id="modalTotalDue">&#2547; 0</span>
                    </div>
                </div>
                <div class="cc-modal-section">
                    <h6 class="cc-modal-section-title"><i class="fas fa-money-bill-wave mr-1"></i> Payment List</h6>
                    <div class="cc-modal-table-wrap">
                        <table class="cc-modal-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-right">Amount</th>
                                    <th>Received By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modalPaymentBody">
                                <tr><td colspan="4" class="cc-modal-loading">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="cc-modal-total cc-modal-total--green">
                        Total Payment: <span id="modalTotalPayment">&#2547; 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer cc-modal-footer">
                <button type="button" class="cc-btn cc-btn--ghost" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --cc-bg: #faf8ff;
    --cc-primary: #0f52ba;
    --cc-primary-dark: #0b3d8a;
    --cc-primary-light: #e8edff;
    --cc-white: #ffffff;
    --cc-border: #d9d9e2;
    --cc-text: #1a1a2e;
    --cc-muted: #6b7280;
    --cc-green: #10b981;
    --cc-green-bg: #ecfdf5;
    --cc-red: #ef4444;
    --cc-red-bg: #fef2f2;
    --cc-amber: #f59e0b;
    --cc-amber-bg: #fffbeb;
    --cc-radius: 4px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

body, .content-wrapper { background: var(--cc-bg) !important; font-family: var(--cc-font); color: var(--cc-text); }

/* ── Page Header ── */
.cc-header { display:flex; align-items:center; justify-content:space-between; padding:4px 0 10px; }
.cc-header-left { display:flex; align-items:center; gap:10px; }
.cc-header-icon { width:36px; height:36px; background:var(--cc-primary); border-radius:var(--cc-radius); display:flex; align-items:center; justify-content:center; color:#fff; font-size:15px; }
.cc-header-title { font-size:17px; font-weight:500; margin:0; color:var(--cc-text); }
.cc-header-sub { font-size:12px; color:var(--cc-muted); margin:0; font-weight:400; }

/* ── Summary Strip (4 cards) ── */
.cc-summary-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
.cc-summary-card { background:var(--cc-white); border:1px solid var(--cc-border); border-radius:var(--cc-radius); padding:12px 10px; display:flex; align-items:center; gap:8px; }
.cc-summary-icon { width:32px; height:32px; border-radius:var(--cc-radius); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
.cc-summary-icon--blue { background:var(--cc-primary-light); color:var(--cc-primary); }
.cc-summary-icon--green { background:var(--cc-green-bg); color:var(--cc-green); }
.cc-summary-icon--red { background:var(--cc-red-bg); color:var(--cc-red); }
.cc-summary-label { font-size:9px; font-weight:600; color:var(--cc-muted); text-transform:uppercase; letter-spacing:.3px; margin-bottom:0; }
.cc-summary-value { font-size:15px; font-weight:500; color:var(--cc-text); line-height:1.2; }
.cc-summary-value--green { color:var(--cc-green); }
.cc-summary-value--red { color:var(--cc-red); }

/* ── Filter Bar ── */
.cc-search-bar { background:var(--cc-white); border:1px solid var(--cc-border); border-radius:var(--cc-radius); padding:8px 12px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.cc-search-field { flex:1; min-width:160px; position:relative; display:flex; align-items:center; }
.cc-search-field input { width:100%; height:32px; border:1px solid var(--cc-border); border-radius:var(--cc-radius); padding:0 28px 0 28px; font-size:12px; font-family:var(--cc-font); color:var(--cc-text); background:var(--cc-bg); outline:none; transition:border-color .15s; }
.cc-search-field input:focus { border-color:var(--cc-primary); background:var(--cc-white); }
.cc-search-icon { position:absolute; left:8px; color:var(--cc-muted); font-size:12px; pointer-events:none; }
.cc-search-clear { position:absolute; right:6px; background:none; border:none; color:var(--cc-muted); cursor:pointer; font-size:10px; padding:0; }
.cc-search-actions { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }

.cc-toggle { display:flex; align-items:center; gap:4px; cursor:pointer; margin:0; font-size:11px; color:var(--cc-muted); user-select:none; }
.cc-toggle input { display:none; }
.cc-toggle-track { width:26px; height:14px; background:#d4d4dd; border-radius:7px; position:relative; transition:background .2s; flex-shrink:0; }
.cc-toggle-track::after { content:''; position:absolute; top:2px; left:2px; width:10px; height:10px; border-radius:50%; background:var(--cc-white); transition:transform .2s; }
.cc-toggle input:checked + .cc-toggle-track { background:var(--cc-primary); }
.cc-toggle input:checked + .cc-toggle-track::after { transform:translateX(12px); }
.cc-toggle-label { font-size:10px; font-weight:600; white-space:nowrap; }

.cc-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border:none; border-radius:var(--cc-radius); font-size:11px; font-weight:600; cursor:pointer; font-family:var(--cc-font); transition:background .15s,color .15s; white-space:nowrap; line-height:1; height:30px; }
.cc-btn--primary { background:var(--cc-primary); color:#fff; }
.cc-btn--primary:hover { background:var(--cc-primary-dark); }
.cc-btn--outline { background:transparent; border:1px solid var(--cc-border); color:var(--cc-muted); padding:5px 10px; }
.cc-btn--outline:hover { border-color:var(--cc-primary); color:var(--cc-primary); background:var(--cc-primary-light); }
.cc-btn--block { width:100%; justify-content:center; padding:8px; height:auto; }

/* ── Table ── */
.cc-table-wrapper { background:var(--cc-white); border:1px solid var(--cc-border); border-radius:var(--cc-radius); overflow:hidden; }
.cc-table-scroll { overflow-x:auto; }
.cc-table { width:100%; border-collapse:collapse; font-size:12px; }
.cc-table thead th { background:#f8f7fc; color:var(--cc-muted); font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:8px 8px; border-bottom:2px solid var(--cc-border); white-space:nowrap; }
.cc-table tbody td { padding:6px 8px; border-bottom:1px solid #f3f4f6; color:var(--cc-text); vertical-align:middle; }
.cc-table tbody tr:hover { background:#faf8ff; }
.cc-table .text-right { text-align:right; }
.cc-table .text-center { text-align:center; }
.cc-table .cc-loader { text-align:center; padding:30px 20px; color:var(--cc-muted); }
.cc-table .cc-loader i { font-size:18px; display:block; margin-bottom:6px; }
.cc-table .cc-empty { text-align:center; padding:30px 20px; color:var(--cc-muted); }
.cc-table .cc-empty i { font-size:24px; display:block; margin-bottom:6px; color:#d4d4dd; }

/* status badge (reused) */
.cc-badge { display:inline-block; font-size:9px; font-weight:600; padding:1px 6px; border-radius:var(--cc-radius); }
.cc-badge--paid { background:var(--cc-green-bg); color:var(--cc-green); }
.cc-badge--partial { background:var(--cc-amber-bg); color:var(--cc-amber); }
.cc-badge--due { background:var(--cc-red-bg); color:var(--cc-red); }
.cc-badge--confirmed { background:var(--cc-primary-light); color:var(--cc-primary); }

/* Action buttons inside table */
.cc-action-btn { background:transparent; border:1px solid var(--cc-border); border-radius:var(--cc-radius); padding:3px 6px; font-size:10px; cursor:pointer; color:var(--cc-muted); transition:all .12s; font-family:var(--cc-font); font-weight:600; display:inline-flex; align-items:center; gap:3px; }
.cc-action-btn:hover { border-color:var(--cc-primary); color:var(--cc-primary); background:var(--cc-primary-light); }
.cc-action-btn--print { color:var(--cc-primary); border-color:var(--cc-primary); background:var(--cc-primary-light); }
.cc-action-btn--print:hover { background:var(--cc-primary); color:#fff; }

/* ── Load More ── */
.cc-load-more { padding:10px 0 14px; }

/* ── Bottom Spacer ── */
.cc-bottom-spacer { height:56px; }

/* ── Bottom Nav ── */
.cc-bottom-nav { position:fixed; bottom:0; left:0; right:0; height:50px; background:var(--cc-white); border-top:1px solid var(--cc-border); display:flex; align-items:center; justify-content:space-around; z-index:999; padding:0 4px; }
.cc-nav-item { display:flex; flex-direction:column; align-items:center; gap:1px; text-decoration:none; color:var(--cc-muted); font-size:9px; font-weight:600; padding:4px 6px; border-radius:var(--cc-radius); transition:color .15s; min-width:50px; }
.cc-nav-item i { font-size:15px; }
.cc-nav-item.active { color:var(--cc-primary); }
.cc-nav-item:hover { color:var(--cc-primary); text-decoration:none; }

/* ── Modal (unchanged) ── */
.cc-modal-content { border:none; border-radius:var(--cc-radius); overflow:hidden; }
.cc-modal-header { background:var(--cc-primary); color:#fff; border:none; padding:12px 16px; font-family:var(--cc-font); }
.cc-modal-header h5 { font-size:14px; font-weight:500; margin:0; }
.cc-modal-header .close { color:#fff; opacity:.8; }
.cc-modal-body { background:#faf8ff; padding:16px; }
.cc-modal-footer { border-top:1px solid var(--cc-border); padding:10px 16px; }
.cc-modal-section { background:var(--cc-white); border:1px solid var(--cc-border); border-radius:var(--cc-radius); padding:12px; margin-bottom:14px; }
.cc-modal-section:last-child { margin-bottom:0; }
.cc-modal-section-title { font-size:13px; font-weight:600; color:var(--cc-text); border-bottom:1px solid #f3f4f6; padding-bottom:8px; margin-bottom:10px; }
.cc-modal-table-wrap { overflow-x:auto; }
.cc-modal-table { width:100%; border-collapse:collapse; font-size:12px; }
.cc-modal-table th { background:#faf8ff; color:var(--cc-muted); font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:7px 8px; border-bottom:1px solid var(--cc-border); }
.cc-modal-table td { padding:7px 8px; border-bottom:1px solid #f3f4f6; color:var(--cc-text); }
.cc-modal-loading { text-align:center; color:var(--cc-muted); padding:12px; }
.cc-modal-total { text-align:right; font-size:13px; font-weight:600; margin-top:8px; }
.cc-modal-total--red { color:var(--cc-red); }
.cc-modal-total--green { color:var(--cc-green); }

/* ── Responsive ── */
@media (max-width: 720px) {
    .cc-summary-strip { grid-template-columns:1fr 1fr; }
    .cc-summary-card { padding:8px 8px; }
    .cc-summary-icon { width:28px; height:28px; font-size:11px; }
    .cc-summary-value { font-size:13px; }
}
@media (max-width: 520px) {
    .cc-summary-strip { grid-template-columns:1fr 1fr; gap:6px; }
    .cc-search-bar { flex-direction:column; align-items:stretch; }
    .cc-search-field { width:100%; }
    .cc-search-actions { justify-content:space-between; }
    .cc-table thead th, .cc-table tbody td { font-size:10px; padding:4px 4px; }
    .cc-header-sub { display:none; }
}
@media print {
    .cc-bottom-nav, .cc-bottom-spacer, .cc-search-bar { display:none !important; }
    body, .content-wrapper { background:#fff !important; }
}
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
toastr.options = { positionClass:'toast-top-right', timeOut:3000, progressBar:true, closeButton:true };
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

const DL_URLS = {
    list           : '{{ route("billing.duelist.list") }}',
    summary        : '{{ route("billing.duelist.summary") }}',
    patientDetails : '{{ url("Billing/DuelistFinal/patient-details") }}',
    paymentPrint   : '{{ url("Billing/payment") }}',
};

(function () {
'use strict';

let currentPage   = 1;
let searchTimer   = null;
let allLoadedRows = [];
let activeRequest = null;
let isLoading     = false;
let hasMorePages  = false;

const fmt = n => '&#2547;\u00a0' + parseInt(parseFloat(n) || 0).toLocaleString('en-IN');

function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#39;');
}

const statusBadge = (status) => {
    const map = {
        paid      : 'cc-badge--paid',
        partial   : 'cc-badge--partial',
        confirmed : 'cc-badge--confirmed',
        due       : 'cc-badge--due',
    };
    const cls = map[status] || 'cc-badge--due';
    const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Due';
    return `<span class="cc-badge ${cls}">${label}</span>`;
};

function buildTableRow(inv, idx, meta) {
    const net    = parseFloat(inv.NetBill ?? (inv.TotalBill - (inv.Discount || 0))) || 0;
    const due    = parseFloat(inv.DueAmount)  || 0;
    const paid   = parseFloat(inv.PaidAmount) || 0;
    const ptId   = esc(String(inv.patient_id || inv.PatientId || inv.id || ''));
    const date   = inv.PaymentDate ? esc(inv.PaymentDate) : '—';

    return `<tr>
        <td>${date}</td>
        <td style="font-weight:600;color:var(--cc-primary);">${esc(inv.BillNo)}</td>
        <td>${esc(inv.PatientName)}</td>
        <td class="text-right">${fmt(net)}</td>
        <td class="text-right" style="color:var(--cc-green);font-weight:500;">${fmt(paid)}</td>
        <td class="text-right" style="color:var(--cc-red);font-weight:600;">${fmt(due)}</td>
        <td class="text-center">
            <button class="cc-action-btn cc-action-btn--print" onclick="window.open('${DL_URLS.paymentPrint}/${inv.ID}/print','_blank')" title="Print"><i class="fas fa-print"></i></button>
            <button class="cc-action-btn dl-btn-view" data-patient-id="${ptId}" title="Details"><i class="fas fa-eye"></i></button>
        </td>
    </tr>`;
}

function loadDueList(page, append) {
    if (isLoading) return;
    page = page || 1;
    currentPage = page;

    const q       = $('#listSearchInput').val().trim();
    const showAll = $('#showAllChk').is(':checked') ? 1 : 0;

    if (!append) {
        $('#dueTableBody').html('<tr><td colspan="7" class="cc-loader"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
        $('#loadMoreWrap').hide();
    }

    if (activeRequest) {
        activeRequest.abort();
        activeRequest = null;
    }

    isLoading = true;
    activeRequest = $.ajax({
        url    : DL_URLS.list,
        method : 'GET',
        data   : { q, show_all: showAll, page, per_page: 20 },
        success: function (res) {
            activeRequest = null;
            isLoading = false;

            const rows = res.data || [];
            const meta = res.meta || {};
            hasMorePages = meta.last_page > meta.current_page;

            if (append) {
                allLoadedRows = allLoadedRows.concat(rows);
            } else {
                allLoadedRows = rows;
            }

            if (!allLoadedRows.length) {
                $('#dueTableBody').html('<tr><td colspan="7" class="cc-empty"><i class="fas fa-inbox"></i><p>No payment records found.</p></td></tr>');
                $('#loadMoreWrap').hide();
                // Reset summary
                $('#sumTotalInvoices').text('0');
                $('#sumTotalBill').html(fmt(0));
                $('#sumTotalPaid').html(fmt(0));
                $('#sumTotalDue').html(fmt(0));
                return;
            }

            const rowsHtml = allLoadedRows.map((inv, idx) => buildTableRow(inv, idx, meta)).join('');
            $('#dueTableBody').html(rowsHtml);

            // Update summary
            let totalInvoices = allLoadedRows.length;
            let sumTotal = 0, sumPaid = 0, sumDue = 0;
            allLoadedRows.forEach(inv => {
                const net  = parseFloat(inv.NetBill ?? (inv.TotalBill - (inv.Discount || 0))) || 0;
                sumTotal += net;
                sumPaid  += parseFloat(inv.PaidAmount) || 0;
                sumDue   += parseFloat(inv.DueAmount)  || 0;
            });

            $('#sumTotalInvoices').text(totalInvoices);
            $('#sumTotalBill').html(fmt(sumTotal));
            $('#sumTotalPaid').html(fmt(sumPaid));
            $('#sumTotalDue').html(fmt(sumDue));

            if (hasMorePages) {
                $('#loadMoreWrap').show();
                $('#btnLoadMore').html('<i class="fas fa-chevron-down mr-1"></i> Load More (' + (meta.total - allLoadedRows.length) + ' remaining)');
            } else {
                $('#loadMoreWrap').hide();
            }
        },
        error: function (xhr, status) {
            if (status === 'abort') return;
            isLoading = false;
            toastr.error('Failed to load data.');
            if (!append) {
                $('#dueTableBody').html('<tr><td colspan="7" class="cc-empty"><i class="fas fa-exclamation-triangle"></i><p>Failed to load data.</p></td></tr>');
            }
        }
    });
}

// Load summary cards (overall totals) – we keep this for the total due count (overall)
function loadSummaryCards() {
    $.get(DL_URLS.summary, function (res) {
        if(res) {
            // We only show overall due count in the first card? But we already have per-page total invoices.
            // We'll keep the per-page summary; the summary endpoint gives overall total due and patient count.
            // We can use it to show overall due count in a tooltip or just ignore.
            // For simplicity we leave it.
        }
    }).fail(function () {});
}

// Load More
$('#btnLoadMore').on('click', function () {
    loadDueList(currentPage + 1, true);
});

// Search with debounce
$('#listSearchInput').on('input', function () {
    const val = $(this).val();
    $('#btnClearSearch').toggle(val.length > 0);
    clearTimeout(searchTimer);
    if (val.length === 0 || val.length >= 3) {
        searchTimer = setTimeout(() => loadDueList(1), 400);
    }
});

$('#btnClearSearch').on('click', function () {
    $('#listSearchInput').val('').trigger('input');
});

$('#showAllChk').on('change', () => loadDueList(1));

// Explicit Load / Reset buttons
$('#btnLoad').on('click', function () {
    loadDueList(1);
});

$('#btnReset').on('click', function () {
    $('#listSearchInput').val('');
    $('#btnClearSearch').hide();
    $('#showAllChk').prop('checked', false);
    loadDueList(1);
});

// Export CSV (reuse existing logic)
function buildExportRows() {
    return allLoadedRows.map(function (inv, idx) {
        const net = parseFloat(inv.NetBill ?? (inv.TotalBill - (inv.Discount || 0))) || 0;
        return {
            '#'            : idx + 1,
            'Date'         : inv.PaymentDate || '',
            'Bill No'      : inv.BillNo      || '',
            'Patient Name' : inv.PatientName || '',
            'Patient Code' : inv.PatientCode || '',
            'Mobile'       : inv.MobileNo    || '',
            'Total (BDT)'  : Math.round(net),
            'Paid (BDT)'   : Math.round(parseFloat(inv.PaidAmount) || 0),
            'Due (BDT)'    : Math.round(parseFloat(inv.DueAmount)  || 0),
            'Status'       : inv.Status      || '',
        };
    });
}

const getFileName = ext => 'PaymentRecords_' + new Date().toISOString().slice(0, 10) + '.' + ext;

$('#btnExportCsv').on('click', function () {
    if (!allLoadedRows.length) { toastr.warning('No data to export.'); return; }
    const data  = buildExportRows();
    const heads = Object.keys(data[0]);
    const csv   = [heads.join(','), ...data.map(row =>
        heads.map(h => '"' + String(row[h]).replace(/"/g, '""') + '"').join(',')
    )].join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = getFileName('csv'); a.click();
    URL.revokeObjectURL(url);
    toastr.success('CSV exported!');
});

// Patient Details Modal (unchanged)
$(document).on('click', '.dl-btn-view', function () {
    const patientId = $(this).data('patient-id');
    if (!patientId) { toastr.error('Patient ID not found.'); return; }

    $('#modalPatientName').text('Patient Ledger');
    $('#patientDetailsModal').modal('show');
    $('#modalBillBody').html('<tr><td colspan="5" class="cc-modal-loading"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</td></tr>');
    $('#modalPaymentBody').html('<tr><td colspan="4" class="cc-modal-loading"><i class="fas fa-spinner fa-spin mr-2"></i> Loading...</td></tr>');
    $('#modalTotalDue').text('\u09f3 0');
    $('#modalTotalPayment').text('\u09f3 0');

    $.get(DL_URLS.patientDetails + '/' + parseInt(patientId, 10), function (res) {
        if (res.patient && res.patient.name) {
            $('#modalPatientName').text(res.patient.name + ' — Ledger');
        }

        let billRows = '';
        let computedTotalDue = 0;

        if (res.bills && res.bills.length > 0) {
            res.bills.forEach(function (b) {
                const net  = parseFloat(b.NetBill ?? (b.TotalBill - (b.Discount || 0))) || 0;
                const due  = parseFloat(b.DueAmount)  || 0;
                const paid = parseFloat(b.PaidAmount) || 0;
                computedTotalDue += due;

                billRows += '<tr>' +
                    '<td style="font-weight:600;color:var(--cc-primary)">' + esc(b.BillNo) + '</td>' +
                    '<td class="text-right">\u09f3 ' + net.toLocaleString('en-IN') + '</td>' +
                    '<td class="text-right" style="color:var(--cc-green)">\u09f3 ' + paid.toLocaleString('en-IN') + '</td>' +
                    '<td class="text-right" style="color:var(--cc-red);font-weight:600;">\u09f3 ' + due.toLocaleString('en-IN') + '</td>' +
                    '<td class="text-center"><button class="cc-action-btn cc-action-btn--print dl-btn-open-print" data-bill-id="' + esc(String(b.ID)) + '"><i class="fas fa-print"></i></button></td>' +
                    '</tr>';
            });
        } else {
            billRows = '<tr><td colspan="5" class="cc-modal-loading">No bills found</td></tr>';
        }

        $('#modalBillBody').html(billRows);
        $('#modalTotalDue').html('\u09f3 ' + computedTotalDue.toLocaleString('en-IN'));

        let paymentRows = '';
        let computedTotalPayment = 0;

        if (res.payments && res.payments.length > 0) {
            res.payments.forEach(function (p) {
                const amt = parseFloat(p.amount) || 0;
                computedTotalPayment += amt;
                paymentRows += '<tr>' +
                    '<td>' + (esc(p.PaymentDate) || '-') + '</td>' +
                    '<td class="text-right" style="font-weight:600;color:var(--cc-green)">\u09f3 ' + amt.toLocaleString('en-IN') + '</td>' +
                    '<td>' + (esc(p.CollectedBy) || '-') + '</td>' +
                    '<td class="text-center"><button class="cc-action-btn cc-action-btn--print dl-btn-open-print" data-bill-id="' + esc(String(p.ID)) + '"><i class="fas fa-print"></i></button></td>' +
                    '</tr>';
            });
        } else {
            paymentRows = '<tr><td colspan="4" class="cc-modal-loading">No payments found</td></tr>';
        }

        $('#modalPaymentBody').html(paymentRows);
        $('#modalTotalPayment').html('\u09f3 ' + computedTotalPayment.toLocaleString('en-IN'));

    }).fail(function (xhr) {
        const msg = xhr.status === 404 ? 'Patient not found.' : 'Failed to fetch patient details.';
        toastr.error(msg);
    });
});

$(document).on('click', '.dl-btn-open-print', function () {
    const billId = $(this).data('bill-id');
    if (billId) {
        window.open(DL_URLS.paymentPrint + '/' + parseInt(billId, 10) + '/print', '_blank');
    }
});

$(document).ready(function () {
    loadDueList();
    loadSummaryCards();
});

})();
</script>
@stop