@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Admit Patient')

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
    <h1 class="text-primary font-weight-bold">Admit Patient new</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div id="ajaxMessage"></div>

<div class="card card-outline card-primary shadow">
    <div class="card-body">

        <form method="POST" action="{{ route('admission.admitpatient.store') }}">
            @csrf

            <div class="form-group">
                <label><b>Select Patient <span class="text-danger">*</span></b></label>
                <select name="patientcode" id="patientcode" class="form-control select2" required>
                    <option value="">-- Search by Name or Mobile --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->patientcode }}">
                            {{ $p->patientname }} - {{ $p->patientcode ?? '' }} ({{ $p->mobile_no ?? 'No Mobile' }})
                        </option>
                    @endforeach
                </select>
            </div>

            @php $admissionDoctors = $doctors ?? collect(); @endphp
            <div class="form-group">
                <label><b>Select Doctor <span class="text-danger">*</span></b></label>
                <select name="doctor_id" id="doctor_id" class="form-control select2" {{ $admissionDoctors->isNotEmpty() ? 'required' : '' }}>
                    <option value="">-- Select Doctor --</option>
                    @foreach($admissionDoctors as $doctor)
                        @php
                            $doctorName = $doctor->doctor_name ?? $doctor->name ?? '';
                            $doctorText = trim($doctorName . (!empty($doctor->speciality) ? ' - ' . $doctor->speciality : '') . (!empty($doctor->reg_no) ? ' [' . $doctor->reg_no . ']' : ''));
                        @endphp
                        <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            {{ $doctorText ?: ('Doctor #' . $doctor->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><b>Assign Bed</b></label>
                <select name="bed_id" id="bed_id" class="form-control select2">
                    <option value="">-- Select Bed --</option>
                    @foreach($availableBeds ?? [] as $bed)
                        <option value="{{ $bed->id }}">
                            {{ $bed->bed_number ?? 'N/A' }} — {{ $bed->bed_type ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <hr>

            <div class="form-group">
                <label><b>Select Medicine Template</b></label>
                <select name="templeteid" id="templeteid" class="form-control select2">
                    <option value="">-- All Template --</option>
                    @foreach($templates ?? [] as $t)
                        <option value="{{ $t->templateid }}">{{ $t->title }}  [{{ $t->templateid }}]</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-2">
                <button type="button" id="loadMedicineBtn" class="btn btn-info btn-sm">
                    <i class="fas fa-pills"></i> Load Medicine
                </button>

                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addMedicineModal">
                    <i class="fas fa-plus"></i> Add New Medicine
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Template</th>
                            <th>Medicine</th>
                            <th>Strength</th>
                            <th>Dose</th>
                            <th>Morning</th>
                            <th>Noon</th>
                            <th>Night</th>
                            <th>Route</th>
                            <th>Duration</th>
                            <th>Timing</th>
                            <th>Instruction</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>

                    <tbody id="medicineRows">
                        @include('admission.partials.admissionmedicine_rows', [
                            'templateMedicines' => $templateMedicines ?? collect()
                        ])
                    </tbody>
                </table>
            </div>

            <div class="form-group mt-3">
                <label>Admission Remarks / Details</label>
                <textarea name="remark" rows="3" class="form-control">{{ old('remark') }}</textarea>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save & Print Admission Slip
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Add Medicine Modal --}}
<div class="modal fade" id="addMedicineModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="#">
                @csrf

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-4 form-group">
                            <label>Template ID</label>
                            <input type="text" name="templeteid" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Medicine</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Strength</label>
                            <input type="text" name="strength" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Dose</label>
                            <input type="text" name="dose" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Morning</label>
                            <input type="text" name="morning" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Noon</label>
                            <input type="text" name="noon" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Night</label>
                            <input type="text" name="night" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Route</label>
                            <input type="text" name="route" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Timing</label>
                            <input type="text" name="timing" class="form-control">
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Instruction</label>
                            <input type="text" name="instruction" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Medicine
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(function () {

    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#loadMedicineBtn').on('click', function () {
        $('#ajaxMessage').empty();

        $('#medicineRows').html(
            '<tr>' +
                '<td colspan="12" class="text-center py-4">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="sr-only">Loading...</span>' +
                    '</div>' +
                    '<div class="mt-2 text-muted small">Loading medicines…</div>' +
                '</td>' +
            '</tr>'
        );

        $.ajax({
            url: "{{ route('admission.admitpatient.medicine_rows') }}",
            type: "GET",
            data: {
                templeteid: $('#templeteid').val()
            },
            dataType: 'html',
            success: function (html) {
                $('#ajaxMessage').empty();
            },
            error: function (xhr) {
                var msg = 'Medicine loading failed.';
                if (xhr.status) {
                    msg += ' (HTTP ' + xhr.status + ')';
                }
                if (xhr.statusText) {
                    msg += ' ' + xhr.statusText;
                }
                $('#ajaxMessage').html(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        msg +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                    '</div>'
                );
                $('#medicineRows').html(
                    '<tr><td colspan="12" class="text-center text-muted">No rows loaded.</td></tr>'
                );
            }
        });
    });

});
</script>
@endsection
