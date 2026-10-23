@extends('adminlte::page')

@section('title', 'Billing — Payment')

@section('content_header')
<div class="cc-header-bar">
    <h1 class="cc-page-title">Billing — Payment</h1>
    <ol class="cc-breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="cc-bc-active">Payment</li>
    </ol>
</div>
@stop

@section('content')

<div class="cc-page">

    {{-- LEFT COLUMN -- Pay Due Amount Form --}}
    <div class="cc-left-col">

        <div class="cc-card">
            <div class="cc-card-head">
                <i class="fas fa-credit-card cc-head-icon"></i>
                Pay Due Amount
            </div>
            <div class="cc-card-body">

                {{-- Invoice Search --}}
                <div class="cc-field">
                    <label class="cc-label">Search Invoice</label>
                    <select id="invoiceSelect" style="width:100%;">
                        <option value="">— Type bill no / name / mobile —</option>
                    </select>
                </div>

                {{-- Invoice Info Box --}}
                <div id="invoiceInfoBox" class="cc-info-box" style="display:none;">
                    <div class="cc-info-top">
                        <div>
                            <span class="cc-info-badge" id="infoBillNo">—</span>
                            <strong class="cc-info-name" id="infoPatientName">—</strong>
                            <span class="cc-info-type" id="infoTypeBadge"></span>
                        </div>
                        <button type="button" id="btnClearInvoice" class="cc-btn cc-btn-clear">&times; Clear</button>
                    </div>
                    <div class="cc-info-stats">
                        <div class="cc-info-stat">
                            <span class="cc-info-stat-label">Total Bill</span>
                            <span class="cc-info-stat-val" id="infoTotal">&#2547; 0</span>
                        </div>
                        <div class="cc-info-stat cc-info-stat-paid">
                            <span class="cc-info-stat-label">Paid</span>
                            <span class="cc-info-stat-val cc-green" id="infoPaid">&#2547; 0</span>
                        </div>
                        <div class="cc-info-stat cc-info-stat-due">
                            <span class="cc-info-stat-label">Due</span>
                            <span class="cc-info-stat-val cc-amber" id="infoDue">&#2547; 0</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="selectedInvoiceId">

                <div id="invoiceRequiredNotice" class="cc-notice cc-notice-warn">
                    <i class="fas fa-exclamation-triangle"></i>
                    Please select an invoice with due amount.
                </div>

                {{-- Payment Fields --}}
                <div id="paymentFields" class="cc-pay-fields" style="display:none;">
                    <div class="cc-field">
                        <label class="cc-label">Paying Amount (&#2547;)</label>
                        <input type="number" id="payingAmountInput" class="cc-input" placeholder="0" min="1" step="1">
                        <div id="payingError" class="cc-field-error" style="display:none;"></div>
                    </div>

                    <div class="cc-field">
                        <label class="cc-label">Payment Date</label>
                        <input type="date" id="payDateInput" class="cc-input" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="cc-field">
                        <label class="cc-label">Pay Method</label>
                        <select id="payMethodInput" class="cc-input">
                            <option value="cash">Cash</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="cc-field">
                        <label class="cc-label">Collected By</label>
                        <input type="text" id="collectedByInput" class="cc-input" placeholder="Staff name">
                    </div>
                </div>

                <button type="button" id="btnPayNow" disabled class="cc-btn cc-btn-primary cc-btn-block">
                    Confirm Payment
                </button>

            </div>
        </div>

    </div>{{-- /LEFT COLUMN --}}


    {{-- RIGHT COLUMN -- Summary + List --}}
    <div class="cc-right-col">

        {{-- Summary Card --}}
        <div class="cc-card cc-card-summary">
            <div class="cc-summary-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="cc-summary-body">
                <div class="cc-summary-label">Total Due</div>
                <div class="cc-summary-value" id="sumTotalDue">&#2547; 0</div>
                <div class="cc-summary-count" id="sumTotalCount">0 invoices</div>
            </div>
        </div>

        {{-- Export Toolbar --}}
        <div class="cc-export-row">
            <button id="btnExportExcel" class="cc-btn cc-btn-export cc-btn-excel">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button id="btnExportCsv" class="cc-btn cc-btn-export cc-btn-csv">
                <i class="fas fa-file-csv"></i> CSV
            </button>
            <button id="btnPrintList" class="cc-btn cc-btn-export cc-btn-print">
                <i class="fas fa-print"></i> Print
            </button>
            <span id="activeTabDueSummary" class="cc-due-summary"></span>
        </div>

        {{-- Search --}}
        <div class="cc-search-row">
            <input type="text" id="listSearchInput" class="cc-input" placeholder="Search name / bill no / mobile...">
            <label class="cc-toggle-label">
                <input type="checkbox" id="showAllChk">
                <span class="cc-toggle-text">Show Paid Also</span>
            </label>
        </div>

        {{-- Card List --}}
        <div id="dueCardList" class="cc-card-list"></div>

        {{-- Load More --}}
        <div id="loadMoreWrap" class="cc-load-more-wrap" style="display:none;">
            <button id="loadMoreBtn" class="cc-btn cc-btn-load-more">Load More</button>
        </div>

    </div>{{-- /RIGHT COLUMN --}}

</div>{{-- /cc-page --}}

{{-- Bottom Navigation --}}
<nav class="cc-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('billing.payment.index') }}" class="cc-nav-item cc-nav-active">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('billing.duelist.index') }}" class="cc-nav-item">
        <i class="fas fa-file-invoice"></i>
        <span>Due List</span>
    </a>
    <a href="{{ route('billing.discharge.index') }}" class="cc-nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Discharge</span>
    </a>
    <a href="{{ route('billing.paynow.index') }}" class="cc-nav-item">
        <i class="fas fa-hand-holding-usd"></i>
        <span>Pay Now</span>
    </a>
</nav>
<div class="cc-bottom-spacer"></div>

{{-- Give Discount Modal --}}
<div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">Give Discount</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <input type="hidden" id="discInvoiceId">

                <div class="cc-info-box">
                    <div class="cc-info-top">
                        <div>
                            <span class="cc-info-badge" id="discBillNo">—</span>
                            <strong class="cc-info-name" id="discPatientName">—</strong>
                        </div>
                        <button type="button" id="btnViewDiscHistory" class="cc-btn cc-btn-history">
                            <i class="fas fa-history"></i> History
                        </button>
                    </div>
                    <div class="cc-info-stats">
                        <div class="cc-info-stat cc-info-stat-due">
                            <span class="cc-info-stat-label">Current Due</span>
                            <span class="cc-info-stat-val cc-amber" id="discCurrentDue">&#2547; 0</span>
                        </div>
                    </div>
                </div>

                <div id="discHistoryBox" style="display:none;"></div>

                <div class="cc-field">
                    <label class="cc-label">Discount Amount (&#2547;)</label>
                    <input type="number" id="discAmountInput" class="cc-input" placeholder="0" min="1" step="1">
                    <div id="discAmountError" class="cc-field-error" style="display:none;"></div>
                </div>

                <div class="cc-field">
                    <label class="cc-label">Reference</label>
                    <select id="discReferenceSelect" class="cc-input" style="width:100%;">
                        <option value="">— Select Reference —</option>
                    </select>
                </div>

                <div class="cc-field">
                    <label class="cc-label">Approved By</label>
                    <select id="discApprovedBySelect" class="cc-input" style="width:100%;">
                        <option value="">— Select Approver —</option>
                    </select>
                </div>

                <div class="cc-field">
                    <label class="cc-label">Note (optional)</label>
                    <textarea id="discNoteInput" class="cc-input" rows="2" style="height:auto;resize:vertical;"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="cc-btn cc-btn-outline" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnSubmitDiscount" class="cc-btn cc-btn-discount">Give Discount</button>
            </div>
        </div>
    </div>
</div>

@stop


@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
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
    --cc-purple: #7c3aed;
    --cc-purple-dark: #6d28d9;
    --cc-purple-bg: #f5f3ff;
    --cc-radius: 4px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

.content-wrapper {
    background: var(--cc-bg) !important;
    font-family: var(--cc-font);
    padding-bottom: 70px !important;
}

* { box-sizing: border-box; }

/* -- Header -- */
.cc-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 4px;
}
.cc-page-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--cc-text);
    margin: 0;
}
.cc-breadcrumb {
    list-style: none;
    display: flex;
    gap: 6px;
    margin: 0;
    padding: 0;
    font-size: 12px;
}
.cc-breadcrumb a { color: var(--cc-primary); text-decoration: none; }
.cc-breadcrumb a:hover { text-decoration: underline; }
.cc-bc-active { color: var(--cc-muted); }
.cc-breadcrumb li + li::before {
    content: '/';
    margin-right: 6px;
    color: #bbb;
}

/* -- Layout -- */
.cc-page {
    display: flex;
    gap: 16px;
    padding: 4px 0 14px;
    align-items: flex-start;
}
.cc-left-col {
    flex: 0 0 38%;
    min-width: 280px;
}
.cc-right-col {
    flex: 1;
    min-width: 240px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* -- Card -- */
.cc-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    overflow: hidden;
}
.cc-card-head {
    padding: 12px 16px;
    border-bottom: 1px solid var(--cc-border);
    font-size: 15px;
    font-weight: 600;
    color: var(--cc-text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.cc-head-icon { color: var(--cc-primary); font-size: 14px; }
.cc-card-body {
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* -- Fields -- */
.cc-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cc-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--cc-muted);
}
.cc-input {
    width: 100%;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 7px 10px;
    font-size: 13px;
    font-family: var(--cc-font);
    color: var(--cc-text);
    background: var(--cc-white);
    height: 34px;
    outline: none;
    transition: border-color .15s;
}
.cc-input:focus { border-color: var(--cc-primary); }
select.cc-input { appearance: auto; }
.cc-field-error {
    font-size: 11px; color: var(--cc-red); margin-top: 2px;
}

/* -- Buttons -- */
.cc-btn {
    border: none;
    border-radius: var(--cc-radius);
    cursor: pointer;
    font-family: var(--cc-font);
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    transition: background .15s, opacity .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.cc-btn-primary {
    background: var(--cc-primary);
    color: var(--cc-white);
}
.cc-btn-primary:hover { background: var(--cc-primary-dark); }
.cc-btn-primary:disabled {
    background: #a0b8d6 !important;
    cursor: not-allowed !important;
    opacity: .7;
}
.cc-btn-block { width: 100%; }
.cc-btn-clear {
    background: none;
    border: 1px solid var(--cc-red);
    color: var(--cc-red);
    font-size: 11px;
    padding: 2px 10px;
    border-radius: var(--cc-radius);
}
.cc-btn-clear:hover { background: var(--cc-red-bg); }
.cc-btn-history {
    background: none;
    border: 1px solid var(--cc-purple);
    color: var(--cc-purple);
    font-size: 11px;
    padding: 2px 10px;
    border-radius: var(--cc-radius);
}
.cc-btn-history:hover { background: var(--cc-purple-bg); }

/* -- Export buttons -- */
.cc-export-row {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}
.cc-btn-export {
    font-size: 12px;
    padding: 5px 12px;
    color: var(--cc-white);
    border-radius: var(--cc-radius);
}
.cc-btn-excel { background: #217346; }
.cc-btn-excel:hover { background: #1a5c38; }
.cc-btn-csv { background: #d97706; }
.cc-btn-csv:hover { background: #b45309; }
.cc-btn-print { background: var(--cc-primary); }
.cc-btn-print:hover { background: var(--cc-primary-dark); }
.cc-due-summary {
    margin-left: auto;
    font-size: 11px;
    color: var(--cc-amber);
    font-weight: 600;
    white-space: nowrap;
}

/* -- Search -- */
.cc-search-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.cc-search-row .cc-input {
    flex: 1;
    min-width: 160px;
    height: 34px;
}
.cc-toggle-label {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-size: 12px;
    color: var(--cc-text);
    white-space: nowrap;
}
.cc-toggle-label input[type="checkbox"] { cursor: pointer; }

/* -- Invoice Info Box -- */
.cc-info-box {
    background: var(--cc-primary-light);
    border: 1px solid #c5d5f0;
    border-radius: var(--cc-radius);
    padding: 10px 12px;
    font-size: 12px;
}
.cc-info-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 8px;
}
.cc-info-badge {
    background: var(--cc-primary);
    color: var(--cc-white);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}
.cc-info-name { margin-left: 6px; font-size: 13px; color: var(--cc-text); }
.cc-info-type {
    margin-left: 6px;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
}
.cc-info-stats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.cc-info-stat {
    flex: 1;
    min-width: 70px;
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 6px 8px;
    text-align: center;
}
.cc-info-stat-paid { border-color: #a7f3d0; }
.cc-info-stat-due { background: var(--cc-amber-bg); border-color: #fcd34d; }
.cc-info-stat-label { font-size: 10px; color: var(--cc-muted); }
.cc-info-stat-val { font-size: 14px; font-weight: 700; color: var(--cc-text); line-height: 1.3; }
.cc-green { color: var(--cc-green); }
.cc-amber { color: var(--cc-amber); }

/* -- Notice -- */
.cc-notice {
    padding: 8px 12px;
    border-radius: var(--cc-radius);
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cc-notice-warn {
    background: var(--cc-amber-bg);
    border: 1px solid #fcd34d;
    color: #92400e;
}

/* -- Payment fields wrapper -- */
.cc-pay-fields {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* -- Summary Card -- */
.cc-card-summary {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
}
.cc-summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.cc-summary-body { display: flex; flex-direction: column; gap: 2px; }
.cc-summary-label { font-size: 11px; color: var(--cc-muted); }
.cc-summary-value { font-size: 18px; font-weight: 700; color: var(--cc-text); line-height: 1.3; }
.cc-summary-count { font-size: 11px; color: var(--cc-muted); }

/* -- Card List -- */
.cc-card-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* -- Invoice Card -- */
.cc-inv-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cc-inv-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
}
.cc-inv-card-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}
.cc-inv-billno {
    font-weight: 600;
    font-size: 13px;
    color: var(--cc-text);
}
.cc-inv-name {
    font-size: 12px;
    color: var(--cc-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cc-inv-card-details { display: flex; flex-direction: column; gap: 4px; }
.cc-inv-card-amounts {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.cc-inv-amount { display: flex; flex-direction: column; gap: 1px; }
.cc-inv-amount-label { font-size: 10px; color: var(--cc-muted); text-transform: uppercase; letter-spacing: .3px; }
.cc-inv-amount-value { font-size: 14px; font-weight: 700; color: var(--cc-text); }
.cc-inv-amount-paid { color: var(--cc-green); }
.cc-inv-amount-due { color: var(--cc-amber); }
.cc-inv-card-meta {
    font-size: 11px;
    color: var(--cc-muted);
}
.cc-inv-card-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
    flex-wrap: wrap;
    padding-top: 6px;
    border-top: 1px solid #eee;
}

/* -- Status Badges (new Clinical Clarity) -- */
.cc-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 10px;
    white-space: nowrap;
    line-height: 1.5;
}
.cc-badge-paid { background: var(--cc-green-bg); color: var(--cc-green); }
.cc-badge-partial, .cc-badge-due { background: var(--cc-red-bg); color: var(--cc-red); }
.cc-badge-pending { background: var(--cc-amber-bg); color: var(--cc-amber); }
.cc-badge-confirmed { background: var(--cc-primary-light); color: var(--cc-primary); }

/* -- Action buttons in cards -- */
.cc-btn-sm {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: var(--cc-radius);
    font-weight: 600;
    cursor: pointer;
    border: none;
    font-family: var(--cc-font);
}
.cc-btn-pay {
    background: var(--cc-primary);
    color: var(--cc-white);
}
.cc-btn-pay:hover { background: var(--cc-primary-dark); }
.cc-btn-discount {
    background: var(--cc-purple);
    color: var(--cc-white);
}
.cc-btn-discount:hover { background: var(--cc-purple-dark); }
.cc-btn-outline {
    background: var(--cc-white);
    color: var(--cc-muted);
    border: 1px solid var(--cc-border);
}
.cc-btn-outline:hover { background: #f5f5f5; }

/* -- Load More -- */
.cc-load-more-wrap {
    text-align: center;
    padding: 4px 0;
}
.cc-btn-load-more {
    background: var(--cc-white);
    color: var(--cc-primary);
    border: 1px solid var(--cc-primary);
    border-radius: var(--cc-radius);
    padding: 8px 28px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: var(--cc-font);
}
.cc-btn-load-more:hover {
    background: var(--cc-primary-light);
}

/* -- Empty / Loading state -- */
.cc-list-state {
    text-align: center;
    color: var(--cc-muted);
    padding: 28px 16px;
    font-size: 12px;
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
}

/* -- Select2 overrides -- */
.select2-container--default .select2-selection--single {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    height: 34px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important;
    font-size: 13px !important;
    font-family: var(--cc-font) !important;
    padding-left: 10px !important;
    color: var(--cc-text) !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--cc-primary) !important;
    box-shadow: 0 0 0 2px rgba(15,82,186,.15) !important;
}
.select2-dropdown {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    font-size: 12px !important;
    font-family: var(--cc-font) !important;
    z-index: 99999 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: var(--cc-primary-light) !important;
    color: var(--cc-primary-dark) !important;
}
.select2-results__option { padding: 6px 10px !important; }

/* -- Discount Modal -- */
#discountModal .modal-content {
    font-family: var(--cc-font);
    border-radius: var(--cc-radius);
    border: none;
}
#discountModal .modal-header {
    border-bottom: 1px solid var(--cc-border);
    padding: 12px 16px;
}
#discountModal .modal-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--cc-text);
}
#discountModal .modal-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
#discountModal .modal-footer {
    border-top: 1px solid var(--cc-border);
    padding: 10px 16px;
}
#discHistoryBox {
    max-height: 160px;
    overflow-y: auto;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    background: #fafafa;
}

/* -- Bottom Nav -- */
.cc-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 56px;
    background: var(--cc-white);
    border-top: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 999;
    padding: 0 4px;
}
.cc-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    color: var(--cc-muted);
    text-decoration: none;
    font-size: 10px;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: var(--cc-radius);
    transition: color .15s;
    min-width: 0;
    flex: 1;
    text-align: center;
}
.cc-nav-item i { font-size: 16px; }
.cc-nav-item span { line-height: 1.2; }
.cc-nav-item:hover { color: var(--cc-primary); }
.cc-nav-active {
    color: var(--cc-primary);
    font-weight: 600;
}
.cc-bottom-spacer { height: 64px; }

/* -- Print styles -- */
@media print {
    .content-header, .main-sidebar, .main-header,
    .cc-bottom-nav, .cc-bottom-spacer,
    .cc-left-col, .cc-export-row, .cc-search-row,
    .cc-btn, .cc-load-more-wrap, .cc-btn-load-more,
    .btn-select-inv, .btn-print-inv, .btn-discount-inv,
    #discountModal { display: none !important; }
    body, .content-wrapper { background: #fff !important; }
    .cc-right-col { width: 100% !important; }
    .cc-inv-card { break-inside: avoid; page-break-inside: avoid; }
}

/* -- Responsive -- */
@media (max-width: 992px) {
    .cc-page { flex-direction: column; }
    .cc-left-col { flex: none; width: 100%; }
    .cc-right-col { width: 100%; }
}
@media (max-width: 600px) {
    .cc-summary-value { font-size: 16px; }
    .cc-export-row { gap: 4px; }
    .cc-btn-export { font-size: 11px; padding: 4px 10px; }
    .cc-due-summary { margin-left: 0; width: 100%; text-align: right; }
    .cc-inv-card { padding: 10px 12px; }
    .cc-inv-card-amounts { gap: 8px; }
}
</style>
@stop


@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
toastr.options = { positionClass:'toast-top-right', timeOut:3000, progressBar:true };
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
</script>

<script>
(function(){
'use strict';

// -- State ------------------------------------------------
let currentPage       = 1;
let searchTimer       = null;
let selectedInvoice   = null;
let allLoadedRows     = [];
let discountRefsLoaded = false;

// ----------------------------------------------------------
// 1. Invoice Select2 — search due invoices
// ----------------------------------------------------------
$('#invoiceSelect').select2({
    placeholder        : 'Type bill no / name / mobile...',
    allowClear         : true,
    minimumInputLength : 0,
    width              : '100%',
    dropdownParent     : $('#invoiceSelect').parent(),
    ajax: {
        url      : '{{ route("billing.invoice.list") }}',
        dataType : 'json',
        delay    : 250,
        data     : function(p){ return { q: p.term || '', per_page: 10, show_all: 1 }; },
        processResults: function(data){
            const due = (data.data || []).filter(i => parseFloat(i.DueAmount) > 0);
            return {
                results: due.map(function(inv){
                    return { id: inv.ID, text: inv.BillNo + ' — ' + inv.PatientName, data: inv };
                })
            };
        },
        cache: false,
    },
    templateResult: function(item){
        if (item.loading) return $('<span style="color:#0f52ba;font-size:12px;">Searching...</span>');
        if (!item.data)   return item.text;
        const inv = item.data;
        const due = parseFloat(inv.DueAmount) || 0;
        return $(
            `<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:2px 0;flex-wrap:wrap;">
                <div>
                    <span style="font-weight:600;color:#1a1a2e;font-size:12px;">${inv.BillNo}</span>
                    <span style="color:#6b7280;font-size:11px;margin-left:6px;">${inv.PatientName}</span>
                    <span style="background:#e8edff;color:#0f52ba;font-size:10px;font-weight:600;
                                 padding:1px 6px;border-radius:8px;margin-left:4px;">
                        ${inv.InvoiceType || ''}
                    </span>
                </div>
                <span style="background:#fef2f2;color:#ef4444;font-size:10px;font-weight:700;
                             padding:1px 7px;border-radius:10px;white-space:nowrap;">
                    Due &#2547;${parseInt(due).toLocaleString()}
                </span>
            </div>`
        );
    },
    templateSelection: function(item){
        if (!item.data) return item.text;
        return item.data.BillNo + ' — ' + item.data.PatientName;
    },
    language:{ noResults:()=>'No due invoices found', searching:()=>'Searching...' },
});

$('#invoiceSelect').on('select2:select', function(e){ selectInvoice(e.params.data.data); });
$('#invoiceSelect').on('select2:unselect select2:clear', clearInvoice);
$('#btnClearInvoice').on('click', function(){ $('#invoiceSelect').val(null).trigger('change'); });

// -- Select invoice --
function selectInvoice(inv){
    selectedInvoice = inv;
    $('#selectedInvoiceId').val(inv.ID);

    const net  = parseFloat(inv.TotalBill) - parseFloat(inv.Discount || 0);
    const paid = parseFloat(inv.PaidAmount) || 0;
    const due  = parseFloat(inv.DueAmount)  || 0;

    $('#infoBillNo').text(inv.BillNo);
    $('#infoPatientName').text(inv.PatientName);
    $('#infoTypeBadge').text(inv.InvoiceType || '').css({ background: '#e8edff', color: '#0f52ba' });
    $('#infoTotal').html('&#2547; ' + parseInt(net).toLocaleString());
    $('#infoPaid').html('&#2547; '  + parseInt(paid).toLocaleString());
    $('#infoDue').html('&#2547; '   + parseInt(due).toLocaleString());

    $('#invoiceInfoBox').slideDown(180);
    $('#invoiceRequiredNotice').hide();

    if (due > 0) {
        $('#payingAmountInput').val(parseInt(due)).attr('max', parseInt(due));
        $('#paymentFields').show().css({ display:'flex', 'flex-direction':'column', gap:'10px' });
        $('#btnPayNow').prop('disabled', false);
    } else {
        $('#paymentFields').hide();
        $('#btnPayNow').prop('disabled', true);
        toastr.info('This invoice is already fully paid.');
    }
}

// -- Clear invoice --
function clearInvoice(){
    selectedInvoice = null;
    $('#selectedInvoiceId').val('');
    $('#invoiceInfoBox').slideUp(180);
    $('#invoiceRequiredNotice').show();
    $('#paymentFields').hide();
    $('#btnPayNow').prop('disabled', true);
    $('#payingAmountInput').val('');
    $('#payingError').hide();
}

// ----------------------------------------------------------
// 2. Paying amount validation
// ----------------------------------------------------------
$('#payingAmountInput').on('input', function(){
    if (!selectedInvoice) return;
    const due    = parseFloat(selectedInvoice.DueAmount) || 0;
    const paying = parseFloat($(this).val()) || 0;
    if (paying > due) {
        $('#payingError').html('Cannot exceed due &#2547;' + parseInt(due).toLocaleString()).show();
        $('#btnPayNow').prop('disabled', true);
    } else if (paying <= 0) {
        $('#payingError').text('Enter a valid amount.').show();
        $('#btnPayNow').prop('disabled', true);
    } else {
        $('#payingError').hide();
        $('#btnPayNow').prop('disabled', false);
    }
});

// ----------------------------------------------------------
// 3. Confirm Payment AJAX
// ----------------------------------------------------------
$('#btnPayNow').on('click', function(){
    if (!selectedInvoice) return;
    const paying = parseFloat($('#payingAmountInput').val()) || 0;
    const due    = parseFloat(selectedInvoice.DueAmount) || 0;
    if (paying <= 0 || paying > due) { toastr.error('Invalid amount.'); return; }

    const payload = {
        invoice_id     : selectedInvoice.ID,
        paying_amount  : paying,
        payment_date   : $('#payDateInput').val(),
        payment_method : $('#payMethodInput').val(),
        collected_by   : $('#collectedByInput').val(),
    };

    const $btn = $(this);
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url         : '{{ route("billing.payment.store") }}',
        method      : 'POST',
        contentType : 'application/json',
        data        : JSON.stringify(payload),
        success: function(res){
            if (res.success) {
                toastr.success('Payment confirmed!');
                window.open('{{ url("Billing/payment") }}/' + res.invoice_id + '/print', '_blank');
                clearInvoice();
                $('#invoiceSelect').val(null).trigger('change');
                loadDueList();
                loadSummaryCards();
            } else {
                toastr.error(res.message || 'Failed.');
            }
        },
        error: function(xhr){
            toastr.error(xhr.responseJSON ? xhr.responseJSON.message : 'Server error.');
        },
        complete: function(){
            $btn.prop('disabled', false).text('Confirm Payment');
        }
    });
});

// ----------------------------------------------------------
// 4. Load Due List (renders cards, supports Load More)
// ----------------------------------------------------------
function loadDueList(page, append){
    page = page || 1;
    currentPage = page;

    const q       = $('#listSearchInput').val().trim();
    const showAll = $('#showAllChk').is(':checked') ? 1 : 0;
    const $list   = $('#dueCardList');

    if (!append) {
        $list.html('<div class="cc-list-state"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#loadMoreWrap').hide();
    }

    $.get('{{ route("billing.invoice.list") }}', {
        q,
        show_all : showAll,
        page,
        per_page : 20,
    }, function(res){
        const rows = res.data || [];
        const meta = res.meta || {};

        if (!append) {
            allLoadedRows = rows;
            $list.empty();
        } else {
            allLoadedRows = allLoadedRows.concat(rows);
        }

        if (!rows.length && !append) {
            $list.html('<div class="cc-list-state">No due invoices found.</div>');
            $('#activeTabDueSummary').text('');
            return;
        }

        let sumTotal = 0, sumPaid = 0, sumDue = 0;

        rows.forEach(function(inv, idx){
            const net    = parseFloat(inv.TotalBill) - parseFloat(inv.Discount || 0);
            const due    = parseFloat(inv.DueAmount)  || 0;
            const paid   = parseFloat(inv.PaidAmount) || 0;
            const status = (inv.Status || 'due').toLowerCase();
            sumTotal += net; sumPaid += paid; sumDue += due;

            const statusMap = {
                paid      : '<span class="cc-badge cc-badge-paid">Paid</span>',
                partial   : '<span class="cc-badge cc-badge-partial">Partial</span>',
                confirmed : '<span class="cc-badge cc-badge-confirmed">Confirmed</span>',
                due       : '<span class="cc-badge cc-badge-due">Due</span>',
            };
            const badge  = statusMap[status] || '<span class="cc-badge cc-badge-pending">' + (inv.Status || '') + '</span>';
            const invEsc = JSON.stringify(inv).replace(/'/g, '&#39;');

            const cardHtml = `
                <div class="cc-inv-card" data-id="${inv.ID}">
                    <div class="cc-inv-card-head">
                        <div class="cc-inv-card-info">
                        <span class="cc-inv-billno">${inv.BillNo}</span>
                        <span class="cc-inv-name">${inv.PatientName}</span>
                    </div>
                    ${parseInt(inv.pending_count || 0) > 0 ? '<span class="cc-badge cc-badge-pending" title="Invoice pending approval">Pending</span>' : ''}
                    ${badge}
                    </div>
                    <div class="cc-inv-card-details">
                        <div class="cc-inv-card-amounts">
                            <div class="cc-inv-amount">
                                <span class="cc-inv-amount-label">Total</span>
                                <span class="cc-inv-amount-value">&#2547; ${parseInt(net).toLocaleString()}</span>
                            </div>
                            <div class="cc-inv-amount">
                                <span class="cc-inv-amount-label">Paid</span>
                                <span class="cc-inv-amount-value cc-inv-amount-paid">&#2547; ${parseInt(paid).toLocaleString()}</span>
                            </div>
                            <div class="cc-inv-amount">
                                <span class="cc-inv-amount-label">Due</span>
                                <span class="cc-inv-amount-value cc-inv-amount-due">&#2547; ${parseInt(due).toLocaleString()}</span>
                            </div>
                        </div>
                        <div class="cc-inv-card-meta">
                            ${inv.PaymentDate || '—'}
                            ${inv.InvoiceType ? ' · ' + inv.InvoiceType : ''}
                            ${inv.MobileNo ? ' · ' + inv.MobileNo : ''}
                            ${inv.PatientCode ? ' · ' + inv.PatientCode : ''}
                        </div>
                    </div>
                    <div class="cc-inv-card-actions">
                        ${due > 0
                            ? '<button class="cc-btn-sm cc-btn-pay btn-select-inv" data-inv=\'' + invEsc + '\'>Pay</button>'
                              + '<button class="cc-btn-sm cc-btn-discount btn-discount-inv" data-inv=\'' + invEsc + '\'>Discount</button>'
                            : ''}
                        <button class="cc-btn-sm cc-btn-outline" onclick="window.open(\'{{ url("Billing/payment") }}/${inv.ID}/print\',\'_blank\')">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
            `;

            $list.append(cardHtml);
        });

        $('#activeTabDueSummary').text(
            'Due: \u09f3' + parseInt(sumDue).toLocaleString()
            + ' (' + rows.length + ' records)'
        );

        const hasMore = meta.current_page < meta.last_page;
        if (hasMore) {
            $('#loadMoreBtn').data('next-page', meta.current_page + 1);
            $('#loadMoreWrap').show();
        } else {
            $('#loadMoreWrap').hide();
        }

    }).fail(function(){
        toastr.error('Failed to load invoices.');
    });
}

// ----------------------------------------------------------
// 5. Summary Card
// ----------------------------------------------------------
function loadSummaryCards(){
    $.get('{{ route("billing.invoice.list") }}', {
        show_all : 0,
        per_page : 9999,
        page     : 1,
    }, function(res){
        const rows     = res.data || [];
        const meta     = res.meta || {};
        const totalDue = rows.reduce((s,i) => s + (parseFloat(i.DueAmount)||0), 0);
        $('#sumTotalDue').html('&#2547; ' + parseInt(totalDue).toLocaleString());
        $('#sumTotalCount').text((meta.total || rows.length) + ' invoices');
    });
}

// ----------------------------------------------------------
// 6. Load More
// ----------------------------------------------------------
$(document).on('click', '#loadMoreBtn', function(){
    const nextPage = $(this).data('next-page') || currentPage + 1;
    loadDueList(nextPage, true);
});

// ----------------------------------------------------------
// 7. Row "Pay" button
// ----------------------------------------------------------
$(document).on('click', '.btn-select-inv', function(){
    selectInvoice($(this).data('inv'));
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ----------------------------------------------------------
// 8. Search & filter
// ----------------------------------------------------------
$('#listSearchInput').on('input', function(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function(){ loadDueList(1); }, 300);
});
$('#showAllChk').on('change', function(){ loadDueList(1); });

// ----------------------------------------------------------
// 9. Export helpers
// ----------------------------------------------------------
function buildExportRows(){
    return allLoadedRows.map(function(inv, idx){
        const net = parseFloat(inv.TotalBill) - parseFloat(inv.Discount || 0);
        return {
            '#'            : idx + 1,
            'Bill No'      : inv.BillNo       || '',
            'Patient Name' : inv.PatientName  || '',
            'Patient Code' : inv.PatientCode  || '',
            'Mobile'       : inv.MobileNo     || '',
            'Date'         : inv.PaymentDate  || '',
            'Category'     : inv.InvoiceType  || '',
            'Total (BDT)'  : parseInt(net),
            'Paid (BDT)'   : parseInt(parseFloat(inv.PaidAmount) || 0),
            'Due (BDT)'    : parseInt(parseFloat(inv.DueAmount)  || 0),
            'Status'       : inv.Status       || '',
        };
    });
}

function getFileName(ext){
    return 'DueList_All_' + new Date().toISOString().slice(0,10) + '.' + ext;
}

// -- Excel --
$('#btnExportExcel').on('click', function(){
    if (!allLoadedRows.length){ toastr.warning('No data on this page to export.'); return; }
    const data = buildExportRows();
    const ws   = XLSX.utils.json_to_sheet(data);
    const wb   = XLSX.utils.book_new();
    ws['!cols'] = [
        {wch:4},{wch:14},{wch:24},{wch:14},{wch:14},
        {wch:12},{wch:16},{wch:12},{wch:12},{wch:12},{wch:10}
    ];
    XLSX.utils.book_append_sheet(wb, ws, 'Due List');
    XLSX.writeFile(wb, getFileName('xlsx'));
    toastr.success('Excel downloaded!');
});

// -- CSV --
$('#btnExportCsv').on('click', function(){
    if (!allLoadedRows.length){ toastr.warning('No data on this page to export.'); return; }
    const data  = buildExportRows();
    const heads = Object.keys(data[0]);
    const csv   = [
        heads.join(','),
        ...data.map(row =>
            heads.map(h => '"' + String(row[h]).replace(/"/g,'""') + '"').join(',')
        )
    ].join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = getFileName('csv'); a.click();
    URL.revokeObjectURL(url);
    toastr.success('CSV downloaded!');
});

// -- Print --
$('#btnPrintList').on('click', function(){
    if (!allLoadedRows.length){ toastr.warning('No data on this page to print.'); return; }
    const data  = buildExportRows();
    const today = new Date().toLocaleDateString('en-GB');

    const totalDue  = data.reduce((s,r) => s + r['Due (BDT)'],  0);
    const totalPaid = data.reduce((s,r) => s + r['Paid (BDT)'], 0);
    const totalBill = data.reduce((s,r) => s + r['Total (BDT)'],0);

    const rows = data.map(r => `
        <tr>
            <td>${r['#']}</td>
            <td><strong>${r['Bill No']}</strong></td>
            <td>
                ${r['Patient Name']}
                <br><small>${r['Patient Code']}${r['Mobile'] ? ' &middot; ' + r['Mobile'] : ''}</small>
            </td>
            <td>${r['Date']}</td>
            <td style="text-align:right;">&#2547; ${r['Total (BDT)'].toLocaleString()}</td>
            <td style="text-align:right;color:#10b981;">&#2547; ${r['Paid (BDT)'].toLocaleString()}</td>
            <td style="text-align:right;color:#ef4444;font-weight:700;">&#2547; ${r['Due (BDT)'].toLocaleString()}</td>
            <td>${r['Status']}</td>
        </tr>`).join('');

    const win = window.open('', '_blank', 'width=960,height=720');
    win.document.write('<!DOCTYPE html><html><head>'
        + '<meta charset="UTF-8">'
        + '<meta name="viewport" content="width=device-width, initial-scale=1">'
        + '<title>Due List</title>'
        + '<style>'
        + '* { box-sizing:border-box; margin:0; padding:0; }'
        + 'body  { font-family:Arial,sans-serif; font-size:12px; padding:20px; color:#222; }'
        + '.hdr  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:8px; }'
        + '.hdr h2 { font-size:16px; }'
        + '.hdr .sub { font-size:11px; color:#888; margin-top:3px; }'
        + '.hdr .badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:12px; background:#fffbeb; color:#f59e0b; }'
        + 'table { width:100%; border-collapse:collapse; margin-top:4px; }'
        + 'th { background:#f5f5f5; border:1px solid #ccc; padding:6px 8px; font-size:11px; text-align:left; }'
        + 'td { border:1px solid #ddd; padding:5px 8px; }'
        + 'tfoot td { background:#ecfdf5; font-weight:700; border:1px solid #ccc; }'
        + 'small { font-size:10px; color:#888; }'
        + '@media print { body { padding:10px; } }'
        + '</style>'
        + '</head><body>'
        + '<div class="hdr">'
        + '<div><h2>Due Invoice List</h2><div class="sub">Printed: ' + today + ' &nbsp;|&nbsp; Records: ' + data.length + '</div></div>'
        + '<span class="badge">Due List</span>'
        + '</div>'
        + '<table><thead><tr>'
        + '<th>#</th><th>Bill No</th><th>Patient</th><th>Date</th>'
        + '<th style="text-align:right;">Total (&#2547;)</th>'
        + '<th style="text-align:right;">Paid (&#2547;)</th>'
        + '<th style="text-align:right;">Due (&#2547;)</th><th>Status</th>'
        + '</tr></thead><tbody>' + rows + '</tbody>'
        + '<tfoot><tr>'
        + '<td colspan="4" style="text-align:right;">Total:</td>'
        + '<td style="text-align:right;">&#2547; ' + totalBill.toLocaleString() + '</td>'
        + '<td style="text-align:right;color:#10b981;">&#2547; ' + totalPaid.toLocaleString() + '</td>'
        + '<td style="text-align:right;color:#ef4444;">&#2547; ' + totalDue.toLocaleString() + '</td><td></td>'
        + '</tr></tfoot></table>'
        + '<script>window.onload = function(){ window.print(); window.close(); };<\/script>'
        + '</body></html>'
    );
    win.document.close();
});

// ----------------------------------------------------------
// 10. Give Discount — modal open + reference dropdown
// ----------------------------------------------------------
$('#discReferenceSelect, #discApprovedBySelect').select2({
    width          : '100%',
    dropdownParent : $('#discountModal'),
    placeholder    : 'Select...',
});

function loadDiscountReferences(){
    if (discountRefsLoaded) return;
    $.get('{{ route("paynow.references") }}', function(res){
        const opts = (res.data || []).map(function(r){
            return '<option value="' + r.ID + '">' + r.Name + (r.ref_type ? ' (' + r.ref_type + ')' : '') + '</option>';
        }).join('');
        $('#discReferenceSelect, #discApprovedBySelect').append(opts);
        discountRefsLoaded = true;
    }).fail(function(){
        toastr.error('Reference list load করা যায়নি।');
    });
}

function openDiscountModal(inv){
    const due = parseFloat(inv.DueAmount) || 0;
    if (due <= 0) { toastr.info('This invoice has no due amount.'); return; }

    $('#discInvoiceId').val(inv.ID);
    $('#discBillNo').text(inv.BillNo);
    $('#discPatientName').text(inv.PatientName);
    $('#discCurrentDue').html('&#2547; ' + parseInt(due).toLocaleString());
    $('#discAmountInput').val('').attr('max', parseInt(due));
    $('#discAmountError').hide();
    $('#discReferenceSelect').val(null).trigger('change');
    $('#discApprovedBySelect').val(null).trigger('change');
    $('#discNoteInput').val('');
    $('#discHistoryBox').hide().empty();

    loadDiscountReferences();
    $('#discountModal').modal('show');
}

$(document).on('click', '.btn-discount-inv', function(){
    openDiscountModal($(this).data('inv'));
});

// ----------------------------------------------------------
// 11. Give Discount — submit AJAX
// ----------------------------------------------------------
$('#btnSubmitDiscount').on('click', function(){
    const invoiceId = $('#discInvoiceId').val();
    if (!invoiceId) return;

    const due    = parseFloat($('#discCurrentDue').text().replace(/[^\d.]/g, '')) || 0;
    const amount = parseFloat($('#discAmountInput').val()) || 0;
    const refId  = $('#discReferenceSelect').val();
    const apprId = $('#discApprovedBySelect').val();
    const note   = $('#discNoteInput').val();

    if (amount <= 0 || amount > due) {
        $('#discAmountError').html('Amount must be between 1 and &#2547;' + parseInt(due).toLocaleString()).show();
        return;
    }
    $('#discAmountError').hide();
    if (!refId)  { toastr.error('Reference select করুন।');    return; }
    if (!apprId) { toastr.error('Approved By select করুন।'); return; }

    const $btn = $(this);
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url         : '{{ route("paynow.adjustDiscount") }}',
        method      : 'POST',
        contentType : 'application/json',
        data        : JSON.stringify({
            invoice_id        : invoiceId,
            adjustment_amount : amount,
            reference_id      : refId,
            approved_by       : apprId,
            note              : note,
        }),
        success: function(res){
            if (res.success) {
                toastr.success('Discount applied!');
                $('#discountModal').modal('hide');
                loadDueList();
                loadSummaryCards();
            } else {
                toastr.error(res.message || 'Failed.');
            }
        },
        error: function(xhr){
            toastr.error(xhr.responseJSON ? xhr.responseJSON.message : 'Server error.');
        },
        complete: function(){
            $btn.prop('disabled', false).text('Give Discount');
        }
    });
});

// ----------------------------------------------------------
// 12. Discount History — inline toggle list
// ----------------------------------------------------------
$('#btnViewDiscHistory').on('click', function(){
    const invoiceId = $('#discInvoiceId').val();
    if (!invoiceId) return;

    const $box = $('#discHistoryBox');
    if ($box.is(':visible')) { $box.slideUp(150); return; }

    $box.html('<div class="cc-list-state" style="padding:10px;border:none;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>').slideDown(150);

    const url = '{{ route("paynow.discountHistory", ["invoiceId" => "__ID__"]) }}'.replace('__ID__', invoiceId);

    $.get(url, function(res){
        const rows = res.data || [];
        if (!rows.length) {
            $box.html('<div class="cc-list-state" style="padding:10px;border:none;">No discount history found.</div>');
            return;
        }
        const html = rows.map(function(r){
            return '<div style="padding:6px 10px;border-bottom:1px solid #eee;font-size:11px;">'
                 + '<strong>&#2547; ' + parseInt(r.adjustment_amount).toLocaleString() + '</strong> discount'
                 + ' &middot; Ref: ' + (r.reference_name || '-')
                 + ' &middot; Approved: ' + (r.approved_by_name || '-')
                 + '<br><span style="color:#6b7280;">' + (r.created_at || '') + (r.note ? ' — ' + r.note : '') + '</span>'
                 + '</div>';
        }).join('');
        $box.html(html);
    }).fail(function(){
        $box.html('<div class="cc-list-state" style="padding:10px;border:none;color:#ef4444;">Load failed.</div>');
    });
});

// ----------------------------------------------------------
// INIT
// ----------------------------------------------------------
loadDueList();
loadSummaryCards();

})();
</script>
@stop