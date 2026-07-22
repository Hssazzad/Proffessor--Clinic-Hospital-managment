@extends('adminlte::page')

@section('title', 'Operation Consent Form')

@section('content_header')
    <h1>অপারেশন সম্মতি ফর্ম তৈরি করুন</h1>
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

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">রোগীর তথ্য (Admission থেকে স্বয়ংক্রিয়)</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>রোগীর নাম</label>
                        <input type="text" class="form-control" value="{{ $admission->patientname }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>বয়স</label>
                        <input type="text" class="form-control" value="{{ $admission->age }}" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>লিঙ্গ</label>
                        <input type="text" class="form-control" value="{{ $admission->gender }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>পিতার/স্বামীর নাম</label>
                        <input type="text" class="form-control"
                               value="{{ $admission->patientfather ?: $admission->patienthusband }}" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Patient Code / Reg No</label>
                        <input type="text" class="form-control" value="{{ $admission->patientcode }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Admission ID</label>
                        <input type="text" class="form-control" value="{{ $admission->admissionid }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>গ্রাম</label>
                        <input type="text" class="form-control" value="{{ $admission->village }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>থানা</label>
                        <input type="text" class="form-control" value="{{ $admission->upozila }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">অপারেশনের তথ্য</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>ওয়ার্ড নং</label>
                        <input type="text" name="ward_no" class="form-control" 
                               value="{{ old('ward_no', $existing->ward_no ?? ($admission->ward_no ?? 'N/A')) }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>বেড নং</label>
                        <input type="text" name="bed_no" class="form-control" 
                               value="{{ old('bed_no', $existing->bed_no ?? ($admission->bed_number ?? 'N/A')) }}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>পো (Post Office)</label>
                        <input type="text" name="post_office" class="form-control"
                               value="{{ old('post_office', $existing->post_office ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>রোগের নাম</label>
                        <input type="text" name="disease_name" class="form-control"
                               value="{{ old('disease_name', $existing->disease_name ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>অপারেশনের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="operation_name" class="form-control" required
                               value="{{ old('operation_name', $existing->operation_name ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>অপারেশনের তারিখ <span class="text-danger">*</span></label>
                        <input type="date" name="operation_date" class="form-control" required
                               value="{{ old('operation_date', isset($existing->operation_date) ? \Carbon\Carbon::parse($existing->operation_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>সময়</label>
                        <input type="text" name="operation_time" class="form-control"
                               placeholder="যেমনঃ সকাল ১০টা"
                               value="{{ old('operation_time', $existing->operation_time ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>সার্জন</label>
                        <input type="text" name="surgeon_name" class="form-control"
                               value="{{ old('surgeon_name', $existing->surgeon_name ?? $admission->doctor_name) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>সহযোগী সার্জন</label>
                        <input type="text" name="assistant_surgeon" class="form-control"
                               value="{{ old('assistant_surgeon', $existing->assistant_surgeon ?? '') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>অজ্ঞানকারী ডাক্তার</label>
                    <input type="text" name="anesthesiologist" class="form-control"
                           value="{{ old('anesthesiologist', $existing->anesthesiologist ?? '') }}">
                </div>

                <div class="form-group">
                    <label>অপারেশন নোট</label>
                    <textarea name="operation_note" class="form-control" rows="3">{{ old('operation_note', $existing->operation_note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">সম্মতি ও স্বাক্ষর</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-light border">
                    আমি ডাক্তার সাহেবের সঙ্গে আলোচনা করিয়া আমার / রোগীর রোগ এবং অস্ত্রোপচারের ঝুঁকি সম্পর্কে অবগত হইয়া সম্পূর্ণ স্ব-ইচ্ছায় এবং কোন প্রকার ভয় ও প্রলোভন ছাড়াই অজ্ঞান করিয়া অস্ত্রোপচার করিতে রাজি আছি। অস্ত্রোপচারে ইচ্ছাকৃত ভুল ছাড়া অন্য কোন দুর্ঘটনার জন্য ক্লিনিক কর্তৃপক্ষ বা ডাক্তার এবং তার সহকর্মীরা দায়ী থাকিবেন না।
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>ডাক্তারের স্বাক্ষর (নাম)</label>
                        <input type="text" name="doctor_signature_name" class="form-control"
                               value="{{ old('doctor_signature_name', $existing->doctor_signature_name ?? $admission->doctor_name) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('operation.consent.search') }}" class="btn btn-secondary">বাতিল</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> সংরক্ষণ ও প্রিন্ট
                </button>
            </div>
        </div>
    </form>
@stop