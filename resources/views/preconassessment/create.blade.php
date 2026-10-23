@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Pre-Con Assessment')

@push('css')
<style>
    /* ====== Hospital EMR — Pre-Con Assessment ====== */
    :root {
        --pca-primary: #2563eb;
        --pca-border: #e2e8f0;
        --pca-border-strong: #cbd5e1;
        --pca-text: #0f172a;
        --pca-text-muted: #64748b;
        --pca-soft: #f8fafc;
        --pca-danger: #dc2626;
    }

    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #6b7280 !important;
        border-radius: 6px !important;
        min-height: 38px !important;
        padding: 4px !important;
    }

    /* Card shell */
    .pca-card {
        background: #fff;
        border: 1px solid var(--pca-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -16px rgba(15, 23, 42, .16);
    }
    .pca-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 20px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-bottom: 1px solid var(--pca-border);
    }
    .pca-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--pca-text);
        letter-spacing: -.01em;
    }
    .pca-card-title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eff6ff;
        color: var(--pca-primary);
        font-size: .95rem;
    }
    .pca-card-badge {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--pca-primary);
        background: #eff6ff;
        border: 1px solid #dbeafe;
        padding: 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .pca-card-body {
        padding: 20px;
    }

    /* Section block */
    .pca-section {
        margin-top: 20px;
    }
    .pca-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--pca-text-muted);
    }
    .pca-section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--pca-border);
    }

    /* O/E field rows */
    .oe-field {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 54px;
        padding: 8px 12px;
        background: var(--pca-soft);
        border: 1px solid var(--pca-border);
        border-radius: 10px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .oe-field:hover {
        border-color: #d3dce8;
    }
    .oe-field:focus-within {
        border-color: var(--pca-primary);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }
    .oe-label {
        flex: 0 0 42%;
        min-width: 0;
        margin: 0;
        padding-right: 8px;
        font-size: .8rem;
        font-weight: 600;
        color: var(--pca-text);
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .oe-input-wrap {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .oe-input {
        flex: 1 1 auto;
        min-width: 0;
        height: 38px;
        padding: 6px 12px;
        font-size: .9rem;
        font-weight: 500;
        color: var(--pca-text);
        background: #fff;
        border: 1px solid var(--pca-border-strong);
        border-radius: 8px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .oe-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
    .oe-input:focus {
        outline: none;
        border-color: var(--pca-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }
    .oe-unit {
        flex: 0 0 auto;
        min-width: 34px;
        text-align: right;
        font-size: .74rem;
        font-weight: 600;
        color: var(--pca-text-muted);
        white-space: nowrap;
    }

    /* Empty state */
    .oe-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 160px;
        text-align: center;
        color: var(--pca-text-muted);
        background: var(--pca-soft);
        border: 1px dashed var(--pca-border-strong);
        border-radius: 12px;
    }
    .oe-empty i {
        font-size: 2rem;
        color: #94a3b8;
    }
    .oe-empty p {
        margin: 0;
        font-size: .9rem;
        font-weight: 600;
    }

    @media (max-width: 575.98px) {
        .pca-card-header { padding: 14px 16px; }
        .pca-card-body { padding: 16px; }
        .oe-label { flex-basis: 40%; }
    }
</style>
@endpush

@section('content_header')
    <h1 class="text-primary font-weight-bold">
        Pre-Consultation Assessment
        <span class="badge badge-info float-right" style="font-size:14px;">Vitals Entry</span>
    </h1>
    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm mt-2">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('content')

<div id="ajaxMessage"></div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // $oeFields comes from the controller, already enriched with latest saved values
    $oeMasters = isset($oeFields) && $oeFields->isNotEmpty() ? $oeFields : collect();
@endphp

<div class="row">
    <div class="col-md-7">

        <div class="card card-outline card-warning shadow">
            <div class="card-header">
                <h5>Patient Search</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('prescriptions.preconassessment') }}">
                    <div class="input-group mb-3">
                        <input type="text" name="q" class="form-control"
                               placeholder="Search by Name / Patient Code / Mobile..."
                               value="{{ $q ?? request('q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('prescriptions.preconassessment') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                @if(isset($patients) && count($patients) > 0)
                <div style="overflow-x:auto; max-height:300px; overflow-y:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead style="position:sticky;top:0;z-index:5;background:#fff;">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $i => $p)
                            <tr class="{{ request('patientcode') == $p->patientcode ? 'table-warning' : '' }}">
                                <td>{{ $i+1 }}</td>
                                <td class="text-primary font-weight-bold">{{ $p->patientcode }}</td>
                                <td>{{ $p->patientname }}</td>
                                <td>{{ $p->age ?? '—' }}</td>
                                <td>{{ $p->mobile_no ?? '—' }}</td>
                                <td>
                                    <a href="?patientcode={{ $p->patientcode }}&q={{ $q ?? request('q') }}"
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-arrow-right"></i> Select
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif(request('q'))
                    <div class="text-muted text-center py-4">No patients match your search.</div>
                @else
                    <div class="text-muted text-center py-4">Type a name or ID above to search.</div>
                @endif
            </div>
        </div>

        @if(request('patientcode'))
        <div class="pca-card">
            <div class="pca-card-header">
                <h5 class="pca-card-title">
                    <i class="fas fa-heartbeat"></i>
                    <span>Clinical Vitals</span>
                </h5>
                <span class="pca-card-badge">O/E Assessment</span>
            </div>
            <div class="pca-card-body">
                <form method="POST" action="{{ route('prescriptions.preconassessment.save') }}">
                    @csrf
                    <input type="hidden" name="patientcode" value="{{ request('patientcode') }}">

                    <div class="form-group">
                        <label><b>Selected Patient</b></label>
                        <div class="alert alert-success py-2 mb-0">
                            <strong>{{ $selectedPatient->patientname ?? '' }}</strong>
                            ({{ request('patientcode') }})
                            — Age: {{ $selectedPatient->age ?? '—' }}
                            — Mobile: {{ $selectedPatient->mobile_no ?? '—' }}
                            <a href="{{ route('prescriptions.preconassessment') }}" class="float-right text-danger">
                                <i class="fas fa-times"></i> Change
                            </a>
                        </div>
                    </div>

                    <div class="pca-section">
                        <h6 class="pca-section-title">
                            <i class="fas fa-stethoscope"></i> Objective Examination
                        </h6>

                        @if($oeMasters->count())
                        <div class="row">
                            @foreach($oeMasters as $oe)
                                @php
                                    $isNumeric = in_array($oe->input_type ?? 'text', ['number', 'decimal'], true);
                                    $step = ($oe->input_type ?? 'text') === 'decimal' ? '0.01' : '1';
                                @endphp
                                <div class="col-12 col-md-6 mb-3">
                                    <div class="oe-field">
                                        <label class="oe-label" for="oe-{{ $oe->id }}"
                                               title="{{ $oe->name }}">{{ $oe->name }}</label>
                                        <div class="oe-input-wrap">
                                            <input type="{{ $isNumeric ? 'number' : 'text' }}"
                                                   id="oe-{{ $oe->id }}"
                                                   name="oe_values[{{ $oe->id }}]"
                                                   class="oe-input"
                                                   @if($isNumeric) step="{{ $step }}" min="0" @endif
                                                   placeholder="—"
                                                   value="{{ old('oe_values.' . $oe->id, $oe->value ?? null) }}">
                                            @if($oe->unit)
                                                <span class="oe-unit">{{ $oe->unit }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @else
                        <div class="oe-empty">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No O/E fields configured.</p>
                        </div>
                        @endif
                    </div>

                    <div class="pca-section">
                        <h6 class="pca-section-title">
                            <i class="fas fa-comment-medical"></i> Notes
                        </h6>
                        <div class="form-group mb-0">
                            <textarea name="notes" rows="3" class="form-control"
                                      placeholder="Describe any visible symptoms or patient complaints...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Confirm &amp; Save Record
                    </button>
                    <a href="{{ route('prescriptions.preconassessment') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
        @endif

    </div>

    <div class="col-md-5">
        @if(isset($selectedPatient))
        <div class="card card-outline card-info shadow">
            <div class="card-header">
                <h5>Patient Profile</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width:100px;">Name</th><td>: <strong>{{ $selectedPatient->patientname ?? '—' }}</strong></td></tr>
                    <tr><th>Code</th><td>: <span class="text-primary">{{ $selectedPatient->patientcode ?? '—' }}</span></td></tr>
                    <tr><th>Age</th><td>: {{ $selectedPatient->age ?? '—' }}</td></tr>
                    <tr><th>Gender</th><td>: {{ $selectedPatient->gender ?? '—' }}</td></tr>
                    <tr><th>Mobile</th><td>: {{ $selectedPatient->mobile_no ?? '—' }}</td></tr>
                    <tr><th>Blood Group</th><td>: {{ $selectedPatient->blood_group ?? '—' }}</td></tr>
                    <tr><th>Address</th><td>: {{ $selectedPatient->address ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
        @endif

        <div class="card card-outline card-primary shadow">
            <div class="card-header">
                <h5>
                    Assessment History
                    <span class="badge badge-primary float-right" id="recordCount">
                        {{ isset($assessmentHistory) ? $assessmentHistory->count() : 0 }}
                    </span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if(isset($assessmentHistory) && $assessmentHistory->count() > 0)
                <div style="overflow-x:auto; max-height:420px; overflow-y:auto;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead style="position:sticky;top:0;z-index:5;background:#fff;">
                            <tr>
                                <th>Date</th>
                                <th>Wt</th>
                                <th>Ht</th>
                                <th>BMI</th>
                                <th>BP</th>
                                <th>Pulse</th>
                                <th>SpO₂</th>
                                <th>Temp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessmentHistory as $r)
                            @php
                                $bmi = ($r->weight && $r->height)
                                    ? number_format($r->weight / (($r->height/100) ** 2), 1)
                                    : '—';
                            @endphp
                            <tr>
                                <td style="white-space:nowrap;">{{ $r->created_at->format('d/m h:i A') }}</td>
                                <td>{{ $r->weight ?? '—' }}</td>
                                <td>{{ $r->height ?? '—' }}</td>
                                <td><span class="text-success font-weight-bold">{{ $bmi }}</span></td>
                                <td><span class="text-danger font-weight-bold">{{ $r->bp_sys ?? '—' }}/{{ $r->bp_dia ?? '—' }}</span></td>
                                <td>{{ $r->pulse ?? '—' }}</td>
                                <td><span class="text-info font-weight-bold">{{ $r->spo2 ?? '—' }}%</span></td>
                                <td>{{ $r->temp ?? '—' }}°F</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-muted text-center py-4">
                    <i class="fas fa-notes-medical fa-2x d-block mb-2"></i>
                    No assessment records found.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@stop

@push('js')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    $('.vital-input').on('keydown', function(e) {
        if (e.key === '-' || e.key === 'e' || e.key === 'E') e.preventDefault();
    });
    $('.vital-input').on('input', function() {
        var val = parseFloat($(this).val());
        if (!isNaN(val) && val < 0) $(this).val('');
    });
});
</script>
@endpush
