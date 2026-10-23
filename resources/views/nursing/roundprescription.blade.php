@extends('adminlte::page')

@section('title', 'Round Prescription | Professor Clinic')

@section('content_header')
<div class="gov-page-header">
    <div class="gov-header-stripe"></div>
    <div class="gov-header-inner">
        <div class="gov-header-left">
            <div class="gov-header-emblem">
                <div class="gov-emblem-ring">
                    <i class="fas fa-sync-alt"></i>
                </div>
            </div>
            <div>
                <h1 class="gov-page-title">Round Prescription</h1>
                <ol class="breadcrumb gov-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('nursing.index') }}">Nursing</a></li>
                    <li class="breadcrumb-item active" id="breadcrumb-current">Select Patient</li>
                </ol>
            </div>
        </div>
        <div class="gov-header-right">
            <a href="{{ route('nursing.index') }}" class="gov-btn-back">
                <i class="fas fa-arrow-left"></i> Back to Nursing
            </a>
        </div>
    </div>
</div>
@stop

@section('content')

{{-- ══ STEP INDICATOR ══ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="gov-stepper">
            <div class="gov-stepper-inner">
                <div class="gov-step gov-step-active" id="gov-step1">
                    <div class="gov-step-num" id="step1-circle">1</div>
                    <div class="gov-step-info">
                        <div class="gov-step-label" id="s1-label">Step 1</div>
                        <div class="gov-step-sub">Select Patient</div>
                    </div>
                </div>
                <div class="gov-step-line" id="step-connector"></div>
                <div class="gov-step gov-step-inactive" id="gov-step2">
                    <div class="gov-step-num gov-step-num-inactive" id="step2-circle">2</div>
                    <div class="gov-step-info">
                        <div class="gov-step-label gov-step-label-inactive" id="step2-label">Step 2</div>
                        <div class="gov-step-sub gov-step-sub-inactive" id="step2-sublabel">Round Prescription</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ SAVE ALERT ══ --}}
<div id="save-alert" class="alert d-none mb-3 gov-alert" role="alert"></div>

{{-- ══ FIXED SEARCH BAR ══ --}}
<div id="fixed-search-bar" class="gov-fixed-bar" style="display:none;">
    <div class="gov-fixed-bar-inner">
        <div class="gov-fixed-bar-brand">
            <span class="gov-fixed-dot"></span>
            <span>Patient Search</span>
        </div>
        <div class="gov-fixed-bar-field">
            <div class="gov-search-group gov-search-group-fixed">
                <i class="fas fa-search gov-search-icon"></i>
                <input type="text" id="patientSearchFixed" class="gov-search-input" placeholder="Search by name, code, or mobile...">
                <button class="gov-search-btn gov-search-btn-fixed" type="button" onclick="filterTableFixed()">Search</button>
            </div>
        </div>
        <div class="gov-fixed-bar-meta">
            <span class="gov-count-tag"><i class="fas fa-users"></i> <strong id="fsc-count">{{ $patients->total() ?? $patients->count() }}</strong> patients</span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     STEP 1 — SELECT PATIENT
══════════════════════════════════════════ --}}
<div id="panel-step1">

    {{-- PATIENT LIST CARD --}}
    <div class="gov-card mb-4">
        <div class="gov-card-header gov-card-header-navy">
            <div class="gov-card-header-left">
                <div class="gov-card-icon-box"><i class="fas fa-users"></i></div>
                <div>
                    <div class="gov-card-title">Select Patient for Round Prescription</div>
                    <div class="gov-card-subtitle">Search and choose a patient to proceed to prescription entry</div>
                </div>
            </div>
            <div class="gov-record-pill"><i class="fas fa-database"></i> {{ $patients->total() ?? $patients->count() }} Patients</div>
        </div>

        <div class="gov-search-bar" id="inline-search-bar">
            <div class="gov-search-bar-inner">
                <div class="gov-search-group gov-search-group-inline">
                    <i class="fas fa-search gov-search-icon"></i>
                    <input type="text" id="patientSearch" class="gov-search-input" placeholder="Search by patient name, code or mobile number...">
                    <button class="gov-search-btn gov-search-btn-primary" type="button" onclick="filterTable()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                <a href="https://profclinic.erpbd.org/patients/newpatient" class="gov-btn-add" target="_blank">
                    <i class="fas fa-user-plus"></i> Add New Patient
                </a>
            </div>
        </div>

        <div class="gov-card-body p-0">
            <div class="gov-table-scroll" id="patient-table-scroll">
                <table class="gov-table" id="patientTable">
                    <thead>
                        <tr>
                            <th style="width:52px;">SL#</th>
                            <th style="width:90px;">Code</th>
                            <th>Patient Name</th>
                            <th style="width:65px;">Age</th>
                            <th style="width:60px;">Sex</th>
                            <th style="width:135px;">Mobile</th>
                            <th>Address</th>
                            <th style="width:75px;">Blood</th>
                            <th style="width:90px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                        @php
                            $pid=$patient->id??''; $pcode=$patient->patientcode??'—'; $pname=$patient->patientname??'—';
                            $page=$patient->age??'—'; $pgender=strtolower($patient->gender??'');
                            $pmobile=$patient->mobile_no??'—'; $paddr=$patient->address??'';
                            $pupo=$patient->upozila??null; $pblood=$patient->blood_group??null;
                        @endphp
                        <tr class="patient-row gov-tr">
                            <td class="gov-td-muted">{{ $pid }}</td>
                            <td><span class="gov-code-tag">{{ $pcode }}</span></td>
                            <td>
                                <div class="gov-name-cell">
                                    <div class="gov-avatar">{{ strtoupper(substr($pname,0,1)) }}</div>
                                    <div>
                                        <div class="gov-name-primary">{{ $pname }}</div>
                                        @if($patient->patientfather??null)
                                        <div class="gov-name-secondary"><i class="fas fa-user-tie fa-xs"></i> {{ $patient->patientfather }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="gov-td-sm">{{ $page }}</td>
                            <td>
                                @if($pgender==='male') <span class="gov-gender-tag gov-gender-m"><i class="fas fa-mars"></i> M</span>
                                @elseif($pgender==='female') <span class="gov-gender-tag gov-gender-f"><i class="fas fa-venus"></i> F</span>
                                @else <span class="gov-dash">—</span>
                                @endif
                            </td>
                            <td class="gov-mono">{{ $pmobile }}</td>
                            <td class="gov-td-muted gov-td-sm">{{ $paddr }}{{ $pupo?', '.$pupo:'' }}</td>
                            <td>@if($pblood)<span class="gov-blood-tag">{{ $pblood }}</span>@else<span class="gov-dash">—</span>@endif</td>
                            <td class="text-center">
                                <button type="button" class="gov-btn-select"
                                    onclick="selectPatient(this)"
                                    data-id="{{ $pid }}" data-name="{{ $pname }}" data-age="{{ $page }}"
                                    data-code="{{ $pcode }}" data-mobile="{{ $pmobile }}"
                                    data-upozila="{{ $pupo }}" data-blood="{{ $pblood }}">
                                    <i class="fas fa-arrow-right"></i> Select
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9"><div class="gov-empty-state"><i class="fas fa-user-slash"></i><p>No patients found.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($patients,'links'))
            <div class="gov-pagination-bar">
                <small class="text-muted"><i class="fas fa-list-ul mr-1"></i>Showing {{ $patients->firstItem()??0 }}–{{ $patients->lastItem()??0 }} of <strong>{{ $patients->total()??0 }}</strong> patients</small>
                {{ $patients->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>

        <div class="gov-card-footer">
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Click <strong>Select</strong> on any patient row to proceed to prescription entry.</small>
            <a href="https://profclinic.erpbd.org/patients/newpatient" class="gov-btn-sm-add" target="_blank"><i class="fas fa-user-plus"></i> Add New Patient</a>
        </div>
    </div>

    {{-- ══ PAST ROUND PRESCRIPTIONS ══ --}}
    <div class="gov-card gov-card-accent-teal">
        <div class="gov-card-header gov-card-header-teal">
            <div class="gov-card-header-left">
                <div class="gov-card-icon-box gov-icon-teal"><i class="fas fa-history"></i></div>
                <div>
                    <div class="gov-card-title">Past Round Prescriptions</div>
                    <div class="gov-card-subtitle">Click <strong>View</strong> for single prescription · <strong>History</strong> to see all visits &amp; vitals</div>
                </div>
            </div>
            <div class="gov-record-pill gov-record-pill-teal"><i class="fas fa-file-medical"></i> {{ $RoundPatients->total()??$RoundPatients->count() }} Records</div>
        </div>

        <div class="gov-search-bar gov-search-bar-teal">
            <div class="gov-search-bar-inner">
                <div class="gov-search-group gov-search-group-inline" style="flex:1;">
                    <i class="fas fa-search gov-search-icon"></i>
                    <input type="text" id="roundRxSearch" class="gov-search-input" placeholder="Search by name, code or mobile..." onkeyup="filterRoundRxTable()">
                    <button class="gov-search-btn gov-search-btn-teal" type="button" onclick="filterRoundRxTable()"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>
        </div>

        <div class="gov-card-body p-0">
            <div class="gov-table-scroll">
                <table class="gov-table" id="roundRxTable">
                    <thead>
                        <tr>
                            <th style="width:52px;">SL#</th>
                            <th style="width:90px;">Rx ID</th>
                            <th>Patient Name</th>
                            <th style="width:65px;">Age</th>
                            <th style="width:60px;">Sex</th>
                            <th style="width:135px;">Mobile</th>
                            <th style="width:120px;">Date</th>
                            <th style="width:75px;">Blood</th>
                            <th style="width:185px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="roundRxTableBody">
                        @forelse($RoundPatients as $rp)
                        @php
                            $rpRxId=$rp->id??''; $rpPtId=$rp->patient_id??'';
                            $rpCode=$rp->patient_code??$rp->patientcode??'—';
                            $rpName=$rp->patient_name??$rp->patientname??'—';
                            $rpAge=$rp->patient_age??$rp->age??'—';
                            $rpGender=strtolower($rp->gender??''); $rpMobile=$rp->mobile_no??'—';
                            $rpBlood=$rp->blood_group??null;
                            $rpRxDate=$rp->prescription_date??$rp->created_at??'';
                            $rpPatCode=$rp->patient_code??$rp->patientcode??'';
                        @endphp
                        <tr class="round-rx-row gov-tr">
                            <td class="gov-td-muted">{{ $loop->iteration }}</td>
                            <td><span class="gov-rxid-tag">#{{ $rpRxId }}</span></td>
                            <td>
                                <div class="gov-name-cell">
                                    <div class="gov-avatar gov-avatar-teal">{{ strtoupper(substr($rpName,0,1)) }}</div>
                                    <div>
                                        <div class="gov-name-primary">{{ $rpName }}</div>
                                        <div class="gov-name-secondary">{{ $rpCode }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="gov-td-sm">{{ $rpAge }}</td>
                            <td>
                                @if($rpGender==='male') <span class="gov-gender-tag gov-gender-m"><i class="fas fa-mars"></i> M</span>
                                @elseif($rpGender==='female') <span class="gov-gender-tag gov-gender-f"><i class="fas fa-venus"></i> F</span>
                                @else <span class="gov-dash">—</span>
                                @endif
                            </td>
                            <td class="gov-mono">{{ $rpMobile }}</td>
                            <td class="gov-td-sm">
                                @if($rpRxDate)
                                    <div class="gov-date-primary">{{ \Carbon\Carbon::parse($rpRxDate)->format('d/m/Y') }}</div>
                                    <div class="gov-date-ago">{{ \Carbon\Carbon::parse($rpRxDate)->diffForHumans() }}</div>
                                @else —
                                @endif
                            </td>
                            <td>@if($rpBlood)<span class="gov-blood-tag">{{ $rpBlood }}</span>@else<span class="gov-dash">—</span>@endif</td>
                            <td class="text-center">
                                <div class="gov-action-group">
                                    <button type="button" class="gov-btn-view" onclick="viewRoundPrescription({{ $rpRxId }})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="gov-btn-history"
                                        onclick="viewPatientHistory({{ $rpPtId }}, '{{ addslashes($rpName) }}', '{{ $rpCode }}', '{{ $rpPatCode }}')"
                                        title="View all visits + vitals">
                                        <i class="fas fa-history"></i> History
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9"><div class="gov-empty-state"><i class="fas fa-file-medical-alt"></i><p>No past round prescriptions found.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($RoundPatients,'links'))
            <div class="gov-pagination-bar">
                <small class="text-muted"><i class="fas fa-list-ul mr-1"></i>Showing {{ $RoundPatients->firstItem()??0 }}–{{ $RoundPatients->lastItem()??0 }} of <strong>{{ $RoundPatients->total()??0 }}</strong> records</small>
                {{ $RoundPatients->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>

</div>{{-- /#panel-step1 --}}

{{-- ══════════════════════════════════════════
     STEP 2 — FORM + PRINT VIEW
══════════════════════════════════════════ --}}
<div id="panel-step2" style="display:none;">

    <div class="gov-patient-banner mb-4">
        <div class="gov-patient-banner-stripe"></div>
        <div class="gov-patient-banner-inner">
            <div class="gov-patient-banner-left">
                <div class="gov-banner-avatar" id="spb-avatar">A</div>
                <div class="gov-banner-info">
                    <div class="gov-banner-label">Selected Patient</div>
                    <div class="gov-banner-name" id="spb-name"></div>
                    <div class="gov-banner-meta" id="spb-meta"></div>
                </div>
            </div>
            <div class="gov-patient-banner-right">
                <span class="gov-banner-badge"><i class="fas fa-sync-alt mr-1"></i> Round Prescription</span>
                <button type="button" class="gov-btn-change" onclick="backToStep1()">
                    <i class="fas fa-exchange-alt"></i> Change Patient
                </button>
            </div>
        </div>
    </div>

    {{-- FORM CARD --}}
    <div class="gov-card" id="rx-form-card">
        <div class="gov-card-header gov-card-header-navy">
            <div class="gov-card-header-left">
                <div class="gov-card-icon-box"><i class="fas fa-notes-medical"></i></div>
                <div>
                    <div class="gov-card-title">Round Prescription Entry</div>
                    <div class="gov-card-subtitle">Add medicines and vitals for today's ward round</div>
                </div>
            </div>
        </div>
        <div class="gov-card-body">
            <form id="rx-form">
                @csrf
                <input type="hidden" id="f-patient-id">

                {{-- ── Section: Patient Information ── --}}
                <div class="gov-section-head"><div class="gov-section-head-bar"></div><span><i class="fas fa-user-injured mr-2"></i>Patient Information</span></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="gov-field">
                            <label class="gov-label">Attending Doctor</label>
                            <select class="gov-input" id="f-doctor" onchange="updateDoctorHeader()">
                                @forelse($doctors as $doc)
                                    @php $displayName=$doc->doctor_name??$doc->name??null; @endphp
                                    <option value="{{ $doc->id }}" data-docname="{{ e($displayName??'') }}" data-doctorno="{{ e($doc->doctor_no??'') }}" data-speciality="{{ e($doc->speciality??'') }}" data-contact="{{ e($doc->contact??'') }}" data-posting="{{ e($doc->Posting??'') }}">
                                        {{ $displayName?:'Doctor ID: '.$doc->id }}
                                    </option>
                                @empty
                                    <option value="">No doctors found</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="gov-field">
                            <label class="gov-label">Patient Code</label>
                            <input type="text" class="gov-input" id="f-patient-code" readonly>
                        </div>
                        <div class="gov-field">
                            <label class="gov-label">Patient Name</label>
                            <input type="text" class="gov-input" id="f-patient-name" placeholder="Full name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="gov-field">
                            <label class="gov-label">Age</label>
                            <input type="text" class="gov-input" id="f-patient-age" placeholder="e.g. 25 yrs">
                        </div>
                        <div class="gov-field">
                            <label class="gov-label">Prescription Date</label>
                            <input type="date" class="gov-input" id="f-date">
                        </div>
                        <div class="gov-field">
                            <label class="gov-label">Round Time</label>
                            <div class="gov-input-icon-wrap">
                                <i class="fas fa-clock gov-input-icon"></i>
                                <input type="time" class="gov-input gov-input-padded" id="f-round-time">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gov-divider"></div>

                {{-- ══ VITALS SECTION ══ --}}
                <div class="gov-section-head"><div class="gov-section-head-bar gov-bar-red"></div><span><i class="fas fa-heartbeat mr-2 text-danger"></i>Round Vitals — Blood Pressure &amp; Pulse</span></div>
                <div class="gov-vitals-panel">
                    <div class="gov-vitals-panel-header">
                        <div class="gov-vitals-header-left">
                            <div class="gov-vitals-icon"><i class="fas fa-heartbeat"></i></div>
                            <div>
                                <div class="gov-vitals-title">Vital Signs</div>
                                <div class="gov-vitals-sub">Record BP &amp; Pulse during this ward round visit</div>
                            </div>
                        </div>
                        <span id="vitals-status-pill" class="gov-vitals-pill gov-vitals-pill-empty">
                            <i class="fas fa-circle" style="font-size:6px;"></i> Not Recorded
                        </span>
                    </div>

                    <div class="gov-vitals-fields">
                        <div class="gov-vital-group">
                            <label class="gov-vital-label"><span class="gov-vital-dot gov-dot-red"></span>Systolic BP</label>
                            <div class="gov-vital-input-box">
                                <input type="number" class="gov-vital-input" id="f-bp-systolic" placeholder="120" min="60" max="250" oninput="onVitalChange()">
                                <span class="gov-vital-unit">mmHg</span>
                            </div>
                        </div>
                        <div class="gov-vital-slash">/</div>
                        <div class="gov-vital-group">
                            <label class="gov-vital-label"><span class="gov-vital-dot gov-dot-blue"></span>Diastolic BP</label>
                            <div class="gov-vital-input-box">
                                <input type="number" class="gov-vital-input" id="f-bp-diastolic" placeholder="80" min="40" max="160" oninput="onVitalChange()">
                                <span class="gov-vital-unit">mmHg</span>
                            </div>
                        </div>
                        <div class="gov-vital-spacer"></div>
                        <div class="gov-vital-group">
                            <label class="gov-vital-label"><span class="gov-vital-dot gov-dot-green"></span>Pulse Rate</label>
                            <div class="gov-vital-input-box">
                                <input type="number" class="gov-vital-input" id="f-pulse" placeholder="72" min="30" max="250" oninput="onVitalChange()">
                                <span class="gov-vital-unit">bpm</span>
                            </div>
                        </div>
                        <div class="gov-vital-live-card" id="vital-live-card" style="display:none;">
                            <div class="gov-vital-live-bp" id="vital-live-bp">—/—</div>
                            <div class="gov-vital-live-unit">mmHg</div>
                            <div class="gov-vital-live-pulse">
                                <i class="fas fa-heartbeat gov-pulse-beat-icon"></i>
                                <span id="vital-live-pulse-val">—</span> bpm
                            </div>
                            <div class="gov-vital-live-status" id="vital-live-status"></div>
                        </div>
                    </div>

                    <div class="gov-vitals-note-row">
                        <label class="gov-vital-label" style="font-size:11px;"><i class="fas fa-comment-medical mr-1"></i> Vitals Note <span class="text-muted font-weight-normal">(optional)</span></label>
                        <input type="text" class="gov-input" id="f-vitals-note" placeholder="e.g. Patient was resting, arm: left...">
                    </div>
                </div>

                <div class="gov-divider"></div>

                {{-- ── MEDICINES ── --}}
                <div class="gov-section-split">
                    <div class="gov-section-head mb-0" style="border:none;padding:0;">
                        <div class="gov-section-head-bar gov-bar-navy"></div>
                        <span><i class="fas fa-pills mr-2"></i>Medicines</span>
                        <span class="gov-med-count-badge" id="sel-med-count-badge">0</span>
                    </div>
                    <button type="button" class="gov-btn-add-row" onclick="addBlankRow()"><i class="fas fa-plus"></i> Add Blank Row</button>
                </div>

                {{-- Selected Medicines --}}
                <div class="gov-med-table-card gov-med-card-selected mb-4">
                    <div class="gov-med-card-header">
                        <div class="d-flex align-items-center">
                            <span class="gov-med-dot gov-med-dot-navy"></span>
                            <span class="gov-med-title">Selected Medicines</span>
                            <span class="gov-med-count gov-med-count-navy" id="sel-med-badge">0</span>
                        </div>
                        <button type="button" class="gov-btn-clear" onclick="clearAllMeds()"><i class="fas fa-trash-alt"></i> Clear All</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="gov-table gov-med-table mb-0" style="min-width:750px;">
                            <thead>
                                <tr>
                                    <th style="width:35px;">#</th><th>Medicine Name</th><th style="width:90px;">Dose</th><th style="width:80px;">Route</th>
                                    <th style="width:110px;">Frequency</th><th style="width:85px;">Duration</th><th style="width:85px;">Timing</th>
                                    <th style="width:110px;">Remarks</th><th style="width:42px;"></th>
                                </tr>
                            </thead>
                            <tbody id="sel-med-tbody">
                                <tr id="empty-sel-row">
                                    <td colspan="9"><div class="gov-empty-state"><i class="fas fa-pills"></i><span>No medicines selected. Click Add Blank Row to begin.</span></div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Available Medicines --}}
                <div class="gov-med-table-card mb-0">
                    <div class="gov-med-card-header">
                        <div class="d-flex align-items-center">
                            <span class="gov-med-dot gov-med-dot-blue"></span>
                            <span class="gov-med-title">Available Medicine List</span>
                            <span class="gov-med-count gov-med-count-blue">{{ $medicines->count() }}</span>
                        </div>
                        <div class="gov-avail-filter">
                            <i class="fas fa-filter" style="color:#92a0b0;font-size:12px;padding:0 8px;"></i>
                            <input type="text" id="med-filter-input" class="gov-avail-input" placeholder="Filter medicines..." oninput="filterCheckboxList(this.value)">
                        </div>
                    </div>
                    <div style="max-height:230px;overflow-y:auto;">
                        <table class="gov-table gov-med-table mb-0">
                            <thead><tr><th width="35"></th><th>Medicine</th><th>Strength</th><th>Dose</th><th>Frequency</th><th>Duration</th><th width="50"></th></tr></thead>
                            <tbody id="avail-med-tbody">
                                @forelse($medicines as $med)
                                <tr class="avail-med-row gov-tr" data-name="{{ strtolower($med->name??'') }}">
                                    <td><input type="checkbox" class="avail-med-cb gov-checkbox" data-name="{{ e($med->name) }}" data-strength="{{ e($med->strength??'') }}" data-dose="{{ e($med->dose??'') }}" data-route="{{ e($med->route??'') }}" data-frequency="{{ e($med->dose??$med->frequency??'') }}" data-duration="{{ e($med->duration??'') }}" data-timing="{{ e($med->instruction??$med->timing??'') }}" data-note="{{ e($med->note??'') }}" onchange="onAvailMedChange(this)"></td>
                                    <td><span class="gov-med-name">{{ $med->name }}</span></td>
                                    <td class="gov-td-muted gov-td-sm">{{ $med->strength??'—' }}</td>
                                    <td class="gov-td-muted gov-td-sm">{{ $med->dose??'—' }}</td>
                                    <td class="gov-td-muted gov-td-sm">{{ $med->frequency??'—' }}</td>
                                    <td class="gov-td-muted gov-td-sm">{{ $med->duration??'—' }}</td>
                                    <td><button type="button" class="gov-btn-quick-add" onclick="quickAddMed(this)"><i class="fas fa-plus"></i></button></td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No medicines found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="gov-divider mt-4"></div>
                <div class="gov-field">
                    <label class="gov-label">Additional Notes / Instructions</label>
                    <textarea class="gov-input" id="f-notes" rows="2" placeholder="Additional clinical notes..."></textarea>
                </div>

                <div class="gov-form-footer">
                    <button type="button" class="gov-btn-back-form" onclick="backToStep1()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="button" class="gov-btn-save" id="btn-save" onclick="saveAndGenerateRx()"><i class="fas fa-save"></i> Save &amp; Generate Prescription</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── PRINT VIEW ── --}}
    <div id="rx-view" style="display:none;">
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0"><div class="gov-summary-card gov-sum-navy"><div class="gov-sum-icon"><i class="fas fa-user"></i></div><div><div class="gov-sum-label">Patient</div><div class="gov-sum-value" id="ib-name">—</div></div></div></div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0"><div class="gov-summary-card gov-sum-teal"><div class="gov-sum-icon"><i class="fas fa-birthday-cake"></i></div><div><div class="gov-sum-label">Age</div><div class="gov-sum-value" id="ib-age">—</div></div></div></div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0"><div class="gov-summary-card gov-sum-slate"><div class="gov-sum-icon"><i class="fas fa-calendar"></i></div><div><div class="gov-sum-label">Date</div><div class="gov-sum-value" id="ib-date">—</div></div></div></div>
            <div class="col-lg-3 col-sm-6"><div class="gov-summary-card gov-sum-maroon"><div class="gov-sum-icon"><i class="fas fa-user-md"></i></div><div><div class="gov-sum-label">Doctor</div><div class="gov-sum-value" id="ib-doctor" style="font-size:12px;">—</div></div></div></div>
        </div>

        <div id="rx-vitals-summary-bar" class="gov-vitals-summary-bar mb-4" style="display:none;">
            <div class="gov-vsb-label"><i class="fas fa-heartbeat mr-2"></i>Round Vitals</div>
            <div class="gov-vsb-chips">
                <div class="gov-vsb-chip gov-vsb-red">
                    <span class="gov-vsb-chip-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <div><div class="gov-vsb-chip-label">Blood Pressure</div><div class="gov-vsb-chip-val" id="rvsb-bp">—</div></div>
                </div>
                <div class="gov-vsb-chip gov-vsb-green">
                    <span class="gov-vsb-chip-icon"><i class="fas fa-heartbeat"></i></span>
                    <div><div class="gov-vsb-chip-label">Pulse Rate</div><div class="gov-vsb-chip-val" id="rvsb-pulse">—</div></div>
                </div>
                <div class="gov-vsb-chip gov-vsb-slate" id="rvsb-status-chip">
                    <span class="gov-vsb-chip-icon"><i class="fas fa-info-circle"></i></span>
                    <div><div class="gov-vsb-chip-label">BP Status</div><div class="gov-vsb-chip-val" id="rvsb-bp-status">—</div></div>
                </div>
            </div>
        </div>

        <div class="gov-card">
            <div class="gov-card-header gov-card-header-navy">
                <div class="gov-card-header-left">
                    <div class="gov-card-icon-box"><i class="fas fa-notes-medical"></i></div>
                    <div><div class="gov-card-title">Round Prescription — Ready to Print</div><div class="gov-card-subtitle">Review before printing</div></div>
                </div>
                <span class="gov-saved-badge"><i class="fas fa-check-circle mr-1"></i> Saved — <span id="rx-badge-name">—</span></span>
            </div>
            <div class="gov-card-body p-0">
                <div id="prescription-print-area">
                    <div class="round-wrapper">
                        <div class="round-header">
                            <div class="round-header-left">
                                <div class="round-logo-row">
                                    <div class="round-cp-logo"><span class="round-cp-c">C</span><span class="round-cp-p">P</span></div>
                                    <div>
                                        <div class="round-clinic-bn">প্রফেসর ক্লিনিক</div>
                                        <div class="round-clinic-address">মাঝিড়া, শাজাহানপুর, বগুড়া।</div>
                                        <div class="round-clinic-phones">মোবাঃ ০১৭২০-০৩৯০০৫, ০১৭২০-০৩৯০০৬</div>
                                        <div class="round-clinic-phones">০১৭২০-০৩৯০০৭, ০১৭২০-০৩৯০০৮</div>
                                    </div>
                                </div>
                            </div>
                            <div class="round-header-right">
                                <div class="round-doctor-title" id="rx-doctor-name">—</div>
                                <div class="round-doctor-deg" id="rx-doctor-speciality"></div>
                                <div class="round-doctor-deg" id="rx-doctor-regno"></div>
                                <div class="round-doctor-college" id="rx-doctor-posting"></div>
                                <div class="round-doctor-deg" id="rx-doctor-contact"></div>
                            </div>
                        </div>
                        <div class="round-nad-row">
                            <div class="round-nad-field"><span class="round-nad-label">Code :</span><span class="round-nad-value" id="rx-code">—</span></div>
                            <div class="round-nad-field"><span class="round-nad-label">Name :</span><span class="round-nad-value" id="rx-name">—</span></div>
                            <div class="round-nad-field"><span class="round-nad-label">Age :</span><span class="round-nad-value" id="rx-age">—</span></div>
                            <div class="round-nad-field"><span class="round-nad-label">Date :</span><span class="round-nad-value" id="rx-date">—</span></div>
                        </div>
                        <div class="round-vitals-row" id="rx-vitals-print-row" style="display:none;">
                            <div class="round-vital-item"><span class="round-vital-label">BP :</span><span class="round-vital-value round-vital-bp" id="rx-print-bp">—</span><span class="round-vital-unit">mmHg</span></div>
                            <div class="round-vital-divider"></div>
                            <div class="round-vital-item"><span class="round-vital-label">Pulse :</span><span class="round-vital-value round-vital-pulse" id="rx-print-pulse">—</span><span class="round-vital-unit">bpm</span></div>
                            <div class="round-vital-divider"></div>
                            <div class="round-vital-item round-vital-status-item"><span class="round-vital-status-badge" id="rx-print-bp-status"></span></div>
                            <div class="round-vital-note" id="rx-print-vitals-note"></div>
                        </div>
                        <div class="round-body">
                            <div class="round-rx-symbol">Rx</div>
                            <div class="round-section-label">Round Prescription</div>
                            <div class="round-time-right" id="rx-round-time-display"></div>
                            <div class="round-med-table-wrap">
                                <table class="round-med-table" id="rx-medicine-print-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="round-th-name">ঔষধের নাম</th>
                                            <th colspan="3" class="round-th-group">কখন খাবেন?</th>
                                            <th colspan="2" class="round-th-group">আহারের</th>
                                            <th rowspan="2" class="round-th-name">কতদিন</th>
                                            <th rowspan="2" class="round-th-name">নির্দেশনা</th>
                                        </tr>
                                        <tr>
                                            <th class="round-th-sub">সকাল</th><th class="round-th-sub">দুপুর</th><th class="round-th-sub">রাত</th>
                                            <th class="round-th-sub">আগে</th><th class="round-th-sub">পরে</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rx-med-print-tbody"></tbody>
                                </table>
                            </div>
                            <div class="round-rx-notes" id="rx-notes"></div>
                        </div>
                        <div class="round-footer">
                            <span>বিঃ দ্রঃ ............................................</span>
                            <span>............... দিন/মাস পর ব্যবস্থাপত্র সহ সাক্ষাৎ করিবেন।</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gov-card-footer">
                <small class="text-muted"><i class="fas fa-clock mr-1"></i> Generated: <span id="gen-time">—</span></small>
                <div style="display:flex;gap:8px;">
                    <button onclick="printRx()" class="gov-btn-print"><i class="fas fa-print"></i> Print</button>
                    <button type="button" class="gov-btn-edit" onclick="editRx()"><i class="fas fa-edit"></i> Edit</button>
                    <button type="button" class="gov-btn-new" onclick="backToStep1()"><i class="fas fa-plus"></i> New</button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /#panel-step2 --}}

{{-- ══ PRINT OVERLAYS ══ --}}
<div id="print-overlay" style="display:none;"></div>
<div id="history-print-overlay" style="display:none;"></div>

{{-- ══ MODAL: SINGLE RX VIEW ══ --}}
<div class="modal fade" id="rxViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content gov-modal-content">
            <div class="modal-header gov-modal-header">
                <div class="d-flex align-items-center">
                    <div class="gov-modal-icon mr-3"><i class="fas fa-file-medical"></i></div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white">Round Prescription</h5>
                        <small style="color:rgba(255,255,255,.75);font-size:12px;" id="modal-subtitle">Loading...</small>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <button type="button" class="gov-modal-print-btn" onclick="printModal()"><i class="fas fa-print mr-1"></i> Print</button>
                    <button type="button" class="gov-modal-close-btn" data-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="modal-loading" class="gov-modal-state"><div style="font-size:32px;margin-bottom:12px;"><i class="fas fa-spinner fa-spin" style="color:#1b3a6b;"></i></div><p>Loading prescription...</p></div>
                <div id="modal-error" class="gov-modal-state d-none"><div style="font-size:32px;color:#b71c1c;margin-bottom:10px;"><i class="fas fa-exclamation-triangle"></i></div><p id="modal-error-msg">Failed to load.</p></div>
                <div id="modal-rx-area" class="d-none">
                    <div class="gov-modal-summary-bar">
                        <div class="gov-msi gov-msi-navy"><i class="fas fa-user"></i><div><div class="gov-msi-label">Patient</div><div class="gov-msi-val" id="m-ib-name">—</div></div></div>
                        <div class="gov-msi gov-msi-green"><i class="fas fa-birthday-cake"></i><div><div class="gov-msi-label">Age</div><div class="gov-msi-val" id="m-ib-age">—</div></div></div>
                        <div class="gov-msi gov-msi-amber"><i class="fas fa-calendar"></i><div><div class="gov-msi-label">Date</div><div class="gov-msi-val" id="m-ib-admission">—</div></div></div>
                        <div class="gov-msi gov-msi-teal"><i class="fas fa-hashtag"></i><div><div class="gov-msi-label">Rx ID</div><div class="gov-msi-val" id="m-ib-id">—</div></div></div>
                    </div>
                    <div id="m-vitals-bar" class="gov-modal-vitals-bar d-none">
                        <div class="gov-mvb-item gov-mvb-red"><i class="fas fa-tachometer-alt mr-1"></i><span class="gov-mvb-label">BP:</span><strong id="m-vb-bp">—</strong><span class="gov-mvb-unit">mmHg</span></div>
                        <div class="gov-mvb-sep"></div>
                        <div class="gov-mvb-item gov-mvb-green"><i class="fas fa-heartbeat mr-1"></i><span class="gov-mvb-label">Pulse:</span><strong id="m-vb-pulse">—</strong><span class="gov-mvb-unit">bpm</span></div>
                        <div class="gov-mvb-sep"></div>
                        <div class="gov-mvb-item"><span class="gov-mvb-status" id="m-vb-status"></span></div>
                    </div>
                    <div id="modal-prescription-print-area" style="padding:20px 24px;">
                        <div class="round-wrapper">
                            <div class="round-header">
                                <div class="round-header-left"><div class="round-logo-row"><div class="round-cp-logo"><span class="round-cp-c">C</span><span class="round-cp-p">P</span></div><div><div class="round-clinic-bn">প্রফেসর ক্লিনিক</div><div class="round-clinic-address">মাঝিড়া, শাজাহানপুর, বগুড়া।</div></div></div></div>
                                <div class="round-header-right"><div class="round-doctor-title" id="m-rx-doctor-name">—</div><div class="round-doctor-deg" id="m-rx-doctor-deg"></div></div>
                            </div>
                            <div class="round-nad-row">
                                <div class="round-nad-field"><span class="round-nad-label">Name :</span><span class="round-nad-value" id="m-rx-name">—</span></div>
                                <div class="round-nad-field"><span class="round-nad-label">Age :</span><span class="round-nad-value" id="m-rx-age">—</span></div>
                                <div class="round-nad-field"><span class="round-nad-label">Date :</span><span class="round-nad-value" id="m-rx-date">—</span></div>
                            </div>
                            <div class="round-vitals-row" id="m-rx-vitals-row" style="display:none;">
                                <div class="round-vital-item"><span class="round-vital-label">BP :</span><span class="round-vital-value round-vital-bp" id="m-rx-print-bp">—</span><span class="round-vital-unit">mmHg</span></div>
                                <div class="round-vital-divider"></div>
                                <div class="round-vital-item"><span class="round-vital-label">Pulse :</span><span class="round-vital-value round-vital-pulse" id="m-rx-print-pulse">—</span><span class="round-vital-unit">bpm</span></div>
                                <div class="round-vital-divider"></div>
                                <div class="round-vital-item"><span class="round-vital-status-badge" id="m-rx-print-bp-status"></span></div>
                                <div class="round-vital-note" id="m-rx-print-vitals-note"></div>
                            </div>
                            <div class="round-body">
                                <div class="round-rx-symbol">Rx</div>
                                <div class="round-section-label">Round Prescription</div>
                                <div class="round-med-table-wrap">
                                    <table class="round-med-table">
                                        <thead>
                                            <tr><th rowspan="2" class="round-th-name">ঔষধের নাম</th><th colspan="3" class="round-th-group">কখন খাবেন?</th><th colspan="2" class="round-th-group">আহারের</th><th colspan="3" class="round-th-group">কতদিন?</th></tr>
                                            <tr><th class="round-th-sub">সকাল</th><th class="round-th-sub">দুপুর</th><th class="round-th-sub">রাত</th><th class="round-th-sub">আগে</th><th class="round-th-sub">পরে</th><th class="round-th-sub">দিন</th><th class="round-th-sub">মাস</th><th class="round-th-sub">চলবে</th></tr>
                                        </thead>
                                        <tbody id="m-rx-med-tbody"></tbody>
                                    </table>
                                </div>
                                <div class="round-rx-notes" id="m-rx-notes"></div>
                            </div>
                            <div class="round-footer"><span>বিঃ দ্রঃ ............................................</span><span>............... দিন/মাস পর সাক্ষাৎ করিবেন।</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer gov-modal-footer">
                <small class="text-muted">Saved: <span id="m-saved-time">—</span></small>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="gov-btn-print" onclick="printModal()"><i class="fas fa-print"></i> Print</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL: PATIENT VISIT HISTORY ══ --}}
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content gov-modal-content">
            <div class="modal-header gov-modal-header">
                <div class="d-flex align-items-center">
                    <div class="gov-modal-icon mr-3"><i class="fas fa-history"></i></div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white" id="history-modal-title">Visit History</h5>
                        <small style="color:rgba(255,255,255,.75);font-size:12px;" id="history-modal-sub">Loading...</small>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <button type="button" class="gov-modal-print-btn" onclick="printHistory()"><i class="fas fa-print mr-1"></i> Print All</button>
                    <button type="button" class="gov-modal-close-btn" data-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="history-loading" class="gov-modal-state"><div style="font-size:32px;margin-bottom:12px;"><i class="fas fa-spinner fa-spin" style="color:#1b3a6b;"></i></div><p>Loading patient history...</p></div>
                <div id="history-error" class="gov-modal-state d-none"><div style="font-size:32px;color:#b71c1c;margin-bottom:10px;"><i class="fas fa-exclamation-triangle"></i></div><p id="history-error-msg">Failed to load history.</p></div>
                <div id="history-area" class="d-none">
                    <div class="history-patient-bar" id="history-patient-bar"></div>
                    <div class="gov-history-tabs" id="history-tabs-wrap">
                        <button class="gov-htab gov-htab-active" id="htab-combined" onclick="switchHistoryTab('combined')"><i class="fas fa-layer-group mr-1"></i> All Timeline</button>
                        <button class="gov-htab" id="htab-rx" onclick="switchHistoryTab('rx')"><i class="fas fa-sync-alt mr-1"></i> Round Rx <span class="gov-htab-count" id="htab-rx-count">0</span></button>
                        <button class="gov-htab" id="htab-vitals" onclick="switchHistoryTab('vitals')"><i class="fas fa-heartbeat mr-1"></i> Pre-Con Vitals <span class="gov-htab-count gov-htab-count-teal" id="htab-vitals-count">0</span></button>
                    </div>
                    <div id="history-panel-combined" style="padding:20px 24px 10px;"><div id="history-timeline"></div></div>
                    <div id="history-panel-rx" style="padding:20px 24px 10px;display:none;"><div id="history-timeline-rx-only"></div></div>
                    <div id="history-panel-vitals" style="padding:20px 24px 10px;display:none;"><div id="history-vitals-table-wrap"></div></div>
                </div>
            </div>
            <div class="gov-modal-footer">
                <small class="text-muted" id="history-footer-info">—</small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Close</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@600;700&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ════════════════════════════════════════════
   GOVERNMENT PROFESSIONAL DESIGN SYSTEM
   Professor Clinic — Round Prescription
   Tone: Authoritative · Structured · Reliable
════════════════════════════════════════════ */
:root {
    --gov-navy:       #1b3a6b;
    --gov-navy-dark:  #122a52;
    --gov-navy-light: #e8edf5;
    --gov-navy-mid:   #2a4f8a;
    --gov-slate:      #3d5166;
    --gov-teal:       #0d6e6e;
    --gov-teal-light: #e0f2f0;
    --gov-teal-mid:   #117a7a;
    --gov-red:        #a52020;
    --gov-red-light:  #fdeaea;
    --gov-amber:      #7a4800;
    --gov-amber-light:#fff4e0;
    --gov-green:      #1a5c2e;
    --gov-green-light:#e7f6ec;
    --gov-maroon:     #6b1b2a;
    --gov-maroon-light:#fceef0;
    --gov-white:      #ffffff;
    --gov-bg:         #eef0f4;
    --gov-border:     #c8d0da;
    --gov-border-dark:#a8b4c0;
    --gov-text:       #1a2332;
    --gov-muted:      #5a6878;
    --gov-rule:       #d0d8e2;
    --radius-xs:      3px;
    --radius-sm:      5px;
    --radius-md:      8px;
    --radius-lg:      12px;
    --shadow-sm:      0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
    --shadow-md:      0 4px 12px rgba(0,0,0,.1), 0 2px 4px rgba(0,0,0,.06);
    --shadow-lg:      0 8px 24px rgba(0,0,0,.12);
    --font-sans:      'IBM Plex Sans', 'Hind Siliguri', Arial, sans-serif;
    --font-serif:     'IBM Plex Serif', Georgia, serif;
}

/* ── BASE ── */
body, .content-wrapper { background: var(--gov-bg) !important; font-family: var(--font-sans); }

/* ════════════════════════════════
   PAGE HEADER
════════════════════════════════ */
.gov-page-header {
    background: var(--gov-white);
    border-bottom: 3px solid var(--gov-navy);
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.gov-header-stripe {
    height: 4px;
    background: repeating-linear-gradient(90deg, var(--gov-navy) 0px, var(--gov-navy) 24px, var(--gov-teal) 24px, var(--gov-teal) 32px);
}
.gov-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.gov-header-left { display: flex; align-items: center; gap: 16px; }
.gov-header-emblem {}
.gov-emblem-ring {
    width: 48px; height: 48px;
    border-radius: 50%;
    border: 2.5px solid var(--gov-navy);
    display: flex; align-items: center; justify-content: center;
    color: var(--gov-navy); font-size: 18px;
    background: var(--gov-navy-light);
}
.gov-page-title {
    font-family: var(--font-serif);
    font-size: 20px; font-weight: 700;
    color: var(--gov-navy); margin: 0; line-height: 1.2;
}
.gov-breadcrumb {
    background: transparent !important;
    padding: 0 !important; margin: 4px 0 0 !important;
    font-size: 11.5px;
}
.gov-breadcrumb .breadcrumb-item a { color: var(--gov-navy-mid); text-decoration: none; }
.gov-breadcrumb .breadcrumb-item.active { color: var(--gov-muted); }
.gov-btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--gov-white);
    border: 1.5px solid var(--gov-border-dark);
    color: var(--gov-navy); border-radius: var(--radius-sm);
    padding: 8px 18px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all .2s;
}
.gov-btn-back:hover { background: var(--gov-navy); color: #fff; border-color: var(--gov-navy); text-decoration: none; }

/* ════════════════════════════════
   STEPPER
════════════════════════════════ */
.gov-stepper {
    background: var(--gov-white);
    border: 1.5px solid var(--gov-border);
    border-left: 4px solid var(--gov-navy);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    padding: 16px 28px;
}
.gov-stepper-inner { display: flex; align-items: center; }
.gov-step { display: flex; align-items: center; }
.gov-step-num {
    width: 38px; height: 38px; border-radius: 50%;
    background: var(--gov-navy);
    border: 2.5px solid var(--gov-navy);
    color: #fff; font-weight: 700; font-size: 15px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .35s;
    box-shadow: 0 0 0 4px rgba(27,58,107,.12);
}
.gov-step-num-inactive { background: var(--gov-white); color: #b0bec5; border-color: var(--gov-border); box-shadow: none; }
.gov-step-info { margin-left: 12px; }
.gov-step-label { font-size: 13px; font-weight: 700; color: var(--gov-navy); line-height: 1.2; }
.gov-step-sub { font-size: 11px; color: var(--gov-muted); margin-top: 1px; }
.gov-step-label-inactive { color: #b0bec5; }
.gov-step-sub-inactive { color: #ccc; }
.gov-step-line {
    flex: 1; max-width: 160px; height: 3px;
    background: var(--gov-border); margin: 0 20px;
    border-radius: 2px; transition: background .4s;
}
.gov-step-line.done { background: var(--gov-navy); }

/* ════════════════════════════════
   ALERT
════════════════════════════════ */
.gov-alert { border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 500; border-left-width: 4px; }

/* ════════════════════════════════
   FIXED SEARCH BAR
════════════════════════════════ */
.gov-fixed-bar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
    background: var(--gov-navy-dark);
    border-bottom: 3px solid var(--gov-teal);
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
    transform: translateY(-100%); transition: transform .3s cubic-bezier(.4,0,.2,1);
    opacity: 0; pointer-events: none;
}
.gov-fixed-bar.visible { transform: translateY(0); opacity: 1; pointer-events: all; }
.gov-fixed-bar-inner { display: flex; align-items: center; gap: 16px; padding: 10px 24px; flex-wrap: wrap; }
.gov-fixed-bar-brand { display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,.9); font-size: 13px; font-weight: 700; flex-shrink: 0; }
.gov-fixed-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gov-teal-mid); box-shadow: 0 0 0 3px rgba(13,110,110,.3); }
.gov-fixed-bar-field { flex: 1; min-width: 240px; }
.gov-count-tag { background: rgba(255,255,255,.15); color: #fff; border-radius: 4px; padding: 5px 14px; font-size: 12.5px; font-weight: 600; }

/* ════════════════════════════════
   CARDS
════════════════════════════════ */
.gov-card {
    background: var(--gov-white);
    border-radius: var(--radius-md);
    border: 1.5px solid var(--gov-border);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 24px;
}
.gov-card-accent-teal { border-top: 3px solid var(--gov-teal); }
.gov-card-header {
    padding: 16px 24px;
    border-bottom: 1.5px solid var(--gov-border);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
}
.gov-card-header-navy { background: linear-gradient(135deg, var(--gov-navy-dark) 0%, var(--gov-navy-mid) 100%); }
.gov-card-header-teal { background: linear-gradient(135deg, var(--gov-teal) 0%, var(--gov-teal-mid) 100%); }
.gov-card-header-left { display: flex; align-items: center; gap: 14px; }
.gov-card-icon-box {
    width: 40px; height: 40px; border-radius: 6px;
    background: rgba(255,255,255,.2); border: 1.5px solid rgba(255,255,255,.3);
    color: #fff; font-size: 17px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.gov-icon-teal { background: rgba(255,255,255,.2); }
.gov-card-title { font-size: 15px; font-weight: 700; color: #fff; line-height: 1.2; font-family: var(--font-serif); }
.gov-card-subtitle { font-size: 11.5px; color: rgba(255,255,255,.72); margin-top: 2px; }
.gov-record-pill { background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3); color: #fff; border-radius: 4px; padding: 5px 14px; font-size: 12.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.gov-record-pill-teal { background: rgba(255,255,255,.15); }
.gov-card-body { padding: 24px; }
.gov-card-footer {
    padding: 13px 24px; border-top: 1.5px solid var(--gov-border);
    background: #f7f9fc; display: flex; align-items: center; justify-content: space-between;
}

/* ════════════════════════════════
   SEARCH BARS
════════════════════════════════ */
.gov-search-bar { padding: 14px 24px; background: #f4f6fa; border-bottom: 1.5px solid var(--gov-border); }
.gov-search-bar-teal { border-bottom-color: var(--gov-teal-light); }
.gov-search-bar-inner { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.gov-search-group {
    display: flex; align-items: center;
    background: var(--gov-white);
    border: 1.5px solid var(--gov-border-dark);
    border-radius: var(--radius-sm); overflow: hidden;
    transition: border-color .2s, box-shadow .2s;
    box-shadow: var(--shadow-sm);
}
.gov-search-group:focus-within { border-color: var(--gov-navy); box-shadow: 0 0 0 3px rgba(27,58,107,.1); }
.gov-search-group-fixed { border: 1.5px solid rgba(255,255,255,.4); background: rgba(255,255,255,.12); }
.gov-search-group-fixed:focus-within { border-color: rgba(255,255,255,.7); }
.gov-search-group-fixed .gov-search-icon { color: rgba(255,255,255,.7); }
.gov-search-group-fixed .gov-search-input { background: transparent; color: #fff; }
.gov-search-group-fixed .gov-search-input::placeholder { color: rgba(255,255,255,.5); }
.gov-search-group-fixed .gov-search-btn-fixed { background: rgba(255,255,255,.2); color: #fff; }
.gov-search-group-inline { flex: 1; min-width: 260px; }
.gov-search-icon { padding: 0 12px; color: #8a9ab0; font-size: 14px; }
.gov-search-input { flex: 1; border: none; outline: none; padding: 10px 6px; font-size: 13.5px; background: transparent; color: var(--gov-text); font-family: var(--font-sans); }
.gov-search-btn { border: none; padding: 10px 20px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background .2s; }
.gov-search-btn-primary { background: var(--gov-navy); color: #fff; }
.gov-search-btn-primary:hover { background: var(--gov-navy-dark); }
.gov-search-btn-teal { background: var(--gov-teal); color: #fff; }
.gov-search-btn-teal:hover { background: var(--gov-teal-mid); }
.gov-btn-add {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--gov-navy-light); color: var(--gov-navy);
    border: 1.5px solid var(--gov-navy); border-radius: var(--radius-sm);
    padding: 9px 16px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all .2s;
}
.gov-btn-add:hover { background: var(--gov-navy); color: #fff; text-decoration: none; }
.gov-btn-sm-add {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; background: var(--gov-navy-light); color: var(--gov-navy);
    border: 1.5px solid var(--gov-navy); border-radius: var(--radius-sm);
    padding: 5px 12px; text-decoration: none; transition: all .2s;
}
.gov-btn-sm-add:hover { background: var(--gov-navy); color: #fff; text-decoration: none; }

/* ════════════════════════════════
   TABLE
════════════════════════════════ */
.gov-table-scroll { overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 360px); }
.gov-table { border-collapse: separate; border-spacing: 0; width: 100%; }
.gov-table thead tr th {
    background: #f0f3f8;
    color: var(--gov-navy); font-size: 11.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    padding: 11px 14px; border-bottom: 2px solid var(--gov-navy);
    border-right: 1px solid var(--gov-border);
    white-space: nowrap; position: sticky; top: 0; z-index: 10;
}
.gov-table thead tr th:last-child { border-right: none; }
.gov-tr { transition: background .15s; }
.gov-tr:hover { background: #f4f7fb; }
.gov-table tbody td {
    padding: 10px 14px; border-bottom: 1px solid var(--gov-border);
    border-right: 1px solid #eaecf0;
    font-size: 13px; color: var(--gov-text); vertical-align: middle;
}
.gov-table tbody td:last-child { border-right: none; }
.gov-table tbody tr:last-child td { border-bottom: none; }
.gov-td-muted { color: var(--gov-muted) !important; font-size: 12px !important; }
.gov-td-sm { font-size: 12.5px; }
.gov-mono { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
.gov-dash { color: #b0bec5; }

/* TAGS */
.gov-code-tag { background: var(--gov-navy-light); color: var(--gov-navy-dark); border: 1px solid #c0cde0; border-radius: var(--radius-xs); padding: 2px 8px; font-size: 11.5px; font-weight: 700; font-family: monospace; }
.gov-rxid-tag { background: #edf0fb; color: #2a3e8a; border: 1px solid #c5cce8; border-radius: var(--radius-xs); padding: 2px 8px; font-size: 11.5px; font-weight: 700; font-family: monospace; }
.gov-blood-tag { background: #fdeaea; color: var(--gov-red); border: 1px solid #f0c0c0; border-radius: var(--radius-xs); padding: 2px 8px; font-size: 11.5px; font-weight: 700; }
.gov-gender-tag { display: inline-flex; align-items: center; gap: 3px; border-radius: var(--radius-xs); padding: 2px 8px; font-size: 11.5px; font-weight: 700; }
.gov-gender-m { background: #e8f0fd; color: #1a3a8a; border: 1px solid #c0d0f0; }
.gov-gender-f { background: #fce8f2; color: #8a1a5a; border: 1px solid #f0c0de; }

/* AVATAR */
.gov-name-cell { display: flex; align-items: center; gap: 10px; }
.gov-avatar {
    width: 32px; height: 32px; border-radius: 4px;
    background: linear-gradient(135deg, var(--gov-navy), var(--gov-navy-mid));
    color: #fff; font-size: 13px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.gov-avatar-teal { background: linear-gradient(135deg, var(--gov-teal), var(--gov-teal-mid)); }
.gov-name-primary { font-weight: 600; font-size: 13px; color: var(--gov-text); }
.gov-name-secondary { font-size: 11px; color: var(--gov-muted); margin-top: 1px; }
.gov-date-primary { font-weight: 600; font-size: 12.5px; color: var(--gov-text); }
.gov-date-ago { font-size: 10.5px; color: var(--gov-muted); }

/* ACTIONS */
.gov-btn-select {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1.5px solid var(--gov-navy); border-radius: var(--radius-sm);
    background: var(--gov-navy); color: #fff;
    padding: 5px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.gov-btn-select:hover { background: var(--gov-navy-dark); border-color: var(--gov-navy-dark); }
.gov-action-group { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
.gov-btn-view {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--gov-navy); color: #fff; border: 1.5px solid var(--gov-navy);
    border-radius: var(--radius-sm); padding: 5px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.gov-btn-view:hover { background: var(--gov-navy-dark); }
.gov-btn-history {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--gov-teal); color: #fff; border: 1.5px solid var(--gov-teal);
    border-radius: var(--radius-sm); padding: 5px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.gov-btn-history:hover { background: var(--gov-teal-mid); }

/* EMPTY STATE */
.gov-empty-state { text-align: center; padding: 40px 20px; color: #b0bec5; }
.gov-empty-state i { font-size: 32px; margin-bottom: 10px; display: block; }
.gov-empty-state p, .gov-empty-state span { font-size: 13.5px; margin: 0; }

/* PAGINATION */
.gov-pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; border-top: 1.5px solid var(--gov-border); background: #f7f9fc; flex-wrap: wrap; gap: 8px; }
.pagination { margin-bottom: 0; }
.page-link { border-radius: var(--radius-xs) !important; border-color: var(--gov-border); color: var(--gov-navy); font-size: 13px; }
.page-item.active .page-link { background: var(--gov-navy); border-color: var(--gov-navy); }

/* ════════════════════════════════
   STEP 2 — PATIENT BANNER
════════════════════════════════ */
.gov-patient-banner {
    border-radius: var(--radius-md);
    border: 1.5px solid var(--gov-navy);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}
.gov-patient-banner-stripe {
    height: 5px;
    background: repeating-linear-gradient(90deg, var(--gov-navy) 0px, var(--gov-navy) 20px, var(--gov-teal) 20px, var(--gov-teal) 28px);
}
.gov-patient-banner-inner {
    background: var(--gov-navy);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;
    padding: 18px 24px;
}
.gov-patient-banner-left { display: flex; align-items: center; gap: 16px; }
.gov-banner-avatar {
    width: 50px; height: 50px; border-radius: 6px;
    background: rgba(255,255,255,.18); border: 2px solid rgba(255,255,255,.4);
    color: #fff; font-size: 22px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.gov-banner-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.6); font-weight: 700; margin-bottom: 2px; }
.gov-banner-name { color: #fff; font-size: 17px; font-weight: 700; font-family: var(--font-serif); line-height: 1.2; }
.gov-banner-meta { color: rgba(255,255,255,.72); font-size: 12px; margin-top: 3px; }
.gov-patient-banner-right { display: flex; align-items: center; gap: 12px; }
.gov-banner-badge {
    background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.35);
    color: rgba(255,255,255,.9); border-radius: 4px; padding: 6px 14px; font-size: 12.5px; font-weight: 600;
}
.gov-btn-change {
    background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.4);
    color: #fff; border-radius: var(--radius-sm); padding: 8px 16px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 6px;
}
.gov-btn-change:hover { background: rgba(255,255,255,.28); }

/* ════════════════════════════════
   FORM FIELDS
════════════════════════════════ */
.gov-section-head {
    display: flex; align-items: center; gap: 10px;
    font-size: 13.5px; font-weight: 700; color: var(--gov-navy);
    margin-bottom: 18px; padding-bottom: 10px;
    border-bottom: 1.5px solid var(--gov-border);
}
.gov-section-head-bar { width: 4px; height: 20px; background: var(--gov-navy); border-radius: 2px; flex-shrink: 0; }
.gov-bar-red { background: var(--gov-red); }
.gov-bar-navy { background: var(--gov-navy); }
.gov-section-split { display: flex; align-items: center; justify-content: space-between; border-top: 1.5px solid var(--gov-border); padding-top: 18px; margin-bottom: 14px; }
.gov-divider { border: none; border-top: 1.5px solid var(--gov-border); margin: 22px 0; }
.gov-field { margin-bottom: 16px; }
.gov-label {
    display: block; font-size: 11.5px; font-weight: 700;
    color: var(--gov-muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px;
}
.gov-input {
    width: 100%;
    border: 1.5px solid var(--gov-border);
    border-radius: var(--radius-sm); padding: 9px 12px;
    font-size: 13.5px; color: var(--gov-text);
    background: var(--gov-white); transition: border-color .2s, box-shadow .2s;
    outline: none; font-family: var(--font-sans);
}
.gov-input:focus { border-color: var(--gov-navy); box-shadow: 0 0 0 3px rgba(27,58,107,.1); }
.gov-input:read-only { background: #f4f6fa; color: var(--gov-muted); }
.gov-input-icon-wrap { position: relative; }
.gov-input-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--gov-muted); pointer-events: none; }
.gov-input-padded { padding-left: 30px; }

/* ════════════════════════════════
   VITALS PANEL
════════════════════════════════ */
.gov-vitals-panel {
    border-radius: var(--radius-md);
    border: 2px solid #d0a0a0;
    background: linear-gradient(135deg, #fdf8f8 0%, #fff9f9 100%);
    overflow: hidden; margin-bottom: 4px;
    box-shadow: 0 2px 8px rgba(165,32,32,.06);
}
.gov-vitals-panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px 12px;
    background: linear-gradient(135deg, #fdeaea, #fdf8f8);
    border-bottom: 1.5px solid #d0a0a0;
    flex-wrap: wrap; gap: 10px;
}
.gov-vitals-header-left { display: flex; align-items: center; }
.gov-vitals-icon {
    width: 38px; height: 38px; border-radius: 6px;
    background: var(--gov-red); color: #fff;
    font-size: 16px; display: flex; align-items: center; justify-content: center;
    margin-right: 12px; flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(165,32,32,.25);
}
.gov-vitals-title { font-size: 14px; font-weight: 700; color: #8b1a1a; font-family: var(--font-serif); }
.gov-vitals-sub { font-size: 11px; color: #c06060; margin-top: 1px; }
.gov-vitals-pill {
    border-radius: 4px; padding: 5px 14px; font-size: 12px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 5px; transition: all .3s;
}
.gov-vitals-pill-empty { background: #f0f0f0; color: #999; border: 1.5px solid #ddd; }
.gov-vitals-pill-filled { background: var(--gov-green-light); color: var(--gov-green); border: 1.5px solid #a5d6b0; }
.gov-vitals-pill-warn { background: var(--gov-amber-light); color: var(--gov-amber); border: 1.5px solid #f0c070; }
.gov-vitals-fields { display: flex; align-items: center; gap: 12px; padding: 20px; flex-wrap: wrap; }
.gov-vital-group { display: flex; flex-direction: column; min-width: 120px; }
.gov-vital-label { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; color: var(--gov-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 7px; }
.gov-vital-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.gov-dot-red { background: var(--gov-red); }
.gov-dot-blue { background: var(--gov-navy-mid); }
.gov-dot-green { background: var(--gov-green); }
.gov-vital-input-box { display: flex; align-items: center; background: #fff; border: 2px solid #d0a0a0; border-radius: 6px; overflow: hidden; transition: border-color .2s; }
.gov-vital-input-box:focus-within { border-color: var(--gov-red); box-shadow: 0 0 0 3px rgba(165,32,32,.1); }
.gov-vital-input { width: 85px; border: none; outline: none; padding: 10px 12px; font-size: 18px; font-weight: 700; color: #8b1a1a; background: transparent; font-family: var(--font-sans); }
.gov-vital-input::placeholder { color: #e0a0a0; font-weight: 400; font-size: 14px; }
.gov-vital-unit { padding: 0 10px; font-size: 10.5px; color: #c07070; font-weight: 700; background: #fdf5f5; border-left: 1px solid #e0c0c0; white-space: nowrap; height: 100%; display: flex; align-items: center; }
.gov-vital-slash { width: 30px; height: 2px; background: #d0a0a0; border-radius: 2px; flex-shrink: 0; align-self: flex-end; margin-bottom: 22px; }
.gov-vital-spacer { width: 20px; flex-shrink: 0; }
.gov-vital-live-card {
    background: var(--gov-red); border-radius: 8px; padding: 14px 18px; color: #fff;
    min-width: 138px; box-shadow: 0 4px 14px rgba(165,32,32,.3); margin-left: 12px;
    animation: govVitalPulse 2s ease-in-out infinite;
    flex-direction: column;
}
@keyframes govVitalPulse { 0%,100%{box-shadow:0 4px 14px rgba(165,32,32,.3);}50%{box-shadow:0 6px 22px rgba(165,32,32,.5);} }
.gov-vital-live-bp { font-size: 22px; font-weight: 800; letter-spacing: -.5px; line-height: 1; }
.gov-vital-live-unit { font-size: 10px; opacity: .72; margin-top: 2px; margin-bottom: 8px; }
.gov-vital-live-pulse { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
.gov-pulse-beat-icon { animation: govHeartBeat .85s ease-in-out infinite; }
@keyframes govHeartBeat { 0%,100%{transform:scale(1);}50%{transform:scale(1.3);} }
.gov-vital-live-status { margin-top: 6px; font-size: 10.5px; font-weight: 700; background: rgba(255,255,255,.2); border-radius: 3px; padding: 2px 8px; text-align: center; }
.gov-vitals-note-row { padding: 0 20px 18px; }

/* ════════════════════════════════
   MEDICINE TABLES
════════════════════════════════ */
.gov-med-table-card { border-radius: var(--radius-sm); border: 1.5px solid var(--gov-border); overflow: hidden; box-shadow: var(--shadow-sm); }
.gov-med-card-selected { border-color: #b0bfd8; }
.gov-med-card-header { padding: 10px 16px; background: #f4f7fb; border-bottom: 1.5px solid var(--gov-border); display: flex; align-items: center; justify-content: space-between; }
.gov-med-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 8px; flex-shrink: 0; }
.gov-med-dot-navy { background: var(--gov-navy); }
.gov-med-dot-blue { background: var(--gov-navy-mid); }
.gov-med-title { font-size: 13px; font-weight: 700; color: var(--gov-text); }
.gov-med-count { border-radius: 3px; padding: 2px 8px; font-size: 11.5px; font-weight: 700; margin-left: 8px; }
.gov-med-count-navy { background: var(--gov-navy-light); color: var(--gov-navy-dark); }
.gov-med-count-blue { background: #e8f0fb; color: var(--gov-navy-mid); }
.gov-med-table thead tr th { background: #f0f3f8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--gov-muted); padding: 8px 12px; border-bottom: 1.5px solid var(--gov-border); }
.gov-med-table tbody td { padding: 7px 12px; border-bottom: 1px solid var(--gov-border); font-size: 12.5px; vertical-align: middle; }
.gov-med-table tbody tr:last-child td { border-bottom: none; }
.gov-med-table tbody tr:hover { background: #f4f7fb; }
.gov-med-table .form-control { padding: 4px 8px !important; font-size: 12.5px !important; border-color: var(--gov-border); }
.gov-med-name { font-weight: 600; color: var(--gov-text); font-size: 13px; }
.gov-med-count-badge { background: var(--gov-navy-light); color: var(--gov-navy-dark); border: 1px solid #b0c0d8; border-radius: 4px; padding: 2px 10px; font-size: 12px; font-weight: 700; margin-left: 8px; }
.gov-btn-add-row { display: inline-flex; align-items: center; gap: 5px; background: var(--gov-navy-light); color: var(--gov-navy); border: 1.5px solid var(--gov-navy); border-radius: var(--radius-sm); padding: 6px 14px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all .18s; }
.gov-btn-add-row:hover { background: var(--gov-navy); color: #fff; }
.gov-btn-clear { background: transparent; border: none; color: var(--gov-red); font-size: 12px; font-weight: 600; cursor: pointer; padding: 4px 10px; border-radius: var(--radius-sm); transition: all .18s; display: inline-flex; align-items: center; gap: 4px; }
.gov-btn-clear:hover { background: var(--gov-red-light); }
.gov-avail-filter { display: flex; align-items: center; background: #fff; border: 1.5px solid var(--gov-border); border-radius: var(--radius-sm); overflow: hidden; transition: border-color .2s; }
.gov-avail-filter:focus-within { border-color: var(--gov-navy); }
.gov-avail-input { border: none; outline: none; padding: 6px 8px; font-size: 13px; background: transparent; width: 170px; font-family: var(--font-sans); }
.gov-checkbox { width: 15px; height: 15px; accent-color: var(--gov-navy); cursor: pointer; }
.gov-btn-quick-add { width: 27px; height: 27px; border-radius: 4px; border: 1.5px solid var(--gov-border-dark); font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all .18s; background: var(--gov-navy-light); color: var(--gov-navy); }
.gov-btn-quick-add:hover { background: var(--gov-navy); color: #fff; border-color: var(--gov-navy); }

/* FORM FOOTER */
.gov-form-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 20px; border-top: 1.5px solid var(--gov-border); }
.gov-btn-back-form { background: #fff; border: 1.5px solid var(--gov-border-dark); color: var(--gov-text); border-radius: var(--radius-sm); padding: 10px 22px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 6px; }
.gov-btn-back-form:hover { background: #f0f3f8; }
.gov-btn-save {
    background: var(--gov-navy); color: #fff;
    border: none; border-radius: var(--radius-sm);
    padding: 11px 28px; font-size: 14px; font-weight: 700;
    cursor: pointer; box-shadow: 0 4px 12px rgba(27,58,107,.28);
    transition: all .2s; display: inline-flex; align-items: center; gap: 7px;
    font-family: var(--font-sans);
}
.gov-btn-save:hover { background: var(--gov-navy-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(27,58,107,.35); }

/* ════════════════════════════════
   SUMMARY CARDS (print view)
════════════════════════════════ */
.gov-summary-card { border-radius: var(--radius-md); padding: 16px 18px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-md); height: 100%; border: 1.5px solid transparent; }
.gov-sum-navy { background: var(--gov-navy); border-color: var(--gov-navy-dark); }
.gov-sum-teal { background: var(--gov-teal); }
.gov-sum-slate { background: var(--gov-slate); }
.gov-sum-maroon { background: var(--gov-maroon); }
.gov-sum-icon { width: 40px; height: 40px; border-radius: 6px; background: rgba(255,255,255,.2); color: #fff; font-size: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gov-sum-label { color: rgba(255,255,255,.72); font-size: 10.5px; text-transform: uppercase; letter-spacing: .6px; font-weight: 700; }
.gov-sum-value { color: #fff; font-size: 14px; font-weight: 700; margin-top: 2px; font-family: var(--font-serif); }
.gov-saved-badge { background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3); color: #fff; border-radius: 4px; padding: 5px 14px; font-size: 12.5px; font-weight: 700; display: inline-flex; align-items: center; }

/* ════════════════════════════════
   VITALS SUMMARY BAR
════════════════════════════════ */
.gov-vitals-summary-bar { border-radius: var(--radius-md); background: #fdf8f8; border: 1.5px solid #d0a0a0; padding: 16px 20px; display: flex; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: var(--shadow-sm); }
.gov-vsb-label { font-size: 13px; font-weight: 700; color: #8b1a1a; display: flex; align-items: center; white-space: nowrap; }
.gov-vsb-chips { display: flex; gap: 12px; flex-wrap: wrap; }
.gov-vsb-chip { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: var(--radius-sm); padding: 10px 16px; border: 1.5px solid #d0a0a0; min-width: 130px; }
.gov-vsb-chip-icon { font-size: 18px; flex-shrink: 0; }
.gov-vsb-red .gov-vsb-chip-icon { color: var(--gov-red); }
.gov-vsb-green .gov-vsb-chip-icon { color: var(--gov-green); }
.gov-vsb-slate .gov-vsb-chip-icon { color: var(--gov-navy-mid); }
.gov-vsb-chip-label { font-size: 10px; font-weight: 700; color: var(--gov-muted); text-transform: uppercase; letter-spacing: .4px; }
.gov-vsb-chip-val { font-size: 16px; font-weight: 800; color: var(--gov-text); margin-top: 1px; }
.gov-vsb-red .gov-vsb-chip-val { color: var(--gov-red); }
.gov-vsb-green .gov-vsb-chip-val { color: var(--gov-green); }

/* PRINT ACTION BUTTONS */
.gov-btn-print { display: inline-flex; align-items: center; gap: 5px; border-radius: var(--radius-sm); padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--gov-navy); background: var(--gov-navy); color: #fff; transition: all .2s; }
.gov-btn-print:hover { background: var(--gov-navy-dark); }
.gov-btn-edit { display: inline-flex; align-items: center; gap: 5px; border-radius: var(--radius-sm); padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--gov-amber); background: var(--gov-amber-light); color: var(--gov-amber); transition: all .2s; }
.gov-btn-edit:hover { background: var(--gov-amber); color: #fff; }
.gov-btn-new { display: inline-flex; align-items: center; gap: 5px; border-radius: var(--radius-sm); padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--gov-border-dark); background: #f4f7fa; color: var(--gov-text); transition: all .2s; }
.gov-btn-new:hover { background: #e8ecf2; }

/* ════════════════════════════════
   MODALS
════════════════════════════════ */
.gov-modal-content { border: none; border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.gov-modal-header { background: var(--gov-navy); border: none; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; }
.gov-modal-icon { width: 40px; height: 40px; border-radius: 6px; background: rgba(255,255,255,.2); color: #fff; font-size: 17px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gov-modal-print-btn { background: rgba(255,255,255,.2); border: 1.5px solid rgba(255,255,255,.4); color: #fff; border-radius: var(--radius-sm); padding: 7px 16px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 5px; }
.gov-modal-print-btn:hover { background: rgba(255,255,255,.3); }
.gov-modal-close-btn { background: rgba(255,255,255,.15); border: none; color: rgba(255,255,255,.85); width: 32px; height: 32px; border-radius: 50%; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; }
.gov-modal-close-btn:hover { background: rgba(255,255,255,.28); color: #fff; }
.gov-modal-state { text-align: center; padding: 50px 20px; color: #90A4AE; }
.gov-modal-state p { font-size: 14px; margin: 0; }
.gov-modal-footer { background: #f7f9fc; border-top: 1.5px solid var(--gov-border); padding: 12px 22px; display: flex; align-items: center; justify-content: space-between; }
.gov-modal-summary-bar { display: flex; border-bottom: 1px solid var(--gov-border); }
.gov-msi { flex: 1; padding: 14px 18px; display: flex; align-items: center; gap: 10px; border-right: 1px solid var(--gov-border); }
.gov-msi:last-child { border-right: none; }
.gov-msi-navy { background: linear-gradient(135deg, var(--gov-navy-light), #fff); }
.gov-msi-green { background: linear-gradient(135deg, var(--gov-green-light), #fff); }
.gov-msi-amber { background: linear-gradient(135deg, var(--gov-amber-light), #fff); }
.gov-msi-teal { background: linear-gradient(135deg, var(--gov-teal-light), #fff); }
.gov-msi > i { font-size: 18px; flex-shrink: 0; }
.gov-msi-navy > i { color: var(--gov-navy); }
.gov-msi-green > i { color: var(--gov-green); }
.gov-msi-amber > i { color: var(--gov-amber); }
.gov-msi-teal > i { color: var(--gov-teal); }
.gov-msi-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--gov-muted); }
.gov-msi-val { font-size: 13px; font-weight: 700; color: var(--gov-text); margin-top: 1px; }
.gov-modal-vitals-bar { display: flex; align-items: center; flex-wrap: wrap; gap: 16px; padding: 12px 24px; background: #fdf8f8; border-bottom: 1.5px solid #d0a0a0; }
.gov-mvb-item { display: flex; align-items: center; gap: 6px; font-size: 13px; }
.gov-mvb-red { color: var(--gov-red); }
.gov-mvb-green { color: var(--gov-green); }
.gov-mvb-label { font-weight: 600; }
.gov-mvb-unit { font-size: 11px; color: var(--gov-muted); margin-left: 2px; }
.gov-mvb-sep { width: 1.5px; height: 20px; background: #d0a0a0; flex-shrink: 0; }
.gov-mvb-status { border-radius: 3px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; }

/* ════════════════════════════════
   HISTORY MODAL
════════════════════════════════ */
.history-patient-bar { background: var(--gov-navy-light); padding: 14px 24px; border-bottom: 1px solid #c0cedf; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.hpb-avatar { width: 44px; height: 44px; border-radius: 6px; background: var(--gov-navy); color: #fff; font-size: 18px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.hpb-name { font-size: 16px; font-weight: 700; color: var(--gov-navy); font-family: var(--font-serif); }
.hpb-meta { font-size: 12px; color: var(--gov-muted); margin-top: 2px; }
.gov-history-tabs { background: #fff; border-bottom: 2px solid var(--gov-border); padding: 0 24px; display: flex; align-items: center; gap: 0; }
.gov-htab { background: transparent; border: none; border-bottom: 3px solid transparent; padding: 12px 20px; font-size: 13px; font-weight: 600; color: var(--gov-muted); cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 6px; margin-bottom: -2px; }
.gov-htab:hover { color: var(--gov-navy); background: var(--gov-navy-light); }
.gov-htab-active { color: var(--gov-navy); border-bottom-color: var(--gov-navy) !important; }
.gov-htab-count { background: var(--gov-navy-light); color: var(--gov-navy-dark); border-radius: 3px; padding: 1px 7px; font-size: 11px; font-weight: 700; }
.gov-htab-count-teal { background: var(--gov-teal-light); color: var(--gov-teal); }

/* ════════════════════════════════
   VISIT TIMELINE
════════════════════════════════ */
.visit-timeline { position: relative; }
.visit-timeline::before { content: ''; position: absolute; left: 22px; top: 0; bottom: 0; width: 2px; background: var(--gov-border); z-index: 0; }
.visit-card { position: relative; margin-bottom: 20px; padding-left: 56px; }
.visit-dot { position: absolute; left: 14px; top: 18px; width: 18px; height: 18px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px var(--gov-border); z-index: 1; display: flex; align-items: center; justify-content: center; }
.visit-dot-rx { background: var(--gov-navy); box-shadow: 0 0 0 2px var(--gov-navy-light); }
.visit-dot-latest { background: var(--gov-green); box-shadow: 0 0 0 2px var(--gov-green-light); }
.visit-dot-vitals { background: var(--gov-teal); box-shadow: 0 0 0 2px var(--gov-teal-light); }
.visit-dot span { color: #fff; font-size: 8px; font-weight: 700; }
.visit-box { background: #fff; border-radius: var(--radius-md); border: 1.5px solid var(--gov-border); box-shadow: var(--shadow-sm); overflow: hidden; }
.visit-box-latest { border-color: #b0c8e8; box-shadow: 0 4px 14px rgba(27,58,107,.1); }
.visit-box-vitals { border-color: #b0d0cc; }
.visit-header { padding: 12px 18px; border-bottom: 1px solid var(--gov-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.visit-header-rx { background: var(--gov-navy-light); }
.visit-header-vitals { background: var(--gov-teal-light); }
.visit-number { border-radius: 3px; padding: 2px 10px; font-size: 11.5px; font-weight: 700; color: #fff; }
.visit-number-rx { background: var(--gov-navy); }
.visit-number-latest { background: var(--gov-green); }
.visit-number-vitals { background: var(--gov-teal); }
.visit-date { font-size: 13px; font-weight: 700; color: var(--gov-navy); }
.visit-date-vitals { color: var(--gov-teal) !important; }
.visit-doctor { font-size: 12px; color: var(--gov-muted); margin-top: 1px; }
.visit-badge-latest { background: var(--gov-green-light); color: var(--gov-green); border: 1.5px solid #a0d0b0; border-radius: 4px; padding: 2px 9px; font-size: 11px; font-weight: 700; }
.visit-badge-vitals { background: var(--gov-teal-light); color: var(--gov-teal); border: 1.5px solid #80c0bc; border-radius: 4px; padding: 2px 9px; font-size: 11px; font-weight: 700; }
.visit-body { padding: 14px 18px; }
.visit-med-table { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'Hind Siliguri', Arial, sans-serif; }
.visit-med-table th { background: #f0f3f8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--gov-muted); padding: 6px 10px; border: 1px solid #d8dce4; text-align: center; white-space: nowrap; }
.visit-med-table th:first-child { text-align: left; }
.visit-med-table td { padding: 6px 10px; border: 1px solid #e8eaee; vertical-align: middle; text-align: center; }
.visit-med-table td:first-child { text-align: left; font-weight: 500; }
.visit-med-table tbody tr:nth-child(even) { background: #fafbfc; }
.visit-med-table tbody tr:hover { background: var(--gov-navy-light); }
.visit-notes { font-size: 12px; color: var(--gov-muted); margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--gov-border); font-style: italic; }
.visit-print-btn { background: var(--gov-navy-light); color: var(--gov-navy); border: 1.5px solid #b0c0d8; border-radius: var(--radius-sm); padding: 4px 12px; font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 4px; }
.visit-print-btn:hover { background: var(--gov-navy); color: #fff; }
.no-meds-note { color: #b0bec5; font-size: 12px; font-style: italic; padding: 10px 0; }

/* VITALS INLINE IN TIMELINE */
.visit-vitals-inline { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 0 4px; border-top: 1px dashed #d0a0a0; margin-top: 10px; }
.vvi-chip { display: flex; align-items: center; gap: 5px; background: #fdf5f5; border: 1px solid #e0b0b0; border-radius: 5px; padding: 5px 10px; font-size: 12px; }
.vvi-chip-green { background: #f0f8f0; border-color: #a0d0a8; }
.vvi-label { font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .3px; }
.vvi-val { font-size: 14px; font-weight: 800; color: var(--gov-red); }
.vvi-val-green { color: var(--gov-green) !important; }
.vvi-unit { font-size: 10px; color: #aaa; }

/* VITALS TABLE */
.vitals-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px; margin-bottom: 8px; }
.vital-chip { background: #f8f9fa; border: 1px solid #e4e8ec; border-radius: 6px; padding: 8px 10px; text-align: center; }
.vital-chip-label { font-size: 10px; color: var(--gov-muted); text-transform: uppercase; letter-spacing: .4px; font-weight: 700; display: block; margin-bottom: 3px; }
.vital-chip-val { font-size: 14px; font-weight: 700; color: var(--gov-text); }
.vital-chip-val.text-danger { color: var(--gov-red) !important; }
.vital-chip-val.text-info { color: #0277BD !important; }
.vital-chip-val.text-success { color: var(--gov-green) !important; }
.vital-chip-unit { font-size: 10px; color: var(--gov-muted); font-weight: 400; }
.bmi-chip { background: var(--gov-green-light); border-color: #a0d0a8; }
.bmi-chip .vital-chip-val { color: var(--gov-green); }
.vitals-table-card { background: #fff; border-radius: var(--radius-md); border: 1.5px solid #b0d0cc; overflow: hidden; box-shadow: var(--shadow-sm); }
.vitals-table-card-header { background: var(--gov-teal-light); padding: 12px 18px; border-bottom: 1px solid #b0d0cc; display: flex; align-items: center; justify-content: space-between; }
.vitals-full-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.vitals-full-table thead th { background: #e0f2f0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--gov-teal); padding: 8px 12px; border-bottom: 1.5px solid #b0d0cc; white-space: nowrap; text-align: center; }
.vitals-full-table thead th:first-child { text-align: left; }
.vitals-full-table tbody td { padding: 8px 12px; border-bottom: 1px solid var(--gov-teal-light); text-align: center; vertical-align: middle; }
.vitals-full-table tbody td:first-child { text-align: left; font-weight: 600; }
.vitals-full-table tbody tr:nth-child(even) { background: #fafffe; }
.vitals-full-table tbody tr:hover { background: var(--gov-teal-light); }
.vbadge { border-radius: 3px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.vbadge-normal { background: var(--gov-green-light); color: var(--gov-green); }
.vbadge-high { background: var(--gov-red-light); color: var(--gov-red); }
.vbadge-low { background: var(--gov-amber-light); color: var(--gov-amber); }

/* ════════════════════════════════
   PRINT PRESCRIPTION LAYOUT
   (Unchanged from original logic)
════════════════════════════════ */
.round-vitals-row { display:flex;align-items:center;flex-wrap:wrap;gap:8px 20px;padding:7px 16px;background:linear-gradient(90deg,#fff3f3,#fff9f9)!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;border-bottom:1.5px solid #ffd0d0;border-top:1px solid #ffcdd2; }
.round-vital-item { display:flex;align-items:center;gap:5px; }
.round-vital-label { font-weight:700;font-size:12px;color:#555; }
.round-vital-value { font-size:14px;font-weight:800; }
.round-vital-bp { color:#a52020!important; }
.round-vital-pulse { color:#1a5c2e!important; }
.round-vital-unit { font-size:11px;color:#777; }
.round-vital-divider { width:1.5px;height:18px;background:#ffcdd2;flex-shrink:0; }
.round-vital-status-badge { border-radius:3px;padding:2px 10px;font-size:11px;font-weight:700; }
.round-vital-note { font-size:11px;color:#888;font-style:italic;margin-left:auto; }
#prescription-print-area { padding:0;background:#fff; }
.round-wrapper { width:100%;max-width:780px;margin:0 auto;background:#fff;border:1px solid #ccc;font-family:'Hind Siliguri',Arial,sans-serif;font-size:12px; }
.round-header { display:flex;justify-content:space-between;align-items:flex-start;background:linear-gradient(135deg,#e8edf5 0%,#c8d4e8 100%)!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;border-bottom:2px solid #1b3a6b;padding:12px 16px 10px;gap:10px; }
.round-header-left { flex:1; }
.round-header-right { text-align:right;border-left:2px solid #1b3a6b;padding-left:12px;flex:1; }
.round-logo-row { display:flex;align-items:center;gap:10px; }
.round-cp-logo { width:46px;height:46px;border-radius:50%;border:2px solid #a52020;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;flex-shrink:0;background:#fff!important;-webkit-print-color-adjust:exact; }
.round-cp-c { color:#a52020; } .round-cp-p { color:#1b3a6b; }
.round-clinic-bn { font-size:22px;font-weight:700;color:#1b2c48;line-height:1.1; }
.round-clinic-address { font-size:11px;color:#444;margin-top:2px; }
.round-clinic-phones { font-size:10px;color:#555; }
.round-doctor-title { font-size:14px;font-weight:700;color:#a52020; }
.round-doctor-deg { font-size:10.5px;color:#1b2c48; }
.round-doctor-college { font-size:10px;color:#a52020;margin-top:2px; }
.round-nad-row { display:flex;flex-wrap:wrap;background:#ecf0f8!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;padding:6px 16px;border-bottom:1px solid #b8c8de;gap:6px 10px; }
.round-nad-field { display:flex;align-items:center;gap:6px;flex:1;min-width:130px; }
.round-nad-label { font-weight:700;font-size:12px;white-space:nowrap; }
.round-nad-value { border-bottom:1px dotted #999;flex:1;padding:0 4px;font-size:12px;min-width:60px; }
.round-body { padding:10px 16px;min-height:280px; }
.round-rx-symbol { font-size:28px;font-weight:900;font-style:italic;color:#1b2c48;margin-bottom:2px; }
.round-section-label { text-align:center;font-weight:700;font-size:13px;text-decoration:underline;margin:4px 0 3px;color:#1b3a6b; }
.round-time-right { text-align:right;font-size:13px;font-style:italic;margin-bottom:6px; }
.round-rx-notes { font-size:11px;color:#444;white-space:pre-wrap;margin-top:8px; }
.round-footer { display:flex;justify-content:space-between;border-top:1px solid #ccc;padding:6px 16px;font-size:11px;background:#ecf0f8!important;-webkit-print-color-adjust:exact;color:#555; }
.round-med-table-wrap { overflow-x:auto;margin-top:4px; }
.round-med-table { width:100%;border-collapse:collapse;font-family:'Hind Siliguri',Arial,sans-serif;font-size:11.5px; }
.round-med-table th,.round-med-table td { border:1px solid #888;padding:4px 5px;text-align:center;vertical-align:middle; }
.round-th-name { text-align:left!important;width:38%;background:#d4dcee!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;font-size:12px;font-weight:700;vertical-align:middle!important; }
.round-th-group { background:#e0e4ec!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;font-weight:700;font-size:11px; }
.round-th-sub { background:#f0f2f6!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;font-size:10.5px;font-weight:700;min-width:32px; }
.round-med-table tbody tr td { font-size:11.5px; }
.round-med-table tbody tr td:first-child { text-align:left;padding-left:6px;font-weight:500; }
.round-med-table tbody tr.empty-row td { height:24px; }
.round-med-table tbody tr:nth-child(even) td { background:#fafbfc!important;-webkit-print-color-adjust:exact; }

/* ════════════════════════════════
   PRINT MEDIA
════════════════════════════════ */
#print-overlay, #history-print-overlay { display:none;position:fixed;top:0;left:0;width:100%;min-height:100%;background:#fff;z-index:9999999;padding:8mm 10mm;box-sizing:border-box; }
@media print {
    body * { visibility:hidden; }
    #print-overlay, #history-print-overlay { visibility:visible!important;position:fixed!important;top:0!important;left:0!important;width:100%!important;height:100%!important;background:#fff!important;padding:10mm!important;box-sizing:border-box!important;z-index:9999999!important; }
    #print-overlay *, #history-print-overlay * { visibility:visible!important; }
    .round-vitals-row { display:flex!important;background:linear-gradient(90deg,#fff3f3,#fff9f9)!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important; }
    @page { size:A4 portrait;margin:0; }
}
</style>
@stop

@section('js')
<script>
/* ══════════════════════════════════════
   ALL JAVASCRIPT LOGIC — UNCHANGED
══════════════════════════════════════ */
var CSRF_TOKEN        = '{{ csrf_token() }}';
var ROUND_STORE_URLS  = ['{{ url("/nursing/roundprescription/store") }}','{{ url("/nursing/RoundPrescription/store") }}'];
var ROUND_DETAIL_URL  = '/nursing/roundprescription/detail/';
var ROUND_HISTORY_URL = '/nursing/roundprescription/patient-history/';
var PRECON_HISTORY_URL= '/preconassessment/history/';

var selectedMeds = [];
var _currentHistoryTab = 'combined';

/* ─── HELPERS ─── */
function todayISO(){return new Date().toISOString().split('T')[0];}
function fmtDateBD(iso){if(!iso)return'—';var p=String(iso).slice(0,10).split('-');return p[2]+'/'+p[1]+'/'+p[0].slice(2);}
function fmtTime(t){if(!t)return'—';var p=String(t).split(':');var hr=parseInt(p[0]);if(isNaN(hr))return t;return(hr%12||12)+':'+p[1]+(hr>=12?' pm':' am');}
function gVal(id){var el=document.getElementById(id);return el?el.value.trim():'';}
function setText(id,v){var el=document.getElementById(id);if(el)el.textContent=(v!==null&&v!==undefined&&String(v).trim()!=='')?v:'—';}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
function showAlert(type,msg){var el=document.getElementById('save-alert');el.className='alert alert-'+type+' gov-alert';el.innerHTML=msg;el.classList.remove('d-none');window.scrollTo({top:0,behavior:'smooth'});setTimeout(function(){el.classList.add('d-none');},6000);}
function showToast(msg,type){var bg=type==='success'?'#1b3a6b':(type==='info'?'#0d6e6e':'#a52020');var t=document.createElement('div');t.style.cssText='position:fixed;bottom:20px;right:20px;z-index:9999;background:'+bg+';color:#fff;padding:10px 18px;border-radius:6px;font-size:13px;box-shadow:0 4px 14px rgba(0,0,0,.2);max-width:320px;';t.innerHTML='<i class="fas fa-check-circle mr-2"></i>'+msg;document.body.appendChild(t);setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove();},300);},2500);}

/* ─── PRINT HELPERS ─── */
function _doPrint(sourceId,overlayId){
    var src=document.getElementById(sourceId);var ovl=document.getElementById(overlayId||'print-overlay');
    if(!src||!ovl){window.print();return;}
    ovl.innerHTML='';ovl.appendChild(src.cloneNode(true));ovl.style.display='block';
    setTimeout(function(){window.print();setTimeout(function(){ovl.style.display='none';ovl.innerHTML='';},1000);},300);
}
function printRx(){_doPrint('prescription-print-area','print-overlay');}
function printModal(){_doPrint('modal-prescription-print-area','print-overlay');}
function printHistory(){
    var src=document.getElementById('history-panel-combined');
    var ovl=document.getElementById('history-print-overlay');
    if(!src||!ovl){window.print();return;}
    ovl.innerHTML='<div style="font-family:\'Hind Siliguri\',Arial,sans-serif;">'+
        '<div style="text-align:center;border-bottom:2px solid #1b3a6b;padding-bottom:8px;margin-bottom:16px;">'+
        '<strong style="font-size:18px;color:#1b3a6b;">প্রফেসর ক্লিনিক — Patient History</strong><br>'+
        '<span style="font-size:13px;color:#555;">'+document.getElementById('history-modal-sub').textContent+'</span></div>'+
        src.innerHTML+'</div>';
    ovl.style.display='block';
    setTimeout(function(){window.print();setTimeout(function(){ovl.style.display='none';ovl.innerHTML='';},1000);},300);
}

/* ══ BP / VITALS LOGIC ══ */
function getBpStatus(sys, dia){
    if(!sys||!dia) return {label:'', cls:'', color:''};
    var s=parseInt(sys), d=parseInt(dia);
    if(isNaN(s)||isNaN(d)) return {label:'', cls:'', color:''};
    if(s<90||d<60)  return {label:'Low BP', cls:'vbadge-low', color:'#7a4800', bg:'#fff4e0'};
    if(s<=120&&d<=80) return {label:'Normal', cls:'vbadge-normal', color:'#1a5c2e', bg:'#e7f6ec'};
    if(s<=129&&d<80)  return {label:'Elevated', cls:'vbadge-low', color:'#7a4800', bg:'#fff4e0'};
    if(s<=139||d<=89) return {label:'Stage 1 HTN', cls:'vbadge-high', color:'#a52020', bg:'#fdeaea'};
    return {label:'Stage 2 HTN', cls:'vbadge-high', color:'#8b1a1a', bg:'#fbd0d0'};
}
function onVitalChange(){
    var sys=gVal('f-bp-systolic'), dia=gVal('f-bp-diastolic'), pulse=gVal('f-pulse');
    var hasBP=(sys!==''&&dia!==''), hasPulse=pulse!=='';
    var liveCard=document.getElementById('vital-live-card');
    var statusPill=document.getElementById('vitals-status-pill');
    if(hasBP||hasPulse){
        liveCard.style.display='flex';liveCard.style.flexDirection='column';
        document.getElementById('vital-live-bp').textContent=hasBP?(sys+'/'+dia):'—/—';
        document.getElementById('vital-live-pulse-val').textContent=hasPulse?pulse:'—';
        if(hasBP){
            var st=getBpStatus(sys,dia);
            document.getElementById('vital-live-status').textContent=st.label;
            statusPill.className='gov-vitals-pill '+(st.label==='Normal'?'gov-vitals-pill-filled':'gov-vitals-pill-warn');
            statusPill.innerHTML='<i class="fas fa-circle" style="font-size:6px;"></i> '+st.label;
        } else {
            statusPill.className='gov-vitals-pill gov-vitals-pill-filled';
            statusPill.innerHTML='<i class="fas fa-circle" style="font-size:6px;"></i> Pulse Recorded';
        }
    } else {
        liveCard.style.display='none';
        statusPill.className='gov-vitals-pill gov-vitals-pill-empty';
        statusPill.innerHTML='<i class="fas fa-circle" style="font-size:6px;"></i> Not Recorded';
    }
}

/* ─── FIXED SEARCH BAR ─── */
(function initFixedBar(){
    var bar=document.getElementById('fixed-search-bar');
    var inlineBar=document.getElementById('inline-search-bar');
    var fixedInput=document.getElementById('patientSearchFixed');
    var inlineInput=document.getElementById('patientSearch');
    if(!bar||!inlineBar)return;
    bar.style.display='';
    function getSbW(){var sb=document.querySelector('.main-sidebar');if(!sb)return 0;var r=sb.getBoundingClientRect();return r.width>10?r.right:0;}
    function updatePos(){bar.style.left=getSbW()+'px';bar.style.right='0';bar.style.width='auto';}
    function onScroll(){
        if(document.getElementById('panel-step1').style.display==='none'){bar.classList.remove('visible');return;}
        var rect=inlineBar.getBoundingClientRect();
        if(rect.bottom<=0){updatePos();bar.classList.add('visible');}else{bar.classList.remove('visible');}
    }
    if(fixedInput&&inlineInput){
        fixedInput.addEventListener('input',function(){inlineInput.value=this.value;filterTable();});
        inlineInput.addEventListener('input',function(){fixedInput.value=this.value;});
    }
    window.addEventListener('scroll',onScroll,{passive:true});
    window.addEventListener('resize',function(){updatePos();onScroll();});
    document.addEventListener('DOMContentLoaded',function(){updatePos();onScroll();});
    document.querySelectorAll('[data-widget="pushmenu"]').forEach(function(btn){btn.addEventListener('click',function(){setTimeout(updatePos,320);});});
})();

/* ─── MEDICINE TABLE ─── */
function refreshSelTable(){
    var tbody=document.getElementById('sel-med-tbody');
    document.getElementById('sel-med-badge').textContent=selectedMeds.length;
    document.getElementById('sel-med-count-badge').textContent=selectedMeds.length;
    if(!selectedMeds.length){tbody.innerHTML='<tr id="empty-sel-row"><td colspan="9"><div class="gov-empty-state"><i class="fas fa-pills"></i><span>No medicines selected. Click Add Blank Row to begin.</span></div></td></tr>';return;}
    tbody.innerHTML=selectedMeds.map(function(m,i){
        return '<tr>'+
        '<td>'+(i+1)+'</td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.medicine_name)+'" onchange="selectedMeds['+i+'].medicine_name=this.value"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.dose)+'" onchange="selectedMeds['+i+'].dose=this.value"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.route)+'" onchange="selectedMeds['+i+'].route=this.value"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.frequency)+'" onchange="selectedMeds['+i+'].frequency=this.value" placeholder="1+0+1"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.duration)+'" onchange="selectedMeds['+i+'].duration=this.value" placeholder="7 days"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.timing)+'" onchange="selectedMeds['+i+'].timing=this.value" placeholder="before/after"></td>'+
        '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.remarks)+'" onchange="selectedMeds['+i+'].remarks=this.value" placeholder="Optional"></td>'+
        '<td class="text-center"><button type="button" class="gov-btn-quick-add" style="background:#fdeaea;color:#a52020;border-color:#d0a0a0;" onclick="removeMed('+i+')"><i class="fas fa-times"></i></button></td>'+
        '</tr>';
    }).join('');
}
function addMedToList(n,d,r,f,dur,t,note){if(!n||!n.trim())return;if(selectedMeds.find(function(m){return m.medicine_name.toLowerCase()===n.toLowerCase();}))return;selectedMeds.push({medicine_name:n,dose:d||'',route:r||'',frequency:f||'',duration:dur||'',timing:t||'',remarks:note||''});refreshSelTable();}
function addBlankRow(){selectedMeds.push({medicine_name:'',dose:'',route:'',frequency:'',duration:'',timing:'',remarks:''});refreshSelTable();}
function removeMed(idx){var name=selectedMeds[idx]?selectedMeds[idx].medicine_name:'';selectedMeds.splice(idx,1);document.querySelectorAll('.avail-med-cb').forEach(function(cb){if((cb.dataset.name||'')===name)cb.checked=false;});refreshSelTable();}
function clearAllMeds(){if(!selectedMeds.length)return;if(!confirm('সব ওষুধ মুছে ফেলতে চান?'))return;selectedMeds=[];document.querySelectorAll('.avail-med-cb').forEach(function(cb){cb.checked=false;});refreshSelTable();}
function onAvailMedChange(cb){if(cb.checked){addMedToList(cb.dataset.name,cb.dataset.dose,cb.dataset.route,cb.dataset.frequency,cb.dataset.duration,cb.dataset.timing,cb.dataset.note);}else{selectedMeds=selectedMeds.filter(function(m){return m.medicine_name!==cb.dataset.name;});refreshSelTable();}}
function quickAddMed(btn){var cb=btn.closest('tr').querySelector('.avail-med-cb');cb.checked=true;onAvailMedChange(cb);}
function filterCheckboxList(q){var lower=q.toLowerCase().trim();document.querySelectorAll('.avail-med-row').forEach(function(r){r.style.display=(!lower||(r.dataset.name||'').includes(lower))?'':'none';});}

/* ─── DOCTOR HEADER ─── */
function updateDoctorHeader(){
    var sel=document.getElementById('f-doctor');if(!sel||!sel.options.length)return;
    var d=sel.options[sel.selectedIndex].dataset;
    setText('rx-doctor-name',d.docname||'');setText('rx-doctor-speciality',d.speciality||'');
    setText('rx-doctor-regno',d.doctorno?'Reg No: '+d.doctorno:'');
    setText('rx-doctor-posting',d.posting||'');setText('rx-doctor-contact',d.contact?'Mobile: '+d.contact:'');
    setText('ib-doctor',d.docname||'—');
}

/* ─── SELECT PATIENT ─── */
function selectPatient(btn){
    var d=btn.dataset;
    document.getElementById('f-patient-id').value=d.id;document.getElementById('f-patient-code').value=d.code;
    document.getElementById('f-patient-name').value=d.name;document.getElementById('f-patient-age').value=d.age;
    document.getElementById('f-date').value=todayISO();document.getElementById('f-round-time').value='';
    document.getElementById('f-bp-systolic').value='';document.getElementById('f-bp-diastolic').value='';
    document.getElementById('f-pulse').value='';document.getElementById('f-vitals-note').value='';
    document.getElementById('vital-live-card').style.display='none';
    document.getElementById('vitals-status-pill').className='gov-vitals-pill gov-vitals-pill-empty';
    document.getElementById('vitals-status-pill').innerHTML='<i class="fas fa-circle" style="font-size:6px;"></i> Not Recorded';
    document.getElementById('spb-avatar').textContent=(d.name||'P').charAt(0).toUpperCase();
    document.getElementById('spb-name').textContent=d.name;
    document.getElementById('spb-meta').textContent=[d.code,d.age,d.mobile,d.blood,d.upozila].filter(Boolean).join(' · ');
    document.getElementById('step1-circle').className='gov-step-num';
    document.getElementById('step1-circle').innerHTML='<i class="fas fa-check" style="font-size:11px;"></i>';
    document.getElementById('step-connector').classList.add('done');
    document.getElementById('step2-circle').className='gov-step-num';
    document.getElementById('step2-label').className='gov-step-label';
    document.getElementById('step2-sublabel').className='gov-step-sub';
    document.getElementById('breadcrumb-current').textContent='Round Prescription';
    document.getElementById('panel-step1').style.display='none';
    document.getElementById('panel-step2').style.display='block';
    document.getElementById('rx-view').style.display='none';
    document.getElementById('rx-form-card').style.display='block';
    document.getElementById('fixed-search-bar').classList.remove('visible');
    selectedMeds=[];document.querySelectorAll('.avail-med-cb').forEach(function(cb){cb.checked=false;});
    refreshSelTable();updateDoctorHeader();window.scrollTo({top:0,behavior:'smooth'});
}

/* ─── SAVE ─── */
function saveAndGenerateRx(){
    var patientId=gVal('f-patient-id');if(!patientId){showAlert('warning','Please select a patient first.');return;}
    var medsToSave=selectedMeds.filter(function(m){return m.medicine_name.trim()!=='';});
    var doctorSel=document.getElementById('f-doctor');
    var doctorName=doctorSel&&doctorSel.options.length?doctorSel.options[doctorSel.selectedIndex].dataset.docname||'':'';
    var bpSys=gVal('f-bp-systolic'), bpDia=gVal('f-bp-diastolic'), pulse=gVal('f-pulse'), vitalsNote=gVal('f-vitals-note');
    var bpString=(bpSys&&bpDia)?(bpSys+'/'+bpDia):'';
    var payload={
        patient_id:patientId,patient_name:gVal('f-patient-name'),patient_age:gVal('f-patient-age'),
        patient_code:gVal('f-patient-code'),doctor_name:doctorName,prescription_date:gVal('f-date'),
        round_time:gVal('f-round-time'),notes:gVal('f-notes'),medicines:medsToSave,
        bp_systolic:bpSys||null,bp_diastolic:bpDia||null,bp:bpString||null,pulse:pulse||null,vitals_note:vitalsNote||null
    };
    var btn=document.getElementById('btn-save');btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    function trySave(idx){
        if(idx>=ROUND_STORE_URLS.length){btn.disabled=false;btn.innerHTML='<i class="fas fa-save mr-1"></i> Save & Generate Prescription';generateRx();return;}
        fetch(ROUND_STORE_URLS[idx],{method:'POST',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(payload)})
        .then(function(r){if(r.status===404){trySave(idx+1);return null;}return r.json();})
        .then(function(data){btn.disabled=false;btn.innerHTML='<i class="fas fa-save mr-1"></i> Save & Generate Prescription';if(!data)return;generateRx();if(data.success)showToast('Saved! ID: #'+data.prescription_id,'success');})
        .catch(function(){trySave(idx+1);});
    }
    trySave(0);
}

/* ─── GENERATE PRINT VIEW ─── */
function generateRx(){
    var pName=gVal('f-patient-name')||'—';var pAge=gVal('f-patient-age')||'—';var pDate=fmtDateBD(gVal('f-date'));var pCode=gVal('f-patient-code')||'—';
    setText('ib-name',pName);setText('ib-age',pAge);setText('ib-date',pDate);
    setText('rx-name',pName);setText('rx-age',pAge);setText('rx-date',pDate);setText('rx-code',pCode);setText('rx-badge-name',pName);setText('rx-notes',gVal('f-notes')||'');
    var rt=fmtTime(gVal('f-round-time'));var el=document.getElementById('rx-round-time-display');if(el)el.textContent=(rt!=='—')?'Round Time: '+rt:'';
    updateDoctorHeader();renderRxMedicines();
    var bpSys=gVal('f-bp-systolic'), bpDia=gVal('f-bp-diastolic'), pulse=gVal('f-pulse'), vitNote=gVal('f-vitals-note');
    var bpStr=(bpSys&&bpDia)?(bpSys+'/'+bpDia):'';
    var hasBP=bpStr!=='', hasPulse=pulse!=='';
    var sumBar=document.getElementById('rx-vitals-summary-bar');
    if(hasBP||hasPulse){
        sumBar.style.display='flex';
        document.getElementById('rvsb-bp').textContent=hasBP?bpStr+' mmHg':'—';
        document.getElementById('rvsb-pulse').textContent=hasPulse?pulse+' bpm':'—';
        if(hasBP){var st=getBpStatus(bpSys,bpDia);document.getElementById('rvsb-bp-status').textContent=st.label||'—';var stChip=document.getElementById('rvsb-status-chip');if(st.label){stChip.style.display='flex';}else{stChip.style.display='none';}}
    } else { sumBar.style.display='none'; }
    var vitalsRow=document.getElementById('rx-vitals-print-row');
    if(hasBP||hasPulse){
        vitalsRow.style.display='flex';
        var bpEl=document.getElementById('rx-print-bp');var pulseEl=document.getElementById('rx-print-pulse');
        var statusEl=document.getElementById('rx-print-bp-status');var noteEl=document.getElementById('rx-print-vitals-note');
        if(bpEl) bpEl.textContent=hasBP?bpStr:'—';if(pulseEl) pulseEl.textContent=hasPulse?pulse:'—';
        if(statusEl){if(hasBP){var st2=getBpStatus(bpSys,bpDia);statusEl.textContent=st2.label;statusEl.style.background=st2.bg||'transparent';statusEl.style.color=st2.color||'inherit';}else{statusEl.textContent='';}}
        if(noteEl) noteEl.textContent=vitNote?'('+vitNote+')':'';
    } else { vitalsRow.style.display='none'; }
    setText('gen-time',new Date().toLocaleString('en-BD',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}));
    document.getElementById('step2-circle').className='gov-step-num';
    document.getElementById('step2-circle').innerHTML='<i class="fas fa-check" style="font-size:11px;"></i>';
    document.getElementById('rx-form-card').style.display='none';document.getElementById('rx-view').style.display='block';
    window.scrollTo({top:0,behavior:'smooth'});
}

/* ─── MEDICINE ROW PARSERS ─── */
function parseMedRow(m){
    var morning='',noon='',night='';var freq=(m.frequency||'').trim();
    var dp=freq.match(/^([^+]+)\+([^+]+)\+([^+]+)/);
    if(dp){morning=dp[1].trim();noon=dp[2].trim();night=dp[3].trim();}else if(freq){morning=freq;}
    var timing=(m.timing||'').toLowerCase();var before='',after='';
    if(timing.includes('before')||timing.includes('আগে')){before='✓';}else if(timing.includes('after')||timing.includes('পরে')){after='✓';}else if(timing){after='✓';}
    var name=(m.route?m.route+' ':'')+m.medicine_name;
    var duration=(m.duration||'').trim();
    var instruction=(m.instruction||m.note||'').trim();
    return{name:name,morning:morning,noon:noon,night:night,before:before,after:after,duration:duration,instruction:instruction};
}
function renderRxMedicines(){
    var tbody=document.getElementById('rx-med-print-tbody');if(!tbody)return;tbody.innerHTML='';
    var meds=selectedMeds.filter(function(m){return m.medicine_name.trim();});
    meds.forEach(function(m){var r=parseMedRow(m);var tr=document.createElement('tr');tr.innerHTML='<td style="text-align:left;padding-left:6px;">• '+esc(r.name)+'</td><td>'+esc(r.morning)+'</td><td>'+esc(r.noon)+'</td><td>'+esc(r.night)+'</td><td>'+esc(r.before)+'</td><td>'+esc(r.after)+'</td><td>'+esc(r.duration)+'</td><td style="text-align:left;padding-left:6px;">'+esc(r.instruction)+'</td>';tbody.appendChild(tr);});
    var needed=Math.max(0,8-meds.length);for(var i=0;i<needed;i++){var tr=document.className='empty-row';var tr=document.createElement('tr');tr.className='empty-row';tr.innerHTML='<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';tbody.appendChild(tr);}
}
function buildMedTableHTML(meds){
    if(!meds||!meds.length)return'<p class="no-meds-note">No medicines recorded for this visit.</p>';
    var rows=meds.filter(function(m){return m&&(m.medicine_name||'').trim();}).map(function(m){var r=parseMedRow(m);return'<tr><td style="text-align:left;padding-left:6px;">• '+esc(r.name)+'</td><td>'+esc(r.morning)+'</td><td>'+esc(r.noon)+'</td><td>'+esc(r.night)+'</td><td>'+esc(r.before)+'</td><td>'+esc(r.after)+'</td><td>'+esc(r.duration)+'</td><td style="text-align:left;padding-left:6px;">'+esc(r.instruction)+'</td></tr>';}).join('');
    if(!rows)return'<p class="no-meds-note">No medicines recorded.</p>';
    return'<table class="visit-med-table"><thead><tr><th rowspan="2" style="text-align:left;min-width:160px;">ঔষধের নাম</th><th colspan="3">কখন খাবেন?</th><th colspan="2">আহারের</th><th rowspan="2">কতদিন</th><th rowspan="2" style="text-align:left;">নির্দেশনা</th></tr><tr><th>সকাল</th><th>দুপুর</th><th>রাত</th><th>আগে</th><th>পরে</th></tr></thead><tbody>'+rows+'</tbody></table>';
}
function buildVisitVitalsInlineHTML(rx){
    var bp=rx.bp||'', pulse=rx.pulse||'', note=rx.vitals_note||'';
    if(!bp&&!pulse) return '';
    var st=getBpStatus(rx.bp_systolic,rx.bp_diastolic);
    return '<div class="visit-vitals-inline">'+
        (bp?'<div class="vvi-chip"><i class="fas fa-tachometer-alt" style="color:#a52020;font-size:12px;"></i><div><div class="vvi-label">BP</div><div class="vvi-val">'+esc(bp)+'</div><div class="vvi-unit">mmHg</div></div></div>':'')+
        (pulse?'<div class="vvi-chip vvi-chip-green"><i class="fas fa-heartbeat" style="color:#1a5c2e;font-size:12px;"></i><div><div class="vvi-label">Pulse</div><div class="vvi-val vvi-val-green">'+esc(pulse)+'</div><div class="vvi-unit">bpm</div></div></div>':'')+
        (st.label&&bp?'<div class="vvi-chip" style="background:'+st.bg+';border-color:'+st.color+';"><span style="font-size:12px;font-weight:700;color:'+st.color+';">'+esc(st.label)+'</span></div>':'')+
        (note?'<div class="vvi-chip" style="background:#f0f3f8;border-color:#b0c0d8;color:#555;font-style:italic;font-size:11.5px;"><i class="fas fa-comment-medical mr-1" style="color:#1b3a6b;"></i>'+esc(note)+'</div>':'')+
    '</div>';
}
function buildVitalsHTML(rec){
    var chips=[
        {label:'Weight',val:rec.weight||'—',unit:'kg',cls:''},
        {label:'Height',val:rec.height||'—',unit:'cm',cls:''},
        {label:'BMI',val:rec.bmi||'—',unit:'',cls:'bmi-chip'},
        {label:'B.P.',val:rec.bp||'—',unit:'mmHg',cls:'',valcls:'text-danger'},
        {label:'Pulse',val:rec.pulse||'—',unit:'bpm',cls:''},
        {label:'SpO2',val:rec.spo2?(rec.spo2+'%'):'—',unit:'',cls:'',valcls:'text-info'},
        {label:'Temp',val:rec.temp||'—',unit:'°C',cls:''},
        {label:'Resp',val:rec.rr||'—',unit:'rpm',cls:''},
    ];
    var html='<div class="vitals-grid">';
    chips.forEach(function(c){html+='<div class="vital-chip '+c.cls+'"><span class="vital-chip-label">'+c.label+'</span><span class="vital-chip-val '+(c.valcls||'')+'">'+esc(String(c.val))+'<span class="vital-chip-unit"> '+esc(c.unit)+'</span></span></div>';});
    html+='</div>';
    if(rec.notes&&rec.notes!=='No notes'&&rec.notes!=='—'){html+='<div class="visit-notes"><i class="fas fa-comment-medical mr-1"></i>'+esc(rec.notes)+'</div>';}
    return html;
}

/* ─── NAVIGATION ─── */
function backToStep1(){
    document.getElementById('step1-circle').className='gov-step-num';document.getElementById('step1-circle').textContent='1';
    document.getElementById('step-connector').classList.remove('done');
    document.getElementById('step2-circle').className='gov-step-num gov-step-num-inactive';
    document.getElementById('step2-label').className='gov-step-label gov-step-label-inactive';
    document.getElementById('step2-sublabel').className='gov-step-sub gov-step-sub-inactive';
    document.getElementById('breadcrumb-current').textContent='Select Patient';
    document.getElementById('panel-step1').style.display='block';
    document.getElementById('panel-step2').style.display='none';
    window.scrollTo({top:0,behavior:'smooth'});
}
function editRx(){document.getElementById('rx-view').style.display='none';document.getElementById('rx-form-card').style.display='block';window.scrollTo({top:0,behavior:'smooth'});}

/* ─── TABLE FILTERS ─── */
function filterTable(){var q=document.getElementById('patientSearch').value.toLowerCase();document.getElementById('patientSearchFixed').value=q;_doFilter(q);}
function filterTableFixed(){var q=document.getElementById('patientSearchFixed').value.toLowerCase();document.getElementById('patientSearch').value=q;_doFilter(q);}
function _doFilter(q){document.querySelectorAll('#patientTable tbody tr').forEach(function(row){row.style.display=row.textContent.toLowerCase().includes(q)?'':'none';});}
function filterRoundRxTable(){var q=(document.getElementById('roundRxSearch').value||'').toLowerCase();document.querySelectorAll('#roundRxTable tbody tr.round-rx-row').forEach(function(row){row.style.display=row.textContent.toLowerCase().includes(q)?'':'none';});}
document.getElementById('patientSearch').addEventListener('keyup',filterTable);
document.addEventListener('DOMContentLoaded',function(){updateDoctorHeader();});

/* ─── VIEW SINGLE Rx MODAL ─── */
function viewRoundPrescription(id){
    document.getElementById('modal-loading').classList.remove('d-none');document.getElementById('modal-error').classList.add('d-none');document.getElementById('modal-rx-area').classList.add('d-none');document.getElementById('modal-subtitle').textContent='Loading...';
    $('#rxViewModal').modal('show');
    $.ajax({url:ROUND_DETAIL_URL+id,method:'GET',dataType:'json'}).done(function(res){if(!res.success||!res.data){showModalError(res.message||'Record not found.');return;}populateSingleModal(res.data);}).fail(function(xhr){showModalError('Failed to load (HTTP '+xhr.status+')');});
}
function showModalError(msg){document.getElementById('modal-loading').classList.add('d-none');document.getElementById('modal-error').classList.remove('d-none');document.getElementById('modal-error-msg').textContent=msg;}
function populateSingleModal(d){
    document.getElementById('modal-subtitle').textContent=(d.patient_name||'—')+' · '+(d.patient_code||d.p_code||'—');
    setText('m-ib-name',d.patient_name);setText('m-ib-age',d.patient_age);setText('m-ib-admission',fmtDateBD(d.prescription_date||d.created_at));setText('m-ib-id','#'+d.id);
    setText('m-rx-name',d.patient_name);setText('m-rx-age',d.patient_age);setText('m-rx-date',fmtDateBD(d.prescription_date||d.created_at));
    setText('m-rx-doctor-name',d.doctor_name||'');setText('m-rx-doctor-deg','');setText('m-rx-notes',d.notes||'');
    var vBar=document.getElementById('m-vitals-bar');
    var bpVal=d.bp||(d.bp_systolic&&d.bp_diastolic?(d.bp_systolic+'/'+d.bp_diastolic):'');var pulseVal=d.pulse||'';
    if(bpVal||pulseVal){
        vBar.classList.remove('d-none');
        var bpEl=document.getElementById('m-vb-bp');var pulseEl=document.getElementById('m-vb-pulse');var stEl=document.getElementById('m-vb-status');
        if(bpEl) bpEl.textContent=bpVal||'—';if(pulseEl) pulseEl.textContent=pulseVal?pulseVal+' bpm':'—';
        if(stEl&&bpVal){var st=getBpStatus(d.bp_systolic,d.bp_diastolic);stEl.textContent=st.label;stEl.className='gov-mvb-status';stEl.style.background=st.bg||'#f5f5f5';stEl.style.color=st.color||'#333';}
        var vRow=document.getElementById('m-rx-vitals-row');
        if(vRow){
            vRow.style.display='flex';
            var bpPrintEl=document.getElementById('m-rx-print-bp');var pulsePrintEl=document.getElementById('m-rx-print-pulse');
            var stPrintEl=document.getElementById('m-rx-print-bp-status');var notePrintEl=document.getElementById('m-rx-print-vitals-note');
            if(bpPrintEl) bpPrintEl.textContent=bpVal||'—';if(pulsePrintEl) pulsePrintEl.textContent=pulseVal||'—';
            if(stPrintEl&&bpVal){var st2=getBpStatus(d.bp_systolic,d.bp_diastolic);stPrintEl.textContent=st2.label;stPrintEl.style.background=st2.bg||'transparent';stPrintEl.style.color=st2.color||'inherit';}
            if(notePrintEl) notePrintEl.textContent=d.vitals_note?'('+d.vitals_note+')':'';
        }
    } else {
        vBar.classList.add('d-none');var vRowM=document.getElementById('m-rx-vitals-row');if(vRowM)vRowM.style.display='none';
    }
    var meds=Array.isArray(d.medicines_decoded)?d.medicines_decoded:(typeof d.medicines==='string'?JSON.parse(d.medicines||'[]'):(Array.isArray(d.medicines)?d.medicines:[]));
    var tbody=document.getElementById('m-rx-med-tbody');tbody.innerHTML='';
    meds.filter(function(m){return m&&(m.medicine_name||'').trim();}).forEach(function(m){var r=parseMedRow(m);var tr=document.createElement('tr');tr.innerHTML='<td style="text-align:left;padding-left:6px;">• '+esc(r.name)+'</td><td>'+esc(r.morning)+'</td><td>'+esc(r.noon)+'</td><td>'+esc(r.night)+'</td><td>'+esc(r.before)+'</td><td>'+esc(r.after)+'</td><td>'+esc(r.days)+'</td><td>'+esc(r.months)+'</td><td>'+esc(r.cont)+'</td>';tbody.appendChild(tr);});
    var needed=Math.max(0,8-meds.length);for(var i=0;i<needed;i++){var tr=document.createElement('tr');tr.className='empty-row';tr.innerHTML='<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';tbody.appendChild(tr);}
    document.getElementById('modal-loading').classList.add('d-none');document.getElementById('modal-rx-area').classList.remove('d-none');
}

/* ══════════════════════════════
   PATIENT HISTORY MODAL
══════════════════════════════ */
function viewPatientHistory(patientId, patientName, patientCode, patientPatCode){
    document.getElementById('history-loading').classList.remove('d-none');
    document.getElementById('history-error').classList.add('d-none');
    document.getElementById('history-area').classList.add('d-none');
    document.getElementById('history-modal-title').textContent='Patient History';
    document.getElementById('history-modal-sub').textContent=(patientName||'Patient')+' · '+(patientCode||'');
    document.getElementById('history-timeline').innerHTML='';document.getElementById('history-timeline-rx-only').innerHTML='';
    document.getElementById('history-vitals-table-wrap').innerHTML='';document.getElementById('history-patient-bar').innerHTML='';
    document.getElementById('history-footer-info').textContent='—';
    _currentHistoryTab='combined';
    document.getElementById('htab-combined').classList.add('gov-htab-active');
    document.getElementById('htab-rx').classList.remove('gov-htab-active');
    document.getElementById('htab-vitals').classList.remove('gov-htab-active');
    document.getElementById('history-panel-combined').style.display='block';
    document.getElementById('history-panel-rx').style.display='none';
    document.getElementById('history-panel-vitals').style.display='none';
    $('#historyModal').modal('show');
    var rxDone=false, vitalsDone=false, rxData=null, vitalsData=null, rxError=null, vitalsError=null;
    function tryRender(){if(!rxDone||!vitalsDone)return;renderCombinedHistory(patientName,patientCode,rxData,vitalsData,rxError,vitalsError);}
    $.ajax({url:ROUND_HISTORY_URL+patientId,method:'GET',dataType:'json'}).done(function(res){rxData=res;rxDone=true;tryRender();}).fail(function(xhr){rxError='Round Rx: HTTP '+xhr.status;rxDone=true;tryRender();});
    var preconCode=patientPatCode||patientCode;
    if(preconCode&&preconCode!=='—'){
        $.ajax({url:PRECON_HISTORY_URL+encodeURIComponent(preconCode),method:'GET',dataType:'json'}).done(function(res){vitalsData=res;vitalsDone=true;tryRender();}).fail(function(){vitalsData={success:true,data:[],total:0};vitalsDone=true;tryRender();});
    } else {vitalsData={success:true,data:[],total:0};vitalsDone=true;}
}

function showHistoryError(msg){document.getElementById('history-loading').classList.add('d-none');document.getElementById('history-error').classList.remove('d-none');document.getElementById('history-error-msg').textContent=msg;}

function renderCombinedHistory(fallbackName,fallbackCode,rxRes,vitalsRes,rxErr,vitalsErr){
    var patient=(rxRes&&rxRes.patient)||{};
    var pName=patient.name||fallbackName||'—';var pCode=patient.code||fallbackCode||'—';
    var pAge=patient.age||'—';var pMobile=patient.mobile_no||'—';var pBlood=patient.blood_group||'';
    var prescriptions=(rxRes&&rxRes.success&&rxRes.prescriptions)||[];
    var vitals=(vitalsRes&&vitalsRes.success&&vitalsRes.data)||[];
    document.getElementById('history-patient-bar').innerHTML=
        '<div class="hpb-avatar">'+esc(pName.charAt(0).toUpperCase())+'</div>'+
        '<div><div class="hpb-name">'+esc(pName)+'</div><div class="hpb-meta">'+[pCode,pAge,pMobile,pBlood].filter(Boolean).map(esc).join(' · ')+'</div></div>'+
        '<div style="margin-left:auto;display:flex;gap:8px;align-items:center;">'+
            '<span style="background:var(--gov-navy-light);color:var(--gov-navy);border:1.5px solid #b0c0d8;border-radius:4px;padding:5px 14px;font-size:12.5px;font-weight:700;"><i class="fas fa-sync-alt mr-1"></i>'+prescriptions.length+' Round Rx</span>'+
            '<span style="background:var(--gov-teal-light);color:var(--gov-teal);border:1.5px solid #80c0bc;border-radius:4px;padding:5px 14px;font-size:12.5px;font-weight:700;"><i class="fas fa-heartbeat mr-1"></i>'+vitals.length+' Vitals</span>'+
        '</div>';
    document.getElementById('htab-rx-count').textContent=prescriptions.length;
    document.getElementById('htab-vitals-count').textContent=vitals.length;
    var timelineItems=[];
    prescriptions.forEach(function(rx){var d=rx.prescription_date||rx.created_at;timelineItems.push({type:'rx',dateStr:d,date:d?new Date(d):new Date(0),data:rx});});
    vitals.forEach(function(v){var d=v.datetime||v.created_at;timelineItems.push({type:'vitals',dateStr:d,date:d?new Date(d):new Date(0),data:v});});
    timelineItems.sort(function(a,b){return a.date-b.date;});
    var combinedEl=document.getElementById('history-timeline');
    if(!timelineItems.length){
        combinedEl.innerHTML='<div class="gov-empty-state"><i class="fas fa-folder-open"></i><p>No history found for this patient.</p></div>';
    } else {
        var timelineDiv=document.createElement('div');timelineDiv.className='visit-timeline';
        var rxCount=0,vitCount=0;
        timelineItems.forEach(function(item){
            var card=document.createElement('div');card.className='visit-card';
            var dotEl=document.createElement('div');var boxEl=document.createElement('div');
            var isRx=(item.type==='rx');var isLastRx=(isRx&&rxCount===prescriptions.length-1);
            if(isRx){
                rxCount++;var rx=item.data;
                var meds=rx.medicines||[];if(typeof meds==='string'){try{meds=JSON.parse(meds);}catch(e){meds=[];}}
                var medCount=meds.filter(function(m){return m&&(m.medicine_name||'').trim();}).length;
                dotEl.className='visit-dot visit-dot-rx'+(isLastRx?' visit-dot-latest':'');
                dotEl.innerHTML='<span>'+rxCount+'</span>';
                boxEl.className='visit-box'+(isLastRx?' visit-box-latest':'');
                var vChips='';
                if(rx.bp) vChips+='<span style="background:#fdeaea;color:#a52020;border:1px solid #d0a0a0;border-radius:4px;padding:2px 8px;font-size:11.5px;font-weight:700;"><i class="fas fa-tachometer-alt mr-1"></i>'+esc(rx.bp)+' mmHg</span>';
                if(rx.pulse) vChips+='<span style="background:#e7f6ec;color:#1a5c2e;border:1px solid #a0d0a8;border-radius:4px;padding:2px 8px;font-size:11.5px;font-weight:700;"><i class="fas fa-heartbeat mr-1"></i>'+esc(rx.pulse)+' bpm</span>';
                boxEl.innerHTML=
                    '<div class="visit-header visit-header-rx">'+
                        '<div>'+
                            '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">'+
                                '<span class="visit-number '+(isLastRx?'visit-number-latest':'visit-number-rx')+'">Round #'+rxCount+'</span>'+
                                '<span class="visit-badge-vitals" style="background:var(--gov-navy-light);color:var(--gov-navy);border-color:#b0c0d8;"><i class="fas fa-sync-alt mr-1" style="font-size:9px;"></i>Round Rx</span>'+
                                (isLastRx?'<span class="visit-badge-latest"><i class="fas fa-star mr-1" style="font-size:9px;"></i>Latest Rx</span>':'')+
                            '</div>'+
                            '<div class="visit-date mt-1"><i class="fas fa-calendar-alt mr-1" style="color:var(--gov-navy);font-size:11px;"></i>'+esc(fmtDateBD(item.dateStr))+(rx.round_time?' <span style="font-size:11px;color:var(--gov-muted);">'+fmtTime(rx.round_time)+'</span>':'')+'</div>'+
                            '<div class="visit-doctor"><i class="fas fa-user-md mr-1" style="color:var(--gov-navy);"></i>'+esc(rx.doctor_name||'—')+'</div>'+
                        '</div>'+
                        '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'+
                            (vChips?vChips:'')+
                            '<span style="background:var(--gov-navy-light);color:var(--gov-navy-dark);border-radius:4px;padding:3px 10px;font-size:11.5px;font-weight:600;"><i class="fas fa-pills mr-1" style="font-size:10px;"></i>'+medCount+' med'+(medCount!==1?'s':'')+'</span>'+
                            '<button class="visit-print-btn" onclick="printSingleVisit('+rx.id+')"><i class="fas fa-print"></i> Print</button>'+
                        '</div>'+
                    '</div>'+
                    '<div class="visit-body">'+buildMedTableHTML(meds)+buildVisitVitalsInlineHTML(rx)+(rx.notes?'<div class="visit-notes"><i class="fas fa-comment-medical mr-1"></i>'+esc(rx.notes)+'</div>':'')+
                    '</div>';
            } else {
                vitCount++;var v=item.data;
                var spo2Num=parseInt(v.spo2);var spo2Class=(spo2Num<94)?'text-danger':'text-success';
                dotEl.className='visit-dot visit-dot-vitals';dotEl.innerHTML='<span>'+vitCount+'</span>';
                boxEl.className='visit-box visit-box-vitals';
                boxEl.innerHTML=
                    '<div class="visit-header visit-header-vitals">'+
                        '<div>'+
                            '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">'+
                                '<span class="visit-number visit-number-vitals">Vitals #'+vitCount+'</span>'+
                                '<span class="visit-badge-vitals"><i class="fas fa-heartbeat mr-1" style="font-size:9px;"></i>Pre-Con Assessment</span>'+
                            '</div>'+
                            '<div class="visit-date visit-date-vitals mt-1"><i class="fas fa-calendar-check mr-1" style="font-size:11px;"></i>'+esc(v.datetime||fmtDateBD(item.dateStr))+'</div>'+
                        '</div>'+
                        '<div style="display:flex;align-items:center;gap:8px;">'+
                            '<span style="background:var(--gov-teal-light);color:var(--gov-teal);border-radius:4px;padding:3px 10px;font-size:11.5px;font-weight:600;"><i class="fas fa-tachometer-alt mr-1" style="font-size:10px;"></i>BP: '+esc(v.bp||'—')+'</span>'+
                            '<span class="'+spo2Class+'" style="font-size:12px;font-weight:700;">SpO2: '+esc(v.spo2?(v.spo2+'%'):'—')+'</span>'+
                        '</div>'+
                    '</div>'+
                    '<div class="visit-body">'+buildVitalsHTML(v)+'</div>';
            }
            card.appendChild(dotEl);card.appendChild(boxEl);timelineDiv.appendChild(card);
        });
        combinedEl.appendChild(timelineDiv);
    }

    /* Rx-only panel */
    var rxOnlyEl=document.getElementById('history-timeline-rx-only');
    if(!prescriptions.length){rxOnlyEl.innerHTML='<div class="gov-empty-state"><i class="fas fa-sync-alt"></i><p>No round prescriptions found.</p></div>';}
    else{
        var rxDiv=document.createElement('div');rxDiv.className='visit-timeline';
        prescriptions.forEach(function(rx,i){
            var isLatest=(i===prescriptions.length-1);
            var meds=rx.medicines||[];if(typeof meds==='string'){try{meds=JSON.parse(meds);}catch(e){meds=[];}}
            var medCount=meds.filter(function(m){return m&&(m.medicine_name||'').trim();}).length;
            var card=document.createElement('div');card.className='visit-card';
            var dotEl=document.createElement('div');dotEl.className='visit-dot visit-dot-rx'+(isLatest?' visit-dot-latest':'');dotEl.innerHTML='<span>'+(i+1)+'</span>';
            var boxEl=document.createElement('div');boxEl.className='visit-box'+(isLatest?' visit-box-latest':'');
            var vChipsRx='';
            if(rx.bp) vChipsRx+='<span style="background:#fdeaea;color:#a52020;border:1px solid #d0a0a0;border-radius:4px;padding:2px 8px;font-size:11.5px;font-weight:700;"><i class="fas fa-tachometer-alt mr-1"></i>'+esc(rx.bp)+'</span>';
            if(rx.pulse) vChipsRx+='<span style="background:#e7f6ec;color:#1a5c2e;border:1px solid #a0d0a8;border-radius:4px;padding:2px 8px;font-size:11.5px;font-weight:700;"><i class="fas fa-heartbeat mr-1"></i>'+esc(rx.pulse)+' bpm</span>';
            boxEl.innerHTML=
                '<div class="visit-header visit-header-rx">'+
                    '<div>'+
                        '<div style="display:flex;align-items:center;gap:8px;">'+
                            '<span class="visit-number '+(isLatest?'visit-number-latest':'visit-number-rx')+'">Visit '+(i+1)+'</span>'+
                            (isLatest?'<span class="visit-badge-latest"><i class="fas fa-star mr-1" style="font-size:9px;"></i>Latest</span>':'')+
                        '</div>'+
                        '<div class="visit-date mt-1">'+esc(fmtDateBD(rx.prescription_date||rx.created_at))+(rx.round_time?' <span style="font-size:11px;color:var(--gov-muted);">'+fmtTime(rx.round_time)+'</span>':'')+'</div>'+
                        '<div class="visit-doctor"><i class="fas fa-user-md mr-1" style="color:var(--gov-navy);"></i>'+esc(rx.doctor_name||'—')+'</div>'+
                    '</div>'+
                    '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'+
                        (vChipsRx?vChipsRx:'')+
                        '<span style="background:var(--gov-navy-light);color:var(--gov-navy-dark);border-radius:4px;padding:3px 10px;font-size:11.5px;font-weight:600;"><i class="fas fa-pills mr-1" style="font-size:10px;"></i>'+medCount+' med'+(medCount!==1?'s':'')+'</span>'+
                        '<button class="visit-print-btn" onclick="printSingleVisit('+rx.id+')"><i class="fas fa-print"></i> Print</button>'+
                    '</div>'+
                '</div>'+
                '<div class="visit-body">'+buildMedTableHTML(meds)+buildVisitVitalsInlineHTML(rx)+(rx.notes?'<div class="visit-notes"><i class="fas fa-comment-medical mr-1"></i>'+esc(rx.notes)+'</div>':'')+
                '</div>';
            card.appendChild(dotEl);card.appendChild(boxEl);rxDiv.appendChild(card);
        });
        rxOnlyEl.appendChild(rxDiv);
    }

    /* Vitals-only panel */
    var vitalsWrap=document.getElementById('history-vitals-table-wrap');
    if(!vitals.length){
        vitalsWrap.innerHTML='<div class="gov-empty-state"><i class="fas fa-heartbeat"></i><p>No Pre-Con Assessment records found.</p><small class="text-muted">Make sure the patient has vitals recorded in the Pre-Con Assessment module.</small></div>';
    } else {
        var rows=vitals.map(function(v){
            var bmiNum=parseFloat(v.bmi);var bmiClass=isNaN(bmiNum)?'':bmiNum<18.5?'vbadge-low':bmiNum<25?'vbadge-normal':'vbadge-high';
            var spo2Num=parseInt(v.spo2);var spo2Class=(spo2Num>=95)?'vbadge-normal':(spo2Num>=90?'vbadge-low':'vbadge-high');
            return '<tr>'+
                '<td style="text-align:left;"><strong>'+esc(v.datetime||'—')+'</strong></td>'+
                '<td>'+esc(v.weight||'—')+' <small class="text-muted">kg</small></td>'+
                '<td>'+esc(v.height||'—')+' <small class="text-muted">cm</small></td>'+
                '<td><span class="vbadge '+bmiClass+'">'+esc(v.bmi||'—')+'</span></td>'+
                '<td class="font-weight-bold" style="color:#a52020;">'+esc(v.bp||'—')+'</td>'+
                '<td>'+esc(v.pulse||'—')+'</td>'+
                '<td><span class="vbadge '+spo2Class+'">'+esc(v.spo2?(v.spo2+'%'):'—')+'</span></td>'+
                '<td>'+esc(v.temp||'—')+'°</td>'+
                '<td>'+esc(v.rr||'—')+'</td>'+
                '<td class="text-muted" style="font-size:12px;">'+esc(v.notes&&v.notes!=='No notes'?v.notes.substring(0,25)+(v.notes.length>25?'…':''):'—')+'</td>'+
            '</tr>';
        }).join('');
        vitalsWrap.innerHTML='<div class="vitals-table-card"><div class="vitals-table-card-header"><div class="d-flex align-items-center gap-2"><i class="fas fa-heartbeat mr-2" style="color:var(--gov-teal);"></i><strong style="color:var(--gov-teal);">Pre-Con Assessment Records</strong><span style="background:var(--gov-teal-light);color:var(--gov-teal);border-radius:4px;padding:1px 10px;font-size:12px;font-weight:700;margin-left:8px;">'+vitals.length+' records</span></div></div><div style="overflow-x:auto;"><table class="vitals-full-table"><thead><tr><th style="text-align:left;">Date &amp; Time</th><th>Weight</th><th>Height</th><th>BMI</th><th>BP</th><th>Pulse</th><th>SpO2</th><th>Temp</th><th>Resp</th><th>Notes</th></tr></thead><tbody>'+rows+'</tbody></table></div></div>';
    }

    document.getElementById('history-footer-info').textContent=prescriptions.length+' round visit'+(prescriptions.length!==1?'s':'')+' · '+vitals.length+' vital record'+(vitals.length!==1?'s':'')+(timelineItems.length?' · Total: '+timelineItems.length+' events':'');
    document.getElementById('history-loading').classList.add('d-none');
    document.getElementById('history-area').classList.remove('d-none');
}

/* ─── TAB SWITCHER ─── */
function switchHistoryTab(tab){
    _currentHistoryTab=tab;
    ['combined','rx','vitals'].forEach(function(t){
        document.getElementById('htab-'+t).classList.toggle('gov-htab-active',t===tab);
        document.getElementById('history-panel-'+t).style.display=(t===tab)?'block':'none';
    });
}

function printSingleVisit(rxId){$('#historyModal').modal('hide');setTimeout(function(){viewRoundPrescription(rxId);},400);}
</script>
@stop