@extends('adminlte::page')

@section('title', 'Payment List')

@section('content_header')
<div class="cc-topbar">
    <div class="cc-topbar-left">
        <div class="cc-topbar-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div>
            <h1 class="cc-page-title">Payment Records</h1>
            <ol class="cc-breadcrumb">
                <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#">Billing</a></li>
                <li class="active">Payment Records</li>
            </ol>
        </div>
    </div>
    <div class="cc-topbar-actions">
        <button class="cc-btn cc-btn-outline" id="btnBulkPrint" style="display:none;" onclick="doBulkPrint()">
            <i class="fas fa-print"></i> Print Selected
        </button>
        <button class="cc-btn cc-btn-outline" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>
@stop

@section('content')

<div id="save-alert" class="cc-alert d-none" role="alert"></div>

{{-- =============================================
     SUMMARY STAT CARDS
============================================= --}}
<div class="cc-stats">

    <div class="cc-stat">
        <div class="cc-stat-icon cc-stat-icon-slate">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Invoices</div>
            <div class="cc-stat-value" id="sumInvoices">—</div>
            <div class="cc-stat-sub" id="sumPatients">— patients</div>
        </div>
    </div>

    <div class="cc-stat">
        <div class="cc-stat-icon cc-stat-icon-primary">
            <i class="fas fa-coins"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Bill</div>
            <div class="cc-stat-value" id="sumBill">—</div>
        </div>
    </div>

    <div class="cc-stat">
        <div class="cc-stat-icon cc-stat-icon-green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Paid</div>
            <div class="cc-stat-value cc-stat-value-green" id="sumPaid">—</div>
        </div>
    </div>

    <div class="cc-stat">
        <div class="cc-stat-icon cc-stat-icon-red">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Due</div>
            <div class="cc-stat-value cc-stat-value-red" id="sumDue">—</div>
        </div>
    </div>

</div>

{{-- =============================================
     FILTER PANEL
============================================= --}}
<div class="cc-panel">

    <div class="cc-panel-head">
        <div class="cc-panel-head-left">
            <span class="cc-panel-head-icon"><i class="fas fa-filter"></i></span>
            <div>
                <div class="cc-panel-title">Filter & Search</div>
                <div class="cc-panel-subtitle">Date, patient or keyword payment search</div>
            </div>
        </div>
        <div class="cc-panel-head-right">
            <span class="cc-badge cc-badge-filter" id="filterBadge">
                <i class="fas fa-calendar-day"></i> Today
            </span>
        </div>
    </div>

    <div class="cc-filter-body">

        <div class="cc-filter-row">
            <div class="cc-field cc-field-grow">
                <label class="cc-label"><i class="fas fa-user"></i> Patient</label>
                <select id="patientSelect" style="width:100%"></select>
            </div>

            <div class="cc-field cc-field-grow">
                <label class="cc-label"><i class="fas fa-search"></i> Search</label>
                <div class="cc-search-group">
                    <input type="text" id="searchQ" class="cc-input"
                           placeholder="Name / Code / Bill No / Mobile..."
                           onkeydown="if(event.key==='Enter') loadData(1)">
                    <button class="cc-btn cc-btn-primary" onclick="loadData(1)">
                        <i class="fas fa-search"></i> Load
                    </button>
                    <button class="cc-btn cc-btn-outline cc-btn-reset" onclick="resetFilter()">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </div>
            </div>

            <div class="cc-field" style="min-width:90px;">
                <label class="cc-label"><i class="fas fa-list-ol"></i> Per Page</label>
                <select id="perPage" class="cc-input" style="height:36px;">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="cc-filter-row cc-filter-row-divider">

            <div class="cc-field">
                <label class="cc-label"><i class="fas fa-calendar-alt"></i> Date Mode</label>
                <div class="cc-seg">
                    <input type="radio" name="dateMode" id="modeAll" value="all" checked>
                    <label for="modeAll">All Time</label>
                    <input type="radio" name="dateMode" id="modeSingle" value="single">
                    <label for="modeSingle">Single</label>
                    <input type="radio" name="dateMode" id="modeRange" value="range">
                    <label for="modeRange">Range</label>
                </div>
            </div>

            <div class="cc-field">
                <label class="cc-label"><i class="fas fa-columns"></i> Date Column</label>
                <div class="cc-seg">
                    <input type="radio" name="dateCol" id="colPayDate" value="PaymentDate" checked>
                    <label for="colPayDate">Payment Date</label>
                    <input type="radio" name="dateCol" id="colCreated" value="created_at">
                    <label for="colCreated">Created At</label>
                </div>
            </div>

            <div id="singleWrap" class="cc-field" style="display:none;">
                <label class="cc-label"><i class="fas fa-calendar-day"></i> Date</label>
                <input type="date" id="singleDate" class="cc-input" style="width:155px;height:36px;">
            </div>

            <div id="rangeWrap" style="display:none;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <div class="cc-field">
                    <label class="cc-label"><i class="fas fa-calendar"></i> From</label>
                    <input type="date" id="dateFrom" class="cc-input" style="width:150px;height:36px;">
                </div>
                <div class="cc-field">
                    <label class="cc-label"><i class="fas fa-calendar"></i> To</label>
                    <input type="date" id="dateTo" class="cc-input" style="width:150px;height:36px;">
                </div>
            </div>

            <div class="cc-hint">
                <i class="fas fa-info-circle"></i>
                Press <kbd>Enter</kbd> or click Load to apply
            </div>

        </div>

    </div>

</div>

{{-- =============================================
     TABLE PANEL
============================================= --}}
<div class="cc-panel cc-panel-cards">

    <div class="cc-panel-head">
        <div class="cc-panel-head-left">
            <span class="cc-panel-head-icon"><i class="fas fa-table"></i></span>
            <div>
                <div class="cc-panel-title">Payment Records</div>
                <div class="cc-panel-subtitle">Detailed payment history</div>
            </div>
        </div>
        <div class="cc-panel-head-right">
            <span class="cc-badge cc-badge-filter" id="tableInfo">—</span>
        </div>
    </div>

    {{-- Toolbar with selection and export --}}
    <div class="cc-toolbar">
        <div class="cc-toolbar-left">
            <button class="cc-btn cc-btn-outline cc-btn-sm" onclick="toggleSelectAll()">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <span class="cc-toolbar-count">
                Selected: <strong id="selectedCount">0</strong>
            </span>
        </div>
        <div class="cc-toolbar-right">
            <button class="cc-btn cc-btn-export" onclick="exportCSV()">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Table container --}}
    <div id="tableWrapper" style="overflow-x:auto; padding:0 0 0 0;">
        <table class="cc-table" id="paymentTable">
            <thead>
                <tr>
                    <th style="width:30px;"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"></th>
                    <th>Date</th>
                    <th>Bill No</th>
                    <th>Patient Name</th>
                    <th class="text-right">Total Bill</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                    <th style="width:60px;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8" class="cc-loader"><i class="fas fa-circle-notch fa-spin"></i> Loading payment records...</td></tr>
            </tbody>
        </table>
    </div>

    <div id="loadMoreWrap" style="display:none; padding:8px 16px 16px;">
        <button id="loadMoreBtn" class="cc-btn cc-btn-load" onclick="loadMore()">
            <i class="fas fa-chevron-down"></i> Load More
        </button>
    </div>

</div>

{{-- =============================================
     BOTTOM NAVIGATION
============================================= --}}
<div class="cc-bottom-spacer"></div>
<nav class="cc-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('billing.paylist.index') }}" class="cc-nav-item cc-nav-active">
        <i class="fas fa-receipt"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('billing.duelist.index') }}" class="cc-nav-item">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Due List</span>
    </a>
    <a href="{{ route('billing.discharge.index') }}" class="cc-nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Discharge</span>
    </a>
    <a href="{{ route('billing.paynow.index') }}" class="cc-nav-item">
        <i class="fas fa-credit-card"></i>
        <span>Pay Now</span>
    </a>
</nav>

<iframe id="printFrame" style="display:none;"></iframe>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
/* ===========================================
   CLINICAL CLARITY DESIGN SYSTEM – COMPACT
=========================================== */
:root {
    --cc-bg:            #faf8ff;
    --cc-primary:       #0f52ba;
    --cc-primary-dark:  #0b3d8a;
    --cc-primary-light: #e8edff;
    --cc-white:         #ffffff;
    --cc-border:        #d9d9e2;
    --cc-text:          #1a1a2e;
    --cc-muted:         #6b7280;
    --cc-green:         #10b981;
    --cc-green-bg:      #ecfdf5;
    --cc-red:           #ef4444;
    --cc-red-bg:        #fef2f2;
    --cc-amber:         #f59e0b;
    --cc-amber-bg:      #fffbeb;
    --cc-radius:        4px;
    --cc-font:          'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

* { box-sizing: border-box; }

body, .content-wrapper {
    background: var(--cc-bg) !important;
    font-family: var(--cc-font);
    color: var(--cc-text);
}

/* ===========================================
   PAGE TOP BAR
=========================================== */
.cc-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding-bottom: 6px;
}
.cc-topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.cc-topbar-icon {
    width: 36px; height: 36px;
    border-radius: var(--cc-radius);
    background: var(--cc-primary);
    color: #fff;
    font-size: 15px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cc-page-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0 0 2px;
    letter-spacing: -.2px;
}
.cc-breadcrumb {
    list-style: none;
    padding: 0; margin: 0;
    display: flex; gap: 4px; align-items: center;
    font-size: 11px; color: var(--cc-muted);
}
.cc-breadcrumb li + li::before { content: '/'; margin-right: 4px; color: var(--cc-border); }
.cc-breadcrumb a { color: var(--cc-muted); text-decoration: none; }
.cc-breadcrumb a:hover { color: var(--cc-primary); }
.cc-breadcrumb .active { color: var(--cc-primary); font-weight: 600; }
.cc-topbar-actions { display: flex; gap: 8px; align-items: center; }

/* ===========================================
   BUTTONS
=========================================== */
.cc-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 6px 12px;
    font-size: 12px; font-weight: 600;
    font-family: var(--cc-font);
    cursor: pointer;
    transition: all .15s;
    line-height: 1;
    white-space: nowrap;
    background: var(--cc-white);
    color: var(--cc-text);
}
.cc-btn:hover { border-color: var(--cc-primary); color: var(--cc-primary); }
.cc-btn-primary {
    background: var(--cc-primary);
    border-color: var(--cc-primary);
    color: #fff;
}
.cc-btn-primary:hover { background: var(--cc-primary-dark); border-color: var(--cc-primary-dark); color: #fff; }
.cc-btn-outline {
    background: var(--cc-white);
    border-color: var(--cc-border);
    color: var(--cc-text);
}
.cc-btn-outline:hover { background: var(--cc-primary-light); border-color: var(--cc-primary); color: var(--cc-primary); }
.cc-btn-reset { color: var(--cc-red); border-color: var(--cc-red); }
.cc-btn-reset:hover { background: var(--cc-red-bg); color: var(--cc-red); border-color: var(--cc-red); }
.cc-btn-export {
    background: var(--cc-green);
    border-color: var(--cc-green);
    color: #fff;
}
.cc-btn-export:hover { background: #059669; border-color: #059669; color: #fff; }
.cc-btn-sm { padding: 4px 10px; font-size: 11px; }
.cc-btn-load {
    width: 100%;
    justify-content: center;
    padding: 10px;
    font-size: 13px;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    background: var(--cc-white);
    color: var(--cc-primary);
    cursor: pointer;
    transition: all .15s;
}
.cc-btn-load:hover { background: var(--cc-primary-light); border-color: var(--cc-primary); }
.cc-btn-load:disabled { color: var(--cc-muted); cursor: default; opacity: .6; }
.cc-btn-load:disabled:hover { background: var(--cc-white); border-color: var(--cc-border); }

/* ===========================================
   STAT CARDS
=========================================== */
.cc-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 14px;
}
@media(max-width: 900px) { .cc-stats { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 480px) { .cc-stats { grid-template-columns: 1fr 1fr; gap: 6px; } }

.cc-stat {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 10px 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.cc-stat-icon {
    width: 32px; height: 32px;
    border-radius: var(--cc-radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.cc-stat-icon-slate   { background: #f0f1f5; color: var(--cc-muted); }
.cc-stat-icon-primary { background: var(--cc-primary-light); color: var(--cc-primary); }
.cc-stat-icon-green   { background: var(--cc-green-bg); color: var(--cc-green); }
.cc-stat-icon-red     { background: var(--cc-red-bg); color: var(--cc-red); }

.cc-stat-body { flex: 1; min-width: 0; }
.cc-stat-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: var(--cc-muted);
    margin-bottom: 2px;
}
.cc-stat-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--cc-text);
    line-height: 1;
}
.cc-stat-value-green { color: var(--cc-green); }
.cc-stat-value-red   { color: var(--cc-red); }
.cc-stat-sub {
    font-size: 10px;
    color: var(--cc-muted);
    margin-top: 3px;
}

/* ===========================================
   PANELS
=========================================== */
.cc-panel {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    margin-bottom: 14px;
    overflow: hidden;
}

.cc-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 14px;
    border-bottom: 1px solid var(--cc-border);
    background: var(--cc-bg);
}
.cc-panel-head-left  { display: flex; align-items: center; gap: 8px; }
.cc-panel-head-right { display: flex; align-items: center; gap: 6px; }
.cc-panel-head-icon {
    width: 28px; height: 28px;
    border-radius: var(--cc-radius);
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    font-size: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cc-panel-title    { font-size: 13px; font-weight: 700; color: var(--cc-text); }
.cc-panel-subtitle { font-size: 11px; color: var(--cc-muted); margin-top: 1px; }

.cc-badge {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: var(--cc-radius);
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.cc-badge-filter {
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    border: 1px solid var(--cc-primary);
}

/* ===========================================
   FILTER BODY
=========================================== */
.cc-filter-body { padding: 10px 14px; }

.cc-filter-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}
.cc-filter-row-divider {
    border-top: 1px solid var(--cc-border);
    padding-top: 10px;
    margin-top: 10px;
}

.cc-field { display: flex; flex-direction: column; gap: 4px; }
.cc-field-grow { flex: 1; min-width: 180px; }

.cc-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: var(--cc-primary);
    display: flex; align-items: center; gap: 4px;
}

.cc-input {
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 5px 8px;
    font-size: 12px;
    font-family: var(--cc-font);
    color: var(--cc-text);
    background: var(--cc-white);
    outline: none;
    transition: border-color .15s;
    height: 32px;
}
.cc-input:focus { border-color: var(--cc-primary); box-shadow: 0 0 0 2px rgba(15,82,186,.10); }

.cc-search-group {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 1;
    min-width: 240px;
}
.cc-search-group .cc-input { flex: 1; }

.cc-seg {
    display: flex;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    overflow: hidden;
}
.cc-seg input[type=radio] { display: none; }
.cc-seg label {
    flex: 1;
    text-align: center;
    padding: 4px 10px;
    font-size: 10.5px;
    font-weight: 600;
    cursor: pointer;
    background: var(--cc-white);
    color: var(--cc-muted);
    transition: all .12s;
    margin: 0;
    white-space: nowrap;
    user-select: none;
    border-right: 1px solid var(--cc-border);
    line-height: 1.4;
}
.cc-seg label:last-of-type { border-right: none; }
.cc-seg input[type=radio]:checked + label {
    background: var(--cc-primary);
    color: #fff;
}

.cc-hint {
    font-size: 10px;
    color: var(--cc-muted);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}
.cc-hint kbd {
    background: #f0f1f5;
    border: 1px solid var(--cc-border);
    border-radius: 3px;
    padding: 1px 4px;
    font-size: 9px;
    color: var(--cc-text);
}

/* ===========================================
   TOOLBAR
=========================================== */
.cc-toolbar {
    background: var(--cc-bg);
    border-bottom: 1px solid var(--cc-border);
    padding: 6px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
}
.cc-toolbar-left,
.cc-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.cc-toolbar-count {
    font-size: 11.5px;
    color: var(--cc-muted);
}
.cc-toolbar-count strong {
    color: var(--cc-primary);
    font-size: 12px;
}

/* ===========================================
   TABLE – COMPACT
=========================================== */
.cc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}
.cc-table thead th {
    background: #f0f1f5;
    color: var(--cc-text);
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding: 6px 6px;
    border-bottom: 2px solid var(--cc-border);
    white-space: nowrap;
    text-align: left;
}
.cc-table thead th.text-right { text-align: right; }
.cc-table thead th.text-center { text-align: center; }
.cc-table tbody td {
    padding: 5px 6px;
    border-bottom: 1px solid #eef0f2;
    vertical-align: middle;
}
.cc-table tbody tr:hover { background: var(--cc-primary-light); }
.cc-table tbody tr.selected-row { background: #d4f5f2; }

.cc-table .text-right { text-align: right; }
.cc-table .text-center { text-align: center; }
.cc-table .text-green { color: var(--cc-green); font-weight: 600; }
.cc-table .text-red   { color: var(--cc-red); font-weight: 600; }

.cc-table .cc-loader {
    text-align: center;
    padding: 30px 0;
    color: var(--cc-primary);
    font-size: 12px;
}
.cc-table .cc-loader i { font-size: 1.4rem; display: block; margin-bottom: 6px; }

.cc-table .cc-empty {
    text-align: center;
    padding: 30px 0;
    color: var(--cc-muted);
}
.cc-table .cc-empty i { font-size: 1.6rem; display: block; margin-bottom: 6px; color: var(--cc-border); }

/* Action buttons inside table */
.cc-action-btn {
    background: transparent;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 2px 5px;
    font-size: 10px;
    cursor: pointer;
    color: var(--cc-muted);
    transition: all .12s;
    font-family: var(--cc-font);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.cc-action-btn:hover { border-color: var(--cc-primary); color: var(--cc-primary); background: var(--cc-primary-light); }
.cc-action-btn-print { color: var(--cc-primary); border-color: var(--cc-primary); background: var(--cc-primary-light); }
.cc-action-btn-print:hover { background: var(--cc-primary); color: #fff; }

/* ===========================================
   LOAD MORE
=========================================== */
#loadMoreWrap {
    padding: 6px 14px 14px;
}

/* ===========================================
   BOTTOM NAV
=========================================== */
.cc-bottom-spacer { height: 56px; }
.cc-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 50px;
    background: var(--cc-white);
    border-top: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 0 4px;
}
.cc-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1px;
    padding: 4px 6px;
    text-decoration: none;
    color: var(--cc-muted);
    font-size: 9px;
    font-weight: 600;
    transition: color .12s;
    min-width: 44px;
}
.cc-nav-item i {
    font-size: 16px;
    transition: color .12s;
}
.cc-nav-item:hover,
.cc-nav-item:hover i {
    color: var(--cc-primary);
    text-decoration: none;
}
.cc-nav-active,
.cc-nav-active i {
    color: var(--cc-primary);
}

/* ===========================================
   SELECT2 OVERRIDE
=========================================== */
.select2-container--bootstrap4 .select2-selection {
    height: 32px !important;
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    background: var(--cc-white) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: 12px !important;
    color: var(--cc-text) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { height: 30px !important; }
.select2-container--bootstrap4 .select2-selection:focus,
.select2-container--bootstrap4 .select2-container--focus .select2-selection {
    border-color: var(--cc-primary) !important;
    box-shadow: 0 0 0 2px rgba(15,82,186,.10) !important;
}

/* ===========================================
   PRINT
=========================================== */
@media print {
    .cc-bottom-nav, .cc-bottom-spacer, .cc-topbar-actions,
    .cc-panel-head, .cc-filter-body, .cc-stats,
    .cc-toolbar, #loadMoreWrap, .content-header,
    .main-header, .main-sidebar { display: none !important; }
    .cc-table { font-size: 10px; }
    .cc-table td, .cc-table th { padding: 3px 4px; }
}

/* ===========================================
   RESPONSIVE
=========================================== */
@media (max-width: 768px) {
    .cc-filter-row { flex-direction: column; align-items: stretch; }
    .cc-search-group { min-width: 100%; }
    .cc-field-grow { min-width: 100%; }
    .cc-seg { flex-wrap: wrap; }
    .cc-seg label { flex: 1 1 auto; border-bottom: 1px solid var(--cc-border); }
    .cc-table { font-size: 10px; }
    .cc-table thead th, .cc-table tbody td { padding: 4px 4px; }
}
@media (max-width: 576px) {
    .cc-stats { grid-template-columns: 1fr 1fr; gap: 4px; }
    .cc-stat { padding: 6px 8px; }
    .cc-stat-value { font-size: 14px; }
    .cc-panel-head { flex-direction: column; align-items: flex-start; }
    .cc-toolbar { flex-direction: column; align-items: stretch; }
    .cc-toolbar-left, .cc-toolbar-right { justify-content: center; }
    .cc-table thead { display: none; }
    .cc-table tbody tr { display: block; border: 1px solid var(--cc-border); margin-bottom: 6px; border-radius: var(--cc-radius); padding: 6px; }
    .cc-table tbody td { display: block; text-align: left !important; border: none; padding: 2px 4px; }
    .cc-table tbody td::before { content: attr(data-label); font-weight: 700; margin-right: 6px; color: var(--cc-muted); font-size: 9px; text-transform: uppercase; }
    .cc-table tbody td:first-child { display: flex; align-items: center; }
    .cc-table tbody td:first-child input { margin-right: 6px; }
    .cc-table tbody td:last-child { text-align: center; }
}
</style>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/* ===========================================
   PAYLIST JS – TABLE VERSION (COMPACT)
=========================================== */
const ROUTES = {
    list:    '{{ route("billing.paylist.list") }}',
    summary: '{{ route("billing.paylist.summary") }}',
    search:  '{{ route("billing.invoice.searchPatient") }}',
    print:   '{{ route("billing.invoice.print", ":id") }}',
};

let currentPage = 1;
let lastPage    = 1;
let selectedIds = new Set();
let allFlatRows = [];
let allSelected = false;
let isLoading   = false;

const today = new Date().toISOString().split('T')[0];
document.getElementById('singleDate').value = today;
document.getElementById('dateFrom').value   = today;
document.getElementById('dateTo').value     = today;

/* -- Select2 -- */
$('#patientSelect').select2({
    theme: 'bootstrap4',
    placeholder: 'All Patients',
    allowClear: true,
    ajax: {
        url: ROUTES.search,
        dataType: 'json',
        delay: 300,
        data: params => ({ q: params.term }),
        processResults: data => ({
            results: data.map(p => ({
                id:   p.id,
                text: p.patientname + ' (' + p.patientcode + ')',
            }))
        }),
        cache: true,
    },
    minimumInputLength: 1,
});

$('#patientSelect').on('change', function () {
    const pid = $(this).val();
    document.querySelectorAll('[name=dateMode]').forEach(r => r.disabled = !!pid);
    const op = pid ? '.4' : '1';
    document.getElementById('singleWrap').style.opacity = op;
    document.getElementById('rangeWrap').style.opacity  = op;
    loadData(1);
});

/* -- Date Mode -- */
document.querySelectorAll('[name=dateMode]').forEach(r => {
    r.addEventListener('change', function () {
        document.getElementById('rangeWrap').style.display  = this.value === 'range'  ? 'flex' : 'none';
        document.getElementById('singleWrap').style.display = this.value === 'single' ? 'flex' : 'none';
    });
});

/* -- Params -- */
function getParams(page) {
    const pid      = $('#patientSelect').val();
    const dateMode = document.querySelector('[name=dateMode]:checked').value;
    const dateCol  = document.querySelector('[name=dateCol]:checked').value;

    const params = new URLSearchParams({
        page:        page,
        per_page:    document.getElementById('perPage').value,
        q:           document.getElementById('searchQ').value.trim(),
        date_column: dateCol,
    });

    if (pid) {
        params.append('patient_id', pid);
        params.append('date_mode', 'all');
        updateFilterBadge('all', true);
    } else if (dateMode === 'range') {
        params.append('date_mode', 'range');
        params.append('date_from', document.getElementById('dateFrom').value);
        params.append('date_to',   document.getElementById('dateTo').value);
        updateFilterBadge('range');
    } else if (dateMode === 'single') {
        params.append('date_mode', 'single');
        params.append('single_date', document.getElementById('singleDate').value);
        updateFilterBadge('single');
    } else {
        params.append('date_mode', 'all');
        updateFilterBadge('all');
    }

    return params;
}

function updateFilterBadge(mode, hasPatient) {
    const badge = document.getElementById('filterBadge');
    if (hasPatient) {
        badge.innerHTML = '<i class="fas fa-user"></i> Patient - All Time';
    } else if (mode === 'range') {
        const f = document.getElementById('dateFrom').value;
        const t = document.getElementById('dateTo').value;
        badge.innerHTML = '<i class="fas fa-calendar-week"></i> ' + f + ' - ' + t;
    } else if (mode === 'all') {
        badge.innerHTML = '<i class="fas fa-infinity"></i> All Time';
    } else {
        const sd = document.getElementById('singleDate').value;
        badge.innerHTML = '<i class="fas fa-calendar-day"></i> ' + sd;
    }
}

/* -- Load -- */
function loadData(page, append) {
    if (isLoading) return;
    isLoading = true;
    currentPage = page;

    if (!append) {
        selectedIds.clear();
        allSelected = false;
        updateSelectUI();
        document.getElementById('tableBody').innerHTML =
            '<tr><td colspan="8" class="cc-loader"><i class="fas fa-circle-notch fa-spin"></i> Loading payment records...</td></tr>';
        document.getElementById('loadMoreWrap').style.display = 'none';
    } else {
        document.getElementById('loadMoreBtn').textContent = 'Loading...';
        document.getElementById('loadMoreBtn').disabled = true;
    }

    const params = getParams(page);

    if (!append) {
        fetch(ROUTES.summary + '?' + params.toString())
            .then(r => r.json()).then(renderSummary).catch(function () {});
    }

    fetch(ROUTES.list + '?' + params.toString())
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (append) {
                allFlatRows = allFlatRows.concat(res.flat || []);
            } else {
                allFlatRows = res.flat || [];
            }
            renderTable(res.flat || [], res.meta, append);
            isLoading = false;
        })
        .catch(function () {
            if (!append) {
                document.getElementById('tableBody').innerHTML =
                    '<tr><td colspan="8" class="cc-empty"><i class="fas fa-exclamation-triangle"></i><p>Error loading data.</p></td></tr>';
            }
            isLoading = false;
        });
}

/* -- Summary -- */
function renderSummary(s) {
    document.getElementById('sumInvoices').textContent = s.total_invoices.toLocaleString();
    document.getElementById('sumPatients').textContent = s.total_patients + ' patient(s)';
    document.getElementById('sumBill').textContent     = '৳ ' + fmt(s.total_bill);
    document.getElementById('sumPaid').textContent     = '৳ ' + fmt(s.total_paid);
    document.getElementById('sumDue').textContent      = '৳ ' + fmt(s.total_due);
}

/* -- Status helpers -- */
function getStatus(r) {
    var total = parseFloat(r.TotalBill || 0);
    var paid  = parseFloat(r.PaidAmount || 0);
    var due   = parseFloat(r.DueAmount || 0);
    if (total > 0 && due === 0) return { label: 'Paid',  cls: 'green' };
    if (due > 0 && paid > 0)    return { label: 'Partial', cls: 'amber' };
    if (due > 0)                return { label: 'Due',    cls: 'red' };
    return { label: 'Pending', cls: 'amber' };
}

/* -- Render Table -- */
function renderTable(rows, meta, append) {
    var tbody = document.getElementById('tableBody');

    if (!rows.length && !append) {
        tbody.innerHTML = '<tr><td colspan="8" class="cc-empty"><i class="fas fa-inbox"></i><p>No payment records found.</p></td></tr>';
        document.getElementById('tableInfo').textContent = 'No records';
        lastPage = meta.last_page;
        document.getElementById('loadMoreWrap').style.display = 'none';
        return;
    }

    document.getElementById('tableInfo').textContent =
        'Showing ' + (meta.from || 0) + '-' + (meta.to || 0) + ' of ' + meta.total + ' records';

    var html = '';
    rows.forEach(function (r) {
        var total  = parseFloat(r.TotalBill  || 0);
        var paid   = parseFloat(r.PaidAmount || 0);
        var due    = parseFloat(r.DueAmount  || 0);
        var status = getStatus(r);
        var date   = r.PaymentDate || r.created_at || '---';
        var checked = selectedIds.has(r.ID) ? 'checked' : '';
        var rowClass = selectedIds.has(r.ID) ? 'selected-row' : '';

        html += '<tr class="' + rowClass + '" data-id="' + r.ID + '">';
        html += '<td><input type="checkbox" class="row-cb" data-id="' + r.ID + '" ' + checked + ' onchange="onRowCheck(this)"></td>';
        html += '<td>' + esc(date) + '</td>';
        html += '<td><strong>' + esc(r.BillNo || '---') + '</strong></td>';
        html += '<td>' + esc(r.PatientName || '---') + '</td>';
        html += '<td class="text-right">৳ ' + fmt(total) + '</td>';
        html += '<td class="text-right text-green">৳ ' + fmt(paid) + '</td>';
        html += '<td class="text-right text-red">৳ ' + fmt(due) + '</td>';
        html += '<td class="text-center">';
        html += '<button class="cc-action-btn cc-action-btn-print" onclick="window.open(\'' + ROUTES.print.replace(':id', r.ID) + '/print\',\'_blank\')" title="Print"><i class="fas fa-print"></i></button>';
        html += '</td>';
        html += '</tr>';
    });

    if (!append) {
        tbody.innerHTML = html;
    } else {
        tbody.insertAdjacentHTML('beforeend', html);
    }

    lastPage = meta.last_page;
    var wrap = document.getElementById('loadMoreWrap');
    if (currentPage < lastPage) {
        wrap.style.display = 'block';
        document.getElementById('loadMoreBtn').innerHTML = '<i class="fas fa-chevron-down"></i> Load More';
        document.getElementById('loadMoreBtn').disabled = false;
    } else {
        wrap.style.display = 'block';
        document.getElementById('loadMoreBtn').innerHTML = '<i class="fas fa-check"></i> All ' + meta.total + ' records loaded';
        document.getElementById('loadMoreBtn').disabled = true;
    }
}

/* -- Load More -- */
function loadMore() {
    if (isLoading || currentPage >= lastPage) return;
    loadData(currentPage + 1, true);
}

/* -- Checkbox & Selection -- */
function onRowCheck(cb) {
    var id = parseInt(cb.dataset.id);
    cb.checked ? selectedIds.add(id) : selectedIds.delete(id);
    updateSelectUI();
    // Highlight row
    var tr = cb.closest('tr');
    if (tr) tr.classList.toggle('selected-row', cb.checked);
}

function toggleSelectAll() {
    allSelected = !allSelected;
    var checkboxes = document.querySelectorAll('.row-cb');
    checkboxes.forEach(function (cb) {
        cb.checked = allSelected;
        var id = parseInt(cb.dataset.id);
        allSelected ? selectedIds.add(id) : selectedIds.delete(id);
        var tr = cb.closest('tr');
        if (tr) tr.classList.toggle('selected-row', allSelected);
    });
    document.getElementById('selectAllCheckbox').checked = allSelected;
    updateSelectUI();
}

function updateSelectUI() {
    document.getElementById('selectedCount').textContent = selectedIds.size;
    document.getElementById('btnBulkPrint').style.display = selectedIds.size > 0 ? 'inline-flex' : 'none';
}

/* -- Bulk Print -- */
function doBulkPrint() {
    if (!selectedIds.size) return;
    var ids  = Array.from(selectedIds);
    var rows = allFlatRows.filter(function (r) { return ids.includes(r.ID); });

    var html = '<html><head><style>';
    html += 'body{font-family:\'Hanken Grotesk\',system-ui,sans-serif;font-size:11px;color:#1a1a2e;}';
    html += '.inv-block{border:1px solid #d9d9e2;margin-bottom:18px;padding:12px;border-radius:4px;page-break-inside:avoid;}';
    html += '.inv-title{font-size:12px;font-weight:700;margin-bottom:8px;color:#0f52ba;padding-bottom:4px;border-bottom:1px solid #d9d9e2;}';
    html += 'table{width:100%;border-collapse:collapse;margin-top:6px;}';
    html += 'th,td{border:1px solid #e8eaef;padding:4px 8px;text-align:left;font-size:10px;}';
    html += 'th{background:#f0f1f5;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:#0f52ba;font-size:9px;}';
    html += '.amount{font-weight:700;}';
    html += '</style></head><body>';

    rows.forEach(function (r) {
        html += '<div class="inv-block">';
        html += '<div class="inv-title">Invoice: ' + esc(r.BillNo || '---') + '</div>';
        html += '<table>';
        html += '<tr><th>Patient</th><td>' + esc(r.PatientName) + ' (' + esc(r.PatientCode) + ')</td><th>Mobile</th><td>' + esc(r.MobileNo || '---') + '</td></tr>';
        html += '<tr><th>Total Bill</th><td class="amount">৳ ' + fmt(r.TotalBill) + '</td><th>Paid</th><td class="amount">৳ ' + fmt(r.PaidAmount) + '</td></tr>';
        html += '<tr><th>Due</th><td class="amount">৳ ' + fmt(r.DueAmount) + '</td><th>Date</th><td>' + (r.PaymentDate || r.created_at || '---') + '</td></tr>';
        html += '</table>';
        html += '</div>';
    });

    html += '</body></html>';

    var frame = document.getElementById('printFrame');
    frame.style.display = 'block';
    frame.contentDocument.open();
    frame.contentDocument.write(html);
    frame.contentDocument.close();
    setTimeout(function () {
        frame.contentWindow.print();
        frame.style.display = 'none';
    }, 400);
}

/* -- Export CSV -- */
function exportCSV() {
    if (!allFlatRows.length) { toastr.warning('No data to export.'); return; }
    var data = allFlatRows.map(function (r, idx) {
        return {
            '#'          : idx + 1,
            'Date'       : r.PaymentDate || r.created_at || '',
            'Bill No'    : r.BillNo || '',
            'Patient'    : r.PatientName || '',
            'Code'       : r.PatientCode || '',
            'Mobile'     : r.MobileNo || '',
            'Total Bill' : parseFloat(r.TotalBill || 0),
            'Paid'       : parseFloat(r.PaidAmount || 0),
            'Due'        : parseFloat(r.DueAmount || 0),
        };
    });
    var heads = Object.keys(data[0]);
    var csv = [heads.join(','), ...data.map(row =>
        heads.map(h => '"' + String(row[h]).replace(/"/g, '""') + '"').join(',')
    )].join('\r\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url; a.download = 'PaymentRecords_' + new Date().toISOString().slice(0,10) + '.csv'; a.click();
    URL.revokeObjectURL(url);
    toastr.success('CSV exported!');
}

/* -- Reset -- */
function resetFilter() {
    $('#patientSelect').val(null).trigger('change');
    document.getElementById('searchQ').value       = '';
    document.getElementById('singleDate').value    = today;
    document.getElementById('dateFrom').value      = today;
    document.getElementById('dateTo').value        = today;
    document.querySelector('#modeAll').checked     = true;
    document.querySelector('#colPayDate').checked  = true;
    document.getElementById('rangeWrap').style.display  = 'none';
    document.getElementById('singleWrap').style.display = 'none';
    document.querySelectorAll('[name=dateMode]').forEach(function (r) { r.disabled = false; });
    document.getElementById('singleWrap').style.opacity = '1';
    document.getElementById('rangeWrap').style.opacity  = '1';
    updateFilterBadge('all');
    loadData(1);
}

/* -- Helpers -- */
function fmt(v) {
    return parseFloat(v || 0).toLocaleString('en-BD', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function formatDate(d) {
    if (!d) return '---';
    return new Date(d).toLocaleDateString('en-BD', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
}

function esc(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

/* -- Init -- */
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('singleWrap').style.display = 'none';
    document.getElementById('rangeWrap').style.display  = 'none';
    updateFilterBadge('all');
    loadData(1);
});

// Include toastr for notifications (if not already)
if (typeof toastr === 'undefined') {
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
    document.head.appendChild(script);
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
    document.head.appendChild(link);
}
</script>
@endpush