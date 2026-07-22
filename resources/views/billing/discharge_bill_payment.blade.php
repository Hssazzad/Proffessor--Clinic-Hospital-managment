@extends('adminlte::page')

@section('title', 'Confirmed Invoices | Professor Clinic')

@section('content_header')
<div class="cc-header">
    <div class="cc-header-icon">
        <i class="fas fa-file-invoice"></i>
    </div>
    <div>
        <h1 class="cc-header-title">Confirmed Invoices</h1>
        <ol class="cc-breadcrumb">
            <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
            <li class="active">Confirmed Invoices</li>
        </ol>
    </div>
</div>
@stop

@section('content')

<div id="save-alert" class="cc-alert d-none" role="alert"></div>

{{-- Stats Row --}}
<div class="cc-stats">
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-stat-icon--blue">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Bills</div>
            <div class="cc-stat-value">{{ number_format($stats['total_bills']) }}</div>
        </div>
    </div>
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-stat-icon--amber">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Total Due</div>
            <div class="cc-stat-value">? {{ number_format($stats['total_due'], 0) }}</div>
        </div>
    </div>
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-stat-icon--green">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Today Bills</div>
            <div class="cc-stat-value">{{ number_format($stats['today_bills']) }}</div>
        </div>
    </div>
    <div class="cc-stat-card">
        <div class="cc-stat-icon cc-stat-icon--primary">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="cc-stat-body">
            <div class="cc-stat-label">Today Revenue</div>
            <div class="cc-stat-value">? {{ number_format($stats['today_revenue'], 0) }}</div>
        </div>
    </div>
</div>

{{-- Main Card with Filter & Table --}}
<div class="cc-card">
    <div class="cc-card-header">
        <div class="cc-card-header-left">
            <div class="cc-card-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div>
                <div class="cc-card-title">All Saved Bills</div>
                <div class="cc-card-subtitle">Investigation and confirmed invoices list</div>
            </div>
        </div>
        <div class="cc-card-actions">
            <span class="cc-card-badge">{{ $pastPayments->total() }} Records</span>
            <button class="cc-btn cc-btn-export" onclick="exportCSV()">
                <i class="fas fa-file-csv"></i> CSV
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="cc-search-bar">
        <form method="GET" action="{{ route('billing.discharge.index') }}" id="paySearchForm" class="cc-search-form">
            <div class="cc-search-row">
                <div class="cc-search-group">
                    <span class="cc-search-icon"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="cc-search-input"
                           value="{{ request('search') }}"
                           placeholder="Search name, receipt no, or mobile">
                    <button type="submit" class="cc-btn cc-btn-primary">
                        <i class="fas fa-search"></i> Load
                    </button>
                </div>
                <select name="status_filter" class="cc-select" onchange="this.form.submit()">
                    <option value="all"     {{ request('status_filter','all') === 'all'     ? 'selected' : '' }}>All Status</option>
                    <option value="paid"    {{ request('status_filter') === 'paid'    ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('status_filter') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="due"     {{ request('status_filter') === 'due'     ? 'selected' : '' }}>Due</option>
                </select>
                <button type="reset" class="cc-btn cc-btn-reset" onclick="resetFilter(event)">
                    <i class="fas fa-times"></i> Reset
                </button>
                @if(request()->has('search') || request()->has('status_filter'))
                    <a href="{{ route('billing.discharge.index') }}" class="cc-clear-link">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="cc-card-body">
        <div id="tableWrapper" style="overflow-x:auto;">
            <table class="cc-table" id="dischargeTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bill No</th>
                        <th>Patient Name</th>
                        <th class="text-right">Total Bill</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:110px;">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($pastPayments as $pp)
                    <tr data-id="{{ $pp->ID }}">
                        <td>{{ $pp->PaymentDate ? \Carbon\Carbon::parse($pp->PaymentDate)->format('d M Y') : '' }}</td>
                        <td><strong>{{ $pp->BillNo }}</strong></td>
                        <td>{{ $pp->PatientName ?? '' }}</td>
                        <td class="text-right">? {{ number_format($pp->TotalBill, 0) }}</td>
                        <td class="text-right text-green">? {{ number_format($pp->PaidAmount, 0) }}</td>
                        <td class="text-right text-red">? {{ number_format($pp->DueAmount, 0) }}</td>
                        <td class="text-center">
                            @if(strtolower($pp->Status) === 'paid')
                                <span class="cc-badge cc-badge--paid">Paid</span>
                            @elseif(strtolower($pp->Status) === 'partial')
                                <span class="cc-badge cc-badge--partial">Partial</span>
                            @else
                                <span class="cc-badge cc-badge--due">Due</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="cc-action-btn cc-action-btn--print" onclick="window.open('{{ url('Billing/payment/'.$pp->ID.'/print') }}','_blank')">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="cc-action-btn cc-action-btn--view" onclick="viewReceipt({{ $pp->ID }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="cc-action-btn cc-action-btn--delete" onclick="deleteRecord({{ $pp->ID }}, this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="cc-empty"><i class="fas fa-receipt"></i><p>No confirmed bills found.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Load More + Pagination Fallback --}}
        @if($pastPayments->hasPages())
        <div class="cc-load-more-wrap" id="loadMoreWrap">
            @if($pastPayments->hasMorePages())
            <button class="cc-load-more" id="loadMoreBtn"
                    data-next="{{ $pastPayments->nextPageUrl() }}"
                    onclick="loadMore(this)">
                <i class="fas fa-chevron-down"></i> Load More
            </button>
            @endif
            <div class="cc-pagination-info">
                Showing {{ $pastPayments->firstItem() }} to {{ $pastPayments->lastItem() }}
                of {{ $pastPayments->total() }} entries
            </div>
            <div class="cc-pagination-fallback d-none">
                {{ $pastPayments->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL: View past receipt (unchanged) --}}
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="cc-modal-content">
            <div class="cc-modal-header">
                <div class="cc-modal-header-left">
                    <div class="cc-modal-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div class="cc-modal-title">Investigation Bill</div>
                        <div class="cc-modal-subtitle" id="modal-subtitle">Loading</div>
                    </div>
                </div>
                <div class="cc-modal-header-actions">
                    <button class="cc-modal-print-btn" onclick="printModal()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="cc-modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="cc-modal-body">
                <div id="modal-loading" class="cc-modal-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading receipt...</p>
                </div>
                <div id="modal-error" class="cc-modal-state cc-modal-state--error d-none">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p id="modal-error-msg">Failed to load.</p>
                </div>
                <div id="modal-rx-area" class="d-none">
                    <div id="modal-prescription-print-area" class="cc-modal-print-area"></div>
                </div>
            </div>
            <div class="cc-modal-footer">
                <button class="cc-modal-close-btn" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<iframe id="print-iframe" style="position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none" title="Print Frame"></iframe>

{{-- Bottom Navigation --}}
<nav class="cc-bottom-nav">
    <a href="{{ url('/') }}" class="cc-bottom-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('billing.paylist.index') }}" class="cc-bottom-nav-item">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('billing.duelist.index') }}" class="cc-bottom-nav-item">
        <i class="fas fa-exclamation-circle"></i>
        <span>Due List</span>
    </a>
    <a href="{{ route('billing.discharge.index') }}" class="cc-bottom-nav-item cc-bottom-nav-item--active">
        <i class="fas fa-file-invoice"></i>
        <span>Discharge</span>
    </a>
    <a href="{{ route('billing.paynow.index') }}" class="cc-bottom-nav-item">
        <i class="fas fa-hand-holding-usd"></i>
        <span>Pay Now</span>
    </a>
</nav>

<div class="cc-bottom-spacer"></div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ===========================================
   CLINICAL CLARITY – COMPACT TABLE STYLE
=========================================== */
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

*,*::before,*::after{box-sizing:border-box}

body,.content-wrapper{
    background:var(--cc-bg)!important;
    font-family:var(--cc-font);
    color:var(--cc-text);
}

/* ─── Alert ─── */
.cc-alert{
    padding:8px 12px;
    border-radius:var(--cc-radius);
    font-size:12px;
    font-weight:500;
    margin-bottom:12px;
    border:1px solid transparent;
}
.cc-alert.alert-success{
    background:var(--cc-green-bg);
    color:var(--cc-green);
    border-color:#a7f3d0;
}
.cc-alert.alert-danger{
    background:var(--cc-red-bg);
    color:var(--cc-red);
    border-color:#fecaca;
}

/* ─── Page Header ─── */
.cc-header{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}
.cc-header-icon{
    width:36px;
    height:36px;
    border-radius:var(--cc-radius);
    background:var(--cc-primary);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--cc-white);
    font-size:16px;
    flex-shrink:0;
}
.cc-header-title{
    font-size:18px;
    font-weight:700;
    color:var(--cc-text);
    margin:0;
    line-height:1.2;
}
.cc-breadcrumb{
    list-style:none;
    padding:0;
    margin:2px 0 0;
    display:flex;
    gap:6px;
    font-size:11px;
    color:var(--cc-muted);
}
.cc-breadcrumb li{display:flex;align-items:center;gap:6px;}
.cc-breadcrumb li+li::before{content:'/';color:var(--cc-border);}
.cc-breadcrumb a{color:var(--cc-primary);text-decoration:none;}
.cc-breadcrumb a:hover{text-decoration:underline;}
.cc-breadcrumb .active{color:var(--cc-muted);}

/* ─── Stats ─── */
.cc-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:14px;
}
@media(max-width:768px){.cc-stats{grid-template-columns:repeat(2,1fr);gap:8px;}}
@media(max-width:480px){.cc-stats{grid-template-columns:1fr 1fr;gap:6px;}}
.cc-stat-card{
    background:var(--cc-white);
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    padding:10px 12px;
    display:flex;
    align-items:center;
    gap:10px;
}
.cc-stat-icon{
    width:32px;
    height:32px;
    border-radius:var(--cc-radius);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    flex-shrink:0;
}
.cc-stat-icon--blue{background:#e8edff;color:var(--cc-primary);}
.cc-stat-icon--amber{background:var(--cc-amber-bg);color:var(--cc-amber);}
.cc-stat-icon--green{background:var(--cc-green-bg);color:var(--cc-green);}
.cc-stat-icon--primary{background:var(--cc-primary-light);color:var(--cc-primary);}
.cc-stat-body{min-width:0;}
.cc-stat-label{
    font-size:9px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.4px;
    color:var(--cc-muted);
    margin-bottom:1px;
}
.cc-stat-value{
    font-size:16px;
    font-weight:700;
    color:var(--cc-text);
    line-height:1.1;
}

/* ─── Main Card ─── */
.cc-card{
    background:var(--cc-white);
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    margin-bottom:20px;
    overflow:hidden;
}
.cc-card-header{
    padding:8px 14px;
    border-bottom:1px solid var(--cc-border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    flex-wrap:wrap;
    background:#fafcfd;
}
.cc-card-header-left{
    display:flex;
    align-items:center;
    gap:8px;
}
.cc-card-icon{
    width:28px;
    height:28px;
    border-radius:var(--cc-radius);
    background:var(--cc-primary-light);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--cc-primary);
    font-size:13px;
    flex-shrink:0;
}
.cc-card-title{
    font-size:13px;
    font-weight:700;
    color:var(--cc-text);
    line-height:1.2;
}
.cc-card-subtitle{
    font-size:10.5px;
    color:var(--cc-muted);
    margin-top:1px;
}
.cc-card-actions{
    display:flex;
    align-items:center;
    gap:6px;
}
.cc-card-badge{
    background:var(--cc-primary-light);
    color:var(--cc-primary);
    border:1px solid #c7d4f0;
    border-radius:var(--cc-radius);
    padding:2px 8px;
    font-size:10px;
    font-weight:600;
    white-space:nowrap;
}
.cc-card-body{
    padding:0;
}

/* ─── Buttons ─── */
.cc-btn{
    display:inline-flex;
    align-items:center;
    gap:4px;
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    padding:5px 10px;
    font-size:11px;
    font-weight:600;
    font-family:var(--cc-font);
    cursor:pointer;
    transition:all .15s;
    line-height:1.2;
    white-space:nowrap;
    background:var(--cc-white);
    color:var(--cc-text);
}
.cc-btn:hover{ border-color:var(--cc-primary);color:var(--cc-primary);}
.cc-btn-primary{
    background:var(--cc-primary);
    border-color:var(--cc-primary);
    color:#fff;
}
.cc-btn-primary:hover{background:var(--cc-primary-dark);border-color:var(--cc-primary-dark);color:#fff;}
.cc-btn-reset{
    background:var(--cc-white);
    border-color:var(--cc-red);
    color:var(--cc-red);
}
.cc-btn-reset:hover{background:var(--cc-red-bg);border-color:var(--cc-red);}
.cc-btn-export{
    background:var(--cc-green);
    border-color:var(--cc-green);
    color:#fff;
}
.cc-btn-export:hover{background:#059669;border-color:#059669;color:#fff;}
.cc-btn-sm{padding:3px 8px;font-size:10px;}

/* ─── Search Bar ─── */
.cc-search-bar{
    padding:10px 14px;
    background:#f8f7fc;
    border-bottom:1px solid var(--cc-border);
}
.cc-search-form{width:100%;}
.cc-search-row{
    display:flex;
    gap:6px;
    align-items:center;
    flex-wrap:wrap;
}
.cc-search-group{
    display:flex;
    align-items:center;
    background:var(--cc-white);
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    overflow:hidden;
    flex:1;
    min-width:180px;
    transition:border-color .15s;
}
.cc-search-group:focus-within{
    border-color:var(--cc-primary);
}
.cc-search-icon{
    padding:0 8px;
    color:var(--cc-muted);
    font-size:12px;
}
.cc-search-input{
    flex:1;
    border:none;
    outline:none;
    padding:5px 8px;
    font-size:12px;
    background:transparent;
    color:var(--cc-text);
    font-family:var(--cc-font);
    height:30px;
}
.cc-search-group .cc-btn{
    border-radius:0;
    height:30px;
    padding:0 12px;
    font-size:11px;
}
.cc-select{
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    padding:4px 8px;
    font-size:12px;
    font-family:var(--cc-font);
    outline:none;
    cursor:pointer;
    background:var(--cc-white);
    color:var(--cc-text);
    transition:border-color .15s;
    height:30px;
}
.cc-select:focus{border-color:var(--cc-primary);}
.cc-clear-link{
    font-size:11px;
    color:var(--cc-primary);
    text-decoration:none;
    font-weight:500;
    white-space:nowrap;
    padding:4px 0;
}
.cc-clear-link:hover{text-decoration:underline;}

/* ─── Table ─── */
.cc-table{
    width:100%;
    border-collapse:collapse;
    font-size:11.5px;
}
.cc-table thead th{
    background:#f0f1f5;
    color:var(--cc-text);
    font-weight:700;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.3px;
    padding:6px 6px;
    border-bottom:2px solid var(--cc-border);
    white-space:nowrap;
    text-align:left;
}
.cc-table thead th.text-right{text-align:right;}
.cc-table thead th.text-center{text-align:center;}
.cc-table tbody td{
    padding:5px 6px;
    border-bottom:1px solid #eef0f2;
    vertical-align:middle;
}
.cc-table tbody tr:hover{background:var(--cc-primary-light);}
.cc-table .text-right{text-align:right;}
.cc-table .text-center{text-align:center;}
.cc-table .text-green{color:var(--cc-green);font-weight:600;}
.cc-table .text-red{color:var(--cc-red);font-weight:600;}

.cc-table .cc-empty{
    text-align:center;
    padding:40px 0;
    color:var(--cc-muted);
}
.cc-table .cc-empty i{font-size:1.8rem;display:block;margin-bottom:8px;color:var(--cc-border);}
.cc-table .cc-empty p{margin:0;font-size:13px;}

/* ─── Badges ─── */
.cc-badge{
    display:inline-block;
    border-radius:var(--cc-radius);
    padding:2px 8px;
    font-size:10px;
    font-weight:600;
    white-space:nowrap;
}
.cc-badge--paid{background:var(--cc-green-bg);color:var(--cc-green);}
.cc-badge--partial{background:var(--cc-amber-bg);color:var(--cc-amber);}
.cc-badge--due{background:var(--cc-red-bg);color:var(--cc-red);}

/* ─── Action Buttons ─── */
.cc-action-btn{
    border-radius:var(--cc-radius);
    padding:3px 5px;
    font-size:10px;
    font-weight:500;
    cursor:pointer;
    transition:all .12s;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid transparent;
    background:transparent;
    color:var(--cc-muted);
    font-family:var(--cc-font);
    min-width:24px;
    height:24px;
}
.cc-action-btn:hover{background:var(--cc-primary-light);color:var(--cc-primary);border-color:var(--cc-primary);}
.cc-action-btn--print{color:var(--cc-primary);border-color:var(--cc-primary);background:var(--cc-primary-light);}
.cc-action-btn--print:hover{background:var(--cc-primary);color:#fff;}
.cc-action-btn--view{color:var(--cc-amber);border-color:var(--cc-amber);background:var(--cc-amber-bg);}
.cc-action-btn--view:hover{background:var(--cc-amber);color:#fff;}
.cc-action-btn--delete{color:var(--cc-red);border-color:var(--cc-red);background:var(--cc-red-bg);}
.cc-action-btn--delete:hover{background:var(--cc-red);color:#fff;}

/* ─── Load More ─── */
.cc-load-more-wrap{
    padding:12px 14px;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:6px;
}
.cc-load-more{
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    background:var(--cc-white);
    color:var(--cc-primary);
    padding:8px 24px;
    font-size:12px;
    font-weight:600;
    cursor:pointer;
    transition:all .12s;
    font-family:var(--cc-font);
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.cc-load-more:hover{
    background:var(--cc-primary-light);
    border-color:var(--cc-primary);
}
.cc-load-more:disabled{opacity:.5;cursor:not-allowed;}
.cc-pagination-info{
    font-size:10.5px;
    color:var(--cc-muted);
}
.d-none{display:none!important;}

/* ─── Modal (unchanged, kept compact) ─── */
.cc-modal-content{
    border:none;
    border-radius:var(--cc-radius);
    overflow:hidden;
    background:var(--cc-white);
}
.cc-modal-header{
    background:var(--cc-primary);
    color:var(--cc-white);
    border:none;
    padding:12px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.cc-modal-header-left{
    display:flex;
    align-items:center;
    gap:10px;
}
.cc-modal-icon{
    width:34px;
    height:34px;
    border-radius:var(--cc-radius);
    background:rgba(255,255,255,.14);
    border:1px solid rgba(255,255,255,.2);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    color:var(--cc-white);
    flex-shrink:0;
}
.cc-modal-title{
    font-size:14px;
    font-weight:700;
    color:var(--cc-white);
    margin:0;
    line-height:1.2;
}
.cc-modal-subtitle{
    font-size:10.5px;
    opacity:.75;
    color:var(--cc-white);
    margin-top:1px;
}
.cc-modal-header-actions{
    display:flex;
    gap:6px;
    align-items:center;
}
.cc-modal-print-btn{
    background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.25);
    color:var(--cc-white);
    border-radius:var(--cc-radius);
    padding:4px 12px;
    font-size:11px;
    font-weight:600;
    cursor:pointer;
    transition:all .12s;
    font-family:var(--cc-font);
    display:inline-flex;
    align-items:center;
    gap:4px;
}
.cc-modal-print-btn:hover{background:rgba(255,255,255,.28);}
.cc-modal-close{
    background:none;
    border:none;
    color:var(--cc-white);
    font-size:16px;
    cursor:pointer;
    opacity:.7;
    transition:opacity .12s;
}
.cc-modal-close:hover{opacity:1;}
.cc-modal-body{
    padding:0;
}
.cc-modal-state{
    text-align:center;
    padding:40px 20px;
    color:var(--cc-muted);
}
.cc-modal-state i{
    font-size:26px;
    color:var(--cc-primary);
    display:block;
    margin-bottom:8px;
}
.cc-modal-state p{margin:0;font-size:12px;}
.cc-modal-state--error i{color:var(--cc-red);}
.cc-modal-state--error p{color:var(--cc-red);}
.cc-modal-print-area{
    padding:16px 20px;
}
.cc-modal-footer{
    background:#f8f7fc;
    border-top:1px solid var(--cc-border);
    padding:10px 16px;
    display:flex;
    justify-content:flex-end;
}
.cc-modal-close-btn{
    background:var(--cc-white);
    border:1px solid var(--cc-border);
    border-radius:var(--cc-radius);
    color:var(--cc-text);
    padding:5px 14px;
    font-size:11px;
    font-weight:500;
    cursor:pointer;
    transition:all .12s;
    font-family:var(--cc-font);
    display:inline-flex;
    align-items:center;
    gap:4px;
}
.cc-modal-close-btn:hover{background:#f0eff5;}

/* ─── Print template (kept as is) ─── */
.print-bill-wrapper{background:#fff;max-width:560px;margin:0 auto;border:1px solid var(--cc-border);font-family:var(--cc-font);}
.print-letterhead{display:flex;justify-content:space-between;align-items:flex-start;padding:16px;border-bottom:2px solid var(--cc-primary);}
.print-lh-left{font-size:11px;color:#444;min-width:100px;}
.print-reg-label{font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.4px;}
.print-reg-val{font-size:12px;font-weight:700;color:var(--cc-primary);}
.print-lh-center{flex:1;text-align:center;padding:0 10px;}
.print-clinic-name{font-size:18px;font-weight:700;color:var(--cc-primary);letter-spacing:.4px;}
.print-clinic-addr{font-size:11px;color:#555;}
.print-clinic-cell{font-size:11px;color:#777;}
.print-lh-right{font-size:11px;color:#555;text-align:right;line-height:1.7;min-width:100px;}
.print-patient-row{display:flex;flex-wrap:wrap;gap:8px;padding:10px 16px;background:#f8f9fa;border-bottom:1px solid #ddd;}
.print-pf{flex:1;min-width:100px;}
.print-pf-label{font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.3px;display:block;}
.print-pf-val{font-size:12px;font-weight:600;color:#333;}
.print-bill-title{text-align:center;font-size:12px;font-weight:700;letter-spacing:2px;padding:7px;background:var(--cc-primary-light);border-bottom:2px solid #c7d4f0;color:var(--cc-primary);text-transform:uppercase;}
.print-bill-table{width:100%;border-collapse:collapse;font-size:12px;}
.print-bill-table thead th{background:var(--cc-primary-light);color:var(--cc-primary);font-size:10px;font-weight:700;text-transform:uppercase;padding:6px 12px;border-bottom:2px solid #c7d4f0;text-align:left;}
.print-bill-table tbody td{padding:6px 12px;border-bottom:1px solid #eee;vertical-align:middle;}
.print-bill-table tfoot td{padding:7px 12px;font-weight:700;}
.print-footer-note{font-size:12px;color:#444;padding:9px 16px;border-top:1px dashed #ccc;background:#fafafa;}
.print-sig-row{display:flex;justify-content:space-between;align-items:flex-end;padding:12px 16px;}
.print-sig-left{font-size:12px;color:#555;}
.print-sig-right{text-align:center;}
.print-sig-line{border-bottom:1px solid #999;width:110px;margin-bottom:4px;}
.print-sig-label{font-size:11px;color:#777;}
@media print{
    *{visibility:hidden!important}
    #prescription-print-area,#prescription-print-area *{visibility:visible!important}
    #prescription-print-area{position:fixed!important;top:0!important;left:0!important;width:100%!important;margin:0!important;padding:0!important;z-index:99999!important;}
    .print-bill-wrapper{box-shadow:none!important;border:1px solid #000!important;max-width:100%!important;width:100%!important;}
    @page{margin:8mm;size:A5}
}

/* ─── Bottom Navigation ─── */
.cc-bottom-nav{
    position:fixed;
    bottom:0;
    left:0;
    right:0;
    height:50px;
    background:var(--cc-white);
    border-top:1px solid var(--cc-border);
    display:flex;
    align-items:center;
    justify-content:space-around;
    z-index:1050;
    padding:0 4px;
}
.cc-bottom-nav-item{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:1px;
    text-decoration:none!important;
    color:var(--cc-muted);
    font-size:9px;
    font-weight:500;
    padding:4px 6px;
    border-radius:var(--cc-radius);
    transition:color .12s;
    min-width:48px;
}
.cc-bottom-nav-item i{
    font-size:16px;
    transition:color .12s;
}
.cc-bottom-nav-item:hover{color:var(--cc-primary);}
.cc-bottom-nav-item--active{
    color:var(--cc-primary);
}
.cc-bottom-nav-item--active i{
    color:var(--cc-primary);
}
.cc-bottom-spacer{
    height:56px;
}

/* ─── Responsive ─── */
@media (max-width: 768px) {
    .cc-search-row{
        flex-direction:column;
        align-items:stretch;
    }
    .cc-search-group{min-width:100%;}
    .cc-search-group .cc-btn{flex-shrink:0;}
}
@media (max-width: 576px) {
    .cc-stats{grid-template-columns:1fr 1fr;gap:4px;}
    .cc-stat-card{padding:6px 8px;}
    .cc-stat-value{font-size:14px;}
    .cc-table thead{display:none;}
    .cc-table tbody tr{display:block;border:1px solid var(--cc-border);margin-bottom:6px;border-radius:var(--cc-radius);padding:6px;}
    .cc-table tbody td{display:block;text-align:left!important;border:none;padding:2px 4px;}
    .cc-table tbody td::before{content:attr(data-label);font-weight:700;margin-right:6px;color:var(--cc-muted);font-size:9px;text-transform:uppercase;}
    .cc-table tbody td:first-child::before{content:'Date';}
    .cc-table tbody td:nth-child(2)::before{content:'Bill No';}
    .cc-table tbody td:nth-child(3)::before{content:'Patient';}
    .cc-table tbody td:nth-child(4)::before{content:'Total';}
    .cc-table tbody td:nth-child(5)::before{content:'Paid';}
    .cc-table tbody td:nth-child(6)::before{content:'Due';}
    .cc-table tbody td:nth-child(7)::before{content:'Status';}
    .cc-table tbody td:nth-child(8)::before{content:'Action';}
    .cc-table tbody td:last-child{text-align:center;}
    .cc-card-actions{width:100%;justify-content:space-between;}
}
</style>
@stop

@section('js')
<script>
function getCsrfToken(){
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
}
function esc(s){
    if(!s) return '';
    return s.toString().replace(/[&<>"']/g, function(m){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
    });
}
function fmtDate(d){
    if(!d) return '';
    return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
}
function showAlert(msg, type){
    var el = document.getElementById('save-alert');
    el.className = 'cc-alert alert-'+(type||'success');
    el.textContent = msg;
    el.classList.remove('d-none');
    window.scrollTo({top:0,behavior:'smooth'});
    setTimeout(function(){ el.classList.add('d-none'); }, 5000);
}

/* ========== LOAD MORE (adapt to table rows) ========== */
function loadMore(btn){
    var nextUrl = btn.getAttribute('data-next');
    if(!nextUrl) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    fetch(nextUrl, {headers:{'Accept':'text/html'}})
    .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.text(); })
    .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newRows = doc.querySelectorAll('#tableBody tr');
        var container = document.getElementById('tableBody');
        newRows.forEach(function(row){ container.appendChild(row); });
        var newWrap = doc.getElementById('loadMoreWrap');
        if(newWrap){
            var newBtn = newWrap.querySelector('.cc-load-more');
            var newInfo = newWrap.querySelector('.cc-pagination-info');
            if(newBtn){
                btn.setAttribute('data-next', newBtn.getAttribute('data-next')||'');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More';
                if(!newBtn.getAttribute('data-next')){
                    btn.style.display = 'none';
                }
            } else {
                btn.style.display = 'none';
            }
            if(newInfo){
                document.querySelector('.cc-pagination-info').textContent = newInfo.textContent;
            }
        } else {
            btn.style.display = 'none';
        }
    })
    .catch(function(e){
        showAlert('Error loading more: '+e.message,'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Load More';
    });
}

/* ========== RESET FILTER ========== */
function resetFilter(e){
    e.preventDefault();
    var form = document.getElementById('paySearchForm');
    form.querySelector('input[name="search"]').value = '';
    form.querySelector('select[name="status_filter"]').value = 'all';
    form.submit();
}

/* ========== VIEW RECEIPT (unchanged) ========== */
function viewReceipt(id){
    $('#receiptModal').modal('show');
    document.getElementById('modal-loading').style.display = '';
    document.getElementById('modal-error').classList.add('d-none');
    document.getElementById('modal-rx-area').classList.add('d-none');

    fetch('{{ url("Billing/DischargeBillPayment/detail") }}/'+id, {headers:{'Accept':'application/json'}})
    .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    .then(function(resp){ if(resp.success) renderModal(resp.data); else showModalError(resp.message||'Not found'); })
    .catch(function(e){ showModalError(e.message); });
}

function renderModal(d){
    var tests   = d.tests || d.items || [];
    var gross   = parseFloat(d.gross_amount    || d.total_amount || 0);
    var less    = parseFloat(d.discount_amount || d.discount     || 0);
    var net     = parseFloat(d.total_amount    || 0);
    var adv     = parseFloat(d.paid_amount     || 0);
    var due     = parseFloat(d.due_amount      || 0);
    var collBy  = d.received_by || d.collected_by || '';

    var rows = tests.map(function(it, i){
        var name   = it.test_name || it.service_name || it.name || '';
        var amount = it.subtotal  || it.amount       || it.price || 0;
        return '<tr><td style="text-align:center">'+(i+1)+'</td><td>'+esc(name)+'</td><td style="text-align:right">'+Number(amount).toLocaleString()+'</td></tr>';
    }).join('');

    var footerRows =
        '<tr style="background:#f0faf8"><td colspan="2" style="text-align:right;font-weight:700">Total</td><td style="text-align:right;font-weight:700">'+Number(gross).toLocaleString()+'</td></tr>'+
        (less>0?'<tr style="color:#ef4444"><td colspan="2" style="text-align:right">Less (Discount)</td><td style="text-align:right"> '+Number(less).toLocaleString()+'</td></tr>':'')+
        '<tr style="background:var(--cc-primary-light, #e8edff)"><td colspan="2" style="text-align:right;font-weight:700;color:var(--cc-primary, #0f52ba)">Net Total</td><td style="text-align:right;font-weight:700;color:var(--cc-primary, #0f52ba)">'+Number(net).toLocaleString()+'</td></tr>'+
        '<tr style="background:#fffbeb"><td colspan="2" style="text-align:right">Paid</td><td style="text-align:right;font-weight:700">'+Number(adv).toLocaleString()+'</td></tr>'+
        (due>0?'<tr style="background:#fef2f2"><td colspan="2" style="text-align:right;font-weight:700;color:#ef4444">Due</td><td style="text-align:right;font-weight:700;color:#ef4444">'+Number(due).toLocaleString()+'</td></tr>':'');

    document.getElementById('modal-prescription-print-area').innerHTML =
        '<div class="print-bill-wrapper" id="prescription-print-area">'+
        '<div class="print-letterhead">'+
            '<div class="print-lh-left"><div class="print-reg-label">Registration no</div><div class="print-reg-val">'+esc(d.patient_code||'')+'</div></div>'+
            '<div class="print-lh-center"><div class="print-clinic-name">Professor Clinic</div><div class="print-clinic-addr">Majhira, Shajahanpur, Bogura</div><div class="print-clinic-cell">Cell: 01720-039006</div></div>'+
            '<div class="print-lh-right"><div>01713-740680</div><div>01720-039005</div><div>01720-039006</div><div>01720-039007</div><div>01720-039008</div></div>'+
        '</div>'+
        '<div class="print-patient-row">'+
            '<div class="print-pf"><span class="print-pf-label">Patient\'s name</span><span class="print-pf-val">'+esc(d.patient_name||'')+'</span></div>'+
            '<div class="print-pf"><span class="print-pf-label">Age</span><span class="print-pf-val">'+esc(d.patient_age||'')+'</span></div>'+
            '<div class="print-pf"><span class="print-pf-label">Date</span><span class="print-pf-val">'+fmtDate(d.payment_date)+'</span></div>'+
            '<div class="print-pf"><span class="print-pf-label">Receipt</span><span class="print-pf-val font-weight-bold">'+esc(d.receipt_no||'')+'</span></div>'+
        '</div>'+
        '<div class="print-bill-title">INVESTIGATION BILL</div>'+
        '<table class="print-bill-table"><thead><tr><th style="width:42px;text-align:center">Sl.</th><th>Investigation</th><th style="text-align:right">Amount (?)</th></tr></thead>'+
        '<tbody>'+rows+'</tbody><tfoot>'+footerRows+'</tfoot></table>'+
        '<div class="print-footer-note">????? ?? ?????? ?????  ?????? ????? ????? ????? ???</div>'+
        '<div class="print-sig-row"><div class="print-sig-left">Collected by: <strong>'+esc(collBy)+'</strong></div>'+
        '<div class="print-sig-right"><div class="print-sig-line"></div><div class="print-sig-label">Authorized Signature</div></div></div>'+
        '</div>';

    document.getElementById('modal-subtitle').textContent = (d.patient_name||'')+'  '+(d.receipt_no||'');
    document.getElementById('modal-loading').style.display = 'none';
    document.getElementById('modal-rx-area').classList.remove('d-none');
}
function showModalError(msg){
    document.getElementById('modal-error-msg').textContent = msg;
    document.getElementById('modal-loading').style.display = 'none';
    document.getElementById('modal-error').classList.remove('d-none');
}

/* ========== DELETE ========== */
function deleteRecord(id, btn){
    if(!confirm('Delete this bill? This cannot be undone.')) return;
    btn.disabled = true;
    fetch('{{ url("Billing/DischargeBillPayment/delete") }}/'+id, {
        method:'DELETE',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':getCsrfToken()}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if(d.success){
            var row = btn.closest('tr');
            if(row) row.remove();
            showAlert('Bill deleted.','success');
        } else {
            showAlert(d.message||'Delete failed.','danger');
            btn.disabled = false;
        }
    })
    .catch(function(e){ showAlert('Error: '+e.message,'danger'); btn.disabled = false; });
}

/* ========== PRINT MODAL ========== */
function printModal(){
    var area = document.getElementById('modal-prescription-print-area');
    if(!area||!area.innerHTML.trim()){ alert('No receipt to print.'); return; }
    var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">'+
        '<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">'+
        '<style>body{margin:0;padding:8mm;font-family:"Hanken Grotesk",Arial,sans-serif;-webkit-print-color-adjust:exact;print-color-adjust:exact}'+
        '.print-bill-wrapper{max-width:100%;margin:0 auto;border:1px solid #d9d9e2}'+
        '.print-letterhead{display:flex;justify-content:space-between;align-items:flex-start;padding:16px;border-bottom:2px solid #0f52ba}'+
        '.print-lh-center{flex:1;text-align:center;padding:0 10px}.print-clinic-name{font-size:18px;font-weight:700;color:#0f52ba}'+
        '.print-clinic-addr,.print-clinic-cell{font-size:11px;color:#555}.print-lh-left{font-size:11px;color:#444;min-width:100px}'+
        '.print-lh-right{font-size:11px;color:#555;text-align:right;line-height:1.7;min-width:100px}'+
        '.print-reg-label{font-size:10px;color:#888;text-transform:uppercase}.print-reg-val{font-size:12px;font-weight:700;color:#0f52ba}'+
        '.print-patient-row{display:flex;flex-wrap:wrap;gap:8px;padding:10px 16px;background:#f8f9fa;border-bottom:1px solid #ddd}'+
        '.print-pf{flex:1;min-width:100px}.print-pf-label{font-size:10px;color:#888;text-transform:uppercase;display:block}'+
        '.print-pf-val{font-size:12px;font-weight:600;color:#333}'+
        '.print-bill-title{text-align:center;font-size:12px;font-weight:700;letter-spacing:2px;padding:7px;background:#e8edff;border-bottom:2px solid #c7d4f0;color:#0f52ba;text-transform:uppercase}'+
        '.print-bill-table{width:100%;border-collapse:collapse;font-size:12px}'+
        '.print-bill-table thead th{background:#e8edff;font-size:10px;font-weight:700;text-transform:uppercase;padding:6px 12px;border-bottom:2px solid #c7d4f0;text-align:left}'+
        '.print-bill-table tbody td,.print-bill-table tfoot td{padding:6px 12px;border-bottom:1px solid #eee;vertical-align:middle}'+
        '.print-footer-note{font-size:12px;color:#444;padding:9px 16px;border-top:1px dashed #ccc;background:#fafafa}'+
        '.print-sig-row{display:flex;justify-content:space-between;align-items:flex-end;padding:12px 16px}'+
        '.print-sig-line{border-bottom:1px solid #999;width:110px;margin-bottom:4px}.print-sig-label{font-size:11px;color:#777}'+
        '.font-weight-bold{font-weight:700}@page{margin:8mm;size:A5}</style>'+
        '</head><body>'+area.innerHTML+
        '<script>window.onload=function(){window.print();}<\/script></body></html>';
    var iframe = document.getElementById('print-iframe');
    iframe.onload = function(){ setTimeout(function(){ try{ iframe.contentWindow.print(); }catch(e){} },500); iframe.onload=null; };
    iframe.srcdoc = html;
}

/* ========== EXPORT CSV ========== */
function exportCSV(){
    var rows = document.querySelectorAll('#tableBody tr');
    if(!rows.length){ toastr.warning('No data to export.'); return; }
    var data = [];
    rows.forEach(function(row, idx){
        var cells = row.querySelectorAll('td');
        if(cells.length < 8) return;
        data.push({
            '#'          : idx + 1,
            'Date'       : cells[0].textContent.trim(),
            'Bill No'    : cells[1].textContent.trim(),
            'Patient'    : cells[2].textContent.trim(),
            'Total Bill' : parseFloat(cells[3].textContent.replace(/[^0-9.-]/g,'')) || 0,
            'Paid'       : parseFloat(cells[4].textContent.replace(/[^0-9.-]/g,'')) || 0,
            'Due'        : parseFloat(cells[5].textContent.replace(/[^0-9.-]/g,'')) || 0,
            'Status'     : cells[6].textContent.trim(),
        });
    });
    if(!data.length){ toastr.warning('No rows to export.'); return; }
    var heads = Object.keys(data[0]);
    var csv = [heads.join(','), ...data.map(row =>
        heads.map(h => '"' + String(row[h]).replace(/"/g, '""') + '"').join(',')
    )].join('\r\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url; a.download = 'ConfirmedInvoices_' + new Date().toISOString().slice(0,10) + '.csv'; a.click();
    URL.revokeObjectURL(url);
    toastr.success('CSV exported!');
}

// Ensure toastr is loaded (if not already)
if(typeof toastr === 'undefined'){
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
    document.head.appendChild(script);
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
    document.head.appendChild(link);
}
</script>
@stop