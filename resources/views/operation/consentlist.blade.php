@extends('adminlte::page')

@section('title', 'Consent Forms List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 page-main-title">
                <span class="page-title-icon"><i class="fas fa-list"></i></span>
                Operation Consent Forms
            </h1>
            <ol class="breadcrumb mt-1 p-0" style="background:transparent;font-size:11px;">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item active">Consent Forms</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary shadow">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Patient Code</th>
                    <th>Reg No</th>
                    <th>Operation</th>
                    <th>Surgeon</th>
                    <th>Date</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consents as $consent)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $consent->patientname }}</td>
                        <td>{{ $consent->patientcode }}</td>
                        <td>{{ $consent->reg_no ?? 'N/A' }}</td>
                        <td>{{ $consent->operation_name ?? 'N/A' }}</td>
                        <td>{{ $consent->surgeon_name ?? 'N/A' }}</td>
                        <td>{{ $consent->consent_date ?? ($consent->created_at ? $consent->created_at->format('d M Y') : 'N/A') }}</td>
                        <td>
                            <a href="{{ route('operation.consent.create', $consent->admission_id) }}" class="btn btn-sm btn-info" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('operation.consent.print', $consent->id) }}" target="_blank" class="btn btn-sm btn-success" title="Print">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No consent forms found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($consents, 'links'))
        <div class="card-footer">
            {{ $consents->links() }}
        </div>
    @endif
</div>
@stop

@push('css')
<style>
    .page-main-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .page-title-icon {
        display: inline-block;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 8px;
        text-align: center;
        line-height: 36px;
        margin-right: 8px;
        font-size: 16px;
    }
</style>
@endpush
