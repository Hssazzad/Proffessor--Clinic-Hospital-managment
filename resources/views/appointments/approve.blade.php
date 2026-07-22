{{-- AdminLTE এর মেইন পেজ লেআউট ব্যবহার করা হচ্ছে --}}
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
    .table-v-align { vertical-align: middle !important; }
    .badge-status { font-size: 0.85rem; padding: 0.4em 0.7em; min-width: 80px; }
    .card-outline-primary { border-top: 3px solid #007bff; }
    /* বাটন হোভার ইফেক্ট */
    .btn-approve:hover { transform: translateY(-1px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
</style>

<div class="container-fluid">
    {{-- সাকসেস মেসেজ দেখানোর জন্য --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-outline-primary shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-list text-primary mr-1"></i> Patient Appointments
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info p-2">Total Records: {{ count($appointments) }}</span>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped m-0 table-v-align">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="22%">Patient Name</th>
                                    <th width="18%">Appointment Date</th>
                                    <th width="12%">Time</th>
                                    <th width="15%" class="text-center">Serial No</th>
                                    <th width="15%">Status</th>
                                    <th width="20%" class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $key => $app)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <span class="text-dark font-weight-bold">{{ $app->patientname }}</span>
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
                                        <span class="badge badge-pill badge-light border">{{ sprintf('%02d', $app->serial) }}</span>
                                    </td>
                                    <td>
                                        @php $status = $app->status ?? 'pending'; @endphp
                                        @if($status == 'pending')
                                            <span class="badge badge-warning badge-status shadow-sm">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                        @elseif($status == 'approved')
                                            <span class="badge badge-success badge-status shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Approved
                                            </span>
                                        @elseif($status == 'completed')
                                            <span class="badge badge-primary badge-status shadow-sm">
                                                <i class="fas fa-clipboard-check mr-1"></i> Done
                                            </span>
                                        @else
                                            <span class="badge badge-secondary badge-status shadow-sm">
                                                {{ ucfirst($status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($status == 'pending')
                                            {{-- Approve করার জন্য ফর্ম --}}
                                            <form action="{{ route('appointments.updateStatus') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                                <button type="submit" class="btn btn-sm btn-success btn-approve px-3 shadow-sm" 
                                                        onclick="return confirm('Are you sure you want to approve this appointment?')">
                                                    <i class="fas fa-check-circle mr-1"></i> Approve
                                                </button>
                                            </form>
                                        @elseif($status == 'approved')
                                            <form action="{{ route('appointments.updateStatus') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm"
                                                        onclick="return confirm('Mark this appointment as done?')">
                                                    <i class="fas fa-clipboard-check mr-1"></i> Done
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-flat btn-light text-muted border" disabled>
                                                <i class="fas fa-user-check"></i> Finalized
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="h5">No appointments found for approval.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- ফুটার যদি প্রয়োজন হয় --}}
                <div class="card-footer bg-white small text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Click the approve button to confirm a patient serial.
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // ৫ সেকেন্ড পর সাকসেস মেসেজ অটো হাইড হবে
            setTimeout(function() {
                $(".alert").fadeOut('slow');
            }, 5000);
        });
    </script>
@stop
