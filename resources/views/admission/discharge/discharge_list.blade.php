@extends('adminlte::page')

@section('title', 'রোগী ছাড়পত্র')

@section('content_header')
    <div class="cc-header" id="dischargePageHeader">
        <div class="cc-header-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="cc-header-text">
            <h1 class="cc-header-title">Patient Discharge</h1>
            <p class="cc-header-subtitle">Complete discharge documentation for admitted patients</p>
            <ul class="cc-breadcrumb">
                <li><a href="#">Admission</a></li>
                <li class="active">Discharge</li>
            </ul>
        </div>
        <div class="cc-header-actions">
            <a href="{{ route('admission.discharge.history') }}" class="cc-btn cc-btn-secondary btn-sm">
                <i class="fas fa-history mr-1"></i> Discharge History
            </a>
        </div>
    </div>
@stop

@section('content')
@php
    $multiIds = function ($value): array {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== ''));
    };
@endphp
<div id="ajaxMessage"></div>
@if(session('success'))
    <div class="cc-alert cc-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="cc-alert cc-alert-danger">{{ session('error') }}</div>
@endif

{{-- Step 1: Patient Selection --}}
<div id="patientSelectionScreen" class="cc-selection-screen">
    <div class="cc-selection-card">
        <h2 class="cc-selection-title">Patient Discharge</h2>
        <p class="cc-selection-subtitle">Select a patient to continue</p>
        <div class="cc-selection-search">
            <select id="patientSearchSelect" class="form-control cc-select-lg">
                <option value="">Search patient by name / ID / mobile...</option>
                @foreach($patients as $p)
                    <option value="{{ route('admission.discharge', ['patientcode' => $p->patientcode]) }}">
                        {{ $p->patientname }} — ID: {{ $p->patientcode }}@if(!empty($p->mobile_no)) — {{ $p->mobile_no }}@endif
                    </option>
                @endforeach
            </select>
        </div>
        <button type="button" id="btnSelectPatientContinue" class="cc-btn cc-btn-primary cc-btn-continue" disabled>
            Continue
        </button>
        @if($patients->count() === 0)
        <div class="cc-selection-empty">
            <p>No patients are currently ready for discharge.</p>
        </div>
        @endif
    </div>
</div>

<div id="dischargeWorkflow" class="cc-workflow">
    <div class="cc-form-pane" id="dischargeFormPane">

        @if($patientcode && $patient)
            @php
                $dischargeStatus = $discharge->status ?? null;
                $isPendingApproval = $dischargeStatus === 'pending';
                $isManagerApproved = $dischargeStatus === 'completed';
                $td = $templateDischarge ?? null;
            @endphp
            @php
                $summaryWard = optional($discharge)->ward ?? optional($dischargeBed)->bed_number ?? '—';
                $summaryDoctor = optional($discharge)->doctor_name ?? optional($dischargeDoctor)->doctor_name ?? '—';
            @endphp

            <!-- Compact Patient Summary -->
            <div class="cc-patient-bar">
                <div class="cc-patient-bar-top">
                    <div class="cc-patient-bar-name">{{ $patient->patientname }}</div>
                    <a href="{{ route('admission.discharge') }}" class="cc-btn cc-btn-secondary btn-sm cc-change-patient">Change Patient</a>
                </div>
                <div class="cc-patient-bar-grid">
                    <div class="cc-patient-bar-cell">
                        <span class="cc-patient-bar-label">ID</span>
                        <span class="cc-patient-bar-value">{{ $patient->patientcode }}@if(!empty($admission->admissionid)) / {{ $admission->admissionid }}@endif</span>
                    </div>
                    <div class="cc-patient-bar-cell">
                        <span class="cc-patient-bar-label">Mobile</span>
                        <span class="cc-patient-bar-value">{{ $patient->mobile_no ?? '—' }}</span>
                    </div>
                    <div class="cc-patient-bar-cell">
                        <span class="cc-patient-bar-label">Ward</span>
                        <span class="cc-patient-bar-value">{{ $summaryWard }}</span>
                    </div>
                    <div class="cc-patient-bar-cell">
                        <span class="cc-patient-bar-label">Doctor</span>
                        <span class="cc-patient-bar-value">{{ $summaryDoctor }}</span>
                    </div>
                    <div class="cc-patient-bar-cell">
                        <span class="cc-patient-bar-label">Status</span>
                        <span class="cc-patient-bar-value">
                            <span class="cc-status-pill {{ $isPendingApproval ? 'pending' : ($isManagerApproved ? 'approved' : 'draft') }}">
                                @if($isPendingApproval)
                                    Pending Approval
                                @elseif($isManagerApproved)
                                    Approved
                                @else
                                    Draft
                                @endif
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="cc-form-sections">
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">01</span>
                    <div>
                        <h4 class="cc-section-title">Patient / Admission Information</h4>
                        <small class="cc-section-desc">Basic patient and admission details</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-info-grid">
                        <div class="cc-info-item">
                            <div class="cc-info-label">মোবাইল নম্বর</div>
                            <div class="cc-info-value">{{ $patient->mobile_no ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-info-item">
                            <div class="cc-info-label">বয়স</div>
                            <div class="cc-info-value">{{ $patient->age ?? 'নেই' }} বছর</div>
                        </div>
                        <div class="cc-info-item">
                            <div class="cc-info-label">রক্ত গ্রুপ</div>
                            <div class="cc-info-value">{{ $patient->blood_group ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-info-item">
                            <div class="cc-info-label">প্রক্রিয়ার ধরন</div>
                            <div class="cc-info-value">
                                @php
                                    $procType = $procedureTypes->firstWhere('id', $admission->procedure_type_id ?? null);
                                @endphp
                                {{ $procType->procedure_name ?? '—' }}
                            </div>
                        </div>
                        <div class="cc-info-item">
                            <div class="cc-info-label">পিতা/স্বামী</div>
                            <div class="cc-info-value">{{ $patient->father_name ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-info-item cc-info-item-span-2">
                            <div class="cc-info-label">ঠিকানা</div>
                            <div class="cc-info-value">
                                {{ implode(', ', array_filter([$patient->village ?? $patient->address ?? null, $patient->post ?? null, $patient->thana ?? null, $patient->district ?? null])) ?: 'নেই' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Summary -->
            @if($templateDischarge && ($templateDischarge->treatment ?? $templateDischarge->condition ?? $templateDischarge->follow_up ?? false))
                <div class="cc-section-card cc-template-summary">
                    <div class="cc-section-header">
                        <span class="cc-section-number">—</span>
                        <div>
                            <h4 class="cc-section-title">Template Discharge Summary</h4>
                            <small class="cc-section-desc">Template ID: {{ $templateDischarge->templateid }}</small>
                        </div>
                    </div>
                    <div class="cc-section-body">
                        <div class="cc-template-grid">
                            @if($templateDischarge->treatment)
                                <div class="cc-template-item">
                                    <div class="cc-template-label">প্রদত্ত চিকিৎসা</div>
                                    <div class="cc-template-text">{{ $templateDischarge->treatment }}</div>
                                </div>
                            @endif
                            @if($templateDischarge->condition)
                                <div class="cc-template-item">
                                    <div class="cc-template-label">ছাড়পত্রের সময় অবস্থা</div>
                                    <div class="cc-template-text">{{ $templateDischarge->condition }}</div>
                                </div>
                            @endif
                            @if($templateDischarge->follow_up)
                                <div class="cc-template-item">
                                    <div class="cc-template-label">ফলোআপ পরামর্শ</div>
                                    <div class="cc-template-text">{{ $templateDischarge->follow_up }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section 02: Discharge Information -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">02</span>
                    <div>
                        <h4 class="cc-section-title">Discharge Information</h4>
                        <small class="cc-section-desc">Discharge date, ward, and registration details</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-status-note {{ $isPendingApproval ? 'pending' : ($isManagerApproved ? 'approved' : '') }}">
                        @if($isPendingApproval)
                            <i class="fas fa-hourglass-half mr-2"></i> Pending manager approval. Final discharge is locked.
                        @elseif($isManagerApproved)
                            <i class="fas fa-check-circle mr-2"></i> Manager approved. Print slips to complete discharge.
                        @else
                            <i class="fas fa-edit mr-2"></i> Fill in discharge details, then submit for manager approval.
                        @endif
                    </div>

                    <div class="cc-form-grid cc-form-grid-4">
                        <div class="cc-field">
                            <label for="discharge_date">ছাড়পত্রের তারিখ</label>
                            <input type="date" id="discharge_date" value="{{ optional($discharge)->discharge_date ? date('Y-m-d', strtotime($discharge->discharge_date)) : date('Y-m-d') }}">
                        </div>
                        <div class="cc-field">
                            <label for="discharge_ward">ওয়ার্ড / কেবিন নং</label>
                            <input type="text" id="discharge_ward" value="{{ optional($discharge)->ward ?? optional($dischargeBed)->bed_number ?? '' }}" placeholder="ওয়ার্ড / কেবিন">
                        </div>
                        <div class="cc-field">
                            <label for="discharge_reg_no">রেজিঃ নং</label>
                            <input type="text" id="discharge_reg_no" value="{{ optional($discharge)->reg_no ?? $patientcode }}" placeholder="রেজিস্ট্রেশন নং">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 03: Diagnosis -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">03</span>
                    <div>
                        <h4 class="cc-section-title">Diagnosis</h4>
                        <small class="cc-section-desc">Primary discharge diagnosis</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-form-grid">
                        <div class="cc-field cc-field-full">
                            <label for="discharge_diagnosis">রোগ নির্ণয়</label>
                            @php $selDiagnosisIds = $multiIds(old('discharge_diagnosis_id', optional($discharge)->discharge_diagnosis_id)); @endphp
                            <div class="cc-chips{{ ($dischargeDiagnoses ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="discharge_diagnosis">
                                @foreach($dischargeDiagnoses ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selDiagnosisIds))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                                @if(optional($discharge)->diagnosis && !optional($discharge)->discharge_diagnosis_id)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $discharge->diagnosis }}" data-text="{{ $discharge->diagnosis }}" checked>
                                        <span>{{ $discharge->diagnosis }}</span>
                                    </label>
                                @elseif(optional($td)->condition && !optional($discharge)->discharge_diagnosis_id)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $td->condition }}" data-text="{{ $td->condition }}" checked>
                                        <span>{{ $td->condition }}</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 04: Operation Information -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">04</span>
                    <div>
                        <h4 class="cc-section-title">Operation Information</h4>
                        <small class="cc-section-desc">Operation details and surgical team</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-form-grid cc-form-grid-3">
                        <div class="cc-field">
                            <label for="operation_name">অপারেশন নাম</label>
                            @php $selOperationNameIds = $multiIds(old('operation_name_id', optional($discharge)->operation_name_id)); @endphp
                            <div class="cc-chips{{ ($operationNames ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="operation_name">
                                @foreach($operationNames ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selOperationNameIds))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cc-field">
                            <label for="operation_date">অপারেশন তারিখ</label>
                            <input type="date" id="operation_date" value="{{ optional($discharge)->operation_date ? date('Y-m-d', strtotime($discharge->operation_date)) : '' }}">
                        </div>
                        <div class="cc-field cc-field-full">
                            <label for="operation_method">অপারেশন পদ্ধতি ও প্রাপ্ত তথ্যাবলী</label>
                            @php $selOperationMethodIds = $multiIds(old('operation_method_id', optional($discharge)->operation_method_id)); @endphp
                            <div class="cc-chips{{ ($operationMethods ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="operation_method">
                                @foreach($operationMethods ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selOperationMethodIds))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                                @if(optional($discharge)->operation_method && !optional($discharge)->operation_method_id)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $discharge->operation_method }}" data-text="{{ $discharge->operation_method }}" checked>
                                        <span>{{ $discharge->operation_method }}</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                        <div class="cc-field">
                            <label for="surgeon">সার্জন</label>
                            @php $selSurgeonIds = $multiIds(old('surgeon_id', optional($discharge)->surgeon_id)); @endphp
                            <div class="cc-chips{{ ($surgeons ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="surgeon">
                                @foreach($surgeons ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->doctor_name }}" @checked(in_array($option->id, $selSurgeonIds))>
                                        <span>{{ $option->doctor_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cc-field">
                            <label for="anesthetist">এ্যানেসথেটিষ্ট</label>
                            @php $selAnesthetistIds = $multiIds(old('anesthesiologist_id', optional($discharge)->anesthesiologist_id)); @endphp
                            <div class="cc-chips{{ ($anesthesiologists ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="anesthetist">
                                @foreach($anesthesiologists ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selAnesthetistIds))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cc-field">
                            <label for="assistant_1">এ্যাসিসট্যান্ট</label>
                            @php $selAssistant1Ids = $multiIds(old('assistant_surgeon_1_id', optional($discharge)->assistant_surgeon_1_id)); @endphp
                            <div class="cc-chips{{ ($assistantSurgeons ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="assistant_1">
                                @foreach($assistantSurgeons ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selAssistant1Ids))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 05: Doctor / Surgical Information -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">05</span>
                    <div>
                        <h4 class="cc-section-title">Doctor / Surgical Information</h4>
                        <small class="cc-section-desc">Attending doctor responsible for discharge</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-form-grid cc-form-grid-3">
                        <div class="cc-field">
                            <label for="discharge_doctor_name">ডাক্তারের নাম</label>
                            <input type="text" id="discharge_doctor_name" value="{{ optional($discharge)->doctor_name ?? optional($dischargeDoctor)->doctor_name ?? '' }}" placeholder="দায়িত্বরত ডাক্তার">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 06: Clinical / Treatment Information -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">06</span>
                    <div>
                        <h4 class="cc-section-title">Clinical / Treatment Information</h4>
                        <small class="cc-section-desc">Investigations, blood group, and allergies</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-form-grid">
                        <div class="cc-field cc-field-full">
                            <label for="investigations">পরীক্ষা নিরীক্ষার নোট</label>
                            <textarea id="investigations" rows="3" placeholder="পরীক্ষা নিরীক্ষার নোট">{{ optional($discharge)->investigations ?? '' }}</textarea>
                        </div>
                        <div class="cc-field">
                            <label for="discharge_blood_group">রক্ত গ্রুপ</label>
                            <input type="text" id="discharge_blood_group" value="{{ optional($discharge)->blood_group ?? ($patient->blood_group ?? '') }}">
                        </div>
                        <div class="cc-field">
                            <label for="drug_allergy">ড্রাগ এ্যালার্জী</label>
                            <input type="text" id="drug_allergy" value="{{ optional($discharge)->drug_allergy ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 07: Discharge Medicines -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">07</span>
                    <div>
                        <h4 class="cc-section-title">Discharge Medicines</h4>
                        <small class="cc-section-desc">Medicines to be given at discharge</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-medicine-toolbar">
                        <div class="cc-medicine-search">
                            <select id="commonMedicineSelect" class="form-control select2" style="width:100%;">
                                <option value="">ঔষধ সার্চ করুন (নাম বা পাওয়ার লিখুন)...</option>
                                @foreach($commonMedicines as $cm)
                                    <option value="{{ $cm->id }}" data-name="{{ $cm->name ?? '' }}" data-strength="{{ $cm->strength ?? '' }}">
                                        {{ $cm->name ?? '' }} {{ $cm->strength ? ' (' . $cm->strength . ')' : '' }} {{ !empty($cm->company) ? ' [' . $cm->company . ']' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="cc-btn cc-btn-primary btn-sm" onclick="addMedicineFromSelect()">
                            <i class="fas fa-plus-circle mr-1"></i> যোগ করুন
                        </button>
                        <button type="button" class="cc-btn cc-btn-secondary btn-sm" onclick="addDischargeMedicineRow()">
                            <i class="fas fa-plus"></i> খালি সারি যোগ করুন
                        </button>
                    </div>
                    <datalist id="medicineSuggestions">
                        @foreach($commonMedicines as $medicineOption)
                            <option value="{{ $medicineOption->name ?? '' }}" data-strength="{{ $medicineOption->strength ?? '' }}">
                                {{ $medicineOption->name ?? '' }} {{ $medicineOption->strength ? '(' . $medicineOption->strength . ')' : '' }}
                            </option>
                        @endforeach
                    </datalist>
                    <div class="cc-med-table-wrapper">
                        <table class="cc-med-table cc-discharge-med-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>ঔষধের নাম</th>
                                    <th>Strength</th>
                                    <th class="cc-dosage-header">সকাল</th>
                                    <th class="cc-dosage-header">দুপুর</th>
                                    <th class="cc-dosage-header">রাত</th>
                                    <th>কত দিন</th>
                                    <th>খাবার</th>
                                    <th style="width: 54px;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="discharge-medicine-body">
                                @forelse($savedMedicines as $i => $m)
                                    <tr class="discharge-medicine-row">
                                        <td class="dm-sl">{{ $i + 1 }}</td>
                                        <td><input type="text" class="dm-name" list="medicineSuggestions" value="{{ $m->medicine_name ?? $m->name ?? '' }}"></td>
                                        <td><input type="text" class="dm-strength" value="{{ $m->strength ?? '' }}"></td>
                                        <td><input type="text" class="dm-morning" value="{{ $m->morning ?? '' }}"></td>
                                        <td><input type="text" class="dm-noon" value="{{ $m->noon ?? '' }}"></td>
                                        <td><input type="text" class="dm-night" value="{{ $m->night ?? '' }}"></td>
                                        <td><input type="text" class="dm-days" placeholder="দিন" value="{{ $m->days ?? '' }}" style="width:60px;"></td>
                                        <td>
                                            <select class="dm-timing" style="width:100%;padding:4px;">
                                                <option value="">-- নির্বাচন --</option>
                                                <option value="খাবার আগে" {{ ($m->timing ?? '') === 'খাবার আগে' ? 'selected' : '' }}>খাবার আগে</option>
                                                <option value="খাবার পরে" {{ ($m->timing ?? '') === 'খাবার পরে' ? 'selected' : '' }}>খাবার পরে</option>
                                                <option value="খাবার সাথে" {{ ($m->timing ?? '') === 'খাবার সাথে' ? 'selected' : '' }}>খাবার সাথে</option>
                                                <option value="খালি পেটে" {{ ($m->timing ?? '') === 'খালি পেটে' ? 'selected' : '' }}>খালি পেটে</option>
                                            </select>
                                        </td>
                                        <td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @empty
                                    <tr class="discharge-medicine-row">
                                        <td class="dm-sl">1</td>
                                        <td><input type="text" class="dm-name" list="medicineSuggestions" placeholder="ঔষধের নাম"></td>
                                        <td><input type="text" class="dm-strength" placeholder="Strength"></td>
                                        <td><input type="text" class="dm-morning" placeholder="0"></td>
                                        <td><input type="text" class="dm-noon" placeholder="0"></td>
                                        <td><input type="text" class="dm-night" placeholder="0"></td>
                                        <td><input type="text" class="dm-days" placeholder="দিন" style="width:60px;"></td>
                                        <td>
                                            <select class="dm-timing" style="width:100%;padding:4px;">
                                                <option value="">-- নির্বাচন --</option>
                                                <option value="খাবার আগে">খাবার আগে</option>
                                                <option value="খাবার পরে">খাবার পরে</option>
                                                <option value="খাবার সাথে">খাবার সাথে</option>
                                                <option value="খালি পেটে">খালি পেটে</option>
                                            </select>
                                        </td>
                                        <td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 08: Follow-up / Instructions -->
            <div class="cc-section-card">
                <div class="cc-section-header">
                    <span class="cc-section-number">08</span>
                    <div>
                        <h4 class="cc-section-title">Follow-up / Instructions</h4>
                        <small class="cc-section-desc">Follow-up visit and discharge instructions</small>
                    </div>
                </div>
                <div class="cc-section-body">
                    <div class="cc-form-grid cc-form-grid-3">
                        <div class="cc-field">
                            <label for="follow_up_date">পরবর্তী ভিজিটের তারিখ</label>
                            <input type="date" id="follow_up_date" value="{{ optional($discharge)->follow_up_date ? date('Y-m-d', strtotime($discharge->follow_up_date)) : '' }}">
                        </div>
                        <div class="cc-field cc-field-span-2">
                            <label for="discharge_notes">চিকিৎসা পরামর্শ / নির্দেশনা</label>
                            @php $selInstructionIds = $multiIds(old('discharge_instruction_id', optional($discharge)->discharge_instruction_id)); @endphp
                            <div class="cc-chips{{ ($dischargeInstructions ?? collect())->count() > 8 ? ' cc-chips-scroll' : '' }}" id="discharge_notes">
                                @foreach($dischargeInstructions ?? [] as $option)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $option->id }}" data-text="{{ $option->name }}" @checked(in_array($option->id, $selInstructionIds))>
                                        <span>{{ $option->name }}</span>
                                    </label>
                                @endforeach
                                @if(optional($discharge)->notes && !optional($discharge)->discharge_instruction_id)
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $discharge->notes }}" data-text="{{ $discharge->notes }}" checked>
                                        <span>{{ $discharge->notes }}</span>
                                    </label>
                                @elseif((optional($td)->treatment || optional($td)->follow_up) && !optional($discharge)->discharge_instruction_id)
                                    @php
                                        $legacyVal = optional($td)->treatment ?? optional($td)->follow_up;
                                    @endphp
                                    <label class="cc-chip-toggle">
                                        <input type="checkbox" value="{{ $legacyVal }}" data-text="{{ $legacyVal }}" checked>
                                        <span>{{ $legacyVal }}</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- /.cc-form-sections --}}

            <!-- Section 09: Final Actions -->
            <div class="cc-action-panel">
                <div class="cc-action-panel-inner">
                    @if($historyMode)
                    <div class="cc-action-primary">
                        <button type="button" onclick="printSlip()" class="cc-btn cc-btn-primary btn-lg">
                            <i class="fas fa-file-pdf mr-2"></i> Print / Save PDF
                        </button>
                        <a href="{{ route('admission.discharge.history') }}" class="cc-btn cc-btn-secondary btn-lg">
                            <i class="fas fa-history mr-2"></i> ইতিহাসে ফিরে যান
                        </a>
                    </div>
                    @else
                    <div class="cc-action-primary">
                        <button type="button" onclick="submitDischargeForApproval()" class="cc-btn cc-btn-primary btn-lg">
                            <i class="fas fa-paper-plane mr-2"></i> Save &amp; Submit for Approval
                        </button>
                        @if($isPendingApproval)
                        <button type="button" onclick="approveDischarge()" class="cc-btn cc-btn-success btn-lg">
                            <i class="fas fa-user-check mr-2"></i> Manager Approval
                        </button>
                        @endif
                    </div>
                    <div class="cc-action-secondary">
                        <button type="button" onclick="printSlip()" class="cc-btn cc-btn-secondary">
                            <i class="fas fa-print mr-2"></i> ৪টি স্লিপ প্রিন্ট করুন
                        </button>
                        <button type="button" onclick="printSlip()" class="cc-btn cc-btn-secondary">
                            <i class="fas fa-file-pdf mr-2"></i> Print / Save PDF
                        </button>
                        <button type="button" onclick="doDischarge()" class="cc-btn cc-btn-outline-danger" {{ $isManagerApproved ? '' : 'disabled' }}>
                            <i class="fas fa-sign-out-alt mr-2"></i> চূড়ান্ত ছাড়পত্র
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Hidden print values -->
            <input type="hidden" id="patientcode" value="{{ $patientcode }}">
            <input type="hidden" id="admission_id" value="{{ $admission->id ?? '' }}">
            <input type="hidden" id="print_patientname" value="{{ $patient->patientname ?? '' }}">
            <input type="hidden" id="print_mobile" value="{{ $patient->mobile_no ?? '' }}">
            <input type="hidden" id="print_admissiondate" value="{{ $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y') }}">
            <input type="hidden" id="print_age" value="{{ $patient->age ?? '' }}">
            <input type="hidden" id="print_father" value="{{ $patient->father_name ?? '' }}">
            <input type="hidden" id="print_address_village" value="{{ $patient->village ?? $patient->address ?? '' }}">
            <input type="hidden" id="print_address_post" value="{{ $patient->post ?? '' }}">
            <input type="hidden" id="print_address_thana" value="{{ $patient->thana ?? '' }}">
            <input type="hidden" id="print_address_district" value="{{ $patient->district ?? '' }}">
            <input type="hidden" id="discharge_status" value="{{ $dischargeStatus ?? '' }}">

        @else
            <!-- Placeholder state -->
            <div class="cc-card p-5 text-center text-muted">
                <i class="fas fa-user-injured fa-3x mb-3 text-silver" style="color:#d1d5db;"></i>
                <h4>কোনো রোগী নির্বাচিত নয়</h4>
                <p class="mb-0">রেকর্ড দেখতে এবং ছাড়পত্র সম্পন্ন করতে ছাড়পত্রের তালিকা থেকে একজন রোগী নির্বাচন করুন।</p>
            </div>
        @endif
    </div>
</div>

<div id="printArea" style="display:none;">
    @include('admission.discharge.partials.discharge_booklet')
</div>
@endsection

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.4/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
/* Select2 customization */
.select2-container--bootstrap4 .select2-selection {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    height: 38px !important;
    padding: 4px 8px !important;
    font-size: 13px !important;
}
.select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: var(--cc-primary) !important;
    box-shadow: 0 0 0 2px rgba(15, 82, 186, .15) !important;
}
:root {
    --cc-bg: #eef1f5;
    --cc-primary: #2563eb;
    --cc-primary-dark: #1d4ed8;
    --cc-primary-light: #eff6ff;
    --cc-white: #ffffff;
    --cc-border: #e2e8f0;
    --cc-text: #0f172a;
    --cc-muted: #64748b;
    --cc-green: #059669;
    --cc-green-bg: #ecfdf5;
    --cc-red: #dc2626;
    --cc-red-bg: #fef2f2;
    --cc-amber: #d97706;
    --cc-amber-bg: #fffbeb;
    --cc-radius: 8px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
    --cc-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    --cc-section-gap: 14px;
    --cc-field-gap: 14px;
}

body, .content-wrapper {
    background: var(--cc-bg) !important;
    font-family: var(--cc-font) !important;
    color: var(--cc-text);
}

.cc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    padding: 12px 0 0;
    flex-wrap: wrap;
}
.cc-header-text { flex: 1; min-width: 200px; }
.cc-header-subtitle {
    font-size: 13px;
    color: var(--cc-muted);
    margin: 4px 0 6px;
}
.cc-header-actions {
    flex-shrink: 0;
    padding-top: 4px;
}
.cc-header-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--cc-radius);
    background: var(--cc-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cc-white);
    font-size: 18px;
    flex-shrink: 0;
}
.cc-header-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
    line-height: 1.2;
}
.cc-breadcrumb {
    list-style: none;
    padding: 0;
    margin: 2px 0 0;
    display: flex;
    gap: 6px;
    font-size: 12px;
    color: var(--cc-muted);
}
.cc-breadcrumb li { display: flex; align-items: center; gap: 6px; }
.cc-breadcrumb li+li::before { content: '/'; color: var(--cc-border); }
.cc-breadcrumb a { color: var(--cc-primary); text-decoration: none; }
.cc-breadcrumb a:hover { text-decoration: underline; }
.cc-breadcrumb .active { color: var(--cc-muted); }

/* Full-width workflow — no sidebar */
#dischargeWorkflow {
    display: none;
    width: 100%;
    max-width: 100%;
}
#dischargeWorkflow.is-visible {
    display: block;
}
.cc-workflow,
.cc-form-pane {
    width: 100%;
    max-width: 100%;
}

/* Section cards */
.cc-form-sections {
    display: flex;
    flex-direction: column;
    gap: var(--cc-section-gap);
    margin-bottom: var(--cc-section-gap);
}
.cc-section-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    box-shadow: var(--cc-shadow);
    overflow: hidden;
}
.cc-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--cc-border);
    background: #fafbfc;
}
.cc-section-body {
    padding: 16px 20px;
}
.cc-section-number {
    font-size: 13px;
    font-weight: 700;
    color: var(--cc-primary);
    font-variant-numeric: tabular-nums;
    min-width: 28px;
    flex-shrink: 0;
}
.cc-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
    line-height: 1.3;
}
.cc-section-desc {
    display: block;
    font-size: 12px;
    color: var(--cc-muted);
    margin-top: 1px;
    font-weight: 400;
}

/* Compact patient bar */
.cc-patient-bar {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-left: 3px solid var(--cc-primary);
    border-radius: var(--cc-radius);
    box-shadow: var(--cc-shadow);
    margin-bottom: var(--cc-section-gap);
    padding: 14px 20px;
}
.cc-patient-bar-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}
.cc-patient-bar-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--cc-text);
}
.cc-patient-bar-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px 24px;
}
.cc-patient-bar-label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: var(--cc-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}
.cc-patient-bar-value {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--cc-text);
    line-height: 1.3;
}
.cc-status-pill {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    line-height: 1.4;
}
.cc-status-pill.pending { background: var(--cc-amber-bg); color: #92400e; }
.cc-status-pill.approved { background: var(--cc-green-bg); color: #047857; }
.cc-status-pill.draft { background: #f1f5f9; color: var(--cc-muted); }

/* Info grid (read-only patient fields) */
.cc-info-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: var(--cc-field-gap);
}
.cc-info-item {
    min-width: 0;
}
.cc-info-item-span-2 {
    grid-column: span 2;
}
.cc-info-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--cc-muted);
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.cc-info-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--cc-text);
    line-height: 1.4;
}

/* Template summary */
.cc-template-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--cc-field-gap);
}
.cc-template-item {
    background: #f8fafc;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 12px 14px;
}
.cc-template-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--cc-muted);
    margin-bottom: 4px;
}
.cc-template-text {
    font-size: 13px;
    color: var(--cc-text);
    line-height: 1.45;
    white-space: pre-wrap;
}

/* Detail Card */
.cc-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    margin-bottom: 16px;
    overflow: hidden;
}
.cc-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--cc-white);
}
.cc-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cc-card-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--cc-radius);
    background: var(--cc-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cc-primary);
    font-size: 14px;
    flex-shrink: 0;
}
.cc-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
}
.cc-card-badge {
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    border: 1px solid #c7d4f0;
    border-radius: var(--cc-radius);
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.cc-card-badge-warning {
    background: var(--cc-amber-bg);
    color: var(--cc-amber);
    border-color: #fde68a;
}
.cc-card-badge-success {
    background: var(--cc-green-bg);
    color: var(--cc-green);
    border-color: #a7f3d0;
}
.cc-card-body {
    padding: 14px 16px;
}

.cc-status-note {
    border: 1px solid var(--cc-border);
    background: #f8fafc;
    color: var(--cc-muted);
    border-radius: var(--cc-radius);
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 14px;
}
.cc-status-note.pending {
    background: var(--cc-amber-bg);
    color: #92400e;
    border-color: #fde68a;
}
.cc-status-note.approved {
    background: var(--cc-green-bg);
    color: #047857;
    border-color: #a7f3d0;
}

.cc-form-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--cc-field-gap);
}
.cc-form-grid-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}
.cc-form-grid-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
.cc-field {
    min-width: 0;
}
.cc-field-full,
.cc-field-wide {
    grid-column: 1 / -1;
}
.cc-field-span-2 {
    grid-column: span 2;
}
.cc-field label {
    display: block;
    font-size: 11px;
    color: var(--cc-muted);
    font-weight: 600;
    margin-bottom: 4px;
    letter-spacing: 0.2px;
}
.cc-field input,
.cc-field textarea,
.cc-field select.form-select {
    width: 100%;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 10px 12px;
    font-size: 13px;
    color: var(--cc-text);
    background: #fff;
    outline: none;
    min-height: 40px;
    line-height: 1.3;
}
.cc-field textarea {
    resize: vertical;
    min-height: 74px;
}
.cc-field input:focus,
.cc-field textarea:focus,
.cc-field select.form-select:focus,
.cc-discharge-med-table input:focus {
    border-color: var(--cc-primary);
    box-shadow: 0 0 0 2px rgba(15, 82, 186, .10);
}
.cc-subsection-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--cc-text);
    margin: 18px 0 10px;
}

/* ═══ Checkbox Chip Groups (multi-select replacement, pure CSS) ═══ */
.cc-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 2px;
}
.cc-chips.cc-chips-scroll {
    max-height: 132px;
    overflow-y: auto;
    padding-right: 6px;
    scrollbar-width: thin;
}
.cc-chip-toggle {
    position: relative;
    cursor: pointer;
    user-select: none;
}
.cc-chip-toggle input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.cc-chip-toggle span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    border: 1px solid var(--cc-border);
    border-radius: 999px;
    background: #fff;
    color: var(--cc-muted);
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.35;
    transition: border-color .15s ease, background .15s ease, color .15s ease, box-shadow .15s ease;
}
.cc-chip-toggle span::before {
    content: '';
    flex: none;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #cbd5e1;
    transition: background .15s ease;
}
.cc-chip-toggle:hover span {
    border-color: #c7d2e0;
    color: var(--cc-text);
}
.cc-chip-toggle input:checked + span {
    background: var(--cc-primary-light);
    border-color: var(--cc-primary);
    color: var(--cc-primary-dark);
}
.cc-chip-toggle input:checked + span::before {
    background: var(--cc-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
}
.cc-chip-toggle input:focus-visible + span {
    box-shadow: 0 0 0 2px rgba(37, 99, 235, .25);
}

/* Patient Summary Grid */
.cc-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.cc-summary-item {
    background: #f8fafc;
    padding: 10px 12px;
    border-radius: var(--cc-radius);
    border: 1px solid var(--cc-border);
}
.cc-summary-label {
    font-size: 11px;
    color: var(--cc-muted);
    font-weight: 500;
    margin-bottom: 2px;
}
.cc-summary-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--cc-text);
}

/* Template Info Boxes */
.cc-info-boxes {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.cc-info-box {
    display: flex;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 12px;
}
.cc-info-box-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--cc-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.cc-info-box-icon.blue { background: var(--cc-primary-light); color: var(--cc-primary); }
.cc-info-box-icon.green { background: var(--cc-green-bg); color: var(--cc-green); }
.cc-info-box-icon.amber { background: var(--cc-amber-bg); color: var(--cc-amber); }
.cc-info-box-content { min-width: 0; }
.cc-info-box-label { font-size: 11px; font-weight: 600; color: var(--cc-muted); margin-bottom: 2px; }
.cc-info-box-text { font-size: 12px; color: var(--cc-text); white-space: pre-wrap; line-height: 1.4; }

/* Medicine Table styling */
.cc-med-table-wrapper {
    overflow-x: auto;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    background: var(--cc-white);
}
.cc-med-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.cc-med-table th {
    background: #f1f5f9;
    padding: 8px 12px;
    font-weight: 600;
    font-size: 12px;
    color: var(--cc-text);
    border-bottom: 1.5px solid var(--cc-border);
    white-space: nowrap;
}
.cc-dosage-header {
    text-align: center;
    min-width: 56px;
}
.cc-med-table td {
    padding: 6px 10px;
    border-bottom: 1px solid var(--cc-border);
    color: var(--cc-text);
    vertical-align: middle;
}
.cc-med-table tr:last-child td {
    border-bottom: none;
}
.cc-med-table tbody tr:nth-child(even) td {
    background: #fafbfc;
}
.cc-med-table tbody tr:hover td {
    background: #f1f5f9;
}
.cc-discharge-med-table input {
    width: 100%;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 5px 7px;
    font-size: 12px;
    outline: none;
}
.cc-discharge-med-table td {
    padding: 4px 5px;
}
.cc-row-remove {
    width: 30px;
    height: 30px;
    border: 1px solid #fecaca;
    background: var(--cc-red-bg);
    color: var(--cc-red);
    border-radius: var(--cc-radius);
    cursor: pointer;
}
.cc-row-remove:hover {
    background: var(--cc-red);
    color: #fff;
}
.cc-btn-primary {
    background: var(--cc-primary);
    color: var(--cc-white) !important;
}
.cc-btn-primary:hover { background: var(--cc-primary-dark); }
.cc-btn-success {
    background: var(--cc-green);
    color: var(--cc-white) !important;
}
.cc-btn-success:hover { opacity: 0.9; }
.cc-btn-secondary {
    background: var(--cc-white);
    color: var(--cc-text);
    border: 1px solid var(--cc-border);
}
.cc-btn-secondary:hover { background: #faf8ff; }
.cc-btn-warning {
    background: var(--cc-amber);
    color: var(--cc-white) !important;
}
.cc-btn-warning:hover { opacity: 0.9; }
.cc-btn-danger {
    background: var(--cc-red);
    color: var(--cc-white) !important;
}
.cc-btn-danger:hover { opacity: 0.9; }
.cc-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .cc-layout-wrapper,
    #dischargeWorkflow.is-visible {
        flex-direction: column;
    }
    .cc-sidebar {
        width: 100% !important;
        flex: 1 0 auto;
    }
    .cc-details-pane {
        width: 100% !important;
        flex: 1 0 auto;
    }
    .cc-summary-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cc-info-boxes {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cc-form-grid,
    .cc-form-grid-3,
    .cc-form-grid-4 {
        grid-template-columns: 1fr;
    }
    .cc-field-wide,
    .cc-field-full {
        grid-column: span 1;
    }
    .cc-field-span-2 {
        grid-column: span 1;
    }
    .cc-info-item-span-2 {
        grid-column: span 1;
    }
    .cc-info-grid {
        grid-template-columns: 1fr 1fr;
    }
    .cc-template-grid {
        grid-template-columns: 1fr;
    }
    .cc-patient-bar-grid {
        grid-template-columns: 1fr 1fr;
    }
}
@media (min-width: 769px) and (max-width: 1199px) {
    .cc-form-grid-4 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .cc-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

    .cc-sidebar {
        width: 100% !important;
        flex: 1 0 auto;
    }
    .cc-details-pane {
        width: 100% !important;
        flex: 1 0 auto;
    }
    .cc-summary-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cc-info-boxes {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cc-form-grid {
        grid-template-columns: 1fr;
    }
    .cc-field-wide {
        grid-column: span 1;
    }
}

/* Print CSS — Letter Landscape, full page booklet */
@media print {
    * { box-sizing: border-box; }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: #fff !important;
    }
    .wrapper, .content-wrapper, .main-sidebar, .main-header, .content, .container, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    body > *:not(#printArea) { display: none !important; }
    #printArea {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        color: #000 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .booklet-sheet {
        width: 100% !important;
        max-width: 100% !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-after: always !important;
    }
    .booklet-sheet:last-child {
        page-break-after: auto !important;
    }
    .booklet-page {
        width: 50% !important;
        max-width: 50% !important;
        min-width: 50% !important;
        height: 100% !important;
        vertical-align: top !important;
    }
@include('admission.discharge.partials.booklet_css')
}
@page {
    size: legal landscape;
    margin: 0 !important;
}


/* ═══════════════════════════════════════════════
   PATIENT SELECTION SCREEN
   ═══════════════════════════════════════════════ */
.cc-selection-screen {
    display: none;
    align-items: center;
    justify-content: center;
    min-height: 65vh;
    padding: 32px 20px;
}
.cc-selection-screen.is-visible {
    display: flex;
}
.cc-selection-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 48px 40px;
    max-width: 580px;
    width: 100%;
    box-shadow: var(--cc-shadow);
}
.cc-selection-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--cc-text);
    margin: 0 0 6px;
    text-align: center;
}
.cc-selection-subtitle {
    font-size: 14px;
    color: var(--cc-muted);
    margin: 0 0 28px;
    line-height: 1.5;
    text-align: center;
}
.cc-select-lg + .select2-container .select2-selection {
    min-height: 48px !important;
    padding: 8px 12px !important;
    font-size: 14px !important;
}
.cc-btn-continue {
    width: 100%;
    margin-top: 20px;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 700;
    border: none;
    border-radius: var(--cc-radius);
    cursor: pointer;
    background: var(--cc-primary);
    color: var(--cc-white);
    transition: background 0.15s;
}
.cc-btn-continue:hover:not(:disabled) {
    background: var(--cc-primary-dark);
}
.cc-btn-continue:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.cc-btn-continue:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.cc-selection-empty {
    margin-top: 24px;
    padding: 24px;
    text-align: center;
    color: var(--cc-muted);
    border: 1px dashed var(--cc-border);
    border-radius: var(--cc-radius);
    background: #f8fafc;
}
.cc-selection-empty i {
    font-size: 28px;
    margin-bottom: 8px;
    color: #cbd5e1;
}
.cc-selection-search {
    text-align: left;
    margin-bottom: 16px;
}
.cc-selection-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--cc-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 6px;
}
.cc-selection-hint {
    font-size: 12px;
    color: var(--cc-muted);
    margin: 16px 0 0;
    text-align: left;
}
.cc-quick-list {
    margin-top: 24px;
    text-align: left;
    border-top: 1px solid var(--cc-border);
    padding-top: 20px;
}
.cc-quick-list-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--cc-muted);
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 10px;
}
.cc-quick-list-items {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 260px;
    overflow-y: auto;
}
.cc-quick-patient {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    color: var(--cc-text) !important;
    text-decoration: none !important;
    transition: border-color 0.15s, background 0.15s;
}
.cc-quick-patient:hover {
    border-color: var(--cc-primary);
    background: var(--cc-primary-light);
}
.cc-quick-patient-arrow {
    font-size: 11px;
    color: var(--cc-muted);
}
.cc-quick-patient-name {
    font-size: 13px;
    font-weight: 600;
}
.cc-quick-patient-meta {
    font-size: 11px;
    color: var(--cc-muted);
}

/* Patient Summary Card */
.cc-patient-summary-card {
    border: 1px solid var(--cc-border);
    box-shadow: var(--cc-shadow);
}
.cc-patient-summary-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px !important;
    flex-wrap: wrap;
}
.cc-patient-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px 20px;
    flex: 1;
    min-width: 0;
}
.cc-summary-stat-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: var(--cc-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 3px;
}
.cc-summary-stat-value {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--cc-text);
    line-height: 1.35;
    word-break: break-word;
}
.cc-patient-summary-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.cc-change-patient {
    white-space: nowrap;
}

/* Form Sections */
.cc-form-sections {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.cc-section-card {
    box-shadow: var(--cc-shadow);
}
.cc-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--cc-border);
    background: #fafbfc;
}
.cc-section-number {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--cc-primary);
    color: var(--cc-white);
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cc-section-desc {
    display: block;
    font-size: 12px;
    color: var(--cc-muted);
    margin-top: 2px;
}
.cc-collapsible-title-wrap {
    flex: 1;
    min-width: 0;
}
.cc-collapsible-header {
    cursor: pointer;
    user-select: none;
    list-style: none;
}
.cc-collapsible-header::-webkit-details-marker { display: none; }
.cc-collapsible-header::marker { display: none; content: ''; }

.cc-collapsible {
    margin-bottom: 16px;
}
.cc-collapsible-arrow {
    font-size: 12px;
    color: var(--cc-muted);
    transition: transform 0.2s;
    margin-left: auto;
    flex-shrink: 0;
}
.cc-collapsible[open] .cc-collapsible-arrow {
    transform: rotate(180deg);
}

/* Medicine toolbar */
.cc-medicine-toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 12px;
    background: #f8fafc;
    padding: 10px 12px;
    border-radius: var(--cc-radius);
    border: 1px solid var(--cc-border);
    flex-wrap: wrap;
}
.cc-medicine-search {
    flex: 1;
    min-width: 200px;
}

/* Action Panel */
.cc-action-panel {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    overflow: hidden;
    box-shadow: var(--cc-shadow);
}
.cc-action-panel-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--cc-border);
    background: #fafbfc;
}
.cc-action-panel-buttons {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding: 20px 22px !important;
    background: #f8fafc !important;
}
.cc-action-panel-buttons .cc-btn-primary,
.cc-action-panel-buttons .btn-primary {
    order: 1;
}
.cc-action-panel-buttons .cc-btn-warning,
.cc-action-panel-buttons .btn-info {
    order: 2;
}
.cc-action-panel-buttons .cc-btn-success {
    order: 0;
}
.cc-action-panel-buttons .cc-btn-danger {
    order: 3;
}
.cc-action-panel-buttons .cc-btn-secondary {
    order: 4;
}

/* Alerts */
.cc-alert {
    padding: 12px 16px;
    border-radius: var(--cc-radius);
    margin-bottom: 16px;
    font-size: 13px;
    font-weight: 600;
}
.cc-alert-success {
    background: var(--cc-green-bg);
    color: #047857;
    border: 1px solid #a7f3d0;
}
.cc-alert-danger {
    background: var(--cc-red-bg);
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Buttons base */
.cc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--cc-radius);
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    text-decoration: none !important;
    cursor: pointer;
    border: none;
    transition: background 0.15s, opacity 0.15s;
    line-height: 1.3;
}

/* ═══════════════════════════════════════════════
   RESPONSIVE — Patient Selection
   ═══════════════════════════════════════════════ */
@media (max-width: 768px) {
    .cc-selection-screen.is-visible { min-height: 55vh; padding: 20px 12px; }
    .cc-selection-card { padding: 28px 20px; }
    .cc-selection-title { font-size: 20px; }
    .cc-patient-summary-body { flex-direction: column; align-items: stretch; }
    .cc-patient-summary-grid { grid-template-columns: 1fr 1fr; }
    .cc-patient-summary-actions { width: 100%; justify-content: space-between; }
    .cc-medicine-toolbar { flex-direction: column; align-items: stretch; }
    .cc-medicine-search { width: 100%; }
    .cc-action-panel-buttons { flex-direction: column; align-items: stretch; }
    .cc-action-panel-buttons .btn,
    .cc-action-panel-buttons .cc-btn { width: 100%; text-align: center; }
    .cc-section-header { flex-wrap: wrap; }
}
@media (max-width: 992px) {
    .cc-patient-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var logoUrl = @json(config('app.logo_url'));
var csrfToken = "{{ csrf_token() }}";
var submitDischargeUrl = "{{ route('admission.discharge.submit') }}";
var approveDischargeUrl = "{{ route('admission.discharge.approve') }}";
var finalDischargeUrl = "{{ route('admission.discharge.do') }}";

function getGroupValues(el, attr) {
    return Array.prototype.slice.call(el.querySelectorAll('input[type="checkbox"]:checked'))
        .map(function (cb) { return String(cb.getAttribute(attr) || '').trim(); })
        .filter(function (v) { return v !== ''; });
}

function getVal(id) {
    var el = document.getElementById(id);
    if (!el) return '';
    if (el.classList.contains('cc-chips')) {
        return getGroupValues(el, 'value').join(',');
    }
    if (el.tagName === 'SELECT' && el.multiple) {
        return Array.prototype.slice.call(el.selectedOptions)
            .map(function (opt) { return String(opt.value || '').trim(); })
            .filter(function (v) { return v !== ''; })
            .join(',');
    }
    return String(el.value || '').trim();
}

function getSelectText(id, sep) {
    var el = document.getElementById(id);
    if (!el) return '';
    var joiner = (sep === undefined || sep === null) ? ', ' : sep;
    if (el.classList.contains('cc-chips')) {
        return getGroupValues(el, 'data-text').join(joiner);
    }
    if (el.tagName === 'SELECT') {
        var opts = el.multiple
            ? Array.prototype.slice.call(el.selectedOptions)
            : (el.selectedIndex >= 0 ? [el.options[el.selectedIndex]] : []);
        return opts
            .map(function (opt) { return String(opt.text || '').trim(); })
            .filter(function (t) { return t !== ''; })
            .join(joiner);
    }
    return String(el.value || '').trim();
}

function escHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function nl2br(value) {
    return escHtml(value).replace(/\r?\n/g, '<br>');
}

function en2bn(str) {
    if (str === null || str === undefined || str === '') return '';
    var en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    var bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    var s = String(str);
    for (var i = 0; i < 10; i++) {
        s = s.split(en[i]).join(bn[i]);
    }
    return s;
}

function fmtDateInput(value) {
    if (!value) return '';
    var parts = String(value).slice(0, 10).split('-');
    if (parts.length !== 3) return value;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
}

function showAjaxMessage(type, message) {
    $('#ajaxMessage').html('<div class="cc-alert cc-alert-' + type + '">' + escHtml(message) + '</div>');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function collectDischargeMedicines() {
    var medicines = [];
    document.querySelectorAll('.discharge-medicine-row').forEach(function(row) {
        var medicine = {
            medicine_name: row.querySelector('.dm-name') ? row.querySelector('.dm-name').value.trim() : '',
            strength:  row.querySelector('.dm-strength') ? row.querySelector('.dm-strength').value.trim() : '',
            morning:   row.querySelector('.dm-morning')  ? row.querySelector('.dm-morning').value.trim()  : '',
            noon:      row.querySelector('.dm-noon')      ? row.querySelector('.dm-noon').value.trim()      : '',
            night:     row.querySelector('.dm-night')     ? row.querySelector('.dm-night').value.trim()     : '',
            days:      row.querySelector('.dm-days')      ? row.querySelector('.dm-days').value.trim()      : '',
            timing:    row.querySelector('.dm-timing')    ? row.querySelector('.dm-timing').value          : ''
        };
        if (medicine.medicine_name) medicines.push(medicine);
    });
    return medicines;
}

function renumberMedicineRows() {
    document.querySelectorAll('.discharge-medicine-row .dm-sl').forEach(function(cell, index) {
        cell.textContent = index + 1;
    });
}

function addDischargeMedicineRow() {
    var tbody = document.getElementById('discharge-medicine-body');
    if (!tbody) return;
    var row = document.createElement('tr');
    row.className = 'discharge-medicine-row';
    row.innerHTML =
        '<td class="dm-sl"></td>' +
        '<td><input type="text" class="dm-name" list="medicineSuggestions" placeholder="ঔষধের নাম"></td>' +
        '<td><input type="text" class="dm-strength" placeholder="Strength"></td>' +
        '<td><input type="text" class="dm-morning" placeholder="0"></td>' +
        '<td><input type="text" class="dm-noon" placeholder="0"></td>' +
        '<td><input type="text" class="dm-night" placeholder="0"></td>' +
        '<td><input type="text" class="dm-days" placeholder="দিন" style="width:60px;"></td>' +
        '<td><select class="dm-timing" style="width:100%;padding:4px;"><option value="">-- নির্বাচন --</option><option value="খাবার আগে">খাবার আগে</option><option value="খাবার পরে">খাবার পরে</option><option value="খাবার সাথে">খাবার সাথে</option><option value="খালি পেটে">খালি পেটে</option></select></td>' +
        '<td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>';
    tbody.appendChild(row);
    renumberMedicineRows();
}

function addMedicineFromSelect() {
    var select = document.getElementById('commonMedicineSelect');
    if (!select || !select.value) {
        return;
    }

    var selectedOption = select.options[select.selectedIndex];
    var medName = selectedOption.getAttribute('data-name') || '';
    var medStrength = selectedOption.getAttribute('data-strength') || '';

    var tbody = document.getElementById('discharge-medicine-body');
    if (!tbody) return;

    var targetRow = null;

    // Check if first row is empty, fill it instead of adding new
    var firstRow = tbody.querySelector('.discharge-medicine-row');
    if (firstRow) {
        var firstNameInput = firstRow.querySelector('.dm-name');
        if (firstNameInput && !firstNameInput.value.trim()) {
            firstNameInput.value = medName;
            var firstStrengthInput = firstRow.querySelector('.dm-strength');
            if (firstStrengthInput) firstStrengthInput.value = medStrength;
            targetRow = firstRow;
        }
    }

    if (!targetRow) {
        var row = document.createElement('tr');
        row.className = 'discharge-medicine-row';
        row.innerHTML =
            '<td class="dm-sl"></td>' +
            '<td><input type="text" class="dm-name" list="medicineSuggestions" value="' + escHtml(medName) + '"></td>' +
            '<td><input type="text" class="dm-strength" value="' + escHtml(medStrength) + '"></td>' +
            '<td><input type="text" class="dm-morning" placeholder="0"></td>' +
            '<td><input type="text" class="dm-noon" placeholder="0"></td>' +
            '<td><input type="text" class="dm-night" placeholder="0"></td>' +
            '<td><input type="text" class="dm-days" placeholder="দিন" style="width:60px;"></td>' +
            '<td><select class="dm-timing" style="width:100%;padding:4px;"><option value="">-- নির্বাচন --</option><option value="খাবার আগে">খাবার আগে</option><option value="খাবার পরে">খাবার পরে</option><option value="খাবার সাথে">খাবার সাথে</option><option value="খালি পেটে">খালি পেটে</option></select></td>' +
            '<td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(row);
        renumberMedicineRows();
        targetRow = row;
    }

    // Focus on morning dosage input for fast data entry
    if (targetRow) {
        var morningInput = targetRow.querySelector('.dm-morning');
        if (morningInput) morningInput.focus();
    }

    // Reset dropdown cleanly with Select2 trigger
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('#commonMedicineSelect').val(null).trigger('change');
    } else {
        select.value = '';
    }
}

function removeDischargeMedicineRow(button) {
    var rows = document.querySelectorAll('.discharge-medicine-row');
    if (rows.length <= 1) {
        var row = rows[0];
        if (!row) return;
        row.querySelectorAll('input').forEach(function(input) { input.value = ''; });
        return;
    }

    button.closest('tr').remove();
    renumberMedicineRows();
}

function collectDischargePayload() {
    return {
        patientcode: getVal('patientcode'),
        admission_id: getVal('admission_id'),
        discharge_date: getVal('discharge_date'),
        ward: getVal('discharge_ward'),
        reg_no: getVal('discharge_reg_no'),
        doctor_name: getVal('discharge_doctor_name'),
        diagnosis: getVal('discharge_diagnosis'),
        discharge_diagnosis_id: getVal('discharge_diagnosis'),
        operation_name: getVal('operation_name'),
        operation_date: getVal('operation_date'),
        operation_method: getVal('operation_method'),
        operation_method_id: getVal('operation_method'),
        surgeon: getVal('surgeon'),
        anesthetist: getVal('anesthetist'),
        assistant_1: getVal('assistant_1'),
        investigations: getVal('investigations'),
        blood_group: getVal('discharge_blood_group'),
        drug_allergy: getVal('drug_allergy'),
        notes: getVal('discharge_notes'),
        discharge_instruction_id: getVal('discharge_notes'),
        follow_up_date: getVal('follow_up_date'),
        medicines: collectDischargeMedicines()
    };
}

function postJson(url, payload, successCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: successCallback,
        error: function(xhr) {
            var msg = 'রিকোয়েস্ট ব্যর্থ হয়েছে।';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            showAjaxMessage('danger', msg);
        }
    });
}

function submitDischargeForApproval() {
    var payload = collectDischargePayload();
    if (!payload.patientcode || !payload.admission_id) {
        showAjaxMessage('danger', 'রোগী বা ভর্তির তথ্য পাওয়া যায়নি।');
        return;
    }

    postJson(submitDischargeUrl, payload, function(response) {
        if (response.success) {
            showAjaxMessage('success', response.message || 'ম্যানেজার অনুমোদনের জন্য পাঠানো হয়েছে।');
            setTimeout(function() { window.location.reload(); }, 1200);
        } else {
            showAjaxMessage('danger', response.message || 'জমা দিতে ব্যর্থ হয়েছে।');
        }
    });
}

function approveDischarge() {
    if (!confirm('এই ছাড়পত্রের রিকোয়েস্ট অনুমোদন করতে চান?')) return;

    postJson(approveDischargeUrl, {
        patientcode: getVal('patientcode'),
        admission_id: getVal('admission_id')
    }, function(response) {
        if (response.success) {
            showAjaxMessage('success', response.message || 'ম্যানেজার অনুমোদন সম্পন্ন হয়েছে।');
            setTimeout(function() { window.location.reload(); }, 1200);
        } else {
            showAjaxMessage('danger', response.message || 'অনুমোদন ব্যর্থ হয়েছে।');
        }
    });
}

function updatePrintField(fieldName, value) {
    document.querySelectorAll('[data-print-field="' + fieldName + '"]').forEach(function(el) {
        el.textContent = value || '';
    });
}

function updatePrintFieldHtml(fieldName, value) {
    document.querySelectorAll('[data-print-field="' + fieldName + '"]').forEach(function(el) {
        el.innerHTML = nl2br(value || '');
    });
}

function updatePrintPreview() {
    // Populate the print area with current form values before printing
    updatePrintField('reg_no', getVal('discharge_reg_no'));
    updatePrintField('ward', getVal('discharge_ward'));
    updatePrintField('discharge_date', fmtDateInput(getVal('discharge_date')));
    updatePrintField('doctor_name', getVal('discharge_doctor_name'));
    updatePrintFieldHtml('diagnosis', getSelectText('discharge_diagnosis'));
    updatePrintField('blood_group', getVal('discharge_blood_group'));
    updatePrintField('drug_allergy', getVal('drug_allergy'));
    updatePrintFieldHtml('investigations', getVal('investigations'));
    updatePrintFieldHtml('notes', getSelectText('discharge_notes', '\n'));
    updatePrintField('follow_up_date', fmtDateInput(getVal('follow_up_date')));

    // Dropdown fields — use selected option text, not the value (ID)
    updatePrintField('operation_name', getSelectText('operation_name'));
    updatePrintField('operation_date', fmtDateInput(getVal('operation_date')));
    updatePrintField('operation_method', getSelectText('operation_method'));
    updatePrintField('surgeon', getSelectText('surgeon'));
    updatePrintField('anesthetist', getSelectText('anesthetist'));
    updatePrintField('assistant_1', getSelectText('assistant_1'));

    // Update medicine list in print area (prescription style)
    var medListEl = document.querySelector('#printArea #print-medicine-list');
    var medicines = collectDischargeMedicines();

    if (medListEl) {
        medListEl.innerHTML = '';
        if (medicines.length > 0) {
            medicines.forEach(function(m, idx) {
                var line = '<div style="margin-bottom:2pt;font-size:12pt;">';
                line += '<span style="font-weight:700;">' + en2bn(idx + 1) + '.</span> ';
                line += '<span style="font-weight:600;">' + escHtml(m.medicine_name) + '</span>';
                if (m.strength) line += ' (' + escHtml(m.strength) + ')';
                if (m.morning || m.noon || m.night) {
                    line += ' — ' + en2bn(m.morning || '0') + '+' + en2bn(m.noon || '0') + '+' + en2bn(m.night || '0');
                }
                if (m.days) line += ' × ' + en2bn(m.days) + ' দিন';
                if (m.timing) line += ' (' + escHtml(m.timing) + ')';
                line += '</div>';
                medListEl.innerHTML += line;
            });
        }
    }
}

function printSlip() {
    updatePrintPreview();

    // Clean standalone print execution via isolated iframe
    var printArea = document.getElementById('printArea');
    var printContent = printArea.innerHTML;

    var frame = document.getElementById('discharge_print_iframe');
    if (!frame) {
        frame = document.createElement('iframe');
        frame.id = 'discharge_print_iframe';
        frame.style.position = 'fixed';
        frame.style.right = '0';
        frame.style.bottom = '0';
        frame.style.width = '0';
        frame.style.height = '0';
        frame.style.border = 'none';
        document.body.appendChild(frame);
    }

    var frameDoc = frame.contentWindow || frame.contentDocument;
    if (frameDoc.document) frameDoc = frameDoc.document;

    frameDoc.open();
    frameDoc.write('<!DOCTYPE html><html><head><title>Discharge Booklet</title>');
    frameDoc.write('<style>');
    frameDoc.write('@page { size: legal landscape; margin: 0 !important; }');
    frameDoc.write('html, body { margin: 0; padding: 0; background: #fff; box-sizing: border-box; }');
    frameDoc.write('* { box-sizing: border-box; }');
    frameDoc.write('.booklet-sheet { display: table; table-layout: fixed; width: 355.6mm !important; height: 215.5mm !important; margin: 0 !important; padding: 0 !important; box-sizing: border-box !important; page-break-after: always; page-break-inside: avoid; border-collapse: separate; border-spacing: 0; }');
    frameDoc.write('.booklet-sheet:last-child { page-break-after: auto; }');
    frameDoc.write('.booklet-page { display: table-cell; width: 50% !important; height: 100%; box-sizing: border-box; padding: 8mm 6mm 6mm; border: 1.5pt solid #000; vertical-align: top; font-family: "Segoe UI", "Hind Siliguri", "Noto Sans Bengali", Arial, sans-serif; font-size: 12pt; line-height: 1.45; color: #000; text-align: left; position: relative; }');
    frameDoc.write('.b-hos-header { display: table; width: 100%; margin-bottom: 1.5mm; }.b-hos-logo,.b-hos-details { display: table-cell; vertical-align: middle; }.b-hos-logo { width: 24mm; padding-right: 3mm; }.b-hos-logo img { display: block; max-width: 20mm; max-height: 20mm; }');
    frameDoc.write('.b-hos-name { font-size: 20pt; line-height: 1.15; font-weight: 700; text-align: left; margin: 0 0 1.5mm; }');
    frameDoc.write('.b-hos-addr { font-size: 11pt; text-align: left; margin: 1.5mm 0 0; }');
    frameDoc.write('.b-hos-phone { font-size: 9.5pt; text-align: left; margin: 1mm 0 0; }');
    frameDoc.write('.b-thinrule { border-top: 1.4pt solid #000; margin: 2mm 0 3mm; }');
    frameDoc.write('.b-title { font-size: 14pt; line-height: 1.2; font-weight: 700; text-align: center; background: #000; color: #fff; padding: 4.5pt 7pt; margin: 0 0 3mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
    frameDoc.write('.b-subtitle { font-weight: 700; font-size: 11.5pt; line-height: 1.25; margin: 3mm 0 2mm; text-decoration: underline; }');
    frameDoc.write('.b-label { font-weight: 700; font-size: 10.5pt; white-space: nowrap; }');
    frameDoc.write('.b-value { border: 1.2pt solid #000; min-height: 30pt; padding: 4pt 6pt; font-size: 11pt; line-height: 1.4; margin-bottom: 2mm; page-break-inside: avoid; }');
    frameDoc.write('.b-note { border: 1.2pt solid #000; min-height: 34pt; padding: 4pt 6pt; font-size: 11pt; line-height: 1.4; margin-bottom: 2mm; page-break-inside: avoid; }');
    frameDoc.write('.b-info { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 4mm; }');
    frameDoc.write('.b-info td { border: 1pt solid #000; padding: 4pt 6pt; vertical-align: middle; font-size: 11pt; }');
    frameDoc.write('.b-info .lbl { width: 22%; font-weight: 700; background: #fff; white-space: nowrap; }');
    frameDoc.write('.b-info .val { width: 28%; font-weight: 600; }');
    frameDoc.write('.b-opgrid { width: 100%; border-collapse: collapse; margin: 0 0 4mm; }');
    frameDoc.write('.b-opgrid td { padding: 8pt 9pt; vertical-align: middle; border: 1.4pt solid #000; }');
    frameDoc.write('.b-opgrid .lbl { width: 28%; font-weight: 700; font-size: 14pt; background: #fff; line-height: 1.4; }');
    frameDoc.write('.b-opgrid .val { font-weight: 600; font-size: 15pt; text-align: center; min-height: 36pt; padding: 8pt 9pt; }');
    frameDoc.write('.b-opgrid .val-wide { font-weight: 600; font-size: 13pt; text-align: center; min-height: 64pt; padding: 8pt 9pt; vertical-align: top; }');
    frameDoc.write('.b-opgrid .val-date { width: 25%; font-weight: 600; font-size: 13pt; text-align: center; padding: 8pt 9pt; }');
    frameDoc.write('.b-feature { list-style: none; margin: 0 0 4mm; padding: 0; font-size: 13pt; line-height: 1.75; }');
    frameDoc.write('.b-feature li { display: block; padding: 3pt 0; }');
    frameDoc.write('.b-sign-block { margin-top: 4mm; text-align: right; page-break-inside: avoid; }');
    frameDoc.write('.b-sign-line { font-size: 10.5pt; font-weight: 700; margin-bottom: 2mm; text-align: right; }');
    frameDoc.write('.b-sign-box { display: inline-block; width: 56mm; height: 16mm; border: 1.2pt solid #000; }');
    frameDoc.write('.b-seal-box { display: inline-block; width: 48mm; height: 16mm; border: 1.2pt solid #000; }');
    frameDoc.write('.b-foot { margin-top: 3mm; font-size: 8.5pt; text-align: center; border-top: 1.2pt solid #000; padding-top: 1.5mm; color: #333; }');
    frameDoc.write('.b-ack { margin-top: 2.5mm; border: 1pt dashed #000; padding: 2.5mm 3mm; font-size: 9pt; text-align: center; page-break-inside: avoid; }');
    frameDoc.write('.b-page-fill { display: table; width: 100%; height: 100%; }');
    frameDoc.write('.b-page-top-row { display: table-row; }');
    frameDoc.write('.b-page-top { display: table-cell; vertical-align: top; }');
    frameDoc.write('.b-page-bottom-row { display: table-row; height: 1px; }');
    frameDoc.write('.b-page-bottom { display: table-cell; vertical-align: bottom; padding-top: 6mm; }');
    frameDoc.write('.b-back .b-note { min-height: 110pt; }');
    frameDoc.write('.b-back .b-info { margin: 0 0 6mm; }');
    frameDoc.write('.b-back .b-feature { font-size: 13.5pt; line-height: 2.1; margin: 0 0 4mm; }');
    frameDoc.write('.b-back .b-feature li { padding: 6pt 0; }');
    frameDoc.write('</style>');
    frameDoc.write('</head><body>');
    frameDoc.write(printContent);
    frameDoc.write('</body></html>');
    frameDoc.close();

    setTimeout(function () {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }, 400);
}

function doDischarge() {
    if (!confirm('এই রোগীকে চূড়ান্তভাবে ছাড়পত্র দিতে চান? ম্যানেজার অনুমোদন আগে থেকেই সম্পন্ন থাকতে হবে।')) return;

    postJson(finalDischargeUrl, {
        patientcode: getVal('patientcode')
    }, function(response) {
        if (response.success) {
            showAjaxMessage('success', response.message || 'রোগী সফলভাবে ছাড়পত্র দেওয়া হয়েছে।');
            setTimeout(function(){ window.location.href = "{{ route('admission.discharge') }}"; }, 1600);
        } else {
            showAjaxMessage('danger', response.message || 'ছাড়পত্র ব্যর্থ হয়েছে।');
        }
    });
}

// Auto-fill strength when user types/selects from datalist in table rows
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('dm-name')) {
        var inputVal = e.target.value.trim().toLowerCase();
        var row = e.target.closest('.discharge-medicine-row');
        if (!row || !inputVal) return;

        var datalist = document.getElementById('medicineSuggestions');
        if (!datalist) return;

        var options = datalist.querySelectorAll('option');
        for (var i = 0; i < options.length; i++) {
            var optVal = (options[i].value || '').trim().toLowerCase();
            if (optVal === inputVal) {
                var strength = options[i].getAttribute('data-strength');
                var strengthInput = row.querySelector('.dm-strength');
                if (strengthInput && strength && !strengthInput.value) {
                    strengthInput.value = strength;
                }
                break;
            }
        }
    }
});

$(document).ready(function() {
    renumberMedicineRows();

    var historyMode = @json($historyMode ?? false);

    function hasExplicitPatientSelection() {
        var params = new URLSearchParams(window.location.search);
        return params.has('patientcode') || params.has('admission_id');
    }

    function applyDischargeStepView() {
        var $selection = $('#patientSelectionScreen');
        var $workflow = $('#dischargeWorkflow');

        if (historyMode || hasExplicitPatientSelection()) {
            $selection.removeClass('is-visible');
            $workflow.addClass('is-visible');
        } else {
            $selection.addClass('is-visible');
            $workflow.removeClass('is-visible');
        }
    }

    applyDischargeStepView();

    // Patient search Select2
    var $patientSearch = $('#patientSearchSelect');
    var $continueBtn = $('#btnSelectPatientContinue');

    function syncContinueButton() {
        if (!$continueBtn.length) return;
        $continueBtn.prop('disabled', !$patientSearch.val());
    }

    if ($patientSearch.length && $.fn.select2) {
        $patientSearch.select2({
            theme: 'bootstrap4',
            placeholder: 'Search by name, patient ID, or mobile number...',
            allowClear: true,
            width: '100%'
        }).on('change select2:select select2:clear', function() {
            syncContinueButton();
        });
    }

    syncContinueButton();

    $continueBtn.on('click', function() {
        var url = $patientSearch.val();
        if (url) {
            window.location.href = url;
            return;
        }
        showAjaxMessage('danger', 'Please select a patient to continue.');
    });

    // Initialize Select2 search on medicine selector
    if ($.fn.select2) {
        $('#commonMedicineSelect').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 ঔষধ সার্চ করুন (নাম বা পাওয়ার লিখুন)...',
            allowClear: true,
            width: '100%'
        }).on('select2:select', function(e) {
            addMedicineFromSelect();
        });
    }
});

@if($historyMode && request()->boolean('print'))
window.addEventListener('load', function () { setTimeout(printSlip, 400); });
@endif
</script>
@endsection
