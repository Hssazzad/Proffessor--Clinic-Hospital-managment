@extends('adminlte::page')

@section('title', 'USG Report')

@section('content_header')
<h1>USG Report</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">USG Report Details</h3>
        <div>
            <a href="{{ route('usg-reports.edit', $usgReport->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('usg-reports.index') }}" class="btn btn-default btn-sm">Back</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-sm mb-4">
            <tr><th style="width:200px;">Patient</th><td>{{ $usgReport->patient->patientname ?? '-' }}</td></tr>
            <tr><th>Patient Code</th><td>{{ $usgReport->patientcode }}</td></tr>
            <tr><th>Phone</th>
                <td>
                    @if($usgReport->patient->mobile_no)
                        <a href="tel:{{ $usgReport->patient->mobile_no }}">{{ $usgReport->patient->mobile_no }}</a>
                    @else - @endif
                </td></tr>
            <tr><th>Doctor</th><td>{{ $usgReport->doctor->doctor_name ?? '-' }}</td></tr>
            <tr><th>Report Date</th><td>{{ $usgReport->report_date->format('d M Y') }}</td></tr>
            <tr><th>Report Status</th><td>{{ ucfirst($usgReport->status) }}</td></tr>
            <tr><th>Follow-up Date</th>
                <td>{{ $usgReport->followup_date ? $usgReport->followup_date->format('d M Y') : '—' }}</td></tr>
            <tr><th>Follow-up Status</th>
                <td>
                    @if($usgReport->followup)
                        {{ ucfirst($usgReport->followup->status) }}
                    @else — @endif
                </td></tr>
            <tr><th>Created By</th><td>{{ $usgReport->creator->name ?? '-' }}</td></tr>
        </table>

        <h5>Findings</h5>
        <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;">{{ $usgReport->findings }}</div>

        <h5 class="mt-3">Impression</h5>
        <div class="border rounded p-3 bg-light">{{ $usgReport->impression ?: '—' }}</div>

        <h5 class="mt-4">Contact History</h5>
        @if($usgReport->followup)
            <table class="table table-bordered table-sm">
                <tr><th>Status</th><td>{{ ucfirst($usgReport->followup->status) }}</td></tr>
                <tr><th>Contacted At</th><td>{{ $usgReport->followup->contacted_at ? $usgReport->followup->contacted_at->format('d M Y h:i A') : '—' }}</td></tr>
                <tr><th>Contacted By</th><td>{{ $usgReport->followup->contactedBy->name ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $usgReport->followup->notes ?: '—' }}</td></tr>
            </table>
        @else
            <p class="text-muted">No follow-up scheduled for this report.</p>
        @endif
    </div>
</div>
@stop
