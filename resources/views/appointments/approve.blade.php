@extends('adminlte::page')

@section('title', 'Approve Appointments')

@section('content_header')
    <h1 class="text-primary">Approve Appointments</h1>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('css/hospital-erp.css') }}">
@endpush

@section('content')
<div class="hospital-page">
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card hospital-box">
        {{-- Box Header --}}
        <div class="card-header with-border">
            <h3 class="card-title">Patient Appointments</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('appointments.list') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-history"></i> Appointment List
                </a>
                <a href="{{ route('appointments.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
                <span class="ml-2 total-label">
                    <i class="fas fa-list-ul"></i> Total: {{ count($appointments) }}
                </span>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive table-wrap">
            <table class="table table-bordered table-hover hospital-table">
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
                        <td class="text-muted">{{ $key + 1 }}</td>
                        <td>
                            <span class="patient-name">{{ $app->patientname }}</span>
                        </td>
                        <td>
                            <i class="far fa-calendar-check text-muted mr-1"></i>
                            {{ date('d M, Y', strtotime($app->appointment_date)) }}
                        </td>
                        <td>
                            @if(data_get($app, 'appointment_time'))
                                <i class="far fa-clock text-muted mr-1"></i>
                                {{ date('h:i A', strtotime($app->appointment_time)) }}
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="serial-badge">{{ sprintf('%02d', $app->serial) }}</span>
                        </td>
                        <td>
                            @php $status = $app->status ?? 'pending'; @endphp
                            @if($status == 'pending')
                                <span class="hosp-badge hosp-badge-pending">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </span>
                            @elseif($status == 'approved')
                                <span class="hosp-badge hosp-badge-approved">
                                    <i class="fas fa-check-circle mr-1"></i> Approved
                                </span>
                            @elseif($status == 'completed')
                                <span class="hosp-badge hosp-badge-completed">
                                    <i class="fas fa-clipboard-check mr-1"></i> Done
                                </span>
                            @else
                                <span class="hosp-badge hosp-badge-default">
                                    {{ ucfirst($status) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if($status == 'pending')
                                <form action="{{ route('appointments.updateStatus') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                    <button type="submit" class="btn btn-success btn-row-action"
                                            onclick="return confirm('Are you sure you want to approve this appointment?')">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>
                                </form>
                            @elseif($status == 'approved')
                                <form action="{{ route('appointments.updateStatus') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-info btn-row-action"
                                            onclick="return confirm('Mark this appointment as done?')">
                                        <i class="fas fa-clipboard-check"></i> Done
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-default btn-row-action" disabled>
                                    <i class="fas fa-user-check"></i> Finalized
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>&nbsp; No appointments found for approval.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="table-footer">
            <div>
                <i class="fas fa-info-circle"></i>&nbsp; Click the approve button to confirm a patient serial.
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert").fadeOut('slow');
            }, 5000);
        });
    </script>
@endpush
