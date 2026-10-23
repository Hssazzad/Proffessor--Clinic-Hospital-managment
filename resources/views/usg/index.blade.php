@extends('adminlte::page')

@section('title', 'USG Reports')

@section('content_header')
<h1>USG Reports</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">All USG Reports</h3>
        <a href="{{ route('usg-reports.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add USG Report
        </a>
    </div>

    <div class="card-body">
        <form method="GET" class="form-row align-items-end mb-3">
            <div class="form-group col-md-3">
                <label>Search (Name / Code / Phone)</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Patient...">
            </div>
            <div class="form-group col-md-3">
                <label>Doctor</label>
                <select name="doctor_id" class="form-control">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" {{ request('doctor_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->doctor_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="form-group col-md-2">
                <label>Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="form-group col-md-2">
                <label>Follow-up</label>
                <select name="followup_status" class="form-control">
                    <option value="">All</option>
                    <option value="with" {{ request('followup_status') == 'with' ? 'selected' : '' }}>With Follow-up</option>
                    <option value="without" {{ request('followup_status') == 'without' ? 'selected' : '' }}>Without Follow-up</option>
                    <option value="due" {{ request('followup_status') == 'due' ? 'selected' : '' }}>Due / Overdue</option>
                    <option value="upcoming" {{ request('followup_status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                </select>
            </div>
            <div class="form-group col-md-12">
                <button class="btn btn-info btn-sm"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('usg-reports.index') }}" class="btn btn-default btn-sm">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Report Date</th>
                        <th>Patient</th>
                        <th>Code</th>
                        <th>Doctor</th>
                        <th>Follow-up Date</th>
                        <th>Follow-up Status</th>
                        <th>Report Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('d M Y') }}</td>
                            <td>{{ $report->patient->patientname ?? '-' }}</td>
                            <td>{{ $report->patientcode }}</td>
                            <td>{{ $report->doctor->doctor_name ?? '-' }}</td>
                            <td>
                                @if($report->followup_date)
                                    {{ $report->followup_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($report->followup)
                                    <span class="badge badge-{{ $report->followup->status == 'pending' ? 'warning' : ($report->followup->status == 'contacted' ? 'info' : ($report->followup->status == 'completed' ? 'success' : 'secondary')) }}">
                                        {{ ucfirst($report->followup->status) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $report->status == 'pending' ? 'light' : ($report->status == 'completed' ? 'success' : 'danger') }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('usg-reports.show', $report->id) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('usg-reports.edit', $report->id) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('usg-reports.destroy', $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No USG reports found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reports->links() }}
    </div>
</div>
@stop
