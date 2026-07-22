@extends('adminlte::page')

@section('title', 'রোগী ছাড়পত্র')

@section('content_header')
    <div class="cc-header">
        <div class="cc-header-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div>
            <h1 class="cc-header-title">রোগী ছাড়পত্র ড্যাশবোর্ড</h1>
            <ul class="cc-breadcrumb">
                <li><a href="#">ভর্তি</a></li>
                <li class="active">ছাড়পত্রের তালিকা</li>
            </ul>
        </div>
    </div>
@stop

@section('content')
<div id="ajaxMessage"></div>
@if(session('success'))
    <div class="cc-alert cc-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="cc-alert cc-alert-danger">{{ session('error') }}</div>
@endif

<div class="cc-layout-wrapper">
    <!-- Left Column: Discharge Queue (Sidebar) -->
    <div class="cc-sidebar">
        <div class="cc-queue-card">
            <div class="cc-queue-header">
                <h5 class="cc-queue-title"><i class="fas fa-list-ul mr-2 text-primary"></i>ছাড়পত্রের তালিকা</h5>
                <span class="cc-queue-badge">{{ $patients->count() }} জন রোগী</span>
            </div>
            <div class="cc-queue-list">
                @forelse($patients as $p)
                    <a href="{{ route('admission.discharge', ['patientcode' => $p->patientcode]) }}"
                       class="cc-patient-item {{ $patientcode === $p->patientcode ? 'active' : '' }}">
                        <div class="cc-patient-name">{{ $p->patientname }}</div>
                        <div class="cc-patient-meta">
                            <span>আইডি: {{ $p->patientcode }}</span>
                            <span>ভর্তি: {{ date('d-m-Y', strtotime($p->created_at)) }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-user-check fa-2x mb-2 text-silver" style="color:#d1d5db;"></i>
                        <p class="mb-0 font-weight-bold">ছাড়পত্রের জন্য কোনো রোগী প্রস্তুত নেই</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: Details Pane (Selected Patient Details) -->
    <div class="cc-details-pane">
        @if($patientcode && $patient)
            @php
                $dischargeStatus = $discharge->status ?? null;
                $isPendingApproval = $dischargeStatus === 'pending';
                $isManagerApproved = $dischargeStatus === 'completed';
                $td = $templateDischarge ?? null;
            @endphp
            <!-- Back to list link for mobile -->
            <div class="mb-3 d-md-none">
                <a href="{{ route('admission.discharge') }}" class="cc-btn cc-btn-secondary w-100">
                    <i class="fas fa-arrow-left mr-2"></i> ছাড়পত্রের তালিকায় ফিরে যান
                </a>
            </div>

            <!-- Patient Information Card -->
            <div class="cc-card">
                <div class="cc-card-header">
                    <div class="cc-card-header-left">
                        <div class="cc-card-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div>
                            <h4 class="cc-card-title">{{ $patient->patientname }}</h4>
                            <small class="text-muted">কোড: {{ $patient->patientcode }} | ভর্তি আইডি: {{ $admission->admissionid ?? 'নেই' }}</small>
                        </div>
                    </div>
                    <span class="cc-card-badge {{ $isPendingApproval ? 'cc-card-badge-warning' : ($isManagerApproved ? 'cc-card-badge-success' : '') }}">
                        @if($isPendingApproval)
                            ম্যানেজার অনুমোদনের অপেক্ষায়
                        @elseif($isManagerApproved)
                            ম্যানেজার অনুমোদিত
                        @else
                            স্টেজ ৬: রোগী রিলিজ
                        @endif
                    </span>
                </div>
                <div class="cc-card-body">
                    <div class="cc-summary-grid">
                        <div class="cc-summary-item">
                            <div class="cc-summary-label">মোবাইল নম্বর</div>
                            <div class="cc-summary-value">{{ $patient->mobile_no ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-summary-item">
                            <div class="cc-summary-label">বয়স</div>
                            <div class="cc-summary-value">{{ $patient->age ?? 'নেই' }} বছর</div>
                        </div>
                        <div class="cc-summary-item">
                            <div class="cc-summary-label">রক্ত গ্রুপ</div>
                            <div class="cc-summary-value">{{ $patient->blood_group ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-summary-item">
                            <div class="cc-summary-label">পিতা/স্বামী</div>
                            <div class="cc-summary-value">{{ $patient->father_name ?? 'নেই' }}</div>
                        </div>
                        <div class="cc-summary-item" style="grid-column: span 2;">
                            <div class="cc-summary-label">ঠিকানা</div>
                            <div class="cc-summary-value">
                                {{ implode(', ', array_filter([$patient->village ?? $patient->address ?? null, $patient->post ?? null, $patient->thana ?? null, $patient->district ?? null])) ?: 'নেই' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Summary -->
            @if($templateDischarge && ($templateDischarge->treatment ?? $templateDischarge->condition ?? $templateDischarge->follow_up ?? false))
                <div class="cc-card">
                    <div class="cc-card-header">
                        <div class="cc-card-header-left">
                            <div class="cc-card-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div>
                                <h4 class="cc-card-title">টেমপ্লেট ছাড়পত্র সারসংক্ষেপ</h4>
                                <small class="text-muted">টেমপ্লেট আইডি: {{ $templateDischarge->templateid }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="cc-card-body">
                        <div class="cc-info-boxes">
                            @if($templateDischarge->treatment)
                                <div class="cc-info-box">
                                    <div class="cc-info-box-icon blue"><i class="fas fa-pills"></i></div>
                                    <div class="cc-info-box-content">
                                        <div class="cc-info-box-label">প্রদত্ত চিকিৎসা</div>
                                        <div class="cc-info-box-text">{{ $templateDischarge->treatment }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($templateDischarge->condition)
                                <div class="cc-info-box">
                                    <div class="cc-info-box-icon green"><i class="fas fa-heartbeat"></i></div>
                                    <div class="cc-info-box-content">
                                        <div class="cc-info-box-label">ছাড়পত্রের সময় অবস্থা</div>
                                        <div class="cc-info-box-text">{{ $templateDischarge->condition }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($templateDischarge->follow_up)
                                <div class="cc-info-box">
                                    <div class="cc-info-box-icon amber"><i class="fas fa-calendar-check"></i></div>
                                    <div class="cc-info-box-content">
                                        <div class="cc-info-box-label">ফলোআপ পরামর্শ</div>
                                        <div class="cc-info-box-text">{{ $templateDischarge->follow_up }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Discharge Slip Form -->
            <div class="cc-card">
                <div class="cc-card-header">
                    <div class="cc-card-header-left">
                        <div class="cc-card-icon">
                            <i class="fas fa-file-medical-alt"></i>
                        </div>
                        <div>
                            <h4 class="cc-card-title">ছাড়পত্রের তথ্য</h4>
                            <small class="text-muted">ডাক্তার এখানে শুধুমাত্র ছাড়পত্রের ঔষধ এবং চূড়ান্ত স্লিপের তথ্য যুক্ত করবেন।</small>
                        </div>
                    </div>
                </div>
                <div class="cc-card-body">
                    <div class="cc-status-note {{ $isPendingApproval ? 'pending' : ($isManagerApproved ? 'approved' : '') }}">
                        @if($isPendingApproval)
                            <i class="fas fa-hourglass-half mr-2"></i> ম্যানেজার অনুমোদনের অপেক্ষায়। চূড়ান্ত ছাড়পত্র লক করা আছে।
                        @elseif($isManagerApproved)
                            <i class="fas fa-check-circle mr-2"></i> ম্যানেজার অনুমোদন করেছেন। স্লিপ প্রিন্ট করে ছাড়পত্র সম্পন্ন করুন।
                        @else
                            <i class="fas fa-edit mr-2"></i> ছাড়পত্রের তথ্য পূরণ করুন, তারপর ম্যানেজার অনুমোদনের জন্য পাঠান।
                        @endif
                    </div>

                    <div class="cc-form-grid">
                        <div class="cc-field">
                            <label>ছাড়পত্রের তারিখ</label>
                            <input type="date" id="discharge_date" value="{{ optional($discharge)->discharge_date ? date('Y-m-d', strtotime($discharge->discharge_date)) : date('Y-m-d') }}">
                        </div>
                        <div class="cc-field">
                            <label>ওয়ার্ড / কেবিন নং</label>
                            <input type="text" id="discharge_ward" value="{{ optional($discharge)->ward ?? optional($dischargeBed)->bed_number ?? '' }}" placeholder="ওয়ার্ড / কেবিন">
                        </div>
                        <div class="cc-field">
                            <label>রেজিঃ নং</label>
                            <input type="text" id="discharge_reg_no" value="{{ optional($discharge)->reg_no ?? $patientcode }}" placeholder="রেজিস্ট্রেশন নং">
                        </div>
                        <div class="cc-field">
                            <label>ডাক্তারের নাম</label>
                            <input type="text" id="discharge_doctor_name" value="{{ optional($discharge)->doctor_name ?? optional($dischargeDoctor)->doctor_name ?? '' }}" placeholder="দায়িত্বরত ডাক্তার">
                        </div>
                        <div class="cc-field cc-field-wide">
                            <label>রোগ নির্ণয়</label>
                            <input type="text" id="discharge_diagnosis" value="{{ optional($discharge)->diagnosis ?? optional($td)->condition ?? '' }}" placeholder="রোগ নির্ণয়">
                        </div>
                    </div>

                    <div class="cc-subsection-title"><i class="fas fa-notes-medical mr-2"></i>অপারেশন নোট</div>
                    <div class="cc-form-grid">
                        <div class="cc-field cc-field-wide">
                            <label>অপারেশন নাম ও তারিখ</label>
                            <input type="text" id="operation_name" value="{{ optional($discharge)->operation_name ?? '' }}" placeholder="অপারেশন নাম ও তারিখ">
                        </div>
                        <div class="cc-field cc-field-wide">
                            <label>অপারেশন পদ্ধতি ও প্রাপ্ত তথ্যাবলী</label>
                            <textarea id="operation_method" rows="3" placeholder="অপারেশন পদ্ধতি / প্রাপ্ত তথ্যাবলী">{{ optional($discharge)->operation_method ?? '' }}</textarea>
                        </div>
                        <div class="cc-field">
                            <label>সার্জন</label>
                            <input type="text" id="surgeon" value="{{ optional($discharge)->surgeon ?? '' }}">
                        </div>
                        <div class="cc-field">
                            <label>এ্যানেসথেটিষ্ট</label>
                            <input type="text" id="anesthetist" value="{{ optional($discharge)->anesthetist ?? '' }}">
                        </div>
                        <div class="cc-field">
                            <label>এ্যাসিসট্যান্ট ১</label>
                            <input type="text" id="assistant_1" value="{{ optional($discharge)->assistant_1 ?? '' }}">
                        </div>
                        <div class="cc-field">
                            <label>এ্যাসিসট্যান্ট ২</label>
                            <input type="text" id="assistant_2" value="{{ optional($discharge)->assistant_2 ?? '' }}">
                        </div>
                    </div>

                    <div class="cc-subsection-title"><i class="fas fa-clipboard-list mr-2"></i>চিকিৎসা পরামর্শ</div>
                    <div class="cc-form-grid">
                        <div class="cc-field cc-field-wide">
                            <label>পরীক্ষা নিরীক্ষার নোট</label>
                            <textarea id="investigations" rows="3" placeholder="পরীক্ষা নিরীক্ষার নোট">{{ optional($discharge)->investigations ?? '' }}</textarea>
                        </div>
                        <div class="cc-field">
                            <label>রক্ত গ্রুপ</label>
                            <input type="text" id="discharge_blood_group" value="{{ optional($discharge)->blood_group ?? ($patient->blood_group ?? '') }}">
                        </div>
                        <div class="cc-field">
                            <label>ড্রাগ এ্যালার্জী</label>
                            <input type="text" id="drug_allergy" value="{{ optional($discharge)->drug_allergy ?? '' }}">
                        </div>
                        <div class="cc-field">
                            <label>পরবর্তী ভিজিটের তারিখ</label>
                            <input type="date" id="follow_up_date" value="{{ optional($discharge)->follow_up_date ? date('Y-m-d', strtotime($discharge->follow_up_date)) : '' }}">
                        </div>
                        <div class="cc-field cc-field-wide">
                            <label>চিকিৎসা পরামর্শ / নির্দেশনা</label>
                            <textarea id="discharge_notes" rows="4" placeholder="পরামর্শ ও ছাড়পত্রের নির্দেশনা">{{ optional($discharge)->notes ?? optional($td)->treatment ?? optional($td)->follow_up ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="cc-subsection-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-pills mr-2"></i>ছাড়পত্রের নতুন ঔষধ</span>
                        <button type="button" class="cc-btn cc-btn-secondary btn-sm" onclick="addDischargeMedicineRow()">
                            <i class="fas fa-plus"></i> ঔষধ যুক্ত করুন
                        </button>
                    </div>
                    <datalist id="medicineSuggestions">
                        @foreach($commonMedicines as $medicineOption)
                            <option value="{{ $medicineOption->name ?? '' }}"></option>
                        @endforeach
                    </datalist>
                    <div class="cc-med-table-wrapper">
                        <table class="cc-med-table cc-discharge-med-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>ঔষধের নাম</th>
                                    <th>Strength</th>
                                    <th>Dose</th>
                                    <th class="cc-dosage-header">সকাল</th>
                                    <th class="cc-dosage-header">দুপুর</th>
                                    <th class="cc-dosage-header">রাত</th>
                                    <th style="width: 54px;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="discharge-medicine-body">
                                @forelse($savedMedicines as $i => $m)
                                    <tr class="discharge-medicine-row">
                                        <td class="dm-sl">{{ $i + 1 }}</td>
                                        <td><input type="text" class="dm-name" list="medicineSuggestions" value="{{ $m->medicine_name ?? $m->name ?? '' }}"></td>
                                        <td><input type="text" class="dm-strength" value="{{ $m->strength ?? '' }}"></td>
                                        <td><input type="text" class="dm-dose" value="{{ $m->dose ?? '' }}"></td>
                                        <td><input type="text" class="dm-morning" value="{{ $m->morning ?? '' }}"></td>
                                        <td><input type="text" class="dm-noon" value="{{ $m->noon ?? '' }}"></td>
                                        <td><input type="text" class="dm-night" value="{{ $m->night ?? '' }}"></td>
                                        <td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @empty
                                    <tr class="discharge-medicine-row">
                                        <td class="dm-sl">1</td>
                                        <td><input type="text" class="dm-name" list="medicineSuggestions" placeholder="ঔষধের নাম"></td>
                                        <td><input type="text" class="dm-strength" placeholder="Strength"></td>
                                        <td><input type="text" class="dm-dose" placeholder="Dose"></td>
                                        <td><input type="text" class="dm-morning" placeholder="0"></td>
                                        <td><input type="text" class="dm-noon" placeholder="0"></td>
                                        <td><input type="text" class="dm-night" placeholder="0"></td>
                                        <td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="cc-card p-3 d-flex justify-content-center gap-3 flex-wrap bg-light">
                <button type="button" onclick="submitDischargeForApproval()" class="cc-btn cc-btn-primary btn-lg px-4 shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i> সংরক্ষণ করে অনুমোদনের জন্য পাঠান
                </button>
                @if($isPendingApproval)
                    <button type="button" onclick="approveDischarge()" class="cc-btn cc-btn-success btn-lg px-4 shadow-sm">
                        <i class="fas fa-user-check mr-2"></i> ম্যানেজার অনুমোদন
                    </button>
                @endif
                <button type="button" onclick="printSlip()" class="cc-btn cc-btn-warning btn-lg px-4 shadow-sm">
                    <i class="fas fa-print mr-2"></i> ৪টি স্লিপ প্রিন্ট করুন
                </button>
                <button type="button" onclick="doDischarge()" class="cc-btn cc-btn-danger btn-lg px-4 shadow-sm" {{ $isManagerApproved ? '' : 'disabled' }}>
                    <i class="fas fa-sign-out-alt mr-2"></i> চূড়ান্ত ছাড়পত্র
                </button>
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

<div id="printArea" style="display:none;"></div>
@endsection

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --cc-bg: #faf8ff;
    --cc-primary: #0f52ba;
    --cc-primary-dark: #0b3d8a;
    --cc-primary-light: #e8edff;
    --cc-white: #ffffff;
    --cc-border: #d9d9e2;
    --cc-text: #1a1a2e;
    --cc-muted: #6b7280;
    --cc-green: #10b981;
    --cc-green-bg: #ecfdf5;
    --cc-red: #ef4444;
    --cc-red-bg: #fef2f2;
    --cc-amber: #f59e0b;
    --cc-amber-bg: #fffbeb;
    --cc-radius: 4px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

body, .content-wrapper {
    background: var(--cc-bg) !important;
    font-family: var(--cc-font) !important;
    color: var(--cc-text);
}

.cc-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding: 10px 4px 0;
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

/* Layout Grid */
.cc-layout-wrapper {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}
.cc-sidebar {
    flex: 0 0 32%;
    min-width: 280px;
}
.cc-details-pane {
    flex: 1;
    min-width: 400px;
}

/* Sidebar List */
.cc-queue-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    overflow: hidden;
}
.cc-queue-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--cc-border);
    background: var(--cc-white);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cc-queue-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
}
.cc-queue-badge {
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}
.cc-queue-list {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}
.cc-patient-item {
    display: block;
    padding: 12px 16px;
    border-bottom: 1px solid var(--cc-border);
    color: var(--cc-text) !important;
    text-decoration: none !important;
    transition: background-color 0.15s;
}
.cc-patient-item:last-child {
    border-bottom: none;
}
.cc-patient-item:hover {
    background: #f8f7fc;
}
.cc-patient-item.active {
    background: var(--cc-primary-light);
    border-left: 4px solid var(--cc-primary);
    padding-left: 12px;
}
.cc-patient-name {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
}
.cc-patient-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--cc-muted);
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
    padding: 16px;
}

.cc-status-note {
    border: 1px solid var(--cc-border);
    background: #faf8ff;
    color: var(--cc-muted);
    border-radius: var(--cc-radius);
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 16px;
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
    gap: 12px;
    margin-bottom: 16px;
}
.cc-field {
    min-width: 0;
}
.cc-field-wide {
    grid-column: span 3;
}
.cc-field label {
    display: block;
    font-size: 11px;
    color: var(--cc-muted);
    font-weight: 700;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.cc-field input,
.cc-field textarea {
    width: 100%;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 8px 10px;
    font-size: 13px;
    color: var(--cc-text);
    background: #fff;
    outline: none;
}
.cc-field textarea {
    resize: vertical;
    min-height: 74px;
}
.cc-field input:focus,
.cc-field textarea:focus,
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

/* Patient Summary Grid */
.cc-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.cc-summary-item {
    background: #faf8ff;
    padding: 10px 12px;
    border-radius: var(--cc-radius);
    border: 1px solid #f0edf7;
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
    background: #faf8ff;
    border: 1px solid #f0edf7;
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
    background: #faf8ff;
    padding: 8px 12px;
    font-weight: 600;
    color: var(--cc-text);
    border-bottom: 1.5px solid var(--cc-border);
}
.cc-med-table td {
    padding: 8px 12px;
    border-bottom: 1px solid var(--cc-border);
    color: var(--cc-text);
    vertical-align: middle;
}
.cc-med-table tr:last-child td {
    border-bottom: none;
}
.cc-med-table tr:hover td {
    background: #fafaff;
}
.cc-discharge-med-table input {
    width: 100%;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    padding: 6px 8px;
    font-size: 12.5px;
    outline: none;
}
.cc-discharge-med-table td {
    padding: 6px;
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
.cc-dosage-col {
    width: 65px;
    text-align: center;
    font-weight: 600;
    background: #faf8ff;
    border-left: 1px solid var(--cc-border);
    color: var(--cc-text);
}
.cc-dosage-header {
    text-align: center;
    border-left: 1px solid var(--cc-border);
    width: 65px;
    background: #faf8ff;
}

/* Alert Styling */
.cc-alert {
    padding: 10px 14px;
    border-radius: var(--cc-radius);
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 12px;
    border: 1px solid transparent;
}
.cc-alert-success {
    background: var(--cc-green-bg);
    color: var(--cc-green);
    border-color: #a7f3d0;
}
.cc-alert-danger {
    background: var(--cc-red-bg);
    color: var(--cc-red);
    border-color: #fecaca;
}

/* Buttons */
.cc-btn {
    border: none;
    border-radius: var(--cc-radius);
    cursor: pointer;
    font-family: var(--cc-font);
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    transition: background .15s, opacity .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none !important;
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

/* Responsive Mobile Layout */
@media (max-width: 768px) {
    .cc-layout-wrapper {
        flex-direction: column;
    }
    .cc-sidebar {
        width: 100% !important;
        flex: 1 0 auto;
        display: {{ $patientcode ? 'none' : 'block' }};
    }
    .cc-details-pane {
        width: 100% !important;
        flex: 1 0 auto;
        display: {{ $patientcode ? 'block' : 'none' }};
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

/* Print CSS */
@media print {
    body * { visibility: hidden !important; }
    #printArea, #printArea * { visibility: visible !important; }
    #printArea {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
    }
    .page {
        page-break-after: always !important;
        page-break-inside: avoid !important;
        display: block !important;
        width: 100% !important;
        min-height: 297mm !important;
        padding: 15mm 20mm !important;
        box-sizing: border-box !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-family: "SolaimanLipi", "Kalpurush", "Siyam Rupali", Arial, sans-serif !important;
    }
    .page:last-child {
        page-break-after: auto !important;
    }
    .print-header {
        border-bottom: 2px solid #2e7d32 !important;
        padding-bottom: 8px !important;
        margin-bottom: 6px !important;
    }
}
@page {
    size: A4 portrait;
    margin: 10mm;
}
</style>
@stop

@section('js')
<script>
var logoUrl = @json(config('app.logo_url'));
var csrfToken = "{{ csrf_token() }}";
var submitDischargeUrl = "{{ route('admission.discharge.submit') }}";
var approveDischargeUrl = "{{ route('admission.discharge.approve') }}";
var finalDischargeUrl = "{{ route('admission.discharge.do') }}";

function getVal(id) {
    var el = document.getElementById(id);
    return el ? String(el.value || '').trim() : '';
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
            strength: row.querySelector('.dm-strength') ? row.querySelector('.dm-strength').value.trim() : '',
            dose: row.querySelector('.dm-dose') ? row.querySelector('.dm-dose').value.trim() : '',
            morning: row.querySelector('.dm-morning') ? row.querySelector('.dm-morning').value.trim() : '',
            noon: row.querySelector('.dm-noon') ? row.querySelector('.dm-noon').value.trim() : '',
            night: row.querySelector('.dm-night') ? row.querySelector('.dm-night').value.trim() : ''
        };

        if (medicine.medicine_name) {
            medicines.push(medicine);
        }
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
        '<td><input type="text" class="dm-dose" placeholder="Dose"></td>' +
        '<td><input type="text" class="dm-morning" placeholder="0"></td>' +
        '<td><input type="text" class="dm-noon" placeholder="0"></td>' +
        '<td><input type="text" class="dm-night" placeholder="0"></td>' +
        '<td><button type="button" class="cc-row-remove" onclick="removeDischargeMedicineRow(this)"><i class="fas fa-times"></i></button></td>';
    tbody.appendChild(row);
    renumberMedicineRows();
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
        operation_name: getVal('operation_name'),
        operation_method: getVal('operation_method'),
        surgeon: getVal('surgeon'),
        anesthetist: getVal('anesthetist'),
        assistant_1: getVal('assistant_1'),
        assistant_2: getVal('assistant_2'),
        investigations: getVal('investigations'),
        blood_group: getVal('discharge_blood_group'),
        drug_allergy: getVal('drug_allergy'),
        notes: getVal('discharge_notes'),
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

function printSlip() {
    var patientcode = $('#patientcode').val() || '';
    var patientname = $('#print_patientname').val() || '';
    var mobile = $('#print_mobile').val() || '';
    var admissiondate = $('#print_admissiondate').val() || '';
    var age = $('#print_age').val() || '';
    var father = $('#print_father').val() || '';
    var village = $('#print_address_village').val() || '';
    var post = $('#print_address_post').val() || '';
    var thana = $('#print_address_thana').val() || '';
    var district = $('#print_address_district').val() || '';
    var ward = getVal('discharge_ward');
    var regNo = getVal('discharge_reg_no') || patientcode;
    var doctorName = getVal('discharge_doctor_name');
    var diagnosis = getVal('discharge_diagnosis');
    var operationName = getVal('operation_name');
    var operationMethod = getVal('operation_method');
    var surgeon = getVal('surgeon');
    var anesthetist = getVal('anesthetist');
    var assistant1 = getVal('assistant_1');
    var assistant2 = getVal('assistant_2');
    var investigations = getVal('investigations');
    var bloodGroup = getVal('discharge_blood_group');
    var drugAllergy = getVal('drug_allergy');
    var notes = getVal('discharge_notes');
    var followUpDate = getVal('follow_up_date');
    var dischargeMedicines = collectDischargeMedicines();

    function fmtDate(d) {
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var yyyy = d.getFullYear();
        return dd + '-' + mm + '-' + yyyy;
    }
    var dischargeDate = fmtDateInput(getVal('discharge_date')) || fmtDate(new Date());

    function dotted(val, width) {
        width = width || 200;
        return '<span style="display:inline-block;min-width:' + width + 'px;border-bottom:1px dotted #333;text-align:center;padding:0 4px;font-weight:bold;">' + escHtml(val || '') + '</span>';
    }

    function spacer(h) {
        return '<div style="height:' + (h || 20) + 'px;"></div>';
    }

    function pageHeader() {
        var logoHtml = logoUrl ? '<td style="width:65px;border:none;vertical-align:middle;text-align:center;"><img src="' + logoUrl + '" style="max-height:55px;max-width:55px;"></td>' : '';
        return '<div class="print-header">' +
            '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
            logoHtml +
            '<td style="border:none;text-align:center;vertical-align:middle;">' +
            '<div style="font-size:22px;font-weight:bold;color:#1b5e20;letter-spacing:2px;">প্রফেসর ক্লিনিক</div>' +
            '<div style="font-size:12px;color:#333;">মাঝিড়া, সাজাহানপুর, বগুড়া।</div>' +
            '<div style="font-size:11px;color:#555;">মোবাইল: ০১৭২০-০৩৯০০৫, ০১৭২০-০৩৯০০৬, ০১৭২০-০৩৯০০৭, ০১৭২০-০৩৯০০৮</div>' +
            '</td></tr></table></div>';
    }

    function titleBar(text) {
        return '<div style="background:#2e7d32;color:#fff;text-align:center;padding:6px 0;font-size:16px;font-weight:bold;margin:8px 0 14px 0;border-radius:4px;">' + escHtml(text) + '</div>';
    }

    function medTable(title, items) {
        if (!items || items.length === 0) return '';
        var rows = '';
        for (var i = 0; i < items.length; i++) {
            var m = items[i];
            rows += '<tr>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (i + 1) + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;">' + escHtml(m.medicine_name || m.name || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + escHtml(m.strength || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + escHtml(m.dose || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + escHtml(m.morning || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + escHtml(m.noon || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + escHtml(m.night || '') + '</td>' +
                '</tr>';
        }
        return '<div style="margin-bottom:10px;">' +
            '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:13px;font-weight:bold;">' + escHtml(title) + '</div>' +
            '<table style="width:100%;border-collapse:collapse;font-size:12px;border:1px solid #2e7d32;">' +
            '<thead><tr style="background:#e8f5e9;">' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">#</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;">ঔষধের নাম</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">Strength</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">Dose</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">সকাল</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">দুপুর</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">রাত</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';
    }

    var medHtml = medTable('Discharge Medicines (ছাড়পত্রের নতুন ঔষধ)', dischargeMedicines);
    var adviceHtml = notes
        ? '<div style="border:1px solid #2e7d32;border-radius:4px;padding:8px 10px;font-size:12px;line-height:1.8;margin-bottom:10px;white-space:normal;">' + nl2br(notes) + '</div>'
        : '<div style="border:1px dashed #2e7d32;height:80px;margin-bottom:14px;"></div>';
    var investigationHtml = investigations
        ? '<div style="border:1px solid #2e7d32;border-radius:4px;padding:8px 10px;font-size:12px;line-height:1.8;margin-bottom:14px;">' + nl2br(investigations) + '</div>'
        : '<div style="border:1px dashed #2e7d32;height:60px;margin-bottom:14px;"></div>';

    var page1 = '<div class="page">' +
        pageHeader() +
        titleBar('ছাড় পত্র') +
        '<div style="font-size:12px;line-height:2.2;">' +
        '<div style="margin-bottom:8px;">' +
        '<span style="font-weight:bold;">ওয়ার্ড/কেবিন নং-</span> ' + dotted(ward, 120) +
        '<span style="margin-left:20px;font-weight:bold;">রেজিঃ নং-</span> ' + dotted(regNo, 120) +
        '</div>' +
        '<div><span style="font-weight:bold;">প্রত্যয়ন করা যাচ্ছে যে,</span> ' + dotted(patientname, 250) + '</div>' +
        '<div><span style="font-weight:bold;">বয়স</span> ' + dotted(age, 80) + ' <span style="font-weight:bold;">পিতা/স্বামী</span> ' + dotted(father, 200) + '</div>' +
        '<div><span style="font-weight:bold;">ঠিকানা : গ্রাম</span> ' + dotted(village, 150) + ' <span style="font-weight:bold;">পোস্ট</span> ' + dotted(post, 120) + '</div>' +
        '<div style="margin-left:68px;"><span style="font-weight:bold;">থানা</span> ' + dotted(thana, 150) + ' <span style="font-weight:bold;">জেলা</span> ' + dotted(district, 150) + '</div>' +
        '<div><span style="font-weight:bold;">অত্র হাসপাতালে</span> ' + dotted(admissiondate, 120) + ' <span style="font-weight:bold;">তারিখ হতে</span> ' + dotted(dischargeDate, 120) + ' <span style="font-weight:bold;">তারিখ</span></div>' +
        '<div><span style="font-weight:bold;">পর্যন্ত চিকিৎসাধীন ছিলেন। তিনি</span> ' + dotted(diagnosis, 250) + '</div>' +
        '<div><span style="font-weight:bold;">রোগে ভুগছিলেন। তিনি</span> ' + dotted(doctorName, 230) + ' <span style="font-weight:bold;">চিকিৎসকের অধীনে চিকিৎসাধীন ছিলেন।</span></div>' +
        spacer(30) +
        '<div style="text-align:right;margin-top:20px;">' +
        '<div style="font-size:13px;font-weight:bold;margin-bottom:5px;">কর্তৃপক্ষ / চিকিৎসকের স্বাক্ষর</div>' +
        '<div><span style="font-weight:bold;">তাং-</span> ' + dotted(dischargeDate, 120) + '</div>' +
        '<div style="margin-top:8px;border:1px solid #333;width:200px;height:50px;float:right;"></div>' +
        '</div>' +
        '<div style="clear:both;"></div>' +
        '</div>' +
        '</div>';

    function opRow(label, value, secondLine) {
        var extra = secondLine ? '<br>' + escHtml(secondLine) : '';
        var body = value
            ? '<div style="min-height:28px;white-space:pre-wrap;">' + nl2br(value) + '</div>'
            : dotted('', 350);
        return '<tr>' +
            '<td style="border:2px solid #2e7d32;padding:6px 10px;font-weight:bold;font-size:13px;background:#e8f5e9;width:200px;">' + escHtml(label) + extra + '</td>' +
            '<td style="border:2px solid #2e7d32;padding:6px 10px;min-height:35px;">' + body + '</td>' +
            '</tr>';
    }
    var page2 = '<div class="page">' +
        pageHeader() +
        titleBar('অপারেশন নোট') +
        '<table style="width:100%;border-collapse:collapse;margin-top:10px;">' +
        opRow('অপারেশন নাম ও তাং', operationName) +
        opRow('অপারেশন পদ্ধতি ও', operationMethod, 'প্রাপ্ত তথ্যাবলী') +
        opRow('সার্জন', surgeon) +
        opRow('এ্যানেসথেটিষ্ট', anesthetist) +
        opRow('এ্যাসিসট্যান্ট', assistant1) +
        opRow('এ্যাসিসট্যান্ট', assistant2) +
        '</table>' +
        '</div>';

    var page3 = '<div class="page">' +
        pageHeader() +
        titleBar('প্রদত্ত চিকিৎসা পরামর্শ') +
        '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:14px;font-weight:bold;margin-bottom:8px;display:inline-block;border-radius:4px;">চিকিৎসা পরামর্শ</div>' +
        adviceHtml +
        '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:14px;font-weight:bold;margin-bottom:8px;display:inline-block;border-radius:4px;">পরীক্ষা নিরীক্ষা</div>' +
        investigationHtml +
        '<div style="font-size:12px;margin-bottom:14px;">' +
        '<span style="font-weight:bold;">রক্ত গ্রুপ</span> ' + dotted(bloodGroup, 100) +
        '<span style="margin-left:20px;font-weight:bold;">ড্রাগ এ্যালার্জী</span> ' + dotted(drugAllergy, 200) +
        '</div>' +
        '<hr style="border-top:1px solid #2e7d32;margin:10px 0;">' +
        '<div style="font-size:13px;font-weight:bold;color:#2e7d32;margin-bottom:8px;">ছাড়পত্রের ঔষধ</div>' +
        medHtml +
        '</div>';

    var page4 = '<div class="page">' +
        pageHeader() +
        titleBar('চিকিৎসা পরামর্শ ও উপদেশাবলী') +
        '<div style="border:1px solid #2e7d32;border-radius:4px;padding:10px;font-size:12px;line-height:1.8;margin-bottom:12px;min-height:90px;">' + (notes ? nl2br(notes) : '') + '</div>' +
        '<div style="font-size:12px;margin-bottom:16px;"><strong>পরবর্তী ভিজিটের তারিখ:</strong> ' + dotted(fmtDateInput(followUpDate), 140) + '</div>' +
        '<div style="background:#2e7d32;color:#fff;padding:6px 10px;font-size:14px;font-weight:bold;margin-bottom:10px;border-radius:4px;">ক্লিনিকের বিশেষ আকর্ষণ :</div>' +
        '<ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:2;">' +
        '<li style="padding:3px 0;">&#9658; ২৪ ঘণ্টা ইমার্জেন্সী সার্ভিস প্রদান।</li>' +
        '<li style="padding:3px 0;">&#9658; দিবা-রাত্রি সব সময় সিজারসহ যে কোনো অপারেশনের সু-ব্যবস্থা।</li>' +
        '<li style="padding:3px 0;">&#9658; মহিলা এম.বি.বি.এস ডাক্তার দ্বারা নরমাল ডেলিভারির সু-ব্যবস্থা।</li>' +
        '<li style="padding:3px 0;">&#9658; গরীব ও দুস্থ রোগীদের সুবিধার্থে প্রতি সোমবার বিকালে ফ্রি প্রেসক্রিপশন প্রদান।</li>' +
        '<li style="padding:3px 0;">&#9658; অন্তঃসত্ত্বা মহিলাদের জন্য চেক-আপের বিশেষ সুবিধাদি প্রদান।</li>' +
        '</ul>' +
        '<div style="margin-top:30px;font-size:12px;text-align:center;color:#555;border-top:1px solid #2e7d32;padding-top:10px;">' +
        'বিঃ দ্রঃ চিকিৎসকের পরামর্শ ব্যতীত ঔষধ সেবন বিপজ্জনক হতে পারে।' +
        '</div>' +
        '</div>';

    var printHtml = page1 + page2 + page3 + page4;

    var printArea = document.getElementById('printArea');
    printArea.innerHTML = printHtml;
    setTimeout(function () { window.print(); }, 300);
    setTimeout(function () { printArea.innerHTML = ''; }, 2000);
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

document.addEventListener('DOMContentLoaded', renumberMedicineRows);
</script>
@endsection