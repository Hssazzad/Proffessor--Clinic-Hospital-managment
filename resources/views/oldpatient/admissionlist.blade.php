@extends('adminlte::page')

@section('title', 'Admission Patient List')

@section('content_header')
    <h1>Admission Patient List (Audit Report)</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form method="get" class="form-row align-items-end">
            <div class="col-md-4 mb-2">
                <label>Name / Code / Admission ID / Doctor</label>
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            </div>
            <div class="col-md-2 mb-2">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    @foreach($stageNames as $code => $label)
                        <option value="{{ $code }}" {{ (string) request('status') === (string) $code ? 'selected' : '' }}>
                            Stage {{ $code }}: {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-md-2 mb-2">
                <label>To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-2 mb-2">
                <button class="btn btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('web-oldpatient.admissionlist') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Code</th>
                    <th>Admission ID</th>
                    <th>Bed</th>
                    <th>Doctor</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Admitted At</th>
                </tr>
            </thead>
            <tbody>
            @forelse($admissions as $row)
                @php
                    $stage = (int) ($row->status ?? 1);
                    $label = $stageNames[$stage] ?? 'Unknown';
                @endphp
                <tr>
                    <td><strong>{{ $row->patientname ?? '-' }}</strong></td>
                    <td>{{ $row->patientcode }}</td>
                    <td>{{ $row->admissionid ?? '-' }}</td>
                    <td>{{ $row->bed_no ?? '-' }}</td>
                    <td>{{ $row->doctor_name ?: '-' }}</td>
                    <td>{{ $row->department ?: '-' }}</td>
                    <td><span class="badge badge-info">Stage {{ $stage }}: {{ $label }}</span></td>
                    <td>{{ $row->created_at ? date('d-m-Y h:i A', strtotime($row->created_at)) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No admission records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($admissions->hasPages())
        <div class="card-footer">{{ $admissions->links() }}</div>
    @endif
</div>
@stop
