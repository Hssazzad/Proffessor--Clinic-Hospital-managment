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
    .table-v-align { vertical-align: middle !important; }
    .badge-status { font-size: 0.85rem; padding: 0.4em 0.7em; min-width: 86px; }
</style>

<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <form method="GET" action="{{ route('appointments.list') }}" class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Appointment Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('appointments.list') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
                <div class="col-md-3 mb-2 text-md-right">
                    <span class="badge badge-info p-2">Total Records: {{ $appointments->total() }}</span>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 table-v-align">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="23%">Patient</th>
                            <th width="14%">Mobile</th>
                            <th width="16%">Date</th>
                            <th width="12%">Time</th>
                            <th width="10%" class="text-center">Serial</th>
                            <th width="12%">Status</th>
                            <th width="8%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $key => $app)
                            @php $status = $app->status ?? 'pending'; @endphp
                            <tr>
                                <td>{{ $appointments->firstItem() + $key }}</td>
                                <td>
                                    <span class="font-weight-bold">{{ $app->patientname ?? 'Unknown Patient' }}</span>
                                    @if(!empty($app->patientcode))
                                        <div class="small text-muted">{{ $app->patientcode }}</div>
                                    @endif
                                </td>
                                <td>{{ $app->mobile_no ?? '-' }}</td>
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
                                    @if($status == 'pending')
                                        <span class="badge badge-warning badge-status">Pending</span>
                                    @elseif($status == 'approved')
                                        <span class="badge badge-success badge-status">Approved</span>
                                    @elseif($status == 'completed')
                                        <span class="badge badge-primary badge-status">Done</span>
                                    @else
                                        <span class="badge badge-secondary badge-status">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $app->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p class="h5">No appointments found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($appointments->hasPages())
            <div class="card-footer bg-white">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</div>
@stop
