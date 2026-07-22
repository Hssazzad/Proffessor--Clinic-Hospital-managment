@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Pre-Con Assessment')

@push('css')
<style>
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #6b7280 !important;
        border-radius: 6px !important;
        min-height: 38px !important;
        padding: 4px !important;
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
        <div class="card card-outline card-success shadow">
            <div class="card-header">
                <h5>Clinical Vitals Entry</h5>
            </div>
            <div class="card-body">
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><b>Weight</b></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" name="weight"
                                           class="form-control" value="{{ old('weight') }}" placeholder="0.0">
                                    <div class="input-group-append"><span class="input-group-text">kg</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><b>Height</b></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" name="height"
                                           class="form-control" value="{{ old('height') }}" placeholder="0.0">
                                    <div class="input-group-append"><span class="input-group-text">cm</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>BP Systolic</b></label>
                                <input type="number" min="0" name="bp_sys"
                                       class="form-control" value="{{ old('bp_sys') }}" placeholder="120">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>BP Diastolic</b></label>
                                <input type="number" min="0" name="bp_dia"
                                       class="form-control" value="{{ old('bp_dia') }}" placeholder="80">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>SpO₂ (%)</b></label>
                                <input type="number" min="0" max="100" name="spo2"
                                       class="form-control" value="{{ old('spo2') }}" placeholder="98">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>Temperature</b></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" name="temp"
                                           class="form-control" value="{{ old('temp') }}" placeholder="98.6">
                                    <div class="input-group-append"><span class="input-group-text">°F</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>Pulse (BPM)</b></label>
                                <input type="number" min="0" name="pulse"
                                       class="form-control" value="{{ old('pulse') }}" placeholder="72">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><b>Resp. Rate (RPM)</b></label>
                                <input type="number" min="0" name="rr"
                                       class="form-control" value="{{ old('rr') }}" placeholder="16">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><b>Observations &amp; Complaints</b></label>
                        <textarea name="notes" rows="3" class="form-control"
                                  placeholder="Describe any visible symptoms or patient complaints...">{{ old('notes') }}</textarea>
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
