@extends('adminlte::page')

@section('title', 'Fresh | Professor Clinic')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 page-main-title">
                <span class="page-title-icon"><i class="fas fa-leaf"></i></span>
                Fresh Prescription
            </h1>
            <ol class="breadcrumb mt-1 p-0" style="background:transparent;font-size:11px;">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nursing.index') }}">Nursing</a></li>
                <li class="breadcrumb-item active" id="breadcrumb-current">Patient Selection</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('nursing.index') }}" class="btn-back-modern">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')

{{-- ══ STEP INDICATOR ══ --}}
<div class="step-wrapper mb-3">
    <div class="step-item-wrap">
        <div class="step-node step-node-active" id="step1-circle"><span class="step-num">1</span></div>
        <div class="step-info">
            <div class="step-title step-title-active">Patient Selection</div>
            <div class="step-sub">Search &amp; select patient</div>
        </div>
    </div>
    <div class="step-line" id="step-connector"></div>
    <div class="step-item-wrap">
        <div class="step-node step-node-idle" id="step2-circle"><span class="step-num">2</span></div>
        <div class="step-info">
            <div class="step-title step-title-idle" id="step2-label">Prescription Entry</div>
            <div class="step-sub" id="step2-sublabel">Fresh medicines &amp; details</div>
        </div>
    </div>
</div>

{{-- ══ SAVE ALERT ══ --}}
<div id="save-alert" class="alert d-none mb-2 gov-alert" role="alert"></div>

{{-- ══ FIXED SEARCH BAR ══ --}}
<div id="fixed-search-bar" class="fixed-search-bar" style="display:none;">
    <div class="fbar-inner">
        <div class="fbar-brand"><i class="fas fa-leaf mr-2"></i><span>Fresh</span></div>
        <div class="fbar-search">
            <div class="gov-search-group gov-search-group-fixed">
                <i class="fas fa-search fsg-icon"></i>
                <input type="text" id="patientSearchFixed" class="fsg-input" placeholder="Search patient name, code or mobile...">
                <button class="fsg-btn" type="button" onclick="filterTableFixed()">Search</button>
            </div>
        </div>
        <div class="fbar-count">
            <span class="fbar-pill">
                <i class="fas fa-users mr-1"></i>
                <strong id="fsc-count">{{ $patients->total() ?? $patients->count() }}</strong> patients
            </span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     STEP 1
══════════════════════════════════════════ --}}
<div id="panel-step1">

<div class="gov-panel" id="patient-list-card">
    <div class="gov-panel-header">
        <div class="gov-panel-title"><i class="fas fa-search mr-2"></i>Patient Selection — Fresh Prescription</div>
        <div class="gov-panel-meta">
            <span class="gov-badge gov-badge-green">
                <i class="fas fa-leaf mr-1"></i>{{ $patients->total() ?? $patients->count() }} patients
            </span>
        </div>
    </div>
    <div class="gov-search-bar" id="inline-search-bar">
        <div class="gov-search-group" style="flex:1;max-width:520px;">
            <i class="fas fa-search fsg-icon"></i>
            <input type="text" id="patientSearch" class="fsg-input" placeholder="Search by patient name, code, or mobile number...">
            <button class="fsg-btn fsg-btn-green" type="button" onclick="filterTable()">Search</button>
        </div>
        <div class="gov-search-info">
            Showing <strong id="visible-count">{{ $patients->count() }}</strong>
            of <strong>{{ $patients->total() ?? $patients->count() }}</strong> records
        </div>
        <a href="https://profclinic.erpbd.org/patients/newpatient" class="btn-add-new-gov" target="_blank">
            <i class="fas fa-user-plus mr-1"></i> New Patient
        </a>
    </div>
    <div class="gov-table-wrap" id="patient-table-scroll">
        <table class="gov-table" id="patientTable">
            <thead>
                <tr>
                    <th style="width:42px;">SL</th>
                    <th style="width:90px;">Code</th>
                    <th style="min-width:180px;">Patient Name</th>
                    <th style="width:52px;">Age</th>
                    <th style="width:50px;">Sex</th>
                    <th style="width:118px;">Mobile</th>
                    <th>Address / Upazila</th>
                    <th style="width:60px;text-align:center;">Blood</th>
                    <th style="width:70px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody id="patientTableBody">
                @forelse($patients as $patient)
                @php
                    $pid=$patient->id??''; $pcode=$patient->patientcode??'—'; $pname=$patient->patientname??'—';
                    $page=$patient->age??'—'; $pgender=strtolower($patient->gender??'');
                    $pmobile=$patient->mobile_no??'—'; $paddr=$patient->address??'';
                    $pupo=$patient->upozila??null; $pblood=$patient->blood_group??null;
                @endphp
                <tr class="patient-row">
                    <td class="sl-cell">{{ $loop->iteration }}</td>
                    <td><span class="code-tag">{{ $pcode }}</span></td>
                    <td>
                        <div class="name-cell">
                            <div class="name-avatar name-avatar-green">{{ strtoupper(substr($pname,0,1)) }}</div>
                            <div>
                                <div class="name-text">{{ $pname }}</div>
                                @if($patient->patientfather??null)
                                    <div class="name-sub"><i class="fas fa-user-tie fa-xs mr-1"></i>{{ $patient->patientfather }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="center-cell">{{ $page }}</td>
                    <td class="center-cell">
                        @if($pgender==='male') <span class="sex-m">M</span>
                        @elseif($pgender==='female') <span class="sex-f">F</span>
                        @else <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="mono-cell">{{ $pmobile }}</td>
                    <td class="addr-cell">{{ $paddr }}{{ $pupo?', '.$pupo:'' }}</td>
                    <td class="center-cell">
                        @if($pblood)<span class="blood-tag">{{ $pblood }}</span>
                        @else<span class="text-muted">—</span>@endif
                    </td>
                    <td class="center-cell">
                        <button type="button" class="btn-select btn-select-green"
                            onclick="selectPatient(this)"
                            data-id="{{ $pid }}" data-name="{{ $pname }}" data-age="{{ $page }}"
                            data-code="{{ $pcode }}" data-mobile="{{ $pmobile }}"
                            data-address="{{ $paddr }}" data-upozila="{{ $pupo }}"
                            data-blood="{{ $pblood }}" data-gender="{{ $patient->gender??'' }}"
                            title="Select {{ $pname }}">
                            <i class="fas fa-check mr-1"></i>Select
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-user-slash"></i><p>No patients found.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($patients,'links'))
    <div class="gov-pagination-bar">
        <div class="pagination-info">
            <i class="fas fa-list mr-1"></i>
            Showing <strong>{{ $patients->firstItem()??0 }}</strong> to <strong>{{ $patients->lastItem()??0 }}</strong>
            of <strong>{{ $patients->total()??0 }}</strong> patients
        </div>
        {{ $patients->links('pagination::bootstrap-4') }}
    </div>
    @endif
    <div class="gov-panel-footer">
        <small><i class="fas fa-info-circle mr-1 text-success"></i>
            Click <strong>Select</strong> on any row to proceed to prescription entry.
        </small>
        <a href="https://profclinic.erpbd.org/patients/newpatient" class="btn-add-new-gov-sm" target="_blank">
            <i class="fas fa-user-plus mr-1"></i> Add New Patient
        </a>
    </div>
</div>

{{-- PAST FRESH PRESCRIPTIONS --}}
<div class="gov-panel" id="past-rx-card">
    <div class="gov-panel-header">
        <div class="gov-panel-title"><i class="fas fa-history mr-2"></i>Past Fresh Prescriptions</div>
        <div class="gov-panel-meta">
            <span class="gov-badge gov-badge-green">
                <i class="fas fa-file-medical mr-1"></i>{{ $FreshPatients->total()??$FreshPatients->count() }} records
            </span>
        </div>
    </div>
    <div class="gov-search-bar">
        <div class="gov-search-group" style="flex:1;max-width:520px;">
            <i class="fas fa-search fsg-icon"></i>
            <input type="text" id="freshRxSearch" class="fsg-input"
                   placeholder="Search by patient name, code or mobile..."
                   onkeyup="filterFreshRxTable()">
            <button class="fsg-btn fsg-btn-green" type="button" onclick="filterFreshRxTable()">Search</button>
        </div>
    </div>
    <div class="gov-table-wrap">
        <table class="gov-table" id="freshRxTable">
            <thead>
                <tr>
                    <th style="width:42px;">SL</th>
                    <th style="width:76px;">Rx ID</th>
                    <th style="min-width:180px;">Patient Name</th>
                    <th style="width:52px;">Age</th>
                    <th style="width:50px;">Sex</th>
                    <th style="width:118px;">Mobile</th>
                    <th style="width:120px;">Prescription Date</th>
                    <th style="width:60px;text-align:center;">Blood</th>
                    <th style="width:80px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody id="freshRxTableBody">
                @forelse($FreshPatients as $fp)
                @php
                    $fpRxId=$fp->id??''; $fpCode=$fp->patient_code??$fp->patientcode??'—';
                    $fpName=$fp->patient_name??$fp->patientname??'—';
                    $fpAge=$fp->patient_age??$fp->age??'—';
                    $fpGender=strtolower($fp->gender??'');
                    $fpMobile=$fp->mobile_no??'—'; $fpBlood=$fp->blood_group??null;
                    $fpRxDate=$fp->prescription_date??$fp->created_at??'';
                @endphp
                <tr class="fresh-rx-row">
                    <td class="sl-cell">{{ $loop->iteration }}</td>
                    <td><span class="rx-id-tag">#{{ $fpRxId }}</span></td>
                    <td>
                        <div class="name-cell">
                            <div class="name-avatar name-avatar-green name-avatar-sm">{{ strtoupper(substr($fpName,0,1)) }}</div>
                            <div><div class="name-text">{{ $fpName }}</div><div class="name-sub">{{ $fpCode }}</div></div>
                        </div>
                    </td>
                    <td class="center-cell">{{ $fpAge }}</td>
                    <td class="center-cell">
                        @if($fpGender==='male') <span class="sex-m">M</span>
                        @elseif($fpGender==='female') <span class="sex-f">F</span>
                        @else <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="mono-cell">{{ $fpMobile }}</td>
                    <td class="date-cell">
                        @if($fpRxDate)
                            {{ \Carbon\Carbon::parse($fpRxDate)->format('d M Y') }}
                            <span class="date-ago">{{ \Carbon\Carbon::parse($fpRxDate)->diffForHumans() }}</span>
                        @else —
                        @endif
                    </td>
                    <td class="center-cell">
                        @if($fpBlood)<span class="blood-tag">{{ $fpBlood }}</span>
                        @else<span class="text-muted">—</span>@endif
                    </td>
                    <td class="center-cell">
                        <button type="button" class="btn-view-rx btn-view-green"
                            onclick="viewFreshPrescription({{ $fpRxId }})">
                            <i class="fas fa-eye mr-1"></i>View
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-file-medical-alt"></i><p>No past fresh prescriptions found.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($FreshPatients,'links'))
    <div class="gov-pagination-bar">
        <div class="pagination-info">
            <i class="fas fa-list mr-1"></i>
            Showing <strong>{{ $FreshPatients->firstItem()??0 }}</strong> to <strong>{{ $FreshPatients->lastItem()??0 }}</strong>
            of <strong>{{ $FreshPatients->total()??0 }}</strong> records
        </div>
        {{ $FreshPatients->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

</div>{{-- /#panel-step1 --}}

{{-- ══════════════════════════════════════════
     STEP 2
══════════════════════════════════════════ --}}
<div id="panel-step2" style="display:none;">

    {{-- ★ HIDDEN: template_medicine (order_type='fresh prescription') — JS auto-load only --}}
    <div id="template-med-data" style="display:none;">
        @foreach($templateMedicines as $tm)
        <span class="template-med-item"
            data-name="{{ e($tm->name??'') }}"
            data-dose="{{ e($tm->dose??'') }}"
            data-route="{{ e($tm->route??'') }}"
            data-frequency="{{ e($tm->frequency??'') }}"
            data-duration="{{ e($tm->duration??'') }}"
            data-timing="{{ e($tm->timing??'') }}"
            data-note="{{ e($tm->note??'') }}">
        </span>
        @endforeach
    </div>

    {{-- Selected Patient Bar --}}
    <div class="patient-selected-bar patient-selected-bar-success mb-4">
        <div class="psb-left">
            <div class="psb-avatar" id="spb-avatar">A</div>
            <div class="psb-info">
                <div class="psb-name" id="spb-name"></div>
                <div class="psb-meta" id="spb-meta"></div>
            </div>
        </div>
        <div class="psb-right">
            <span class="psb-status-dot psb-status-dot-green"></span>
            <span class="psb-status-label">Fresh Prescription</span>
            <button type="button" class="btn btn-psb-change" onclick="backToStep1()">
                <i class="fas fa-exchange-alt mr-1"></i> Change Patient
            </button>
        </div>
    </div>

    {{-- Medicine load status bar --}}
    <div id="med-load-bar" class="med-load-bar" style="display:none;"></div>

    {{-- Prescription Form --}}
    <div class="modern-card" id="rx-form-card">
        <div class="modern-card-header">
            <div class="modern-card-title">
                <span class="card-title-icon bg-success-soft"><i class="fas fa-notes-medical text-success"></i></span>
                <div>
                    <h5 class="mb-0 font-weight-bold">Fresh Prescription</h5>
                    <small class="text-muted">Fill in patient &amp; medicine details</small>
                </div>
            </div>
        </div>
        <div class="modern-card-body">
            <input type="hidden" id="f-patient-id">
            <input type="hidden" id="f-patient-code">
            <hr class="section-divider mt-0 mb-4">

            <div class="section-heading mb-3">
                <i class="fas fa-user-injured mr-2 text-success"></i>
                <span>Patient &amp; Clinical Information</span>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="modern-field-group">
                        <label class="modern-label">Doctor</label>
                        <select class="modern-input" id="f-doctor" onchange="updateDoctorHeader()">
                            @forelse($doctors as $doc)
                                @php $displayName=$doc->doctor_name??$doc->name??null; @endphp
                                <option value="{{ $doc->id }}"
                                    data-docname="{{ e($displayName??'') }}"
                                    data-doctorno="{{ e($doc->doctor_no??'') }}"
                                    data-speciality="{{ e($doc->speciality??'') }}"
                                    data-contact="{{ e($doc->contact??'') }}"
                                    data-posting="{{ e($doc->Posting??'') }}">
                                    {{ $displayName?:'Doctor ID: '.$doc->id.' (No Name)' }}
                                </option>
                            @empty
                                <option value="">No doctors found</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="modern-field-group">
                        <label class="modern-label">Patient Name</label>
                        <input type="text" class="modern-input" id="f-patient-name" placeholder="Full name">
                    </div>
                    <div class="modern-field-group">
                        <label class="modern-label">Age</label>
                        <input type="text" class="modern-input" id="f-patient-age" placeholder="e.g. 25 yrs">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="modern-field-group">
                        <label class="modern-label">Prescription Date</label>
                        <input type="date" class="modern-input" id="f-date">
                    </div>
                    <div class="modern-field-group">
                        <label class="modern-label">C/C (Chief Complaint)</label>
                        <input type="text" class="modern-input" id="f-cc" placeholder="Chief complaint...">
                    </div>
                    <input type="hidden" id="f-pulse" value="">
                    <input type="hidden" id="f-bp" value="">
                </div>
            </div>

            <div class="section-divider-full mt-4 mb-4">
                <div class="section-heading mb-0">
                    <i class="fas fa-pills mr-2 text-success"></i>
                    <span>Medicines</span>
                    <span class="badge badge-pill ml-2" id="med-count-badge"
                          style="background:#e8f5e9;color:#2e7d32;font-size:12px;padding:4px 10px;">0</span>
                    <small id="auto-loaded-note" class="text-success ml-3 d-none" style="font-size:11px;font-weight:600;">
                        <i class="fas fa-check-circle mr-1"></i>Auto-loaded from template
                    </small>
                </div>
                <div>
                    <button type="button" class="btn-med-action btn-med-add-success" onclick="addMedRow()">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                    <button type="button" class="btn-med-action btn-med-clear-success" onclick="clearAllMeds()" style="margin-left:6px;">
                        <i class="fas fa-trash-alt mr-1"></i> Clear All
                    </button>
                </div>
            </div>

            {{-- Selected Medicines Table --}}
            <div class="med-table-card selected-med-card-success mb-4">
                <div class="med-table-card-header" style="background:#f9fff9;border-bottom-color:#c8e6c9;">
                    <div class="d-flex align-items-center">
                        <span class="med-table-dot" style="background:#43a047;"></span>
                        <span class="med-table-title">Selected Medicines</span>
                        <span class="med-count-pill med-count-pill-success" id="sel-med-badge">0</span>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table med-table mb-0" style="min-width:750px;">
                        <thead>
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>Medicine Name</th>
                                <th style="width:90px;">Dose</th>
                                <th style="width:80px;">Route</th>
                                <th style="width:110px;">Frequency</th>
                                <th style="width:85px;">Duration</th>
                                <th style="width:85px;">Timing</th>
                                <th style="width:110px;">Remarks</th>
                                <th style="width:42px;"></th>
                            </tr>
                        </thead>
                        <tbody id="sel-med-tbody">
                            <tr id="empty-sel-row">
                                <td colspan="9">
                                    <div class="med-empty-state">
                                        <i class="fas fa-pills" style="color:#43a047;"></i>
                                        <span>Patient select করলে medicines auto-load হবে।</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ★ Available Medicines — Source: common_medicine table --}}
            <div class="med-table-card mb-0">
                <div class="med-table-card-header">
                    <div class="d-flex align-items-center">
                        <span class="med-table-dot" style="background:#1976d2;"></span>
                        <span class="med-table-title">আরো Medicine যোগ করুন</span>
                        <span class="med-count-pill" style="background:#e3f2fd;color:#1565c0;">{{ $medicines->count() }}</span>
                        <small class="text-muted ml-2" style="font-size:10px;">(common_medicine)</small>
                    </div>
                    <div class="avail-filter-wrap">
                        <i class="fas fa-filter avail-filter-icon"></i>
                        <input type="text" class="avail-filter-input" id="med-filter" placeholder="Filter medicines...">
                    </div>
                </div>
                <div style="max-height:220px;overflow-y:auto;">
                    <table class="table med-table mb-0">
                        <thead>
                            <tr>
                                <th width="35"><input type="checkbox" id="select-all-med" style="cursor:pointer;"></th>
                                <th>Medicine Name</th>
                                <th>Group</th>
                                <th>Strength</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="avail-med-tbody">
                            @forelse($medicines as $med)
                            {{-- Source: common_medicine — no dose/route/freq columns --}}
                            <tr class="avail-med-row" data-name="{{ strtolower($med->name??'') }}">
                                <td>
                                    <input type="checkbox" class="avail-med-cb modern-checkbox"
                                        data-id="{{ $med->id }}"
                                        data-name="{{ e($med->name??'') }}"
                                        data-strength="{{ e($med->strength??'') }}"
                                        data-dose=""
                                        data-route=""
                                        data-frequency=""
                                        data-duration=""
                                        data-timing=""
                                        data-note=""
                                        onchange="onAvailMedChange(this)">
                                </td>
                                <td><span class="avail-med-name">{{ $med->name??'—' }}</span></td>
                                <td><span class="text-muted small">{{ $med->GroupName??'—' }}</span></td>
                                <td><span class="text-muted small">{{ $med->strength??'—' }}</span></td>
                                <td>
                                    <button type="button" class="btn-quick-add btn-quick-add-green" onclick="quickAdd(this)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No medicines found in common_medicine.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <div class="modern-field-group">
                    <label class="modern-label">
                        <i class="fas fa-comment-medical mr-1 text-secondary"></i>
                        Additional Notes / Rx Text
                    </label>
                    <textarea class="modern-input modern-textarea" id="f-notes" rows="3"
                              placeholder="Additional notes or Rx text..."></textarea>
                </div>
            </div>

            <div class="form-footer mt-4">
                <button type="button" class="btn btn-footer-back" onclick="backToStep1()">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </button>
                <button type="button" class="btn btn-footer-save-success" id="btn-save-rx" onclick="saveAndGenerateRx()">
                    <i class="fas fa-save mr-1"></i> Save &amp; Generate Prescription
                </button>
            </div>
        </div>
    </div>

    {{-- PRESCRIPTION PRINT VIEW --}}
    <div id="rx-view" style="display:none;">
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="rx-summary-card rx-card-green">
                    <div class="rx-summary-icon"><i class="fas fa-user"></i></div>
                    <div class="rx-summary-content"><div class="rx-summary-label">Patient</div><div class="rx-summary-value" id="ib-name">—</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="rx-summary-card rx-card-teal">
                    <div class="rx-summary-icon"><i class="fas fa-birthday-cake"></i></div>
                    <div class="rx-summary-content"><div class="rx-summary-label">Age</div><div class="rx-summary-value" id="ib-age">—</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="rx-summary-card rx-card-orange">
                    <div class="rx-summary-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="rx-summary-content"><div class="rx-summary-label">Date</div><div class="rx-summary-value" id="ib-date">—</div></div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="rx-summary-card rx-card-blue">
                    <div class="rx-summary-icon"><i class="fas fa-user-md"></i></div>
                    <div class="rx-summary-content"><div class="rx-summary-label">Doctor</div><div class="rx-summary-value" id="ib-doctor" style="font-size:12px;">—</div></div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <div class="modern-card-title">
                    <span class="card-title-icon bg-success-soft"><i class="fas fa-notes-medical text-success"></i></span>
                    <div><h5 class="mb-0 font-weight-bold">Fresh Prescription</h5><small class="text-muted">Ready to print</small></div>
                </div>
                <span class="rx-saved-badge"><i class="fas fa-check-circle mr-1"></i> Saved <span class="ml-1" id="rx-badge-name">—</span></span>
            </div>
            <div class="modern-card-body p-0">
                <div id="prescription-print-area">
                    <div class="fresh-wrapper">
                        <div class="fresh-header">
                            <div class="fresh-header-left">
                                <div class="fresh-logo-row">
                                    <div class="fresh-cp-logo"><span class="fresh-cp-c">C</span><span class="fresh-cp-p">P</span></div>
                                    <div>
                                        <div class="fresh-clinic-bn">প্রফেসর ক্লিনিক</div>
                                        <div class="fresh-clinic-address">মাঝিড়া, শাজাহানপুর, বগুড়া।</div>
                                        <div class="fresh-clinic-phones">মোবাঃ ০১৭২০-০৩৯০০৫, ০১৭২০-০৩৯০০৬</div>
                                        <div class="fresh-clinic-phones">০১৭২০-০৩৯০০৭, ০১৭২০-০৩৯০০৮</div>
                                    </div>
                                </div>
                            </div>
                            <div class="fresh-header-right">
                                <div class="fresh-doctor-title"   id="rx-doctor-name">—</div>
                                <div class="fresh-doctor-deg"     id="rx-doctor-speciality"></div>
                                <div class="fresh-doctor-deg"     id="rx-doctor-regno"></div>
                                <div class="fresh-doctor-college" id="rx-doctor-posting"></div>
                                <div class="fresh-doctor-deg"     id="rx-doctor-contact"></div>
                            </div>
                        </div>
                        <div class="fresh-nad-row">
                            <div class="fresh-nad-field"><span class="fresh-nad-label">Code :</span><span class="fresh-nad-value" id="rx-code">—</span></div>
                            <div class="fresh-nad-field"><span class="fresh-nad-label">Name :</span><span class="fresh-nad-value" id="rx-name">—</span></div>
                            <div class="fresh-nad-field"><span class="fresh-nad-label">Age :</span><span class="fresh-nad-value" id="rx-age">—</span></div>
                            <div class="fresh-nad-field"><span class="fresh-nad-label">Date :</span><span class="fresh-nad-value" id="rx-date">—</span></div>
                        </div>
                        <div class="fresh-body">
                            <div class="fresh-left">
                                <div class="fresh-cc-section">
                                    <div class="fresh-section-title">C/C</div>
                                    <div class="fresh-cc-text" id="rx-cc">—</div>
                                </div>
                                <div class="fresh-oe-section">
                                    <div class="fresh-section-title">O/E</div>
                                    <ul class="fresh-list">
                                        <li>Pulse    <span class="fresh-val" id="rx-pulse">—</span></li>
                                        <li>BP       <span class="fresh-val" id="rx-bp">—</span></li>
                                        <li>Anaemia  <span class="fresh-val" id="rx-anaemia">—</span></li>
                                        <li>Jaundice <span class="fresh-val" id="rx-jaundice">—</span></li>
                                        <li>Tem      <span class="fresh-val" id="rx-tem">—</span></li>
                                        <li>Oedema   <span class="fresh-val" id="rx-oedema">—</span></li>
                                        <li>Weight   <span class="fresh-val" id="rx-weight">—</span></li>
                                        <li>Heart    <span class="fresh-val" id="rx-heart">—</span></li>
                                        <li>Lungs    <span class="fresh-val" id="rx-lungs">—</span></li>
                                        <li>FM       <span class="fresh-val" id="rx-fm">—</span></li>
                                    </ul>
                                </div>
                                <div class="fresh-inv-section">
                                    <div class="fresh-section-title">Inv</div>
                                    <ul class="fresh-list">
                                        @forelse($investigations as $inv)
                                            <li>{{ $inv->name??$inv->investigation_name??'' }}</li>
                                        @empty
                                            <li>CBC/Hb%</li><li>Urine R/M/E</li><li>RBS/FBS</li><li>HBs Ag</li>
                                            <li>VDRL</li><li>Blood grouping</li><li>S. bilirubin</li><li>Widal test</li>
                                            <li>Blood urea</li><li>S. creatinine</li><li>ASo titre</li><li>RA test</li>
                                            <li>U.R/E</li><li>USG of</li><li>X–ray of</li><li>ECG</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            <div class="fresh-right">
                                <div class="fresh-rx-symbol">Rx</div>
                                <div class="fresh-med-table-wrap">
                                    <table class="fresh-med-table" id="rx-medicine-print-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="fresh-th-name">ঔষধের নাম</th>
                                                <th colspan="3" class="fresh-th-group">কখন খাবেন?</th>
                                                <th colspan="2" class="fresh-th-group">আহারের</th>
                                                <th colspan="3" class="fresh-th-group">কতদিন?</th>
                                            </tr>
                                            <tr>
                                                <th class="fresh-th-sub">সকাল</th><th class="fresh-th-sub">দুপুর</th><th class="fresh-th-sub">রাত</th>
                                                <th class="fresh-th-sub">আগে</th><th class="fresh-th-sub">পরে</th>
                                                <th class="fresh-th-sub">দিন</th><th class="fresh-th-sub">মাস</th><th class="fresh-th-sub">চলবে</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rx-med-print-tbody"></tbody>
                                    </table>
                                </div>
                                <div id="rx-rx-text"     style="margin-top:10px;font-size:12px;color:#222;white-space:pre-wrap;"></div>
                                <div id="rx-notes-print" style="margin-top:8px;font-size:11px;color:#444;white-space:pre-wrap;"></div>
                            </div>
                        </div>
                        <div class="fresh-footer">
                            <span>বিঃ দ্রঃ ............................................</span>
                            <span>............... দিন/মাস পর ব্যবস্থাপত্র সহ সাক্ষাৎ করিবেন।</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-card-footer">
                <small class="text-muted"><i class="fas fa-clock mr-1"></i> Generated: <span id="gen-time">—</span></small>
                <div style="display:flex;gap:8px;">
                    <button onclick="printRx()" class="btn-rx-action btn-rx-print"><i class="fas fa-print mr-1"></i> Print</button>
                    <button type="button" class="btn-rx-action btn-rx-edit" onclick="editRx()"><i class="fas fa-edit mr-1"></i> Edit</button>
                    <button type="button" class="btn-rx-action btn-rx-new" onclick="backToStep1()"><i class="fas fa-plus mr-1"></i> New</button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /#panel-step2 --}}

{{-- PRESCRIPTION VIEW MODAL --}}
<div class="modal fade" id="rxViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content rx-modal-content">
            <div class="modal-header rx-modal-header">
                <div class="d-flex align-items-center">
                    <div class="rx-modal-icon mr-3"><i class="fas fa-leaf"></i></div>
                    <div>
                        <h5 class="modal-title mb-0 font-weight-bold text-white">Fresh Prescription</h5>
                        <small class="modal-subtitle-text" id="modal-subtitle">Loading...</small>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <button type="button" class="btn-rx-modal-print" onclick="printModal()"><i class="fas fa-print mr-1"></i> Print</button>
                    <button type="button" class="btn-rx-modal-close" data-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="modal-loading" class="modal-state-wrap">
                    <div class="modal-spinner-icon"><i class="fas fa-spinner fa-spin"></i></div>
                    <p class="modal-state-text">Loading prescription...</p>
                </div>
                <div id="modal-error" class="modal-state-wrap d-none">
                    <div class="modal-error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <p class="modal-state-text" id="modal-error-msg">Failed to load prescription.</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-dismiss="modal">Close</button>
                </div>
                <div id="modal-rx-area" class="d-none">
                    <div class="modal-summary-bar">
                        <div class="modal-summary-item msi-green"><i class="fas fa-user"></i><div><div class="msi-label">Patient</div><div class="msi-val" id="m-ib-name">—</div></div></div>
                        <div class="modal-summary-item msi-teal"><i class="fas fa-birthday-cake"></i><div><div class="msi-label">Age</div><div class="msi-val" id="m-ib-age">—</div></div></div>
                        <div class="modal-summary-item msi-orange"><i class="fas fa-calendar-alt"></i><div><div class="msi-label">Date</div><div class="msi-val" id="m-ib-date">—</div></div></div>
                        <div class="modal-summary-item msi-blue"><i class="fas fa-hashtag"></i><div><div class="msi-label">Rx ID</div><div class="msi-val" id="m-ib-id">—</div></div></div>
                    </div>
                    <div id="modal-prescription-print-area" style="padding:20px 24px;">
                        <div class="fresh-wrapper">
                            <div class="fresh-header">
                                <div class="fresh-header-left">
                                    <div class="fresh-logo-row">
                                        <div class="fresh-cp-logo"><span class="fresh-cp-c">C</span><span class="fresh-cp-p">P</span></div>
                                        <div>
                                            <div class="fresh-clinic-bn">প্রফেসর ক্লিনিক</div>
                                            <div class="fresh-clinic-address">মাঝিড়া, শাজাহানপুর, বগুড়া।</div>
                                            <div class="fresh-clinic-phones">মোবাঃ ০১৭২০-০৩৯০০৫, ০১৭২০-০৩৯০০৬</div>
                                            <div class="fresh-clinic-phones">০১৭২০-০৩৯০০৭, ০১৭২০-০৩৯০০৮</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fresh-header-right">
                                    <div class="fresh-doctor-title"   id="m-rx-doctor-name">—</div>
                                    <div class="fresh-doctor-deg"     id="m-rx-doctor-speciality"></div>
                                    <div class="fresh-doctor-deg"     id="m-rx-doctor-regno"></div>
                                    <div class="fresh-doctor-college" id="m-rx-doctor-posting"></div>
                                    <div class="fresh-doctor-deg"     id="m-rx-doctor-contact"></div>
                                </div>
                            </div>
                            <div class="fresh-nad-row">
                                <div class="fresh-nad-field"><span class="fresh-nad-label">Code :</span><span class="fresh-nad-value" id="m-rx-code">—</span></div>
                                <div class="fresh-nad-field"><span class="fresh-nad-label">Name :</span><span class="fresh-nad-value" id="m-rx-name">—</span></div>
                                <div class="fresh-nad-field"><span class="fresh-nad-label">Age :</span><span class="fresh-nad-value" id="m-rx-age">—</span></div>
                                <div class="fresh-nad-field"><span class="fresh-nad-label">Date :</span><span class="fresh-nad-value" id="m-rx-date">—</span></div>
                            </div>
                            <div class="fresh-body">
                                <div class="fresh-left">
                                    <div class="fresh-cc-section"><div class="fresh-section-title">C/C</div><div class="fresh-cc-text" id="m-rx-cc">—</div></div>
                                    <div class="fresh-oe-section">
                                        <div class="fresh-section-title">O/E</div>
                                        <ul class="fresh-list">
                                            <li>Pulse    <span class="fresh-val" id="m-rx-pulse">—</span></li>
                                            <li>BP       <span class="fresh-val" id="m-rx-bp">—</span></li>
                                            <li>Anaemia  <span class="fresh-val" id="m-rx-anaemia">—</span></li>
                                            <li>Jaundice <span class="fresh-val" id="m-rx-jaundice">—</span></li>
                                            <li>Tem      <span class="fresh-val" id="m-rx-tem">—</span></li>
                                            <li>Oedema   <span class="fresh-val" id="m-rx-oedema">—</span></li>
                                            <li>Weight   <span class="fresh-val" id="m-rx-weight">—</span></li>
                                            <li>Heart    <span class="fresh-val" id="m-rx-heart">—</span></li>
                                            <li>Lungs    <span class="fresh-val" id="m-rx-lungs">—</span></li>
                                            <li>FM       <span class="fresh-val" id="m-rx-fm">—</span></li>
                                        </ul>
                                    </div>
                                    <div class="fresh-inv-section">
                                        <div class="fresh-section-title">Inv</div>
                                        <ul class="fresh-list">
                                            @forelse($investigations as $inv)
                                                <li>{{ $inv->name??$inv->investigation_name??'' }}</li>
                                            @empty
                                                <li>CBC/Hb%</li><li>Urine R/M/E</li><li>RBS/FBS</li><li>HBs Ag</li>
                                                <li>VDRL</li><li>Blood grouping</li><li>S. bilirubin</li><li>Widal test</li>
                                                <li>Blood urea</li><li>S. creatinine</li><li>ASo titre</li><li>RA test</li>
                                                <li>U.R/E</li><li>USG of</li><li>X–ray of</li><li>ECG</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                <div class="fresh-right">
                                    <div class="fresh-rx-symbol">Rx</div>
                                    <div class="fresh-med-table-wrap">
                                        <table class="fresh-med-table">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="fresh-th-name">ঔষধের নাম</th>
                                                    <th colspan="3" class="fresh-th-group">কখন খাবেন?</th>
                                                    <th colspan="2" class="fresh-th-group">আহারের</th>
                                                    <th rowspan="2" class="fresh-th-name">কতদিন</th>
                                                    <th rowspan="2" class="fresh-th-name">নির্দেশনা</th>
                                                </tr>
                                                <tr>
                                                    <th class="fresh-th-sub">সকাল</th><th class="fresh-th-sub">দুপুর</th><th class="fresh-th-sub">রাত</th>
                                                    <th class="fresh-th-sub">আগে</th><th class="fresh-th-sub">পরে</th>
                                                    <th class="fresh-th-sub">দিন</th><th class="fresh-th-sub">মাস</th><th class="fresh-th-sub">চলবে</th>
                                                </tr>
                                            </thead>
                                            <tbody id="m-rx-med-print-tbody"></tbody>
                                        </table>
                                    </div>
                                    <div id="m-rx-rx-text" style="margin-top:10px;font-size:12px;color:#222;white-space:pre-wrap;"></div>
                                    <div id="m-rx-notes"   style="margin-top:8px;font-size:11px;color:#444;white-space:pre-wrap;"></div>
                                </div>
                            </div>
                            <div class="fresh-footer">
                                <span>বিঃ দ্রঃ ............................................</span>
                                <span>............... দিন/মাস পর ব্যবস্থাপত্র সহ সাক্ষাৎ করিবেন।</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer rx-modal-footer">
                <small class="text-muted"><i class="fas fa-clock mr-1"></i>Saved: <span id="m-saved-time">—</span></small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="print-overlay"></div>

@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
<style>
{{-- ══ ALL CSS UNCHANGED ══ --}}
:root {
    --green-deep:#1B5E20;--green-mid:#2E7D32;--green-btn:#43A047;--green-light:#E8F5E9;--green-soft:#C8E6C9;
    --blue-deep:#0D47A1;--blue-mid:#1565C0;--blue-light:#E3F2FD;
    --teal-mid:#00695C;--teal-light:#E0F2F1;--orange:#BF360C;
    --text-primary:#1a2332;--text-muted:#5a6678;--border:#d0d5dd;--border-dark:#b0b8c4;
    --row-odd:#ffffff;--row-even:#f7f9f7;--row-hover:#f0faf0;
    --radius-sm:4px;--radius-md:8px;--radius-lg:12px;
    --shadow-sm:0 1px 3px rgba(0,0,0,.08);--shadow-md:0 3px 10px rgba(0,0,0,.1);
    --font-base:'Source Sans Pro','Hind Siliguri',Arial,sans-serif;
}
body,.content-wrapper{background:#eef2ee!important;font-family:var(--font-base);}
.page-main-title{font-size:20px;font-weight:700;color:#1A237E;display:flex;align-items:center;gap:8px;}
.page-title-icon{width:34px;height:34px;border-radius:6px;background:var(--green-light);display:inline-flex;align-items:center;justify-content:center;color:var(--green-mid);font-size:15px;}
.btn-back-modern{background:#fff;border:1.5px solid var(--border-dark);color:var(--text-primary);border-radius:var(--radius-sm);font-weight:600;padding:6px 14px;font-size:12.5px;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;}
.btn-back-modern:hover{background:#f0f4f0;border-color:var(--green-mid);color:var(--green-deep);text-decoration:none;}
.step-wrapper{display:flex;align-items:center;background:#fff;border:1.5px solid var(--border);border-radius:var(--radius-md);padding:12px 20px;box-shadow:var(--shadow-sm);}
.step-item-wrap{display:flex;align-items:center;gap:10px;}
.step-node{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid;font-weight:700;font-size:13px;}
.step-node-active{background:var(--green-btn);border-color:var(--green-btn);color:#fff;box-shadow:0 0 0 3px rgba(67,160,71,.18);}
.step-node-done{background:var(--green-deep);border-color:var(--green-deep);color:#fff;}
.step-node-idle{background:#f5f5f5;border-color:#ccc;color:#aaa;}
.step-num{line-height:1;}
.step-title{font-size:13px;font-weight:700;line-height:1.2;}
.step-title-active{color:var(--green-btn);}
.step-title-done{color:var(--green-deep);}
.step-title-idle{color:#aaa;}
.step-sub{font-size:11px;color:var(--text-muted);margin-top:1px;}
.step-line{flex:1;height:2px;background:#e0e0e0;margin:0 16px;border-radius:2px;}
.step-line.done{background:var(--green-deep);}
.gov-alert{border-radius:var(--radius-sm);border:none;font-size:13px;font-weight:500;box-shadow:var(--shadow-sm);}
.fixed-search-bar{position:fixed;top:0;left:0;right:0;z-index:9999;background:linear-gradient(135deg,#1B5E20 0%,#2E7D32 100%);box-shadow:0 3px 20px rgba(27,94,32,.4);transform:translateY(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1),opacity .3s;opacity:0;pointer-events:none;}
.fixed-search-bar.visible{transform:translateY(0);opacity:1;pointer-events:all;}
.fbar-inner{display:flex;align-items:center;gap:14px;padding:9px 20px;flex-wrap:wrap;}
.fbar-brand{display:flex;align-items:center;color:rgba(255,255,255,.9);font-size:13px;font-weight:700;white-space:nowrap;flex-shrink:0;}
.fbar-search{flex:1;min-width:240px;}
.fbar-pill{background:rgba(255,255,255,.18);color:#fff;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;}
.gov-panel{background:#fff;border:1.5px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);margin-bottom:16px;overflow:hidden;}
.gov-panel-header{background:#2c3e50;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.gov-panel-title{color:#fff;font-size:13.5px;font-weight:700;display:flex;align-items:center;letter-spacing:.2px;}
.gov-badge{border-radius:3px;padding:3px 10px;font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;}
.gov-badge-green{background:var(--green-light);color:var(--green-deep);border:1px solid var(--green-soft);}
.gov-panel-footer{background:#f8f9fa;padding:8px 16px;border-top:1px solid var(--border);font-size:12px;display:flex;align-items:center;justify-content:space-between;}
.gov-search-bar{background:#f5f6f8;border-bottom:1px solid var(--border);padding:10px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.gov-search-group{display:flex;align-items:center;background:#fff;border:1.5px solid var(--border-dark);border-radius:var(--radius-sm);overflow:hidden;transition:border-color .2s;box-shadow:inset 0 1px 2px rgba(0,0,0,.05);}
.gov-search-group:focus-within{border-color:var(--green-mid);box-shadow:0 0 0 2px rgba(46,125,50,.12);}
.gov-search-group-fixed{border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.14);}
.gov-search-group-fixed:focus-within{border-color:rgba(255,255,255,.75);}
.gov-search-group-fixed .fsg-icon{color:rgba(255,255,255,.7);}
.gov-search-group-fixed .fsg-input{background:transparent;color:#fff;}
.gov-search-group-fixed .fsg-input::placeholder{color:rgba(255,255,255,.55);}
.gov-search-group-fixed .fsg-btn{background:rgba(255,255,255,.22);color:#fff;}
.fsg-icon{padding:0 10px;color:#889;font-size:13px;flex-shrink:0;}
.fsg-input{flex:1;border:none;outline:none;padding:8px 4px;font-size:13px;background:transparent;color:var(--text-primary);}
.fsg-btn{border:none;padding:8px 18px;font-size:12.5px;font-weight:700;cursor:pointer;transition:background .2s;letter-spacing:.2px;}
.fsg-btn-green{background:var(--green-mid);color:#fff;}
.fsg-btn-green:hover{background:var(--green-deep);}
.gov-search-info{font-size:12px;color:var(--text-muted);white-space:nowrap;}
.btn-add-new-gov{background:var(--green-light);color:var(--green-deep);border:1.5px solid var(--green-soft);border-radius:var(--radius-sm);padding:6px 14px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;transition:all .2s;white-space:nowrap;}
.btn-add-new-gov:hover{background:var(--green-mid);color:#fff;text-decoration:none;}
.btn-add-new-gov-sm{background:var(--green-light);color:var(--green-deep);border:1.5px solid var(--green-soft);border-radius:var(--radius-sm);padding:4px 10px;font-size:11.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;transition:all .2s;}
.gov-table-wrap{overflow-x:auto;overflow-y:auto;max-height:400px;}
.gov-table{border-collapse:collapse;width:100%;font-size:12.5px;}
.gov-table thead tr th{background:#3d5166;color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;padding:9px 11px;border-right:1px solid rgba(255,255,255,.12);white-space:nowrap;position:sticky;top:0;z-index:10;}
.gov-table thead tr th:last-child{border-right:none;}
.gov-table tbody tr{border-bottom:1px solid #e8ecf0;}
.gov-table tbody tr:nth-child(odd){background:var(--row-odd);}
.gov-table tbody tr:nth-child(even){background:var(--row-even);}
.gov-table tbody tr:hover{background:var(--row-hover);}
.gov-table tbody td{padding:7px 11px;vertical-align:middle;border-right:1px solid #edf0f4;color:var(--text-primary);}
.gov-table tbody td:last-child{border-right:none;}
.sl-cell{color:#888;font-size:12px;text-align:center;}
.center-cell{text-align:center;}
.mono-cell{font-family:monospace;font-size:12px;letter-spacing:.3px;}
.addr-cell{font-size:12px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.date-cell{font-size:12px;}
.date-ago{display:block;font-size:10.5px;color:var(--text-muted);margin-top:1px;}
.code-tag{background:#e8eaf6;color:#283593;border-radius:3px;padding:2px 7px;font-size:11px;font-weight:700;font-family:monospace;border:1px solid #c5cae9;}
.rx-id-tag{background:var(--green-light);color:var(--green-deep);border-radius:3px;padding:2px 7px;font-size:11px;font-weight:700;font-family:monospace;border:1px solid var(--green-soft);}
.blood-tag{background:#ffebee;color:#c62828;border-radius:3px;padding:2px 7px;font-size:11px;font-weight:700;border:1px solid #ffcdd2;}
.sex-m{background:#e3f2fd;color:#1565C0;border-radius:3px;padding:2px 7px;font-size:11px;font-weight:700;}
.sex-f{background:#fce4ec;color:#880e4f;border-radius:3px;padding:2px 7px;font-size:11px;font-weight:700;}
.name-cell{display:flex;align-items:center;gap:8px;}
.name-avatar{width:26px;height:26px;border-radius:50%;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.name-avatar-green{background:linear-gradient(135deg,var(--green-deep),#43a047);}
.name-avatar-sm{width:24px;height:24px;font-size:10px;}
.name-text{font-size:12.5px;font-weight:600;color:var(--text-primary);}
.name-sub{font-size:11px;color:var(--text-muted);margin-top:1px;}
.btn-select{border:none;border-radius:var(--radius-sm);padding:5px 12px;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;}
.btn-select-green{background:linear-gradient(135deg,var(--green-mid),var(--green-btn));color:#fff;box-shadow:0 1px 4px rgba(46,125,50,.25);}
.btn-select-green:hover{background:linear-gradient(135deg,var(--green-deep),var(--green-mid));box-shadow:0 3px 8px rgba(46,125,50,.35);transform:translateY(-1px);}
.btn-view-rx{border:none;border-radius:var(--radius-sm);padding:5px 12px;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;}
.btn-view-green{background:linear-gradient(135deg,var(--green-mid),var(--green-btn));color:#fff;box-shadow:0 1px 4px rgba(46,125,50,.22);}
.btn-view-green:hover{background:linear-gradient(135deg,var(--green-deep),var(--green-mid));transform:translateY(-1px);}
.empty-state{text-align:center;padding:30px;color:#b0bec5;}
.empty-state i{font-size:28px;margin-bottom:8px;display:block;}
.empty-state p{font-size:13px;margin:0;}
.gov-pagination-bar{background:#f8f9fa;border-top:1px solid var(--border);padding:8px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.pagination-info{font-size:12px;color:var(--text-muted);}
.pagination{margin-bottom:0;}
.page-link{border-radius:var(--radius-sm)!important;border-color:var(--border);color:var(--green-mid);font-size:12px;padding:4px 10px;}
.page-item.active .page-link{background:var(--green-mid);border-color:var(--green-mid);}
.modern-card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow-md);border:1px solid var(--border);overflow:hidden;margin-bottom:24px;}
.modern-card-header{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#fafbfd;}
.modern-card-title{display:flex;align-items:center;gap:10px;}
.card-title-icon{width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.bg-success-soft{background:var(--green-light);}
.text-success{color:var(--green-mid)!important;}
.modern-card-body{padding:22px;}
.modern-card-footer{padding:12px 22px;border-top:1px solid var(--border);background:#fafbfd;display:flex;align-items:center;justify-content:space-between;}
.patient-selected-bar{border-radius:var(--radius-md);padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.patient-selected-bar-success{background:linear-gradient(135deg,#1B5E20 0%,#2E7D32 100%);box-shadow:0 4px 14px rgba(27,94,32,.22);}
.psb-left{display:flex;align-items:center;gap:12px;}
.psb-avatar{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.22);border:2px solid rgba(255,255,255,.55);color:#fff;font-size:18px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.psb-name{color:#fff;font-size:15px;font-weight:700;line-height:1.2;}
.psb-meta{color:rgba(255,255,255,.78);font-size:11.5px;margin-top:2px;}
.psb-right{display:flex;align-items:center;gap:10px;}
.psb-status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;}
.psb-status-dot-green{background:#a5d6a7;box-shadow:0 0 0 3px rgba(165,214,167,.3);}
.psb-status-label{color:rgba(255,255,255,.85);font-size:12px;font-weight:500;}
.btn-psb-change{background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.45);color:#fff;border-radius:var(--radius-sm);padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;}
.btn-psb-change:hover{background:rgba(255,255,255,.28);color:#fff;}
.med-load-bar{border-radius:var(--radius-sm);padding:11px 16px;margin-bottom:14px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;border:1.5px solid transparent;}
.med-load-bar.loading{background:#E3F2FD;color:var(--blue-deep);border-color:#90caf9;}
.med-load-bar.success{background:#E8F5E9;color:var(--green-deep);border-color:#a5d6a7;}
.med-load-bar.warning{background:#FFF8E1;color:#E65100;border-color:#FFE082;}
.med-load-bar.error{background:#FFEBEE;color:#c62828;border-color:#FFCDD2;}
.section-heading{display:flex;align-items:center;font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:14px;}
.section-divider{border:none;border-top:1.5px solid var(--border);}
.section-divider-full{border-top:1.5px solid var(--border);padding-top:16px;display:flex;align-items:center;justify-content:space-between;}
.modern-field-group{margin-bottom:14px;}
.modern-label{display:block;font-size:11.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;}
.modern-input{width:100%;border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;color:var(--text-primary);background:#fff;transition:border-color .2s,box-shadow .2s;outline:none;font-family:var(--font-base);}
.modern-input:focus{border-color:var(--green-mid);box-shadow:0 0 0 2px rgba(46,125,50,.12);}
.modern-textarea{resize:vertical;min-height:80px;}
.btn-med-action{border-radius:var(--radius-sm);padding:6px 12px;font-size:12px;font-weight:600;border:1.5px solid transparent;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;}
.btn-med-add-success{background:var(--green-light);color:var(--green-deep);border-color:var(--green-soft);}
.btn-med-add-success:hover{background:var(--green-mid);color:#fff;}
.btn-med-clear-success{background:#ffebee;color:#c62828;border:1.5px solid #ffcdd2;border-radius:var(--radius-sm);padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:4px;}
.btn-med-clear-success:hover{background:#c62828;color:#fff;}
.med-table-card{border-radius:var(--radius-md);border:1.5px solid var(--border);overflow:hidden;box-shadow:var(--shadow-sm);}
.selected-med-card-success{border-color:var(--green-soft);}
.med-table-card-header{padding:10px 14px;background:#f9fafb;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.med-table-dot{width:7px;height:7px;border-radius:50%;margin-right:7px;flex-shrink:0;}
.med-table-title{font-size:13px;font-weight:700;color:var(--text-primary);}
.med-count-pill{border-radius:20px;padding:2px 9px;font-size:11px;font-weight:700;margin-left:7px;}
.med-count-pill-success{background:var(--green-light);color:var(--green-deep);}
.med-table{border-collapse:collapse;width:100%;}
.med-table thead tr th{background:#f5f7fa;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);padding:8px 11px;border-bottom:1.5px solid var(--border);white-space:nowrap;}
.med-table tbody td{padding:7px 11px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;}
.med-table tbody tr:last-child td{border-bottom:none;}
.med-table tbody tr:hover{background:#f0faf0;}
.med-table .form-control{padding:4px 7px!important;font-size:12px!important;}
.med-empty-state{text-align:center;color:#b0bec5;padding:20px;font-size:12.5px;display:flex;align-items:center;justify-content:center;gap:8px;}
.avail-med-name{font-weight:600;color:var(--text-primary);font-size:12.5px;}
.avail-filter-wrap{display:flex;align-items:center;background:#fff;border:1.5px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;transition:border-color .2s;}
.avail-filter-wrap:focus-within{border-color:var(--green-mid);}
.avail-filter-icon{padding:0 8px;color:#aab;font-size:12px;}
.avail-filter-input{border:none;outline:none;padding:5px 4px;font-size:12.5px;background:transparent;width:160px;}
.modern-checkbox{width:14px;height:14px;accent-color:var(--green-mid);cursor:pointer;}
.btn-quick-add{width:24px;height:24px;border-radius:4px;border:1.5px solid transparent;font-size:10px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .18s;}
.btn-quick-add-green{background:var(--green-light);color:var(--green-deep);border-color:#a5d6a7;}
.btn-quick-add-green:hover{background:var(--green-deep);color:#fff;}
.form-footer{display:flex;align-items:center;justify-content:space-between;padding-top:18px;border-top:1.5px solid var(--border);}
.btn-footer-back{background:#fff;border:1.5px solid var(--border);color:var(--text-primary);border-radius:var(--radius-sm);padding:9px 20px;font-size:13px;font-weight:600;transition:all .2s;}
.btn-footer-back:hover{background:#f0f4f0;color:var(--text-primary);}
.btn-footer-save-success{background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;border:none;border-radius:var(--radius-sm);padding:10px 26px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(27,94,32,.28);transition:all .2s;display:inline-flex;align-items:center;gap:6px;}
.btn-footer-save-success:hover{background:linear-gradient(135deg,#0a2e0f,#1B5E20);transform:translateY(-1px);color:#fff;}
.rx-summary-card{border-radius:var(--radius-md);padding:14px 16px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow-sm);height:100%;}
.rx-card-green{background:linear-gradient(135deg,#1B5E20,#2E7D32);}
.rx-card-teal{background:linear-gradient(135deg,#00695C,#00897B);}
.rx-card-orange{background:linear-gradient(135deg,#E65100,#F57C00);}
.rx-card-blue{background:linear-gradient(135deg,#1565C0,#1976D2);}
.rx-summary-icon{width:38px;height:38px;border-radius:8px;background:rgba(255,255,255,.22);color:#fff;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rx-summary-label{color:rgba(255,255,255,.75);font-size:11px;text-transform:uppercase;letter-spacing:.6px;font-weight:600;}
.rx-summary-value{color:#fff;font-size:13.5px;font-weight:700;margin-top:2px;}
.rx-saved-badge{background:var(--green-light);color:var(--green-deep);border:1.5px solid #a5d6a7;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;}
.btn-rx-action{border-radius:var(--radius-sm);padding:7px 16px;font-size:12.5px;font-weight:600;border:1.5px solid transparent;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;}
.btn-rx-print{background:var(--blue-deep);color:#fff;border-color:var(--blue-deep);}
.btn-rx-print:hover{background:var(--blue-mid);}
.btn-rx-edit{background:#fff7e0;color:#bf360c;border-color:#ffcc80;}
.btn-rx-edit:hover{background:#bf360c;color:#fff;}
.btn-rx-new{background:#f0f4f8;color:var(--text-primary);border-color:var(--border);}
.btn-rx-new:hover{background:#e8ecf2;}
.rx-modal-content{border:none;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);}
.rx-modal-header{background:linear-gradient(135deg,#1B5E20 0%,#2E7D32 100%);border:none;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;}
.rx-modal-icon{width:38px;height:38px;border-radius:8px;background:rgba(255,255,255,.2);color:#fff;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.modal-subtitle-text{color:rgba(255,255,255,.75);font-size:11.5px;display:block;margin-top:2px;}
.btn-rx-modal-print{background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.45);color:#fff;border-radius:var(--radius-sm);padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;}
.btn-rx-modal-print:hover{background:rgba(255,255,255,.32);}
.btn-rx-modal-close{background:rgba(255,255,255,.15);border:none;color:rgba(255,255,255,.85);width:30px;height:30px;border-radius:50%;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;}
.btn-rx-modal-close:hover{background:rgba(255,255,255,.28);color:#fff;}
.rx-modal-footer{background:#fafbfd;border-top:1px solid var(--border);padding:10px 20px;display:flex;align-items:center;justify-content:space-between;}
.modal-summary-bar{display:flex;border-bottom:1px solid var(--border);}
.modal-summary-item{flex:1;padding:12px 16px;display:flex;align-items:center;gap:9px;border-right:1px solid var(--border);}
.modal-summary-item:last-child{border-right:none;}
.msi-green{background:linear-gradient(135deg,#e8f5e9,#fff);}
.msi-teal{background:linear-gradient(135deg,#e0f2f1,#fff);}
.msi-orange{background:linear-gradient(135deg,#fff3e0,#fff);}
.msi-blue{background:linear-gradient(135deg,#e3f2fd,#fff);}
.modal-summary-item>i{font-size:16px;flex-shrink:0;}
.msi-green>i{color:var(--green-deep);}
.msi-teal>i{color:var(--teal-mid);}
.msi-orange>i{color:var(--orange);}
.msi-blue>i{color:var(--blue-mid);}
.msi-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);}
.msi-val{font-size:12.5px;font-weight:700;color:var(--text-primary);margin-top:1px;}
.modal-state-wrap{text-align:center;padding:44px 20px;color:#90A4AE;}
.modal-spinner-icon{font-size:32px;margin-bottom:10px;color:var(--green-mid);}
.modal-error-icon{font-size:34px;margin-bottom:10px;color:#ef5350;}
.modal-state-text{font-size:13.5px;margin:0;}
#prescription-print-area{padding:0;background:#fff;}
.fresh-wrapper{width:100%;max-width:780px;margin:0 auto;background:#fff;border:1px solid #ccc;font-family:'Hind Siliguri',Arial,sans-serif;font-size:12px;}
.fresh-header{display:flex;justify-content:space-between;align-items:flex-start;background:linear-gradient(135deg,#e8f4fd 0%,#d0eaf8 100%);border-bottom:2px solid #4a90d9;padding:12px 16px 10px;gap:10px;}
.fresh-header-left{flex:1;}
.fresh-header-right{text-align:right;border-left:2px solid #4a90d9;padding-left:12px;flex:1;}
.fresh-logo-row{display:flex;align-items:center;gap:10px;}
.fresh-cp-logo{width:46px;height:46px;border-radius:50%;border:2px solid #c0392b;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;flex-shrink:0;background:#fff;}
.fresh-cp-c{color:#c0392b;}.fresh-cp-p{color:#2980b9;}
.fresh-clinic-bn{font-size:22px;font-weight:700;color:#2c3e50;line-height:1.1;}
.fresh-clinic-address{font-size:11px;color:#444;margin-top:2px;}
.fresh-clinic-phones{font-size:10px;color:#555;}
.fresh-doctor-title{font-size:14px;font-weight:700;color:#c0392b;}
.fresh-doctor-deg{font-size:10.5px;color:#2c3e50;}
.fresh-doctor-college{font-size:10px;color:#c0392b;margin-top:2px;}
.fresh-nad-row{display:flex;flex-wrap:wrap;background:#f8e8ee;padding:6px 16px;border-bottom:1px solid #e0aab8;gap:6px 10px;}
.fresh-nad-field{display:flex;align-items:center;gap:6px;flex:1;min-width:130px;}
.fresh-nad-label{font-weight:700;font-size:12px;white-space:nowrap;}
.fresh-nad-value{border-bottom:1px dotted #999;flex:1;padding:0 4px;font-size:12px;min-width:60px;}
.fresh-body{display:flex;min-height:400px;}
.fresh-left{width:38%;border-right:1px solid #ccc;padding:10px 12px;background:#f0f8ff;}
.fresh-section-title{font-weight:700;font-size:12px;text-decoration:underline;margin-bottom:4px;margin-top:8px;}
.fresh-section-title:first-child{margin-top:0;}
.fresh-cc-text{font-size:11.5px;color:#222;min-height:30px;padding:2px 0;}
.fresh-list{list-style:none;padding:0;margin:0;}
.fresh-list li{font-size:11.5px;line-height:1.7;display:flex;justify-content:space-between;align-items:center;padding:0 2px;}
.fresh-list li::before{content:"· ";color:#333;}
.fresh-val{font-size:11px;color:#2c3e50;font-weight:600;margin-left:4px;}
.fresh-right{flex:1;padding:10px 14px;position:relative;overflow:hidden;}
.fresh-rx-symbol{font-size:28px;font-weight:900;font-style:italic;color:#2c3e50;margin-bottom:4px;}
.fresh-footer{display:flex;justify-content:space-between;border-top:1px solid #ccc;padding:6px 16px;font-size:11px;background:#f8e8ee;color:#555;}
.fresh-med-table-wrap{overflow-x:auto;margin-top:4px;}
.fresh-med-table{width:100%;border-collapse:collapse;font-family:'Hind Siliguri',Arial,sans-serif;font-size:11px;}
.fresh-med-table th,.fresh-med-table td{border:1px solid #999;padding:3px 4px;text-align:center;vertical-align:middle;}
.fresh-th-name{text-align:left!important;width:38%;background:#f0f0f0;font-size:11px;font-weight:700;}
.fresh-th-group{background:#e8e8e8;font-weight:700;font-size:10.5px;}
.fresh-th-sub{background:#f5f5f5;font-size:10px;font-weight:600;min-width:26px;}
.fresh-med-table tbody tr td{font-size:11px;}
.fresh-med-table tbody tr td:first-child{text-align:left;padding-left:6px;}
.fresh-med-table tbody tr.empty-row td{height:22px;}
#print-overlay{display:none;position:fixed;top:0;left:0;width:100%;min-height:100%;background:#fff;z-index:9999999;padding:8mm 10mm;box-sizing:border-box;}
@media print{
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    body *{visibility:hidden;}
    #print-overlay,#print-overlay *{visibility:visible!important;}
    #print-overlay{display:block!important;position:fixed!important;top:0!important;left:0!important;width:100%!important;background:#fff!important;padding:8mm 10mm!important;box-sizing:border-box!important;}
    @page{size:A4 portrait;margin:0;}
}
</style>
@stop

@section('js')
<script>
var CSRF_TOKEN        = '{{ csrf_token() }}';
var FRESH_HISTORY_URL = '{{ url("nursing/fresh/patient-admission") }}';
var FRESH_STORE_URLS  = ['{{ url("/nursing/Fresh/store") }}','{{ url("/nursing/fresh/store") }}'];
var FRESH_DETAIL_URL  = '/nursing/fresh/detail';

var selectedMeds = [];

/* ═══════════════════════════════════════════════════
   FIXED SEARCH BAR
═══════════════════════════════════════════════════ */
(function initFixedBar(){
    var bar=document.getElementById('fixed-search-bar');
    var inlineBar=document.getElementById('inline-search-bar');
    var fixedInput=document.getElementById('patientSearchFixed');
    var inlineInput=document.getElementById('patientSearch');
    if(!bar||!inlineBar) return;
    bar.style.display='';
    function getSW(){ var sb=document.querySelector('.main-sidebar'); if(!sb) return 0; var r=sb.getBoundingClientRect(); return r.width>10?r.right:0; }
    function updatePos(){ bar.style.left=getSW()+'px'; bar.style.right='0'; bar.style.width='auto'; }
    function onScroll(){
        if(document.getElementById('panel-step1').style.display==='none'){ bar.classList.remove('visible'); return; }
        var rect=inlineBar.getBoundingClientRect();
        if(rect.bottom<=0){ updatePos(); bar.classList.add('visible'); } else { bar.classList.remove('visible'); }
    }
    if(fixedInput&&inlineInput){
        fixedInput.addEventListener('input',function(){ inlineInput.value=this.value; filterTable(); });
        inlineInput.addEventListener('input',function(){ fixedInput.value=this.value; });
    }
    window.addEventListener('scroll',onScroll,{passive:true});
    window.addEventListener('resize',function(){ updatePos(); onScroll(); });
    document.addEventListener('DOMContentLoaded',function(){ updatePos(); onScroll(); });
    document.querySelectorAll('[data-widget="pushmenu"]').forEach(function(btn){ btn.addEventListener('click',function(){ setTimeout(updatePos,320); }); });
})();

/* ═══════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════ */
function todayISO(){ return new Date().toISOString().split('T')[0]; }
function fmtDateBD(iso){ if(!iso) return '—'; var p=String(iso).slice(0,10).split('-'); return p[2]+'/'+p[1]+'/'+p[0].slice(2); }
function gVal(id){ var el=document.getElementById(id); return el?el.value.trim():''; }
function setText(id,txt){ var el=document.getElementById(id); if(el) el.textContent=(txt!==null&&txt!==undefined&&txt!=='')?txt:'—'; }
function esc(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function showAlert(type,msg){
    var el=document.getElementById('save-alert');
    el.className='alert alert-'+type+' gov-alert'; el.innerHTML=msg; el.classList.remove('d-none');
    window.scrollTo({top:0,behavior:'smooth'});
    setTimeout(function(){el.classList.add('d-none');},7000);
}
function showToast(msg,type){
    var bg=type==='success'?'#2e7d32':(type==='info'?'#0288d1':'#e65100');
    var t=document.createElement('div');
    t.style.cssText='position:fixed;bottom:20px;right:20px;z-index:9999;background:'+bg+';color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;box-shadow:0 4px 12px rgba(0,0,0,.2);transition:opacity .3s;max-width:320px;';
    t.innerHTML='<i class="fas fa-check-circle mr-2"></i>'+msg;
    document.body.appendChild(t);
    setTimeout(function(){t.style.opacity='0';setTimeout(function(){t.remove();},300);},2500);
}
function setLoadBar(type,msg){
    var bar=document.getElementById('med-load-bar');
    bar.className='med-load-bar '+type; bar.innerHTML=msg; bar.style.display='flex';
}
function hideLoadBar(){ document.getElementById('med-load-bar').style.display='none'; }

/* ═══════════════════════════════════════════════════
   PRINT
═══════════════════════════════════════════════════ */
function _doPrint(sourceId){
    var src=document.getElementById(sourceId);
    var ovl=document.getElementById('print-overlay');
    if(!src||!ovl){ window.print(); return; }
    var wrapper=src.querySelector('.fresh-wrapper');
    ovl.innerHTML=''; ovl.appendChild((wrapper||src).cloneNode(true)); ovl.style.display='block';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
        window.print();
        var cleanup=function(){ ovl.style.display='none'; ovl.innerHTML=''; window.removeEventListener('focus',cleanup); };
        window.addEventListener('focus',cleanup);
        setTimeout(function(){ ovl.style.display='none'; ovl.innerHTML=''; window.removeEventListener('focus',cleanup); },60000);
    }); });
}
function printRx()    { _doPrint('prescription-print-area'); }
function printModal() { _doPrint('modal-prescription-print-area'); }

/* ═══════════════════════════════════════════════════
   DOCTOR HEADER
═══════════════════════════════════════════════════ */
function updateDoctorHeader(){
    var sel=document.getElementById('f-doctor'); if(!sel||!sel.options.length) return;
    var d=sel.options[sel.selectedIndex].dataset;
    setText('rx-doctor-name',       d.docname    ||'—');
    setText('rx-doctor-speciality', d.speciality ||'');
    setText('rx-doctor-regno',      d.doctorno   ?'Reg No: '+d.doctorno:'');
    setText('rx-doctor-posting',    d.posting    ||'');
    setText('rx-doctor-contact',    d.contact    ?'Mobile: '+d.contact:'');
    setText('ib-doctor',            d.docname    ||'—');
}

/* ═══════════════════════════════════════════════════
   MEDICINE TABLE
═══════════════════════════════════════════════════ */
function refreshSelTable(){
    var tbody=document.getElementById('sel-med-tbody');
    document.getElementById('sel-med-badge').textContent=selectedMeds.length;
    document.getElementById('med-count-badge').textContent=selectedMeds.length;
    if(!selectedMeds.length){
        tbody.innerHTML='<tr id="empty-sel-row"><td colspan="9"><div class="med-empty-state"><i class="fas fa-pills" style="color:#43a047;"></i><span>No medicines selected yet.</span></div></td></tr>';
        return;
    }
    tbody.innerHTML=selectedMeds.map(function(m,i){
        return '<tr>'+
            '<td>'+(i+1)+'</td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.medicine_name||'')+'" onchange="selectedMeds['+i+'].medicine_name=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.dose||'')+'" onchange="selectedMeds['+i+'].dose=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.route||'')+'" onchange="selectedMeds['+i+'].route=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.frequency||'')+'" onchange="selectedMeds['+i+'].frequency=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.duration||'')+'" onchange="selectedMeds['+i+'].duration=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.timing||'')+'" onchange="selectedMeds['+i+'].timing=this.value"></td>'+
            '<td><input type="text" class="form-control form-control-sm" value="'+esc(m.remarks||'')+'" onchange="selectedMeds['+i+'].remarks=this.value" placeholder="Optional"></td>'+
            '<td class="center-cell"><button type="button" class="btn-quick-add" style="background:#ffebee;color:#c62828;border-color:#ffcdd2;" onclick="removeMed('+i+')"><i class="fas fa-times"></i></button></td>'+
        '</tr>';
    }).join('');
}
function addMedToList(name,dose,route,freq,dur,timing,remarks){
    if(!name||!name.trim()) return;
    if(selectedMeds.find(function(m){ return m.medicine_name.toLowerCase()===name.toLowerCase(); })) return;
    selectedMeds.push({medicine_name:name,dose:dose||'',route:route||'',frequency:freq||'',duration:dur||'',timing:timing||'',remarks:remarks||''});
    refreshSelTable();
}
function addMedRow(){ selectedMeds.push({medicine_name:'',dose:'',route:'',frequency:'',duration:'',timing:'',remarks:''}); refreshSelTable(); }
function removeMed(idx){
    var n=selectedMeds[idx]?selectedMeds[idx].medicine_name:'';
    selectedMeds.splice(idx,1);
    // uncheck from avail table if name matches
    document.querySelectorAll('.avail-med-cb').forEach(function(cb){
        if((cb.dataset.name||'').toLowerCase()===(n||'').toLowerCase()) cb.checked=false;
    });
    refreshSelTable();
}
function clearAllMeds(){
    if(!selectedMeds.length) return;
    if(!confirm('সব medicine মুছে ফেলবেন?')) return;
    selectedMeds=[];
    document.querySelectorAll('.avail-med-cb').forEach(function(cb){cb.checked=false;});
    var sa=document.getElementById('select-all-med'); if(sa) sa.checked=false;
    document.getElementById('auto-loaded-note').classList.add('d-none');
    hideLoadBar(); refreshSelTable();
}
function onAvailMedChange(cb){
    if(cb.checked){
        addMedToList(cb.dataset.name,cb.dataset.dose,cb.dataset.route,
                     cb.dataset.frequency,cb.dataset.duration,cb.dataset.timing,cb.dataset.note);
    } else {
        selectedMeds=selectedMeds.filter(function(m){
            return m.medicine_name.toLowerCase()!==(cb.dataset.name||'').toLowerCase();
        });
        refreshSelTable();
    }
}
function quickAdd(btn){
    var cb=btn.closest('tr').querySelector('.avail-med-cb');
    cb.checked=true; onAvailMedChange(cb);
}

/* ═══════════════════════════════════════════════════
   DOM READY
═══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded',function(){
    var mf=document.getElementById('med-filter');
    if(mf) mf.addEventListener('input',function(){
        var q=this.value.toLowerCase();
        document.querySelectorAll('.avail-med-row').forEach(function(r){
            r.style.display=(r.dataset.name||'').includes(q)?'':'none';
        });
    });
    var sa=document.getElementById('select-all-med');
    if(sa) sa.addEventListener('change',function(){
        document.querySelectorAll('.avail-med-cb').forEach(function(cb){
            cb.checked=sa.checked; onAvailMedChange(cb);
        });
    });
    var ps=document.getElementById('patientSearch');
    if(ps) ps.addEventListener('keyup',filterTable);
    updateDoctorHeader();
});

/* ═══════════════════════════════════════════════════
   SELECT PATIENT
═══════════════════════════════════════════════════ */
function selectPatient(btn){
    var d=btn.dataset;
    document.getElementById('f-patient-id').value   = d.id    ||'';
    document.getElementById('f-patient-code').value = d.code  ||'';
    document.getElementById('f-patient-name').value = d.name  ||'';
    document.getElementById('f-patient-age').value  = d.age   ||'';
    document.getElementById('f-date').value          = todayISO();
    document.getElementById('spb-avatar').textContent=(d.name||'P').charAt(0).toUpperCase();
    document.getElementById('spb-name').textContent  = d.name||'—';
    document.getElementById('spb-meta').textContent  = [d.code,d.age,d.mobile,d.blood,d.upozila].filter(Boolean).join(' · ');
    document.getElementById('step1-circle').className='step-node step-node-done';
    document.getElementById('step1-circle').innerHTML='<i class="fas fa-check" style="font-size:11px;"></i>';
    document.getElementById('step-connector').classList.add('done');
    document.getElementById('step2-circle').className='step-node step-node-active';
    document.getElementById('step2-label').className='step-title step-title-active';
    document.getElementById('breadcrumb-current').textContent='Prescription Entry';
    document.getElementById('panel-step1').style.display='none';
    document.getElementById('panel-step2').style.display='block';
    document.getElementById('rx-view').style.display='none';
    document.getElementById('rx-form-card').style.display='block';
    document.getElementById('fixed-search-bar').classList.remove('visible');

    // Reset
    selectedMeds=[];
    document.querySelectorAll('.avail-med-cb').forEach(function(cb){cb.checked=false;});
    var sa=document.getElementById('select-all-med'); if(sa) sa.checked=false;
    document.getElementById('auto-loaded-note').classList.add('d-none');
    hideLoadBar(); refreshSelTable(); updateDoctorHeader();

    // ★ Auto-load from template_medicine (hidden #template-med-data)
    autoLoadTemplateMedicines();
    // ★ Then merge with patient history
    fetchAndAutoLoadMedicines(d.id);
    window.scrollTo({top:0,behavior:'smooth'});
}

/* ═══════════════════════════════════════════════════
   ★ AUTO-LOAD — template_medicine (order_type='fresh prescription')
     Reads from hidden #template-med-data spans.
     Does NOT tick avail-med-cb (those are common_medicine rows).
═══════════════════════════════════════════════════ */
function autoLoadTemplateMedicines(){
    selectedMeds = [];

    document.querySelectorAll('#template-med-data .template-med-item').forEach(function(el){
        var name = (el.dataset.name || '').trim();
        if(!name) return;
        selectedMeds.push({
            medicine_name : el.dataset.name      || '',
            dose          : el.dataset.dose       || '',
            route         : el.dataset.route      || '',
            frequency     : el.dataset.frequency  || '',
            duration      : el.dataset.duration   || '',
            timing        : el.dataset.timing     || '',
            remarks       : el.dataset.note       || ''
        });
    });

    // avail-med-cb (common_medicine) সব uncheck রাখো
    document.querySelectorAll('.avail-med-cb').forEach(function(cb){
        cb.checked = false;
    });

    refreshSelTable();

    if(selectedMeds.length > 0){
        document.getElementById('auto-loaded-note').classList.remove('d-none');
        setLoadBar('success',
            '<i class="fas fa-pills mr-2"></i>' +
            '<strong>' + selectedMeds.length + ' টি medicine</strong> template (fresh prescription) থেকে auto-load হয়েছে। ' +
            '<small style="opacity:.8;">Patient history cross-check চলছে...</small>'
        );
    } else {
        setLoadBar('warning',
            '<i class="fas fa-exclamation-circle mr-2"></i>' +
            'Template এ কোনো fresh prescription medicine নেই। নিচ থেকে manually যোগ করুন।'
        );
    }
}

/* ═══════════════════════════════════════════════════
   FETCH PATIENT HISTORY & MERGE
   Cross-checks template medicines (selectedMeds) with
   admission/post-surgery/fresh history records and
   fills in dose/freq if currently empty.
═══════════════════════════════════════════════════ */
function fetchAndAutoLoadMedicines(patientId){
    fetch(FRESH_HISTORY_URL + '/' + patientId, {
        headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF_TOKEN}
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(!data.success){
            setLoadBar('success',
                '<i class="fas fa-check-circle mr-2"></i>' +
                '<strong>' + selectedMeds.length + ' টি medicine</strong> template থেকে loaded। Patient history পাওয়া যায়নি।'
            );
            return;
        }
        processAndLoadMedicines(data);
    })
    .catch(function(err){
        console.error('History fetch error:', err);
        setLoadBar('success',
            '<i class="fas fa-check-circle mr-2"></i>' +
            '<strong>' + selectedMeds.length + ' টি medicine</strong> template থেকে loaded। History load এ সমস্যা।'
        );
    });
}

function processAndLoadMedicines(data){
    /*
     * selectedMeds এ template medicines আছে।
     * History থেকে matching medicine এর dose/freq update করো (যদি খালি থাকে)।
     */
    function mergeHistoryDose(rawName, dose, freq, dur, timing){
        if(!rawName||!rawName.trim()) return;
        var lower = rawName.trim().toLowerCase();
        var existing = selectedMeds.find(function(m){
            var mn = m.medicine_name.toLowerCase();
            return mn===lower || mn.includes(lower) || lower.includes(mn);
        });
        if(existing){
            if(!existing.dose      && dose)   existing.dose      = dose;
            if(!existing.frequency && freq)   existing.frequency = freq;
            if(!existing.duration  && dur)    existing.duration  = dur;
            if(!existing.timing    && timing) existing.timing    = timing;
        }
    }

    // 1. Admission medicines
    (data.medicines||[]).forEach(function(m){
        mergeHistoryDose(m.medicine_name||m.name||'', m.dose, m.frequency, m.duration, m.timing);
    });
    // 2. Post-surgery medicines
    (data.post_surgery_medicines||[]).forEach(function(m){
        mergeHistoryDose(m.medicine_name||m.name||'', m.dose, m.frequency, m.duration, m.timing);
    });
    // 3. Previous fresh prescriptions
    (data.fresh_prescriptions||[]).forEach(function(rx){
        (rx.lines||[]).forEach(function(line){
            var name=(line.trim().split(/\s+/)[0])||'';
            mergeHistoryDose(name,'','','','');
        });
    });

    refreshSelTable();

    var count = selectedMeds.length;
    if(count > 0){
        document.getElementById('auto-loaded-note').classList.remove('d-none');
        setLoadBar('success',
            '<i class="fas fa-check-circle mr-2"></i>' +
            '<strong>' + count + ' টি medicine</strong> loaded (template + history merge)।' +
            ' <small style="opacity:.8;">প্রয়োজনে নিচের list থেকে আরো যোগ করুন বা সম্পাদনা করুন।</small>'
        );
    } else {
        setLoadBar('warning',
            '<i class="fas fa-exclamation-circle mr-2"></i>' +
            'কোনো medicine পাওয়া যায়নি। নিচের list থেকে manually যোগ করুন।'
        );
    }
}

/* ═══════════════════════════════════════════════════
   SAVE & GENERATE
═══════════════════════════════════════════════════ */
function saveAndGenerateRx(){
    var patientId=gVal('f-patient-id');
    if(!patientId){ showAlert('warning','Please select a patient first.'); return; }
    var medsToSave=selectedMeds.filter(function(m){return m.medicine_name.trim()!=='';});
    var medsPayload=medsToSave.map(function(m){
        return {id:'fresh_'+Date.now(),name:m.medicine_name,strength:'',
                dose:m.dose,route:m.route,frequency:m.frequency,
                duration:m.duration,timing:m.timing,note:m.remarks};
    });
    var doctorSel=document.getElementById('f-doctor');
    var doctorName=doctorSel&&doctorSel.options.length?doctorSel.options[doctorSel.selectedIndex].dataset.docname||'':'';
    var payload={
        patient_id:patientId, patient_name:gVal('f-patient-name'),
        patient_age:gVal('f-patient-age'), patient_code:gVal('f-patient-code'),
        doctor_name:doctorName, prescription_date:gVal('f-date'),
        rx_text:gVal('f-notes'), notes:gVal('f-notes'), medicines:medsPayload
    };
    var btn=document.getElementById('btn-save-rx');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    function trySave(idx){
        if(idx>=FRESH_STORE_URLS.length){
            btn.disabled=false; btn.innerHTML='<i class="fas fa-save mr-1"></i> Save &amp; Generate Prescription';
            generateRxView(); return;
        }
        fetch(FRESH_STORE_URLS[idx],{method:'POST',
            headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify(payload)})
        .then(function(r){ if(r.status===404){trySave(idx+1);return null;} return r.json(); })
        .then(function(data){
            btn.disabled=false; btn.innerHTML='<i class="fas fa-save mr-1"></i> Save &amp; Generate Prescription';
            if(!data) return;
            generateRxView();
            if(data.success) showToast('Saved! ID: #'+data.prescription_id,'success');
        })
        .catch(function(){trySave(idx+1);});
    }
    trySave(0);
}

/* ═══════════════════════════════════════════════════
   GENERATE RX VIEW
═══════════════════════════════════════════════════ */
function generateRxView(){
    var pName=gVal('f-patient-name')||'—'; var pAge=gVal('f-patient-age')||'—';
    var pDate=fmtDateBD(gVal('f-date')); var pCode=gVal('f-patient-code')||'—';
    setText('ib-name',pName); setText('ib-age',pAge); setText('ib-date',pDate);
    setText('rx-name',pName); setText('rx-age',pAge); setText('rx-date',pDate);
    setText('rx-code',pCode); setText('rx-badge-name',pName);
    setText('rx-cc', gVal('f-cc')||'');
    setText('rx-pulse','—'); setText('rx-bp','—');
    var rxTextEl=document.getElementById('rx-rx-text');     if(rxTextEl) rxTextEl.textContent=gVal('f-notes')||'';
    var rxNotesEl=document.getElementById('rx-notes-print'); if(rxNotesEl) rxNotesEl.textContent='';
    updateDoctorHeader();
    renderRxMedicines('rx-med-print-tbody');
    setText('gen-time',new Date().toLocaleString('en-BD',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}));
    document.getElementById('step2-circle').className='step-node step-node-done';
    document.getElementById('step2-circle').innerHTML='<i class="fas fa-check" style="font-size:11px;"></i>';
    document.getElementById('rx-form-card').style.display='none';
    document.getElementById('rx-view').style.display='block';
    window.scrollTo({top:0,behavior:'smooth'});
}

/* ═══════════════════════════════════════════════════
   RENDER MEDICINE PRINT TABLE
═══════════════════════════════════════════════════ */
function renderRxMedicines(tbodyId){
    var tbody=document.getElementById(tbodyId||'rx-med-print-tbody'); if(!tbody) return; tbody.innerHTML='';
    var printMeds=selectedMeds.filter(function(m){return m.medicine_name.trim();});
    printMeds.forEach(function(m){
        var morning='',noon='',night='';
        var freq=(m.frequency||'').trim();
        var dp=freq.match(/^(\S+)\+(\S+)\+(\S+)/);
        if(dp){morning=dp[1];noon=dp[2];night=dp[3];}else if(freq){morning=freq;}
        var timing=(m.timing||'').toLowerCase();
        var before=(timing.includes('before')||timing.includes('আগে'))?'✓':'';
        var after=(timing.includes('after')||timing.includes('পরে'))?'✓':'';
        var duration=esc(m.duration||'');
        var name=m.medicine_name; if(m.route&&m.route!=='') name=m.route+' '+name;
        var instruction=esc(m.instruction||m.note||'');
        var tr=document.createElement('tr');
        tr.innerHTML='<td style="text-align:left;padding-left:6px;">• '+esc(name)+'</td>'+
            '<td>'+morning+'</td><td>'+noon+'</td><td>'+night+'</td>'+
            '<td>'+before+'</td><td>'+after+'</td>'+
            '<td>'+duration+'</td>'+
            '<td style="text-align:left;padding-left:6px;">'+instruction+'</td>';
        tbody.appendChild(tr);
    });
    var needed=Math.max(0,8-printMeds.length);
    for(var i=0;i<needed;i++){
        var tr=document.createElement('tr');tr.className='empty-row';
        tr.innerHTML='<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
        tbody.appendChild(tr);
    }
}

/* ═══════════════════════════════════════════════════
   NAVIGATION
═══════════════════════════════════════════════════ */
function backToStep1(){
    document.getElementById('step1-circle').className='step-node step-node-active';
    document.getElementById('step1-circle').innerHTML='<span class="step-num">1</span>';
    document.getElementById('step-connector').classList.remove('done');
    document.getElementById('step2-circle').className='step-node step-node-idle';
    document.getElementById('step2-label').className='step-title step-title-idle';
    document.getElementById('breadcrumb-current').textContent='Patient Selection';
    document.getElementById('panel-step1').style.display='block';
    document.getElementById('panel-step2').style.display='none';
    hideLoadBar();
    window.scrollTo({top:0,behavior:'smooth'});
}
function editRx(){
    document.getElementById('rx-view').style.display='none';
    document.getElementById('rx-form-card').style.display='block';
    window.scrollTo({top:0,behavior:'smooth'});
}

/* ═══════════════════════════════════════════════════
   TABLE FILTERS
═══════════════════════════════════════════════════ */
function filterTable(){
    var q=document.getElementById('patientSearch').value.toLowerCase();
    document.getElementById('patientSearchFixed').value=q; _doFilter(q);
}
function filterTableFixed(){
    var q=document.getElementById('patientSearchFixed').value.toLowerCase();
    document.getElementById('patientSearch').value=q; _doFilter(q);
}
function _doFilter(q){
    var visible=0;
    document.querySelectorAll('#patientTable tbody tr.patient-row').forEach(function(row){
        var show=row.textContent.toLowerCase().includes(q);
        row.style.display=show?'':'none'; if(show) visible++;
    });
    var vc=document.getElementById('visible-count'); if(vc) vc.textContent=visible;
}
function filterFreshRxTable(){
    var q=(document.getElementById('freshRxSearch').value||'').toLowerCase();
    document.querySelectorAll('#freshRxTable tbody tr.fresh-rx-row').forEach(function(row){
        row.style.display=row.textContent.toLowerCase().includes(q)?'':'none';
    });
}

/* ═══════════════════════════════════════════════════
   VIEW PAST FRESH PRESCRIPTION MODAL
═══════════════════════════════════════════════════ */
function viewFreshPrescription(prescriptionId){
    document.getElementById('modal-loading').classList.remove('d-none');
    document.getElementById('modal-error').classList.add('d-none');
    document.getElementById('modal-rx-area').classList.add('d-none');
    document.getElementById('modal-subtitle').textContent='Loading...';
    $('#rxViewModal').modal('show');
    $.ajax({url:FRESH_DETAIL_URL+'/'+prescriptionId,method:'GET',dataType:'json'})
    .done(function(res){
        if(!res.success||!res.data){showModalError(res.message||'Record not found.');return;}
        populateFreshModal(res.data);
    })
    .fail(function(xhr){ showModalError('Failed to load prescription (HTTP '+xhr.status+')'); });
}
function showModalError(msg){
    document.getElementById('modal-loading').classList.add('d-none');
    document.getElementById('modal-error').classList.remove('d-none');
    document.getElementById('modal-error-msg').textContent=msg;
}
function populateFreshModal(d){
    document.getElementById('modal-subtitle').textContent=(d.patient_name||'—')+'  ·  '+(d.patient_code||d.p_code||'—');
    setText('m-ib-name',d.patient_name); setText('m-ib-age',d.patient_age);
    setText('m-ib-date',fmtDateBD(d.prescription_date||d.created_at)); setText('m-ib-id','#'+d.id);
    setText('m-rx-code',d.patient_code||d.p_code||'—'); setText('m-rx-name',d.patient_name);
    setText('m-rx-age',d.patient_age); setText('m-rx-date',fmtDateBD(d.prescription_date||d.created_at));
    setText('m-rx-doctor-name',d.doctor_name||'—'); setText('m-rx-doctor-speciality',d.doctor_speciality||'');
    setText('m-rx-doctor-regno',d.doctor_regno?'Reg No: '+d.doctor_regno:'');
    setText('m-rx-doctor-posting',d.doctor_posting||'');
    setText('m-rx-doctor-contact',d.doctor_contact?'Mobile: '+d.doctor_contact:'');
    setText('m-rx-cc',d.cc||'—'); setText('m-rx-pulse',d.pulse||'—'); setText('m-rx-bp',d.bp||'—');
    var rxTextEl=document.getElementById('m-rx-rx-text'); if(rxTextEl) rxTextEl.textContent=d.rx_text||d.notes||'';
    var notesEl=document.getElementById('m-rx-notes'); if(notesEl) notesEl.textContent=d.notes||'';
    var meds=[];
    if(d.medicines_decoded&&Array.isArray(d.medicines_decoded)) meds=d.medicines_decoded;
    else if(typeof d.medicines==='string'){try{meds=JSON.parse(d.medicines);}catch(e){meds=[];}}
    else if(Array.isArray(d.medicines)) meds=d.medicines;
    var normMeds=meds.filter(function(m){return m&&((m.medicine_name||m.name||'').trim());}).map(function(m){
        return {medicine_name:m.medicine_name||m.name||'',dose:m.dose||'',route:m.route||'',
                frequency:m.frequency||'',duration:m.duration||'',
                timing:m.timing||m.instruction||'',remarks:m.note||m.remarks||''};
    });
    var savedMeds=selectedMeds; selectedMeds=normMeds;
    renderRxMedicines('m-rx-med-print-tbody');
    selectedMeds=savedMeds;
    setText('m-saved-time',d.created_at?new Date(d.created_at).toLocaleString('en-BD',
        {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}):'—');
    document.getElementById('modal-loading').classList.add('d-none');
    document.getElementById('modal-rx-area').classList.remove('d-none');
}
</script>
@stop