@extends('adminlte::page')

@section('title', 'Payment List')

@section('content_header')
<div class="d-flex align-items-center justify-content-between mb-2">
    <div>
        <h1 class="m-0" style="font-size:18px;font-weight:700;">Payment Records</h1>
        <small class="text-muted">Billing &mdash; payment history</small>
    </div>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>
@stop

@section('content')

{{-- ALERT --}}
<div id="save-alert" class="alert d-none" role="alert"></div>

{{-- =============================================
     SUMMARY — 5 white boxes
============================================= --}}
<div class="row mb-3">
    <div class="col-lg col-sm-6 mb-2">
        <div class="wbox">
            <div class="wbox-label">Total Invoices</div>
            <div class="wbox-val" id="sumInvoices">—</div>
            <div class="wbox-sub" id="sumPatients">—</div>
        </div>
    </div>
    <div class="col-lg col-sm-6 mb-2">
        <div class="wbox">
            <div class="wbox-label">Total Bill</div>
            <div class="wbox-val" id="sumBill">—</div>
        </div>
    </div>
    <div class="col-lg col-sm-6 mb-2">
        <div class="wbox">
            <div class="wbox-label">Total Discount</div>
            <div class="wbox-val wbox-discount" id="sumDiscount">—</div>
        </div>
    </div>
    <div class="col-lg col-sm-6 mb-2">
        <div class="wbox">
            <div class="wbox-label">Total Paid</div>
            <div class="wbox-val wbox-green" id="sumPaid">—</div>
        </div>
    </div>
    <div class="col-lg col-sm-6 mb-2">
        <div class="wbox">
            <div class="wbox-label">Total Due</div>
            <div class="wbox-val wbox-red" id="sumDue">—</div>
        </div>
    </div>
</div>

{{-- =============================================
     PATIENT SEARCH — just one big dropdown
============================================= --}}
<div class="mb-3">
    <select id="patientSelect" style="width:100%"></select>
</div>

<div class="d-flex align-items-end mb-3 flex-wrap" style="gap:8px;">
    <div>
        <label for="paymentDate" class="small text-muted mb-1 d-block">Payment Date</label>
        <input type="date" id="paymentDate" class="form-control form-control-sm" onchange="loadData(1)">
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showAllDates()">All Dates</button>
    <div class="ml-2">
        <label for="paymentSource" class="small text-muted mb-1 d-block">Payment Source</label>
        <select id="paymentSource" class="form-control form-control-sm" style="width:auto;min-width:130px;" onchange="loadData(1)">
            <option value="all">All Sources</option>
            <option value="reception">Reception</option>
            <option value="manager">Manager</option>
            <option value="legacy">Legacy</option>
        </select>
    </div>
</div>

{{-- =============================================
     TABLE — white card
============================================= --}}
<div class="card">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <span style="font-size:13px;font-weight:700;"><i class="fas fa-table mr-1"></i> Payment Records</span>
        <span class="fbadge" id="tableInfo">—</span>
    </div>
    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAll()">
                <i class="fas fa-check-square"></i> All
            </button>
            <small class="text-muted">Selected: <strong id="selectedCount">0</strong></small>
            <select id="perPage" class="form-control form-control-sm" style="width:auto;height:28px;font-size:12px;" onchange="loadData(1)">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="btnBulkPrint" style="display:none;" onclick="doBulkPrint()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="exportCSV()">
                <i class="fas fa-file-csv mr-1"></i> CSV
            </button>
        </div>
    </div>
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:30px;" class="text-center"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"></th>
                    <th>Date</th>
                    <th>Bill No</th>
                    <th>Patient</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Cash</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Due</th>
                    <th class="text-center" style="width:70px;">Status</th>
                    <th class="text-center" style="width:50px;">Print</th>
                    <th class="text-center" style="width:50px;">Del</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="11" class="text-center py-3"><i class="fas fa-circle-notch fa-spin mr-1"></i>Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="loadMoreWrap" class="px-3 py-2 d-none">
        <button id="loadMoreBtn" class="btn btn-block btn-outline-secondary btn-sm" onclick="loadMore()">
            <i class="fas fa-chevron-down mr-1"></i> Load More
        </button>
    </div>
</div>

<div style="height:54px;"></div>

{{-- BOTTOM NAV --}}
<nav class="bnav">
    <a href="{{ route('admin.dashboard') }}" class="bnav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="{{ route('billing.paylist.index') }}" class="bnav-item bnav-active"><i class="fas fa-receipt"></i><span>Payments</span></a>
    <a href="{{ route('billing.duelist.index') }}" class="bnav-item"><i class="fas fa-exclamation-triangle"></i><span>Due</span></a>
    <a href="{{ route('billing.discharge.index') }}" class="bnav-item"><i class="fas fa-sign-out-alt"></i><span>Discharge</span></a>
    <a href="{{ route('billing.paynow.index') }}" class="bnav-item"><i class="fas fa-credit-card"></i><span>Pay</span></a>
</nav>

<iframe id="printFrame" style="display:none;"></iframe>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
/* =============================================
   SIMPLE WHITE — bare minimum
============================================= */

.wbox {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 12px 14px;
}
.wbox-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6c757d;
    margin-bottom: 4px;
}
.wbox-val {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}
.wbox-green { color: #28a745; }
.wbox-red   { color: #dc3545; }
.wbox-discount { color: #e67e22; }
.wbox-sub {
    font-size: 11px;
    color: #6c757d;
    margin-top: 3px;
}

.fbadge {
    background: #e9ecef;
    color: #495057;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
}

.st-paid    { color: #28a745; font-weight: 700; font-size: 11px; }
.st-partial { color: #ffc107; font-weight: 700; font-size: 11px; }
.st-due     { color: #dc3545; font-weight: 700; font-size: 11px; }

.col-green { color: #28a745; font-weight: 700; }
.col-red   { color: #dc3545; font-weight: 700; }

.pbtn {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 3px 7px;
    font-size: 11px;
    cursor: pointer;
    color: #6c757d;
}
.pbtn:hover { border-color: #343a40; color: #343a40; }
.pbtn-danger { color: #dc3545; border-color: #f3c2c6; }
.pbtn-danger:hover { background: #dc3545; border-color: #dc3545; color: #fff; }

.bnav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50px;
    background: #fff;
    border-top: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 1000;
}
.bnav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 4px 8px;
    text-decoration: none;
    color: #6c757d;
    font-size: 10px;
    font-weight: 600;
    min-width: 44px;
}
.bnav-item i { font-size: 16px; }
.bnav-item:hover { color: #343a40; text-decoration: none; }
.bnav-active { color: #343a40; }

/* Select2 — boro dropdown */
.select2-container--bootstrap4 .select2-selection--single {
    height: 40px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 4px !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
    font-size: 14px !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
    font-size: 14px !important;
    color: #6c757d !important;
    line-height: 38px !important;
}
.select2-container--bootstrap4 .select2-container--focus .select2-selection {
    border-color: #343a40 !important;
    box-shadow: none !important;
}
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background: #343a40 !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
    font-size: 18px !important;
    margin-right: 8px !important;
}

/* Boro dropdown result list */
.select2-container--bootstrap4 .select2-results__option {
    font-size: 14px !important;
    padding: 8px 12px !important;
}

input[type="checkbox"] { accent-color: #343a40; }
.table-hover tbody tr.row-selected { background-color: #e9ecef !important; }

/* Toast */
.toast-box { position:fixed; top:12px; right:12px; z-index:9999; }
.toast-item {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px 14px;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    animation: tSlide .2s;
}
.toast-item.t-success { border-left: 3px solid #28a745; }
.toast-item.t-error   { border-left: 3px solid #dc3545; }
.toast-item.t-warning { border-left: 3px solid #ffc107; }
@keyframes tSlide { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

@media print {
    .bnav, .card-header, #loadMoreWrap { display:none !important; }
}
</style>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/* =============================================
   PAYMENT LIST — ONE dropdown only
============================================= */
var ROUTES = {
    list:    '{{ route("billing.paylist.list") }}',
    summary: '{{ route("billing.paylist.summary") }}',
    search:  '{{ route("billing.paylist.search") }}',
    print:   '{{ route("billing.invoice.print", ":id") }}',
    delete:  '{{ route("billing.paylist.destroy") }}',
};
var CSRF_TOKEN = '{{ csrf_token() }}';

var currentPage = 1, lastPage = 1, isLoading = false;
var selectedIds = new Set(), allFlatRows = [], allSelected = false;

/* Toast */
if (!document.getElementById('toastBox')) {
    var t = document.createElement('div');
    t.className = 'toast-box'; t.id = 'toastBox';
    document.body.appendChild(t);
}

/* =============================================
   PATIENT DROPDOWN — boro Select2
============================================= */
  $('#patientSelect').select2({
    theme: 'bootstrap4',
    width: '100%',
    placeholder: 'Search bill no / name / code / mobile...',
    allowClear: true,
    ajax: {
        url: ROUTES.search,
        dataType: 'json',
        delay: 300,
        data: function (params) {
            return {
                q: params.term,
                payment_source: document.getElementById('paymentSource').value,
            };
        },
        processResults: function (data) {
            return { results: data || [] };
        },
        cache: true,
    },
    minimumInputLength: 1,
});

/* Select korle auto load — clear korle abar all load */
 $('#patientSelect').on('change', function () {
    loadData(1);
});

/* =============================================
   GET PARAMS
 ============================================= */
function getParams(page) {
    var selected = ($('#patientSelect').select2('data') || [])[0] || {};
    var paymentDate = document.getElementById('paymentDate').value;
    var paymentSource = document.getElementById('paymentSource').value;
    var params = new URLSearchParams({
        page:           page,
        per_page:       document.getElementById('perPage').value,
        date_mode:      paymentDate ? 'single' : 'all',
        date_column:    'PaymentDate',
        payment_source: paymentSource,
    });
    if (paymentDate) { params.append('single_date', paymentDate); }
    if (selected.type === 'patient' && selected.patient_id) {
        params.append('patient_id', selected.patient_id);
    } else if (selected.type === 'invoice' && selected.bill_no) {
        params.append('q', selected.bill_no);
    }
    return params;
}

function showAllDates() {
    document.getElementById('paymentDate').value = '';
    loadData(1);
}

/* =============================================
   LOAD DATA — AJAX
============================================= */
function loadData(page, append) {
    if (isLoading) return;
    isLoading = true;
    currentPage = page;

    if (!append) {
        selectedIds.clear();
        allSelected = false;
        updateSelectUI();
        document.getElementById('selectAllCheckbox').checked = false;
        document.getElementById('tableBody').innerHTML =
            '<tr><td colspan="11" class="text-center py-3"><i class="fas fa-circle-notch fa-spin mr-1"></i>Loading...</td></tr>';
        document.getElementById('loadMoreWrap').classList.add('d-none');
    } else {
        document.getElementById('loadMoreBtn').innerHTML = 'Loading...';
        document.getElementById('loadMoreBtn').disabled = true;
    }

    var params = getParams(page);

    if (!append) {
        fetch(ROUTES.summary + '?' + params.toString())
            .then(function (r) { return r.json(); })
            .then(renderSummary)
            .catch(function () {});
    }

    fetch(ROUTES.list + '?' + params.toString())
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (append) { allFlatRows = allFlatRows.concat(res.flat || []); }
            else { allFlatRows = res.flat || []; }
            renderTable(res.flat || [], res.meta, append);
            isLoading = false;
        })
        .catch(function () {
            if (!append) {
                document.getElementById('tableBody').innerHTML =
                    '<tr><td colspan="11" class="text-center py-3 text-danger">Error loading data.</td></tr>';
            }
            isLoading = false;
        });
}

/* =============================================
   RENDER SUMMARY
============================================= */
function renderSummary(s) {
    document.getElementById('sumInvoices').textContent = s.total_invoices.toLocaleString();
    document.getElementById('sumPatients').textContent = s.total_patients + ' patient(s)';
    document.getElementById('sumBill').textContent     = '৳ ' + fmt(s.total_bill);
    document.getElementById('sumDiscount').textContent = '৳ ' + fmt(s.total_discount);
    document.getElementById('sumPaid').textContent     = '৳ ' + fmt(s.total_paid);
    document.getElementById('sumDue').textContent      = '৳ ' + fmt(s.total_due);
}

/* =============================================
   STATUS
============================================= */
function getStatus(r) {
    var total = parseFloat(r.InvoiceTotalBill || 0);
    var paid  = parseFloat(r.InvoicePaidAmount || 0);
    var due   = parseFloat(r.DueAmount || 0);
    if (total > 0 && due === 0) return { label: 'Paid',    cls: 'st-paid' };
    if (due > 0 && paid > 0)    return { label: 'Partial', cls: 'st-partial' };
    if (due > 0)                return { label: 'Due',     cls: 'st-due' };
    return { label: 'Pending', cls: 'st-partial' };
}

function renderSourceBadge(source) {
    var s = (source || '').toLowerCase().trim();
    if (s === 'manager') {
        return '<span class="badge badge-primary ml-1" style="font-size:10px;font-weight:600;padding:2px 5px;">Manager</span>';
    }
    if (s === 'reception') {
        return '<span class="badge badge-info ml-1" style="font-size:10px;font-weight:600;padding:2px 5px;">Reception</span>';
    }
    return '<span class="badge badge-secondary ml-1" style="font-size:10px;font-weight:500;padding:2px 5px;">Legacy</span>';
}

/* =============================================
   RENDER TABLE
============================================= */
function renderTable(rows, meta, append) {
    var tbody = document.getElementById('tableBody');

    if (!rows.length && !append) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center py-3 text-muted">No records found.</td></tr>';
        document.getElementById('tableInfo').textContent = 'No records';
        document.getElementById('loadMoreWrap').classList.add('d-none');
        lastPage = 1;
        return;
    }

    document.getElementById('tableInfo').textContent =
        (meta.from || 0) + '–' + (meta.to || 0) + ' of ' + meta.total;

    var html = '';
    rows.forEach(function (r) {
        var total  = parseFloat(r.TransactionAmount || 0);
        var paid   = parseFloat(r.PaidAmount || 0);
        var due    = parseFloat(r.DueAmount  || 0);
        var status = getStatus(r);
        var date   = (r.PaymentDate || r.created_at || '---').substring(0, 10);
        var rowKey = r.GroupKey || (r.ID + ':' + date);
        var checked = selectedIds.has(rowKey) ? 'checked' : '';
        var rowCls = selectedIds.has(rowKey) ? 'row-selected' : '';

        html += '<tr class="' + rowCls + '" data-id="' + r.ID + '">';
        html += '<td class="text-center"><input type="checkbox" class="row-cb" data-id="' + rowKey + '" ' + checked + ' onchange="onRowCheck(this)"></td>';
        html += '<td>' + esc(date) + '</td>';
        html += '<td><strong>' + esc(r.BillNo || '---') + '</strong> ' + renderSourceBadge(r.payment_source) + '</td>';
        html += '<td>' + esc(r.PatientName || '---') + '</td>';
        html += '<td class="text-right">৳ ' + fmt(total) + '</td>';
        html += '<td class="text-right col-green">৳ ' + fmt(paid) + '</td>';
        html += '<td class="text-right col-red">৳ ' + fmt(r.DiscountAmount) + '</td>';
        html += '<td class="text-right col-red">৳ ' + fmt(due) + '</td>';
        html += '<td class="text-center"><span class="' + status.cls + '">' + status.label + '</span></td>';
        html += '<td class="text-center"><button class="pbtn" title="Patient Copy" onclick="window.open(\'' + ROUTES.print.replace(':id', r.ID) + '?copy=patient\',\'_blank\')">P</button> <button class="pbtn" title="Office Copy" onclick="window.open(\'' + ROUTES.print.replace(':id', r.ID) + '?copy=office\',\'_blank\')">O</button> <button class="pbtn" title="Reprint" onclick="window.open(\'' + ROUTES.print.replace(':id', r.ID) + '?copy=reprint\',\'_blank\')">R</button></td>';
        html += '<td class="text-center"><button class="pbtn pbtn-danger" title="Delete invoice" onclick="deleteInvoice(' + r.ID + ', this)"><i class="fas fa-trash-alt"></i></button></td>';
        html += '</tr>';
    });

    if (!append) { tbody.innerHTML = html; }
    else { tbody.insertAdjacentHTML('beforeend', html); }

    lastPage = meta.last_page;
    var wrap = document.getElementById('loadMoreWrap');
    wrap.classList.remove('d-none');
    var btn = document.getElementById('loadMoreBtn');
    if (currentPage < lastPage) {
        btn.innerHTML = '<i class="fas fa-chevron-down mr-1"></i>Load More (' + (meta.total - meta.to) + ')';
        btn.disabled = false;
    } else {
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>All ' + meta.total + ' loaded';
        btn.disabled = true;
    }
}

/* =============================================
   LOAD MORE
============================================= */
function loadMore() {
    if (isLoading || currentPage >= lastPage) return;
    loadData(currentPage + 1, true);
}

/* =============================================
   DELETE INVOICE
============================================= */
function deleteInvoice(id, btn) {
    if (!confirm('Delete this invoice? This will permanently remove the invoice, its items and payment transactions. This cannot be undone.')) {
        return;
    }
    var original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(ROUTES.delete, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ id: id }),
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.success) {
            var tr = btn.closest('tr');
            if (tr) tr.remove();
            selectedIds.delete(id);
            loadData(1);
            toast(res.message || 'Invoice deleted.', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = original;
            toast(res.message || 'Failed to delete invoice.', 'error');
        }
    })
    .catch(function () {
        btn.disabled = false;
        btn.innerHTML = original;
        toast('error', 'Failed to delete invoice. Please try again.');
    });
}

/* =============================================
   CHECKBOX
============================================= */
function onRowCheck(cb) {
    var id = cb.dataset.id;
    cb.checked ? selectedIds.add(id) : selectedIds.delete(id);
    var tr = cb.closest('tr');
    if (tr) tr.classList.toggle('row-selected', cb.checked);
    updateSelectUI();
}

function toggleSelectAll() {
    allSelected = !allSelected;
    document.querySelectorAll('.row-cb').forEach(function (cb) {
        cb.checked = allSelected;
        var id = cb.dataset.id;
        allSelected ? selectedIds.add(id) : selectedIds.delete(id);
        var tr = cb.closest('tr');
        if (tr) tr.classList.toggle('row-selected', allSelected);
    });
    document.getElementById('selectAllCheckbox').checked = allSelected;
    updateSelectUI();
}

function updateSelectUI() {
    document.getElementById('selectedCount').textContent = selectedIds.size;
    document.getElementById('btnBulkPrint').style.display = selectedIds.size > 0 ? 'inline-flex' : 'none';
}


function doBulkPrint() {
    if (!selectedIds.size) return;
    var ids  = Array.from(selectedIds);
    var rows = allFlatRows.filter(function (r) {
        var date = (r.PaymentDate || '').substring(0, 10);
        return ids.includes(r.GroupKey || (r.ID + ':' + date));
    });

    var html = '<html><head><style>';
    html += 'body{font-family:Arial,sans-serif;font-size:11px;padding:12px;}';
    html += 'table{width:100%;border-collapse:collapse;margin-bottom:12px;}';
    html += 'th,td{border:1px solid #ccc;padding:4px 8px;font-size:11px;}th{background:#f0f0f0;}';
    html += 'h3{font-size:12px;margin:0 0 4px;}';
    html += '</style></head><body>';

    rows.forEach(function (r) {
        var st = getStatus(r);
        html += '<h3>Invoice: ' + esc(r.BillNo) + ' — ' + st.label + '</h3>';
        html += '<table>';
        html += '<tr><th>Patient</th><td>' + esc(r.PatientName) + '</td><th>Mobile</th><td>' + esc(r.MobileNo || '---') + '</td></tr>';
        html += '<tr><th>Amount</th><td>৳ ' + fmt(r.TransactionAmount) + '</td><th>Paid</th><td>৳ ' + fmt(r.PaidAmount) + '</td></tr>';
        html += '<tr><th>Due</th><td>৳ ' + fmt(r.DueAmount) + '</td><th>Date</th><td>' + (r.PaymentDate || '---').substring(0,10) + '</td></tr>';
        html += '</table>';
    });

    html += '</body></html>';

    var frame = document.getElementById('printFrame');
    frame.style.display = 'block';
    frame.contentDocument.open();
    frame.contentDocument.write(html);
    frame.contentDocument.close();
    setTimeout(function () { frame.contentWindow.print(); frame.style.display = 'none'; }, 400);
    toast('Printing ' + ids.length + ' invoices', 'success');
}


function exportCSV() {
    if (!allFlatRows.length) { toast('No data', 'warning'); return; }
    var data = allFlatRows.map(function (r, i) {
        return {
            '#': i + 1,
            'Date': (r.PaymentDate || '').substring(0, 10),
            'Bill No': r.BillNo || '',
            'Patient': r.PatientName || '',
            'Code': r.PatientCode || '',
            'Mobile': r.MobileNo || '',
            'Amount': parseFloat(r.TransactionAmount || 0),
            'Paid': parseFloat(r.PaidAmount || 0),
            'Discount': parseFloat(r.DiscountAmount || 0),
            'Due': parseFloat(r.DueAmount || 0),
            'Source': r.payment_source ? (r.payment_source.charAt(0).toUpperCase() + r.payment_source.slice(1)) : 'Legacy',
            'Status': getStatus(r).label,
        };
    });
    var heads = Object.keys(data[0]);
    var csv = [heads.join(','), ...data.map(function (row) {
        return heads.map(function (h) { return '"' + String(row[h]).replace(/"/g, '""') + '"'; }).join(',');
    })].join('\r\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'Payments_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
    toast('CSV exported', 'success');
}


function toast(msg, type) {
    var box = document.getElementById('toastBox');
    var el = document.createElement('div');
    el.className = 'toast-item t-' + (type || 'success');
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(function () { el.remove(); }, 2500);
}


function fmt(v) {
    return parseFloat(v || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function esc(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}


document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var src = urlParams.get('payment_source');
    if (src && ['all', 'reception', 'manager', 'legacy'].includes(src.toLowerCase())) {
        document.getElementById('paymentSource').value = src.toLowerCase();
    }
    document.getElementById('paymentDate').value = '{{ now()->toDateString() }}';
    loadData(1);
});
</script>
@endpush
