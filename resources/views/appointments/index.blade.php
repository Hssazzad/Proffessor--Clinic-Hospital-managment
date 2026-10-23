@extends('adminlte::page')

@section('title', 'Appointment List')

@section('content_header')
    <h1 class="text-primary">Appointment List</h1>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('css/hospital-erp.css') }}">
@endpush

@section('content')
<div class="hospital-page">
    <div class="card hospital-box">
        {{-- Box Header --}}
        <div class="card-header with-border">
            <h3 class="card-title">Appointment List</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('appointments.approve') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-check-circle"></i> Approval
                </a>
                <a href="{{ route('appointments.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="filter-bar">
            <form method="GET" action="{{ route('appointments.list') }}" class="row">
                <div class="col-sm-4 col-md-3">
                    <div class="form-group">
                        <label>Appointment Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                    </div>
                </div>
                <div class="col-sm-4 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-4 col-md-6">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('appointments.list') }}" class="btn btn-default">
                                Reset
                            </a>
                            <span class="ml-2 total-label">
                                <i class="fas fa-list-ul"></i> Total: {{ $appointments->total() }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive table-wrap">
            <table class="table table-bordered table-hover hospital-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Patient</th>
                        <th style="width: 12%;">Mobile</th>
                        <th style="width: 13%;">Date</th>
                        <th style="width: 10%;">Time</th>
                        <th style="width: 7%;" class="text-center">Serial</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;">Remarks</th>
                        <th style="width: 8%;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $key => $app)
                        @php $status = $app->status ?? 'pending'; @endphp
                        <tr>
                            <td class="text-muted">{{ $appointments->firstItem() + $key }}</td>
                            <td>
                                <span class="patient-name">{{ $app->patientname ?? 'Unknown Patient' }}</span>
                                @if(!empty($app->patientcode))
                                    <div class="patient-code">{{ $app->patientcode }}</div>
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
                                <span class="serial-badge">{{ sprintf('%02d', $app->serial) }}</span>
                            </td>
                            <td>
                                @if($status == 'pending')
                                    <span class="hosp-badge hosp-badge-pending">Pending</span>
                                @elseif($status == 'approved')
                                    <span class="hosp-badge hosp-badge-approved">Approved</span>
                                @elseif($status == 'completed')
                                    <span class="hosp-badge hosp-badge-completed">Done</span>
                                @else
                                    <span class="hosp-badge hosp-badge-default">{{ ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $app->remarks ?? '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('appointments.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-row-action" title="Delete Appointment">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>&nbsp; No appointments found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($appointments->hasPages())
            <div class="table-footer">
                <div>
                    Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }}
                    of {{ $appointments->total() }} entries
                </div>
                <div>
                    {{ $appointments->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@stop
