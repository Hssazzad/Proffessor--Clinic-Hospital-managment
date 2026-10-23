@extends('adminlte::page')

@section('title', 'Payment List')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="p-icon-box"><i class="fas fa-receipt"></i></div>
        <div>
            <h1 class="p-page-title">Payment Records</h1>
            <ol class="breadcrumb p-breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="#">Billing</a></li>
                <li class="breadcrumb-item active">Payment Records</li>
            </ol>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="p-btn p-btn--outline p-btn--sm" id="btnBulkPrint" style="display:none;" onclick="doBulkPrint()">
            <i class="fas fa-print"></i> Print Selected (<span id="selectedCount">0</span>)
        </button>
        <button class="p-btn p-btn--outline p-btn--sm" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>
@stop

@section('content')

<div id="save-alert" class="alert d-none" role="alert" style="border-radius:var(--p-radius);"></div>

{{-- =============================================
     SUMMARY STAT CARDS
============================================= --}}
<div class="row mb-3">
    <div class="col-lg-3 col-sm-6 mb-2">
        <div class="p-stat">
            <div class="p-stat-icon p-stat-icon--gray"><i class="fas fa-file-invoice"></i></div>
            <div class="p-stat-body">
                <span class="p-stat-label">Total Invoices</span>
                <span class="p-stat-val" id="sumInvoices">—</span>
                <span class="p-stat-sub" id="sumPatients">— patients</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-2">
        <div class="p-stat">
            <div class="p-stat-icon p-stat-icon--dark"><i class="fas fa-coins"></i></div>
            <div class="p-stat-body">
                <span class="p-stat-label">Total Bill</span>
                <span class="p-stat-val" id="sumBill">—</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-2">
        <div class="p-stat">
            <div class="p-stat-icon p-stat-icon--green"><i class="fas fa-check-circle"></i></div>
            <div class="p-stat-body">
                <span class="p-stat-label">Total Paid</span>
                <span class="p-stat-val p-stat-val--green" id="sumPaid">—</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 mb-2">
        <div class="p-stat">
            <div class="p-stat-icon p-stat-icon--red"><i class="fas fa-exclamation-circle"></i></div>
            <div class="p-stat-body">
                <span class="p-stat-label">Total Due</span>
                <span class="p-stat-val p-stat-val--red" id="sumDue">—</span>
            </div>
        </div>
    </div>
</div>

{{-- =============================================
     FILTER PANEL — AdminLTE card + collapse
============================================= --}}
<div class="card p-card mb-3">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="p-card-head-icon"><i class="fas fa-filter"></i></span>
            <div>
                <span class="p-card-title">Filter & Search</span>
                <span class="p-card-sub">Date, patient or keyword search</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="p-badge" id="filterBadge"><i class="fas fa-calendar-day"></i> Today</span>
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Toggle filter">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body py-2">
        {{-- Row 1: Patient + Search + Per Page --}}
        <div class="form-row align-items-end">
            <div class="col-lg col-md-6 mb-2">
                <label class="p-field-label"><i class="fas fa-user mr-1"></i> Patient</label>
                <select id="patientSelect" style="width:100%"></select>
            </div>
            <div class="col-lg col-md-6 mb-2">
                <label class="p-field-label"><i class="fas fa-search mr-1"></i> Search</label>
                <div class="d-flex gap-1">
                    <input type="text" id="searchQ" class="form-control form-control-sm flex-grow-1"
                           placeholder="Name / Code / Bill No / Mobile..."
                           onkeydown="if(event.key==='Enter') loadData(1)">
                    <button class="p-btn p-btn--solid" onclick="loadData(1)"><i class="fas fa-search"></i> Load</button>
                    <button class="p-btn p-btn--outline" onclick="resetFilter()" style="color:var(--p-danger);border-color:var(--p-danger);">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-auto mb-2" style="min-width:80px;">
                <label class="p-field-label"><i class="fas fa-list-ol mr-1"></i> Per Page</label>
                <select id="perPage" class="form-control form-control-sm" onchange="loadData(1)">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="col-auto mb-2">
                <label class="p-field-label"><i class="fas fa-layer-group mr-1"></i> Source</label>
                <div class="p-seg">
                    <input type="radio" name="paymentSource" id="srcAll" value="all" checked onchange="loadData(1)">
                    <label for="srcAll">All</label>
                    <input type="radio" name="paymentSource" id="srcReception" value="reception" onchange="loadData(1)">
                    <label for="srcReception">Reception</label>
                    <input type="radio" name="paymentSource" id="srcManager" value="manager" onchange="loadData(1)">
                    <label for="srcManager">Manager</label>
                    <input type="radio" name="paymentSource" id="srcLegacy" value="legacy" onchange="loadData(1)">
                    <label for="srcLegacy">Legacy</label>
                </div>
            </div>
            <div class="col-auto mb-2">
                <label class="p-field-label"><i class="fas fa-filter mr-1"></i> Show</label>
                <div class="p-seg">
                    <input type="radio" name="showMode" id="showPaid" value="paid" checked onchange="loadData(1)">
                    <label for="showPaid">Paid Only</label>
                    <input type="radio" name="showMode" id="showAll" value="all" onchange="loadData(1)">
                    <label for="showAll">All</label>
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- Row 2: Date controls --}}
        <div class="form-row align-items-end">
            <div class="col-lg-4 col-md-6 mb-2">
                <label class="p-field-label"><i class="fas fa-calendar-alt mr-1"></i> Date Mode</label>
                <div class="p-seg">
                    <input type="radio" name="dateMode" id="modeAll" value="all">
                    <label for="modeAll">All Time</label>
                    <input type="radio" name="dateMode" id="modeSingle" value="single" checked>
                    <label for="modeSingle">Single</label>
                    <input type="radio" name="dateMode" id="modeRange" value="range">
                    <label for="modeRange">Range</label>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-2">
                <label class="p-field-label"><i class="fas fa-columns mr-1"></i> Date Column</label>
                <div class="p-seg">
                    <input type="radio" name="dateCol" id="colPayDate" value="PaymentDate" checked>
                    <label for="colPayDate">Pay Date</label>
                    <input type="radio" name="dateCol" id="colCreated" value="created_at">
                    <label for="colCreated">Created</label>
                </div>
            </div>
            <div id="singleWrap" class="col-auto mb-2">
                <label class="p-field-label"><i class="fas fa-calendar-day mr-1"></i> Date</label>
                <input type="date" id="singleDate" class="form-control form-control-sm" style="width:150px;">
            </div>
            <div id="rangeWrap" class="col-auto mb-2 d-flex gap-2 align-items-end" style="display:none;">
                <div>
                    <label class="p-field-label"><i class="fas fa-calendar mr-1"></i> From</label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm" style="width:145px;">
                </div>
                <div>
                    <label class="p-field-label">To</label>
                    <input type="date" id="dateTo" class="form-control form-control-sm" style="width:145px;">
                </div>
            </div>
            <div class="col-auto mb-2">
                <small class="text-muted d-flex align-items-center gap-1">
                    <i class="fas fa-info-circle"></i>
                    Press <kbd class="p-kbd">Enter</kbd> or click Load
                </small>
            </div>
        </div>
    </div>
</div>

{{-- =============================================
     TABLE PANEL — AdminLTE card
============================================= --}}
<div class="card p-card">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="p-card-head-icon"><i class="fas fa-table"></i></span>
            <div>
                <span class="p-card-title">Payment Records</span>
                <span class="p-card-sub">Detailed payment history</span>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="p-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <button class="p-btn p-btn--outline p-btn--sm" onclick="toggleSelectAll()">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <small class="text-muted">Selected: <strong id="selectedCount" style="color:var(--p-primary);">0</strong></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="p-btn p-btn--sm" onclick="exportCSV()" style="background:var(--p-success);border-color:var(--p-success);color:#fff;">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="p-table" id="paymentTable">
            <thead>
                <tr>
                    <th style="width:36px;" class="text-center"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" aria-label="Select all"></th>
                    <th>Date</th>
                    <th>Bill No</th>
                    <th>Patient Name</th>
                    <th class="text-right">Total Bill</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                    <th class="text-center" style="width:60px;">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8" class="p-table-msg"><i class="fas fa-circle-notch fa-spin"></i> Loading...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Traditional Pagination --}}
    <div class="card-footer py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted small">
                Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="showingTotal">0</span> entries
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="pagination">
                    <!-- Pagination will be rendered here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="p-bottom-spacer"></div>

{{-- =============================================
     BOTTOM NAVIGATION
============================================= --}}
<nav class="p-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="p-nav-link"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="{{ route('billing.paylist.index') }}" class="p-nav-link p-nav-link--active"><i class="fas fa-receipt"></i><span>Payments</span></a>
    <a href="{{ route('billing.duelist.index') }}" class="p-nav-link"><i class="fas fa-exclamation-triangle"></i><span>Due List</span></a>
    <a href="{{ route('billing.discharge.index') }}" class="p-nav-link"><i class="fas fa-sign-out-alt"></i><span>Discharge</span></a>
    <a href="{{ route('billing.paynow.index') }}" class="p-nav-link"><i class="fas fa-credit-card"></i><span>Pay Now</span></a>
</nav>

<iframe id="printFrame" style="display:none;"></iframe>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
/* =============================================
   PROFESSIONAL BILLING UI
   Dark slate #374151 primary, system fonts
============================================= */
:root {
    --p-primary:       #374151;
    --p-primary-hover: #1f2937;
    --p-primary-light: #f3f4f6;
    --p-success:       #059669;
    --p-success-bg:    #ecfdf5;
    --p-danger:        #dc2626;
    --p-danger-bg:     #fef2f2;
    --p-warning:       #d97706;
    --p-warning-bg:    #fffbeb;
    --p-bg:            #f9fafb;
    --p-card:          #ffffff;
    --p-border:        #e5e7eb;
    --p-border-dark:   #d1d5db;
    --p-text:          #111827;
    --p-muted:         #6b7280;
    --p-muted-light:   #9ca3af;
    --p-radius:        5px;
    --p-font:          -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --p-transition:    .15s ease;
}

body, .content-wrapper {
    background: var(--p-bg) !important;
    font-family: var(--p-font) !important;
    color: var(--p-text);
}

/* ── Page Header ── */
.p-icon-box {
    width: 36px; height: 36px;
    background: var(--p-primary);
    color: #fff;
    border-radius: var(--p-radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.p-page-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--p-text);
    margin: 0;
    letter-spacing: -.2px;
}
.p-breadcrumb { font-size: 11px; }

/* ── Stat Cards ── */
.p-stat {
    background: var(--p-card);
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: border-color var(--p-transition);
}
.p-stat:hover { border-color: var(--p-border-dark); }

.p-stat-icon {
    width: 36px; height: 36px;
    border-radius: var(--p-radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.p-stat-icon--gray  { background: var(--p-primary-light); color: var(--p-muted); }
.p-stat-icon--dark  { background: var(--p-primary-light); color: var(--p-primary); }
.p-stat-icon--green { background: var(--p-success-bg);    color: var(--p-success); }
.p-stat-icon--red   { background: var(--p-danger-bg);     color: var(--p-danger); }

.p-stat-body { display: flex; flex-direction: column; gap: 2px; }
.p-stat-label {
    font-size: 10px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .5px; color: var(--p-muted);
}
.p-stat-val {
    font-size: 17px; font-weight: 700; color: var(--p-text); line-height: 1.1;
}
.p-stat-val--green { color: var(--p-success); }
.p-stat-val--red   { color: var(--p-danger); }
.p-stat-sub {
    font-size: 11px; color: var(--p-muted);
}

/* ── Card overrides for panel style ── */
.p-card {
    border-color: var(--p-border) !important;
    border-radius: var(--p-radius) !important;
}
.p-card > .card-header {
    background: var(--p-primary-light);
    border-bottom: 1px solid var(--p-border);
    padding: 8px 14px;
}
.p-card-head-icon {
    width: 28px; height: 28px;
    border-radius: var(--p-radius);
    background: var(--p-primary-light);
    color: var(--p-primary);
    font-size: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.p-card-title {
    font-size: 13px; font-weight: 700; color: var(--p-text); display: block;
}
.p-card-sub {
    font-size: 11px; color: var(--p-muted); display: block; margin-top: 1px;
}
.p-card .btn-tool { color: var(--p-muted) !important; transition: color var(--p-transition); }
.p-card .btn-tool:hover { color: var(--p-primary) !important; }

/* ── Badge ── */
.p-badge {
    background: var(--p-primary-light);
    color: var(--p-primary);
    border-radius: var(--p-radius);
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* ── Field Labels ── */
.p-field-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .3px; color: var(--p-primary);
    display: flex; align-items: center;
    margin-bottom: 4px;
}

/* ── Segmented Control ── */
.p-seg {
    display: flex;
    border: 1px solid var(--p-border);
    border-radius: var(--p-radius);
    overflow: hidden;
}
.p-seg input[type=radio] { display: none; }
.p-seg label {
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    background: var(--p-card);
    color: var(--p-muted);
    transition: all .12s ease;
    margin: 0;
    white-space: nowrap;
    user-select: none;
    border-right: 1px solid var(--p-border);
    text-align: center;
    line-height: 1.4;
    font-family: var(--p-font);
}
.p-seg label:last-of-type { border-right: none; }
.p-seg label:hover { background: var(--p-primary-light); color: var(--p-primary); }
.p-seg input[type=radio]:checked + label {
    background: var(--p-primary);
    color: #fff;
    border-color: var(--p-primary);
}

/* ── Buttons ── */
.p-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border: 1px solid var(--p-border);
    border-radius: var(--p-radius); font-size: 12px; font-weight: 600;
    font-family: var(--p-font); cursor: pointer;
    transition: all var(--p-transition); white-space: nowrap;
    line-height: 1; height: 34px;
    background: var(--p-card); color: var(--p-text); outline: none;
}
.p-btn:focus-visible { box-shadow: 0 0 0 2px var(--p-primary-light), 0 0 0 4px var(--p-primary); }
.p-btn--solid { background: var(--p-primary); border-color: var(--p-primary); color: #fff; }
.p-btn--solid:hover { background: var(--p-primary-hover); border-color: var(--p-primary-hover); }
.p-btn--outline { background: var(--p-card); border-color: var(--p-border); color: var(--p-muted); }
.p-btn--outline:hover { border-color: var(--p-primary); color: var(--p-primary); }
.p-btn--sm { padding: 4px 10px; font-size: 11px; height: 30px; }
.p-btn--full { width: 100%; justify-content: center; height: auto; padding: 10px; }

/* ── kbd ── */
.p-kbd {
    background: var(--p-primary-light);
    border: 1px solid var(--p-border);
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 10px;
    font-family: var(--p-font);
}

/* ── Toolbar ── */
.p-toolbar { background: var(--p-primary-light); }

/* ── Table ── */
.p-table {
    width: 100%; border-collapse: collapse; font-size: 12.5px;
}
.p-table thead th {
    background: var(--p-primary-light); color: var(--p-muted);
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; padding: 10px 10px;
    border-bottom: 1px solid var(--p-border);
    white-space: nowrap; text-align: left;
}
.p-table thead th.text-right  { text-align: right; }
.p-table thead th.text-center { text-align: center; }

.p-table tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--p-primary-light);
    vertical-align: middle; color: var(--p-text);
}
.p-table tbody tr { transition: background var(--p-transition); }
.p-table tbody tr:hover { background: var(--p-primary-light); }
.p-table tbody tr.p-row-selected { background: var(--p-success-bg) !important; }

.p-table .text-right  { text-align: right; }
.p-table .text-center { text-align: center; }

.p-table-msg {
    text-align: center; padding: 32px 16px;
    color: var(--p-muted); font-size: 13px;
}
.p-table-msg i { margin-right: 6px; }

/* ── Action button ── */
.p-act {
    background: transparent; border: 1px solid var(--p-border);
    border-radius: var(--p-radius); padding: 4px 7px;
    font-size: 11px; cursor: pointer; color: var(--p-muted);
    transition: all var(--p-transition); font-family: var(--p-font);
    font-weight: 600; display: inline-flex; align-items: center; gap: 3px; outline: none;
}
.p-act:hover { border-color: var(--p-primary); color: var(--p-primary); }
.p-act:focus-visible { box-shadow: 0 0 0 2px var(--p-primary-light); }
.p-act--print {
    color: var(--p-primary); border-color: var(--p-primary); background: var(--p-primary-light);
}
.p-act--print:hover { background: var(--p-primary); color: #fff; }

/* ── Bottom Spacer & Nav ── */
.p-bottom-spacer { height: 52px; }
.p-bottom-nav {
    position: fixed; bottom: 0; left: 0; right: 0;
    height: 52px; background: var(--p-card);
    border-top: 1px solid var(--p-border);
    display: flex; align-items: center; justify-content: space-around;
    z-index: 999; padding: 0 4px;
}
.p-nav-link {
    display: flex; flex-direction: column; align-items: center;
    gap: 2px; padding: 6px 8px; text-decoration: none;
    color: var(--p-muted); font-size: 10px; font-weight: 600;
    min-width: 50px; transition: color var(--p-transition); position: relative;
}
.p-nav-link i { font-size: 16px; }
.p-nav-link:hover { color: var(--p-primary); text-decoration: none; }
.p-nav-link--active { color: var(--p-primary); }
.p-nav-link--active::after {
    content: ''; position: absolute;
    bottom: 0; left: 50%; transform: translateX(-50%);
    width: 18px; height: 2px;
    background: var(--p-primary); border-radius: 2px;
}

/* ── Select2 ── */
.select2-container--bootstrap4 .select2-selection {
    height: 32px !important;
    border: 1px solid var(--p-border) !important;
    border-radius: var(--p-radius) !important;
    background: var(--p-card) !important;
    transition: border-color var(--p-transition) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: 13px !important;
    font-family: var(--p-font) !important;
    color: var(--p-text) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { height: 30px !important; }
.select2-container--bootstrap4 .select2-container--focus .select2-selection {
    border-color: var(--p-primary) !important;
    box-shadow: none !important;
}
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background: var(--p-primary) !important;
}
.select2-container--bootstrap4 .select2-results__option {
    font-family: var(--p-font) !important;
}

/* ── Checkbox ── */
input[type="checkbox"] { accent-color: var(--p-primary); cursor: pointer; }

/* ── Form-control overrides ── */
.form-control-sm {
    height: 32px !important;
    font-size: 13px !important;
    font-family: var(--p-font) !important;
    border-radius: var(--p-radius) !important;
}
.form-control:focus {
    border-color: var(--p-primary) !important;
    box-shadow: 0 0 0 2px rgba(55,65,81,.12) !important;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .form-row { flex-direction: column; align-items: stretch; }
    .p-seg { flex-wrap: wrap; }
    .p-seg label { flex: 1 1 auto; }
}
@media (max-width: 520px) {
    .p-page-title { font-size: 16px; }
    .p-stat { padding: 10px; }
    .p-stat-val { font-size: 15px; }
    .p-stat-icon { width: 32px; height: 32px; font-size: 12px; }

    .p-table thead { display: none; }
    .p-table tbody tr {
        display: block;
        border: 1px solid var(--p-border);
        border-radius: var(--p-radius);
        padding: 10px;
        margin-bottom: 6px;
    }
    .p-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        padding: 3px 0;
        font-size: 12px;
    }
    .p-table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--p-muted);
        font-size: 10px;
        text-transform: uppercase;
        min-width: 80px;
        flex-shrink: 0;
    }
    .p-table tbody td:last-child {
        justify-content: center;
        border-top: 1px solid var(--p-primary-light);
        padding-top: 6px; margin-top: 4px;
    }
    .p-table tbody td:last-child::before { content: ''; }

    .p-toolbar { flex-direction: column; align-items: stretch; }
}

/* ── Print ── */
@media print {
    .p-bottom-nav, .p-bottom-spacer, .p-toolbar,
    .card-header, #loadMoreWrap, .content-header,
    .main-header, .main-sidebar { display: none !important; }
    body, .content-wrapper { background: #fff !important; }
    .p-card { border: 1px solid #ccc; }
    .p-stat { border: 1px solid #ccc; }
    .p-table { font-size: 10px; }
    .p-table td, .p-table th { padding: 3px 4px; }
}
</style>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
/* =============================================
   PAYMENT LIST — Professional AJAX handler
   Default view = TODAY only
   Summary cards = calculated from the SAME rows shown in the table
   (fixes mismatch between cards and table totals)
============================================= */
toastr.options = { positionClass:'toast-top-right', timeOut:3000, progressBar:true, closeButton:true };
$.ajaxSetup({ headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}' } });

var ROUTES = {
    list:    '{{ route("billing.duelist.list") }}',
    summary: '{{ route("billing.duelist.summary") }}',
    search:  '{{ route("billing.invoice.searchPatient") }}',
    print:   '{{ route("billing.invoice.print", ":id") }}',
};

var currentPage = 1, lastPage = 1, isLoading = false;
var selectedIds = new Set(), allFlatRows = [], allSelected = false;

var today = new Date().toISOString().split('T')[0];
document.getElementById('singleDate').value = today;
document.getElementById('dateFrom').value   = today;
document.getElementById('dateTo').value     = today;

/* ── Select2 ── */
$('#patientSelect').select2({
    theme: 'bootstrap4',
    placeholder: 'All Patients',
    allowClear: true,
    ajax: {
        url: ROUTES.search,
        dataType: 'json',
        delay: 300,
        data: function (params) {
            return {
                q: params.term,
                payment_source: (document.querySelector('[name=paymentSource]:checked') || {}).value || 'all'
            };
        },
        processResults: function (data) {
            return { results: data.map(function (p) { return { id: p.id, text: p.patientname + ' (' + p.patientcode + ')' }; }) };
        },
        cache: true,
    },
    minimumInputLength: 1,
});

$('#patientSelect').on('change', function () {
    var pid = $(this).val();
    document.querySelectorAll('[name=dateMode]').forEach(function (r) { r.disabled = !!pid; });
    loadData(1);
});

/* ── Date Mode ── */
document.querySelectorAll('[name=dateMode]').forEach(function (r) {
    r.addEventListener('change', function () {
        document.getElementById('rangeWrap').style.display  = this.value === 'range'  ? 'flex' : 'none';
        document.getElementById('singleWrap').style.display = this.value === 'single' ? 'flex' : 'none';
        updateBadge();
    });
});
document.querySelectorAll('[name=dateCol]').forEach(function (r) {
    r.addEventListener('change', function () { updateBadge(); });
});

/* ── Badge (shows what filter is active on top) ── */
function updateBadge(mode, hasPatient) {
    var badge = document.getElementById('filterBadge');
    var pid = $('#patientSelect').val();
    var m = mode || document.querySelector('[name=dateMode]:checked').value;

    if (pid || hasPatient) {
        badge.innerHTML = '<i class="fas fa-user"></i> Patient — All Time';
    } else if (m === 'range') {
        badge.innerHTML = document.getElementById('dateFrom').value + ' — ' + document.getElementById('dateTo').value;
    } else if (m === 'single') {
        var chosenDate = document.getElementById('singleDate').value;
        var label = chosenDate === today ? 'Today' : chosenDate;
        badge.innerHTML = '<i class="fas fa-calendar-day"></i> ' + label;
    } else {
        badge.innerHTML = '<i class="fas fa-infinity"></i> All Time';
    }
}

/* ── Params ── */
function getParams(page) {
    var pid = $('#patientSelect').val();
    var dateMode = document.querySelector('[name=dateMode]:checked').value;
    var dateCol  = document.querySelector('[name=dateCol]:checked').value;
    var showMode = document.querySelector('[name=showMode]:checked').value;
    var paymentSource = (document.querySelector('[name=paymentSource]:checked') || {}).value || 'all';
    var params = new URLSearchParams({
        page: page,
        per_page: document.getElementById('perPage').value,
        q: document.getElementById('searchQ').value.trim(),
        date_column: dateCol,
        show_all: showMode === 'all' ? 1 : 0,
        payment_source: paymentSource,
    });

    if (pid) {
        params.append('patient_id', pid);
        params.append('date_mode', 'all');
        updateBadge('all', true);
    } else if (dateMode === 'range') {
        params.append('date_mode', 'range');
        params.append('date_from', document.getElementById('dateFrom').value);
        params.append('date_to',   document.getElementById('dateTo').value);
        updateBadge('range');
    } else if (dateMode === 'single') {
        params.append('date_mode', 'single');
        params.append('single_date', document.getElementById('singleDate').value);
        updateBadge('single');
    } else {
        params.append('date_mode', 'all');
        updateBadge('all');
    }
    return params;
}

/* ── Load Data ── */
function loadData(page) {
    if (isLoading) return;
    isLoading = true;
    currentPage = page;

    selectedIds.clear();
    allSelected = false;
    updateSelectUI();
    document.getElementById('selectAllCheckbox').checked = false;
    document.getElementById('tableBody').innerHTML =
        '<tr><td colspan="8" class="p-table-msg"><i class="fas fa-circle-notch fa-spin"></i> Loading...</td></tr>';

    var params = getParams(page);

    fetch(ROUTES.summary + '?' + params.toString())
        .then(function (r) { return r.json(); })
        .then(function (s) {
            if (s.success) {
                document.getElementById('sumInvoices').textContent = (s.total_invoices || 0).toLocaleString();
                document.getElementById('sumPatients').textContent = (s.total_patients || 0) + ' patient(s)';
                document.getElementById('sumBill').textContent      = '৳ ' + fmt(s.total_bill);
                document.getElementById('sumPaid').textContent      = '৳ ' + fmt(s.total_paid);
                document.getElementById('sumDue').textContent       = '৳ ' + fmt(s.total_due);
            }
        })
        .catch(function () {});

    fetch(ROUTES.list + '?' + params.toString())
        .then(function (r) { return r.json(); })
        .then(function (res) {
            allFlatRows = res.flat || [];
            renderTable(res.flat || [], res.meta, false);
            isLoading = false;
        })
        .catch(function () {
            isLoading = false;
            document.getElementById('tableBody').innerHTML =
                '<tr><td colspan="8" class="p-table-msg"><i class="fas fa-exclamation-triangle" style="color:var(--p-danger);"></i> Error loading data.</td></tr>';
        });
}

/* ── Summary cards — built from the SAME rows shown in the table below ── */
function calcSummary(rows) {
    var totalBill = 0;
    var totalPaid = 0;
    var totalDue  = 0;

    rows.forEach(function (r) {
        totalBill += parseFloat(r.TotalBill  || 0);
        totalPaid += parseFloat(r.PaidAmount || 0);
        totalDue  += parseFloat(r.DueAmount  || 0);
    });

    document.getElementById('sumInvoices').textContent = rows.length;
    document.getElementById('sumPatients').textContent = rows.length + ' patient(s)';
    document.getElementById('sumBill').textContent      = '৳ ' + fmt(totalBill);
    document.getElementById('sumPaid').textContent      = '৳ ' + fmt(totalPaid);
    document.getElementById('sumDue').textContent       = '৳ ' + fmt(totalDue);
}

/* ── Format ── */
function fmt(v) {
    return parseFloat(v || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/* ── Status ── */
function getStatus(r) {
    var total = parseFloat(r.TotalBill || 0);
    var paid  = parseFloat(r.PaidAmount || 0);
    var due   = parseFloat(r.DueAmount || 0);
    if (total > 0 && due === 0) return { label: 'Paid',    cls: 'p-status--paid' };
    if (due > 0 && paid > 0)    return { label: 'Partial', cls: 'p-status--partial' };
    if (due > 0)                return { label: 'Due',     cls: 'p-status--due' };
    return { label: 'Pending', cls: 'p-status--partial' };
}

function renderSourceBadge(source) {
    var s = (source || '').toLowerCase().trim();
    if (s === 'manager') {
        return '<span class="badge badge-primary px-1 ml-1" style="font-size:10px;font-weight:600;">Manager</span>';
    }
    if (s === 'reception') {
        return '<span class="badge badge-info px-1 ml-1" style="font-size:10px;font-weight:600;">Reception</span>';
    }
    return '<span class="badge badge-secondary px-1 ml-1" style="font-size:10px;font-weight:500;">Legacy</span>';
}

/* ── Render Table ── */
function renderTable(rows, meta, append) {
    var tbody = document.getElementById('tableBody');

    if (!rows.length && !append) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-table-msg"><i class="fas fa-inbox" style="font-size:20px;display:block;margin-bottom:6px;color:var(--p-border-dark);"></i>No records found.</td></tr>';
        document.getElementById('showingFrom').textContent = '0';
        document.getElementById('showingTo').textContent = '0';
        document.getElementById('showingTotal').textContent = '0';
        document.getElementById('pagination').innerHTML = '';
        lastPage = 1;
        return;
    }

    document.getElementById('showingFrom').textContent = meta.from || 0;
    document.getElementById('showingTo').textContent = meta.to || 0;
    document.getElementById('showingTotal').textContent = meta.total;

    var html = '';
    rows.forEach(function (r) {
        var total  = parseFloat(r.TotalBill  || 0);
        var paid   = parseFloat(r.PaidAmount || 0);
        var due    = parseFloat(r.DueAmount  || 0);
        var date   = (r.PaymentDate || r.created_at || '---').substring(0, 10);
        var checked = selectedIds.has(r.ID) ? 'checked' : '';
        var rowCls = selectedIds.has(r.ID) ? 'p-row-selected' : '';

        html += '<tr class="' + rowCls + '" data-id="' + r.ID + '">';
        html += '<td data-label="Select" class="text-center"><input type="checkbox" class="row-cb" data-id="' + r.ID + '" ' + checked + ' onchange="onRowCheck(this)"></td>';
        html += '<td data-label="Date">' + esc(date) + '</td>';
        html += '<td data-label="Bill No" style="font-weight:700;color:var(--p-primary);">' + esc(r.BillNo || '---') + ' ' + renderSourceBadge(r.payment_source) + '</td>';
        html += '<td data-label="Patient">' + esc(r.PatientName || '---') + '</td>';
        html += '<td data-label="Bill" class="text-right">৳ ' + fmt(total) + '</td>';
        html += '<td data-label="Paid" class="text-right" style="color:var(--p-success);font-weight:600;">৳ ' + fmt(paid) + '</td>';
        html += '<td data-label="Due" class="text-right" style="color:var(--p-danger);font-weight:600;">৳ ' + fmt(due) + '</td>';
        html += '<td data-label="Action" class="text-center">';
        html += '<button class="p-act p-act--print" onclick="window.open(\'' + ROUTES.print.replace(':id', r.InvoiceId) + '?copy=patient\',\'_blank\')" title="Patient Copy">P</button>';
        html += ' <button class="p-act p-act--print" onclick="window.open(\'' + ROUTES.print.replace(':id', r.InvoiceId) + '?copy=office\',\'_blank\')" title="Office Copy">O</button>';
        html += ' <button class="p-act p-act--print" onclick="window.open(\'' + ROUTES.print.replace(':id', r.InvoiceId) + '?copy=reprint\',\'_blank\')" title="Reprint">R</button>';
        html += '</td></tr>';
    });

    if (!append) { tbody.innerHTML = html; }
    else { tbody.insertAdjacentHTML('beforeend', html); }

    lastPage = meta.last_page;
    renderPagination(meta);
}

/* ── Render Pagination ── */
function renderPagination(meta) {
    var pagination = document.getElementById('pagination');
    var current = meta.current_page;
    var last = meta.last_page;
    var html = '';

    if (last <= 1) {
        pagination.innerHTML = '';
        return;
    }

    // Previous button
    html += '<li class="page-item ' + (current === 1 ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="loadData(' + (current - 1) + '); return false;">&laquo;</a>';
    html += '</li>';

    // Page numbers
    var start = Math.max(1, current - 2);
    var end = Math.min(last, current + 2);

    if (start > 1) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="loadData(1); return false;">1</a></li>';
        if (start > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for (var i = start; i <= end; i++) {
        html += '<li class="page-item ' + (i === current ? 'active' : '') + '">';
        html += '<a class="page-link" href="#" onclick="loadData(' + i + '); return false;">' + i + '</a>';
        html += '</li>';
    }

    if (end < last) {
        if (end < last - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += '<li class="page-item"><a class="page-link" href="#" onclick="loadData(' + last + '); return false;">' + last + '</a></li>';
    }

    // Next button
    html += '<li class="page-item ' + (current === last ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="loadData(' + (current + 1) + '); return false;">&raquo;</a>';
    html += '</li>';

    pagination.innerHTML = html;
}

/* ── Checkbox ── */
function onRowCheck(cb) {
    var id = parseInt(cb.dataset.id);
    cb.checked ? selectedIds.add(id) : selectedIds.delete(id);
    var tr = cb.closest('tr');
    if (tr) tr.classList.toggle('p-row-selected', cb.checked);
    updateSelectUI();
}

function toggleSelectAll() {
    allSelected = !allSelected;
    document.querySelectorAll('.row-cb').forEach(function (cb) {
        cb.checked = allSelected;
        var id = parseInt(cb.dataset.id);
        allSelected ? selectedIds.add(id) : selectedIds.delete(id);
        var tr = cb.closest('tr');
        if (tr) tr.classList.toggle('p-row-selected', allSelected);
    });
    document.getElementById('selectAllCheckbox').checked = allSelected;
    updateSelectUI();
}

function updateSelectUI() {
    var countEl = document.getElementById('selectedCount');
    var btnEl = document.getElementById('btnBulkPrint');
    if (countEl) countEl.textContent = selectedIds.size;
    if (btnEl) btnEl.style.display = selectedIds.size > 0 ? 'inline-flex' : 'none';
}

/* ── Bulk Print ── */
function doBulkPrint() {
    if (!selectedIds.size) return;
    var ids  = Array.from(selectedIds);
    var rows = allFlatRows.filter(function (r) { return ids.includes(r.ID); });

    var html = '<html><head><style>';
    html += 'body{font-family:-apple-system,sans-serif;font-size:11px;color:#111827;padding:12px;}';
    html += '.inv-block{border:1px solid #e5e7eb;margin-bottom:16px;padding:12px;border-radius:4px;page-break-inside:avoid;}';
    html += '.inv-title{font-size:12px;font-weight:700;margin-bottom:8px;color:#374151;padding-bottom:6px;border-bottom:2px solid #374151;}';
    html += 'table{width:100%;border-collapse:collapse;margin-top:6px;}';
    html += 'th,td{border:1px solid #e5e7eb;padding:5px 8px;text-align:left;font-size:11px;}';
    html += 'th{background:#f3f4f6;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#374151;font-size:10px;}';
    html += '.amount{font-weight:700;}';
    html += '</style></head><body>';

    rows.forEach(function (r) {
        html += '<div class="inv-block">';
        html += '<div class="inv-title">Invoice: ' + esc(r.BillNo || '---') + '</div>';
        html += '<table>';
        html += '<tr><th>Patient</th><td>' + esc(r.PatientName) + ' (' + esc(r.PatientCode) + ')</td><th>Mobile</th><td>' + esc(r.MobileNo || '---') + '</td></tr>';
        html += '<tr><th>Total Bill</th><td class="amount">৳ ' + fmt(r.TotalBill) + '</td><th>Paid</th><td class="amount">৳ ' + fmt(r.PaidAmount) + '</td></tr>';
        html += '<tr><th>Due</th><td class="amount">৳ ' + fmt(r.DueAmount) + '</td><th>Date</th><td>' + (r.PaymentDate || r.created_at || '---').substring(0,10) + '</td></tr>';
        html += '</table></div>';
    });

    html += '</body></html>';

    var frame = document.getElementById('printFrame');
    frame.style.display = 'block';
    frame.contentDocument.open();
    frame.contentDocument.write(html);
    frame.contentDocument.close();
    setTimeout(function () { frame.contentWindow.print(); frame.style.display = 'none'; }, 400);
    toastr.info('Printing ' + ids.length + ' invoices');
}

/* ── CSV Export ── */
function exportCSV() {
    if (!allFlatRows.length) { toastr.warning('No data to export.'); return; }
    var data = allFlatRows.map(function (r, idx) {
        return {
            '#': idx + 1,
            'Date': (r.PaymentDate || r.created_at || '').substring(0,10),
            'Bill No': r.BillNo || '',
            'Patient': r.PatientName || '',
            'Code': r.PatientCode || '',
            'Mobile': r.MobileNo || '',
            'Total Bill': (parseFloat(r.TotalBill || 0)).toFixed(2),
            'Paid': (parseFloat(r.PaidAmount || 0)).toFixed(2),
            'Due': (parseFloat(r.DueAmount || 0)).toFixed(2),
            'Source': r.payment_source ? (r.payment_source.charAt(0).toUpperCase() + r.payment_source.slice(1)) : 'Legacy',
        };
    });
    var heads = Object.keys(data[0]);
    var csv = [heads.join(',')];
    for (var i = 0; i < data.length; i++) {
        csv.push(heads.map(function (h) { return '"' + String(data[i][h]).replace(/"/g, '""') + '"'; }).join(','));
    }
    csv = csv.join('\r\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url; a.download = 'PaymentRecords_' + today + '.csv'; a.click();
    URL.revokeObjectURL(url);
    toastr.success('CSV exported');
}

/* ── Reset (goes back to Today) ── */
function resetFilter() {
    $('#patientSelect').val(null).trigger('change');
    document.getElementById('searchQ').value = '';
    document.getElementById('singleDate').value = today;
    document.getElementById('dateFrom').value = today;
    document.getElementById('dateTo').value = today;
    document.getElementById('modeSingle').checked = true;
    document.getElementById('colPayDate').checked = true;
    var srcAll = document.getElementById('srcAll');
    if (srcAll) srcAll.checked = true;
    document.getElementById('rangeWrap').style.display = 'none';
    document.getElementById('singleWrap').style.display = 'flex';
    document.querySelectorAll('[name=dateMode]').forEach(function (r) { r.disabled = false; });
    updateBadge('single');
    loadData(1);
    toastr.info('Filters reset to Today');
}

/* ── Helpers ── */
function esc(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

/* ── Init: page loads showing TODAY's data by default ── */
document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var src = urlParams.get('payment_source');
    if (src && ['all', 'reception', 'manager', 'legacy'].includes(src.toLowerCase())) {
        var el = document.querySelector('[name=paymentSource][value="' + src.toLowerCase() + '"]');
        if (el) el.checked = true;
    }
    document.getElementById('singleWrap').style.display = 'flex';
    document.getElementById('rangeWrap').style.display  = 'none';
    updateBadge('single');
    loadData(1);
});
</script>
@endpush