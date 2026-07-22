@extends('adminlte::page')

@section('title', 'Appointment List')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Appointment List</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('appointments.approve') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-check-circle"></i> Approval
                </a>
                <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
<style>
    .appointment-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .appointment-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .appointment-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .appointment-card-body {
        padding: 0;
    }

    /* Filter Form */
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 180px;
    }

    .filter-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.375rem;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background: #ffffff;
        color: #111827;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        text-decoration: none;
    }

    .btn-filter-primary {
        background: #3b82f6;
        color: #ffffff;
        border: 1px solid #3b82f6;
    }

    .btn-filter-primary:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        text-decoration: none;
    }

    .btn-filter-outline {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-filter-outline:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        text-decoration: none;
    }

    .total-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #0369a1;
        background: #e0f2fe;
        border-radius: 0.375rem;
    }

    /* Table Styles */
    .appointment-table {
        width: 100%;
        text-align: left;
        font-size: 0.875rem;
        border-collapse: collapse;
    }

    .appointment-table thead {
        background: #f9fafb;
    }

    .appointment-table thead th {
        padding: 1rem 1rem;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .appointment-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.15s ease-in-out;
    }

    .appointment-table tbody tr:last-child {
        border-bottom: none;
    }

    .appointment-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .appointment-table tbody td {
        padding: 1rem;
        color: #374151;
        vertical-align: middle;
    }

    .patient-name {
        font-weight: 600;
        color: #111827;
    }

    .patient-code {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.125rem;
    }

    .text-muted-custom {
        color: #9ca3af;
    }

    .serial-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-default {
        background: #f3f4f6;
        color: #374151;
    }

    /* Delete Button */
    .btn-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        padding: 0;
        font-size: 0.875rem;
        color: #ef4444;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.125rem;
        font-weight: 500;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
        }

        .filter-group {
            width: 100%;
        }

        .filter-actions {
            width: 100%;
        }

        .btn-filter {
            flex: 1;
            justify-content: center;
        }

        .appointment-table {
            font-size: 0.8125rem;
        }

        .appointment-table thead th,
        .appointment-table tbody td {
            padding: 0.75rem 0.5rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="appointment-card">
        {{-- Header --}}
        <div class="appointment-card-header">
            <h2 class="appointment-card-title">
                <i class="fas fa-calendar-check mr-2" style="color: #3b82f6;"></i>
                Appointments
            </h2>
        </div>

        {{-- Filter Section --}}
        <div class="appointment-card-header" style="border-top: 1px solid #e5e7eb;">
            <form method="GET" action="{{ route('appointments.list') }}">
                <div class="filter-form">
                    <div class="filter-group">
                        <label>Appointment Date</label>
                        <input type="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-filter-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('appointments.list') }}" class="btn-filter btn-filter-outline">
                            Reset
                        </a>
                    </div>
                    <div class="filter-group" style="flex: 0; text-align: right;">
                        <span class="total-badge">
                            <i class="fas fa-list-ul mr-1"></i>
                            Total: {{ $appointments->total() }}
                        </span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="appointment-card-body">
            <div class="table-responsive">
                <table class="appointment-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 22%;">Patient</th>
                            <th style="width: 13%;">Mobile</th>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 11%;">Time</th>
                            <th style="width: 8%;" class="text-center">Serial</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Remarks</th>
                            <th style="width: 6%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $key => $app)
                            @php $status = $app->status ?? 'pending'; @endphp
                            <tr>
                                <td class="text-muted-custom">{{ $appointments->firstItem() + $key }}</td>
                                <td>
                                    <span class="patient-name">{{ $app->patientname ?? 'Unknown Patient' }}</span>
                                    @if(!empty($app->patientcode))
                                        <div class="patient-code">{{ $app->patientcode }}</div>
                                    @endif
                                </td>
                                <td>{{ $app->mobile_no ?? '-' }}</td>
                                <td>
                                    <i class="far fa-calendar-check text-muted-custom mr-1"></i>
                                    {{ date('d M, Y', strtotime($app->appointment_date)) }}
                                </td>
                                <td>
                                    @if(data_get($app, 'appointment_time'))
                                        <i class="far fa-clock text-muted-custom mr-1"></i>
                                        {{ date('h:i A', strtotime($app->appointment_time)) }}
                                    @else
                                        <span class="text-muted-custom">Not set</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="serial-badge">{{ sprintf('%02d', $app->serial) }}</span>
                                </td>
                                <td>
                                    @if($status == 'pending')
                                        <span class="status-badge status-pending">Pending</span>
                                    @elseif($status == 'approved')
                                        <span class="status-badge status-approved">Approved</span>
                                    @elseif($status == 'completed')
                                        <span class="status-badge status-completed">Done</span>
                                    @else
                                        <span class="status-badge status-default">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted-custom">{{ $app->remarks ?? '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('appointments.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete Appointment">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No appointments found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($appointments->hasPages())
            <div class="appointment-card-header" style="border-top: 1px solid #e5e7eb;">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</div>
@stop
