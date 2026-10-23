@extends('adminlte::page')

@section('title', 'Pending Payment Approvals')

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
    .ref-chip {
        background: #e0f2f1;
        color: #00695c;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
        white-space: nowrap;
    }
    .table-hover tbody tr {
        transition: background 0.15s ease;
    }
    .pending-table td {
        vertical-align: middle;
    }
    .pending-table .money {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .empty-state {
        padding: 48px 20px;
        text-align: center;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 42px;
        color: #c8e6c9;
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            <i class="fas fa-hourglass-half text-warning mr-2"></i>Pending Payment Approvals
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.invoice.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Pending Approvals</li>
        </ol>
    </div>
@stop

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="row mb-4">
    <div class="col-md-4 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-warning mr-3"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-label">Pending Invoices</div>
                    <div class="stat-value">{{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-2">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center py-3">
                <div class="stat-icon bg-teal mr-3"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="stat-label">Total Amount</div>
                    <div class="stat-value">৳ {{ number_format($summary['total_amount'], 0) }}</div>
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

<div class="card">
    <div class="card-header py-3">
        <h3 class="card-title">
            <i class="fas fa-list-ul text-primary mr-2"></i>Payments Awaiting Approval
        </h3>
        <span class="badge badge-warning ml-2">{{ $pendingPayments->count() }} Pending</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="pendingTable" class="table table-hover table-striped pending-table mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Bill No</th>
                        <th>Patient</th>
                        <th>Mobile</th>
                        <th class="money">Total Bill</th>
                        <th class="money">Discount</th>
                        <th class="money">Paid</th>
                        <th class="money">Due</th>
                        <th>Reference By</th>
                        <th>Payment Date</th>
                        <th>Collected By</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingPayments as $payment)
                        @php
                            $discountAmount = $payment->Discount ?? $payment->DiscountAmount ?? 0;
                            $discountPercent = $payment->DiscountPercent
                                ?? ($payment->TotalBill > 0 ? round(($discountAmount / $payment->TotalBill) * 100, 1) : 0);
                        @endphp
                        <tr>
                            <td>
                                <span class="font-weight-bold text-primary">{{ $payment->BillNo }}</span>
                            </td>
                            <td>
                                <div class="font-weight-semibold">{{ $payment->PatientName }}</div>
                                <div class="text-muted small">{{ $payment->PatientCode }}</div>
                            </td>
                            <td>{{ $payment->MobileNo }}</td>
                            <td class="money">{{ number_format($payment->TotalBill, 0) }}</td>
                            <td class="money">
                                @if ($discountAmount > 0)
                                    <div class="font-weight-bold text-warning">{{ number_format($discountAmount, 0) }}</div>
                                    <div class="text-muted small">{{ $discountPercent }}%</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="money font-weight-bold text-success">{{ number_format($payment->PaidAmount, 0) }}</td>
                            <td class="money font-weight-bold text-danger">{{ number_format($payment->DueAmount, 0) }}</td>
                            <td><span class="ref-chip">{{ $payment->reference_name ?? '-' }}</span></td>
                            <td>{{ $payment->PaymentDate ? \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y') : '-' }}</td>
                            <td>{{ $payment->CollectedBy }}</td>
                            <td class="text-center" style="white-space:nowrap;">
                                <form action="{{ route('billing.admin.approve', $payment->ID) }}" method="POST" class="approve-form d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm px-3">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('billing.admin.reject', $payment->ID) }}" method="POST" class="reject-form d-inline-block ml-1">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h5 class="text-muted">No pending payments right now</h5>
                                    <p class="mb-0 small">All invoices are approved and ready to print.</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    @if ($pendingPayments->count() > 0)
    $('#pendingTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            search: '<i class="fas fa-search"></i> Search:',
            searchPlaceholder: 'Patient, bill no, mobile...',
            emptyTable: 'No pending payments found.'
        }
    });
    @endif

    $('.approve-form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Approve this payment?',
            html: 'The invoice will be marked as <b>approved</b> and can be printed.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Yes, approve',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $('.reject-form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Reject this payment?',
            html: 'The invoice will be marked as <b>rejected</b>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times"></i> Yes, reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
