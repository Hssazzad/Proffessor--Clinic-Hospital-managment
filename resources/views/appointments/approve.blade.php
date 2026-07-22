@extends('adminlte::page')

@section('title', 'Approve Appointments')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Approve Appointments</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('appointments.list') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-history"></i> Appointment List
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
    .approve-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .approve-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .approve-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
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
    .approve-table {
        width: 100%;
        text-align: left;
        font-size: 0.875rem;
        border-collapse: collapse;
    }

    .approve-table thead {
        background: #f9fafb;
    }

    .approve-table thead th {
        padding: 1rem 1rem;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .approve-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.15s ease-in-out;
    }

    .approve-table tbody tr:last-child {
        border-bottom: none;
    }

    .approve-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .approve-table tbody td {
        padding: 1rem;
        color: #374151;
        vertical-align: middle;
    }

    .patient-name {
        font-weight: 600;
        color: #111827;
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

    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        border: none;
    }

    .btn-approve {
        background: #10b981;
        color: #ffffff;
    }

    .btn-approve:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }

    .btn-done {
        background: #3b82f6;
        color: #ffffff;
    }

    .btn-done:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }

    .btn-finalized {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
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

    /* Footer */
    .approve-card-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 0.8125rem;
        color: #6b7280;
    }

    /* Alert Styles */
    .alert-modern {
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success-modern {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-danger-modern {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert-close {
        margin-left: auto;
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        opacity: 0.7;
    }

    .alert-close:hover {
        opacity: 1;
    }
</style>

<div class="container-fluid">
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert-modern alert-success-modern">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-modern alert-danger-modern">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="approve-card">
        {{-- Header --}}
        <div class="approve-card-header">
            <h2 class="approve-card-title">
                <i class="fas fa-clipboard-check mr-2" style="color: #10b981;"></i>
                Patient Appointments
            </h2>
            <span class="total-badge">
                <i class="fas fa-list-ul mr-1"></i>
                Total: {{ count($appointments) }}
            </span>
        </div>

        {{-- Table --}}
        <div>
            <div class="table-responsive">
                <table class="approve-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 22%;">Patient Name</th>
                            <th style="width: 18%;">Appointment Date</th>
                            <th style="width: 12%;">Time</th>
                            <th style="width: 12%;" class="text-center">Serial No</th>
                            <th style="width: 13%;">Status</th>
                            <th style="width: 18%;" class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $key => $app)
                        <tr>
                            <td class="text-muted-custom">{{ $key + 1 }}</td>
                            <td>
                                <span class="patient-name">{{ $app->patientname }}</span>
                            </td>
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
                                @php $status = $app->status ?? 'pending'; @endphp
                                @if($status == 'pending')
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($status == 'approved')
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @elseif($status == 'completed')
                                    <span class="status-badge status-completed">
                                        <i class="fas fa-clipboard-check mr-1"></i> Done
                                    </span>
                                @else
                                    <span class="status-badge status-default">
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($status == 'pending')
                                    <form action="{{ route('appointments.updateStatus') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                        <button type="submit" class="btn-action btn-approve"
                                                onclick="return confirm('Are you sure you want to approve this appointment?')">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>
                                    </form>
                                @elseif($status == 'approved')
                                    <form action="{{ route('appointments.updateStatus') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn-action btn-done"
                                                onclick="return confirm('Mark this appointment as done?')">
                                            <i class="fas fa-clipboard-check"></i> Done
                                        </button>
                                    </form>
                                @else
                                    <button class="btn-action btn-finalized" disabled>
                                        <i class="fas fa-user-check"></i> Finalized
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No appointments found for approval.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="approve-card-footer">
            <i class="fas fa-info-circle mr-1"></i> Click the approve button to confirm a patient serial.
        </div>
    </div>
</div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert-modern").fadeOut('slow');
            }, 5000);
        });
    </script>
@stop
