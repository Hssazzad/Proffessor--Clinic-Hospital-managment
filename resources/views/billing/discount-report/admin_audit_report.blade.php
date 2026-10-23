@extends('adminlte::page')

@section('title', 'Discount / Reference Audit Report')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .stat-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .stat-card .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
    }
    .stat-card .stat-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #6c757d;
    }
    .stat-card .stat-value {
        font-size: 22px;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }
    .audit-table td {
        vertical-align: middle;
    }
    .audit-table .money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .status-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    .status-approved { background: #e8f5e9; color: #2e7d32; }
    .status-pending  { background: #fff3e0; color: #e65100; }
    .status-rejected { background: #ffebee; color: #c62828; }
    .empty-state {
        padding: 48px 20px;
        text-align: center;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 42px;
        color: #cfd8dc;
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Discount / Reference Audit Report
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.invoice.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Audit Report</li>
        </ol>
    </div>
@stop

@section('content')

<div class="row mb-4">
    <div class="col-md-4 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-primary mr-3"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="stat-label">Total Cases</div>
                    <div class="stat-value">{{ $summary['total_cases'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-success mr-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-value">{{ $summary['approved'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-danger mr-3"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-label">Total Due</div>
                    <div class="stat-value">৳ {{ number_format($summary['total_due'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-warning mr-3"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-secondary mr-3"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value">{{ $summary['rejected'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter text-primary mr-2"></i>Filters</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('billing.admin.audit') }}" class="form-inline flex-wrap">
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-size:13px;">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-size:13px;">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mr-3 mb-2">
                <label class="mr-2" style="font-size:13px;">Status</label>
                <select name="status" class="form-control form-control-sm" style="min-width:140px;">
                    <option value="" {{ !$status ? 'selected' : '' }}>All Status</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group mr-2 mb-2">
                <button type="submit" class="btn btn-teal btn-sm px-4">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="form-group mb-2">
                <a href="{{ route('billing.admin.audit') }}" class="btn btn-outline-secondary btn-sm px-4">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <h3 class="card-title">
            <i class="fas fa-list-ul text-primary mr-2"></i>Reference / Discount Cases
        </h3>
        <div class="card-tools">
            <span class="text-muted small mr-2">{{ $fromDate || $toDate ? ($fromDate ?: 'Start') . ' → ' . ($toDate ?: 'Today') : 'All dates' }}</span>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="auditTable" class="table table-hover table-striped audit-table mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Bill No</th>
                        <th>Patient</th>
                        <th class="money">Total</th>
                        <th class="money">Discount</th>
                        <th class="money">Paid</th>
                        <th class="money">Due</th>
                        <th>Reference By</th>
                        <th>Collected By</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $row)
                        @php
                            $discountAmount = $row->Discount ?? $row->DiscountAmount ?? 0;
                            $discountPercent = $row->DiscountPercent
                                ?? ($row->TotalBill > 0 ? round(($discountAmount / $row->TotalBill) * 100, 1) : 0);
                        @endphp
                        <tr>
                            <td><span class="font-weight-bold text-primary">{{ $row->BillNo }}</span></td>
                            <td>
                                <div class="font-weight-semibold">{{ $row->PatientName }}</div>
                                <div class="text-muted small">{{ $row->PatientCode }}</div>
                            </td>
                            <td class="money">{{ number_format($row->TotalBill, 0) }}</td>
                            <td class="money">
                                @if ($discountAmount > 0)
                                    <div class="font-weight-bold text-warning">{{ number_format($discountAmount, 0) }}</div>
                                    <div class="text-muted small">{{ $discountPercent }}%</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="money font-weight-bold text-success">{{ number_format($row->PaidAmount, 0) }}</td>
                            <td class="money font-weight-bold text-danger">{{ number_format($row->DueAmount, 0) }}</td>
                            <td><span class="badge badge-teal font-weight-semibold" style="padding:4px 10px;">{{ $row->reference_name }}</span></td>
                            <td>{{ $row->CollectedBy }}</td>
                            <td>
                                @if ($row->approval_status === 'approved')
                                    <span class="status-badge status-approved"><i class="fas fa-check-circle mr-1"></i>Approved</span>
                                @elseif ($row->approval_status === 'rejected')
                                    <span class="status-badge status-rejected"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                                @else
                                    <span class="status-badge status-pending"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>
                                @endif
                            </td>
                            <td>{{ $row->ApprovedByName ?? '-' }}</td>
                            <td>{{ $row->PaymentDate ? \Carbon\Carbon::parse($row->PaymentDate)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5 class="text-muted">No cases found</h5>
                                    <p class="mb-0 small">No reference/discount cases for the selected date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop

@push('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
    @if ($payments->count() > 0)
    $('#auditTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 15,
        language: {
            search: '<i class="fas fa-search"></i> Search:',
            searchPlaceholder: 'Patient, bill no, reference...',
            emptyTable: 'No cases found.'
        }
    });
    @endif
});
</script>
@endpush
