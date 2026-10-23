@extends('adminlte::page')

@section('title', 'Operation Consent Form')

@section('content_header')
    <h1>Create Operation Consent Form</h1>
@stop

@section('content')

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('operation.consent.store') }}">
        @csrf
        <input type="hidden" name="admission_id" value="{{ $admission->admission_id }}">
        <input type="hidden" name="patient_id" value="{{ $admission->patient_id }}">

        <div class="card">
            <div class="card-body text-center py-3">
                <h3 class="mb-1 font-weight-bold">{{ config('clinic.name_bn') }}</h3>
                <div class="text-muted">{{ config('clinic.name') }}</div>
                <small class="text-muted">
                    {{ config('clinic.address') }} — মোবাইলঃ {{ config('clinic.phone') }}
                </small>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Patient Information (Auto-filled from Admission)</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Patient Name</label>
                        <input type="text" class="form-control" value="{{ $admission->patientname }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Age</label>
                        <input type="text" class="form-control" value="{{ $admission->age }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Gender</label>
                        <input type="text" class="form-control" value="{{ $admission->gender }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Weight (kg)</label>
                        <input type="text" name="weight" class="form-control"
                               value="{{ old('weight', $existing->weight ?? '') }}" placeholder="e.g. 60">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Father/Husband's Name</label>
                        <input type="text" class="form-control"
                               value="{{ $admission->patientfather ?: $admission->patienthusband }}" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Patient Id</label>
                        <input type="text" class="form-control" value="{{ $admission->patientcode }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Admission ID</label>
                        <input type="text" class="form-control" value="{{ $admission->admissionid }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Village</label>
                        <input type="text" class="form-control" value="{{ $admission->village }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Thana</label>
                        <input type="text" class="form-control" value="{{ $admission->upozila }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Operation Information</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Ward No.</label>
                        <input type="text" name="ward_no" class="form-control" 
                               value="{{ old('ward_no', $existing->ward_no ?? ($admission->bed_number ?? 'N/A')) }}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Bed No.</label>
                        <input type="text" name="bed_no" class="form-control" 
                               value="{{ old('bed_no', $existing->bed_no ?? ($admission->bed_name ?? ($admission->bed_number ?? 'N/A'))) }}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Disease Name</label>
                        <input type="text" name="disease_name" class="form-control"
                               value="{{ old('disease_name', $existing->disease_name ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Operation Name <span class="text-danger">*</span></label>
                        <select name="operation_name" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($operationNames as $op)
                                <option value="{{ $op->name }}"
                                    {{ old('operation_name', $existing->operation_name ?? '') == $op->name ? 'selected' : '' }}>
                                    {{ $op->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Operation Date <span class="text-danger">*</span></label>
                        <input type="date" name="operation_date" class="form-control" required
                               value="{{ old('operation_date', isset($existing->operation_date) ? \Carbon\Carbon::parse($existing->operation_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Time</label>
                        <input type="text" name="operation_time" class="form-control"
                               placeholder="e.g. 10:00 AM"
                               value="{{ old('operation_time', $existing->operation_time ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Surgeon</label>
                        <select name="surgeon_name" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->doctor_name }}"
                                    {{ old('surgeon_name', $existing->surgeon_name ?? $admission->doctor_name) == $doc->doctor_name ? 'selected' : '' }}>
                                    {{ $doc->doctor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Assistant Surgeon</label>
                        <select name="assistant_surgeon" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($assistantSurgeons as $doc)
                                <option value="{{ $doc->name }}"
                                    {{ old('assistant_surgeon', $existing->assistant_surgeon ?? '') == $doc->name ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Anesthesiologist</label>
                    <select name="anesthesiologist" class="form-control">
                        <option value="">-- Select --</option>
                        @foreach($anesthesiologists as $doc)
                            <option value="{{ $doc->name }}"
                                {{ old('anesthesiologist', $existing->anesthesiologist ?? '') == $doc->name ? 'selected' : '' }}>
                                {{ $doc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Operation Note</label>
                    <textarea name="operation_note" class="form-control" rows="3">{{ old('operation_note', $existing->operation_note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Consent & Signature</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-light border">
                    আমি ডাক্তার সাহেবের সঙ্গে আলোচনা করিয়া আমার / রোগীর রোগ এবং অস্ত্রোপচারের ঝুঁকি সম্পর্কে অবগত হইয়া সম্পূর্ণ স্ব-ইচ্ছায় এবং কোন প্রকার ভয় ও প্রলোভন ছাড়াই অজ্ঞান করিয়া অস্ত্রোপচার করিতে রাজি আছি। অস্ত্রোপচারে ইচ্ছাকৃত ভুল ছাড়া অন্য কোন দুর্ঘটনার জন্য ক্লিনিক কর্তৃপক্ষ বা ডাক্তার এবং তার সহকর্মীরা দায়ী থাকিবেন না।
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Patient Realitive signature (Name)</label>
                        <input type="text" name="doctor_signature_name" class="form-control"
                               value="{{ old('doctor_signature_name', $existing->doctor_signature_name ?? $admission->doctor_name) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('operation.consent.search') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save & Print
                </button>
            </div>
        </div>
    </form>
@stop
