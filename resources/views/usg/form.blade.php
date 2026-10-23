@extends('adminlte::page')

@section('title', $report ? 'Edit USG Report' : 'Add USG Report')

@section('content_header')
<h1>{{ $report ? 'Edit USG Report' : 'Add USG Report' }}</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST"
              action="{{ $report ? route('usg-reports.update', $report->id) : route('usg-reports.store') }}">
            @csrf
            @if($report) @method('PUT') @endif

            {{-- Patient dropdown --}}
            <div class="form-group">
                <label>Patient <span class="text-danger">*</span></label>
                <select name="patient_id" id="patient_id" class="form-control select2 @error('patient_id') is-invalid @enderror">
                    <option value="">Select Patient</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}"
                                data-code="{{ $p->patientcode }}"
                                data-phone="{{ $p->mobile_no ?? '' }}"
                                data-name="{{ $p->patientname }}"
                                {{ (old('patient_id', optional($report)->patient_id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->patientname }} ({{ $p->patientcode }}) @if($p->mobile_no) - {{ $p->mobile_no }} @endif
                        </option>
                    @endforeach
                </select>
                @error('patient_id')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            {{-- Patient identity panel --}}
            <div id="patient_panel" class="alert alert-info d-none">
                <div class="row">
                    <div class="col-md-4"><strong>Name:</strong> <span id="p_name"></span></div>
                    <div class="col-md-4"><strong>Code:</strong> <span id="p_code"></span></div>
                    <div class="col-md-4"><strong>Phone:</strong> <span id="p_phone"></span></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Doctor <span class="text-danger">*</span></label>
                    <select name="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror">
                        <option value="">Select Doctor</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}" {{ (old('doctor_id', $report->doctor_id ?? '') == $d->id) ? 'selected' : '' }}>
                                {{ $d->doctor_name }} ({{ $d->speciality ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Report Date <span class="text-danger">*</span></label>
                    <input type="date" name="report_date" class="form-control @error('report_date') is-invalid @enderror"
                           value="{{ old('report_date', ($report->report_date ?? now())->format('Y-m-d')) }}">
                    @error('report_date')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Findings <span class="text-danger">*</span></label>
                <textarea name="findings" id="findings" rows="12"
                          class="form-control @error('findings') is-invalid @enderror"
                          placeholder="Enter the full USG report findings...">{{ old('findings', $report->findings ?? '') }}</textarea>
                @error('findings')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Impression</label>
                <textarea name="impression" rows="3" class="form-control @error('impression') is-invalid @enderror"
                          placeholder="Short conclusion...">{{ old('impression', $report->impression ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label>Follow-up Date</label>
                <input type="date" name="followup_date" class="form-control @error('followup_date') is-invalid @enderror"
                       value="{{ old('followup_date', optional($report)->followup_date ? $report->followup_date->format('Y-m-d') : '') }}">
                <small class="form-text text-muted">Leave empty if no follow-up is needed.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $report ? 'Update Report' : 'Save Report' }}
            </button>
            <a href="{{ route('usg-reports.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@stop

@push('js')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    var select = document.getElementById('patient_id');
    var panel = document.getElementById('patient_panel');

    function showSelected() {
        var opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            panel.classList.add('d-none');
            return;
        }
        document.getElementById('p_name').textContent = opt.getAttribute('data-name') || '-';
        document.getElementById('p_code').textContent = opt.getAttribute('data-code') || '-';
        document.getElementById('p_phone').textContent = opt.getAttribute('data-phone') || '-';
        panel.classList.remove('d-none');
    }

    select.addEventListener('change', showSelected);
    showSelected();

    if (window.jQuery && jQuery.fn.select2) {
        jQuery('#patient_id').select2({
            placeholder: 'Search patient by name, code or phone...',
            allowClear: true,
            width: '100%'
        });
    }
})();
</script>
@endpush
