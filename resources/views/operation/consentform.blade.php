@extends('adminlte::page')

@section('title', 'Operation Consent Form')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 page-main-title">
                <span class="page-title-icon"><i class="fas fa-file-signature"></i></span>
                Operation Consent Form
            </h1>
            <ol class="breadcrumb mt-1 p-0" style="background:transparent;font-size:11px;">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('operation.consent.index') }}">Consent List</a></li>
                <li class="breadcrumb-item active">Consent Form</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('operation.consent.index') }}" class="btn-back-modern">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
@php
    $data = $existingConsent ?? null;
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif

<div class="card card-outline card-primary shadow">
    <div class="card-header">
        <h3 class="card-title">Patient Information</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width:20%">Patient Name</th>
                <td style="width:30%">{{ $patient->patientname ?? ($admission->patient_name ?? 'N/A') }}</td>
                <th style="width:20%">Patient Code</th>
                <td style="width:30%">{{ $patient->patientcode ?? ($admission->patientcode ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Father/Husband</th>
                <td>{{ $admissionsData->father_husband_name ?? $admission->father_husband_name ?? 'N/A' }}</td>
                <th>Age</th>
                <td>{{ $patient->age ?? $admission->age ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Village</th>
                <td>{{ $admissionsData->village ?? $admission->village ?? 'N/A' }}</td>
                <th>Post Office</th>
                <td>{{ $admissionsData->post_office ?? $admission->post_office ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Police Station</th>
                <td>{{ $admissionsData->police_station ?? $admission->police_station ?? 'N/A' }}</td>
                <th>District</th>
                <td>{{ $admissionsData->district ?? $admission->district ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Reg No</th>
                <td>{{ $admission->reg_no ?? 'N/A' }}</td>
                <th>Admission Date</th>
                <td>{{ $admission->admission_date ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
</div>

<form method="POST" action="{{ route('operation.consent.store') }}">
    @csrf
    <input type="hidden" name="admission_id" value="{{ $admission_id }}">
    <input type="hidden" name="patientcode" value="{{ $patient->patientcode ?? ($admission->patientcode ?? '') }}">
    <input type="hidden" name="patient_name" value="{{ $patient->patientname ?? ($admission->patient_name ?? '') }}">
    <input type="hidden" name="father_husband_name" value="{{ $admissionsData->father_husband_name ?? $admission->father_husband_name ?? '' }}">
    <input type="hidden" name="age" value="{{ $patient->age ?? $admission->age ?? '' }}">
    <input type="hidden" name="village" value="{{ $admissionsData->village ?? $admission->village ?? '' }}">
    <input type="hidden" name="post_office" value="{{ $admissionsData->post_office ?? $admission->post_office ?? '' }}">
    <input type="hidden" name="police_station" value="{{ $admissionsData->police_station ?? $admission->police_station ?? '' }}">
    <input type="hidden" name="district" value="{{ $admissionsData->district ?? $admission->district ?? '' }}">

    <div class="card card-outline card-info shadow">
        <div class="card-header">
            <h3 class="card-title">Operation Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Operation Name <span class="text-danger">*</span></label>
                        <input type="text" name="operation_name" class="form-control" value="{{ old('operation_name', $data->operation_name ?? $admissionsData->operation_name ?? '') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Operation Date</label>
                        <input type="date" name="operation_date" class="form-control" value="{{ old('operation_date', $data->operation_date ?? $admissionsData->op_date ?? '') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Surgeon <span class="text-danger">*</span></label>
                        <input type="text" name="surgeon_name" class="form-control" value="{{ old('surgeon_name', $data->surgeon_name ?? $admissionsData->surgeon_name ?? '') }}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Anesthetist</label>
                        <input type="text" name="anesthetist" class="form-control" value="{{ old('anesthetist', $data->anesthetist ?? $admissionsData->anesthetist ?? '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Consent Date</label>
                        <input type="date" name="consent_date" class="form-control" value="{{ old('consent_date', $data->consent_date ?? date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success shadow">
        <div class="card-header">
            <h3 class="card-title">Consent Statement</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Consent Text</label>
                <textarea name="consent_text" class="form-control" rows="8" style="font-size:14px;">{{ old('consent_text', $data->consent_text ?? '') }}</textarea>
                <small class="text-muted">Default consent statement will be used if left empty.</small>
            </div>
        </div>
    </div>

    <div class="card card-outline card-warning shadow">
        <div class="card-header">
            <h3 class="card-title">Guardian / Attendant Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $data->guardian_name ?? '') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Relation</label>
                        <input type="text" name="relation" class="form-control" value="{{ old('relation', $data->relation ?? '') }}" placeholder="e.g. Father, Mother, Spouse">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-lg btn-primary px-5">
                <i class="fas fa-save mr-1"></i>
                {{ $data ? 'Update Consent Form' : 'Save Consent Form' }}
            </button>
            @if($data)
                <a href="{{ route('operation.consent.print', $data->id) }}" target="_blank" class="btn btn-lg btn-success px-4 ml-2">
                    <i class="fas fa-print mr-1"></i> Print
                </a>
            @endif
        </div>
    </div>
</form>
@stop

@push('css')
<style>
    .table th {
        background: #f4f6f9;
    }
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
    .btn-back-modern {
        display: inline-block;
        padding: 6px 16px;
        background: #6c757d;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s;
    }
    .btn-back-modern:hover {
        background: #5a6268;
        color: #fff;
        text-decoration: none;
    }
</style>
@endpush
