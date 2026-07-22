@extends('adminlte::page')

@section('title', 'Search Patients')

@section('content_header')
<h1 class="text-primary">Search Patient</h1>
@stop

@section('content')

@php $today = now()->toDateString(); @endphp

<div class="container mt-3">
    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Search Form --}}
            <form method="GET" action="{{ route('patients.searchpatient') }}" class="row mb-3">
                <div class="col-md-6 mb-2">
                    <input type="text" name="q" value="{{ $query }}" class="form-control"
                        placeholder="Search by Name, Mobile or Patient Code">
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block w-100">Search</button>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('patients.newpatient') }}" class="btn btn-success">+ Add Patient</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Age</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Edit</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $p)
                        <tr>
                            <td><span class="badge badge-info">{{ $p->patientcode }}</span></td>
                            <td>
                                <img src="{{ !empty($p->photo) ? asset('uploads/photos/' . $p->photo) : asset('uploads/photos/no_image.png') }}"
                                     style="width:45px;height:45px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                            </td>
                            <td>{{ $p->patientname }}</td>
                            <td>{{ $p->mobile_no }}</td>
                            <td>{{ $p->age }}</td>
                            <td>{{ optional($p->date_of_birth)->format('d-m-Y') }}</td>
                            <td>{{ $p->gender }}</td>
		<td>
    @if(!empty($p->id))
        <a href="{{ route('patients.editpatient', ['id' => $p->id]) }}"
           class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Edit
        </a>
    @else
        <span class="text-danger">ID missing</span>
    @endif
</td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-primary btn-open-appointment"
                                    data-patient-id="{{ $p->id }}" data-patient-name="{{ $p->patientname }}">
                                    Appointment
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted">No patient found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $patients->links() }}

        </div>
    </div>
</div>

{{-- ================= Appointment Modal ================= --}}
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <form method="POST" action="{{ route('appointments.store') }}" id="appointmentForm">
        @csrf

        <input type="hidden" name="patient_id" id="ap_patient_id">

        <div class="modal-header">
          <h5 class="modal-title">New Appointment<br>
            <small id="ap_patient_label" class="text-muted"></small>
          </h5>

          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          {{-- error box --}}
          <div id="ap_error" class="alert alert-danger d-none"></div>

          {{-- Date --}}
          <div class="mb-3">
            <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="appointment_date" id="ap_date"
                min="{{ $today }}" required>
          </div>

          {{-- Time --}}
          <div class="mb-3">
            <label class="form-label">Appointment Time <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="appointment_time" id="ap_time" required>
          </div>

          {{-- Serial --}}
          <div class="mb-3">
            <label class="form-label">Serial <span class="text-danger">*</span></label>
            <select class="form-control" name="serial_no" id="ap_serial" disabled required>
                <option value="">Select date first</option>
            </select>
          </div>

          {{-- Remarks --}}
          <div class="mb-2">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" rows="2"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Appointment</button>
        </div>

      </form>
    </div>
  </div>
</div>

@stop

@push('js')
<script>
$(document).ready(function () {

    function showError(msg){
        $("#ap_error").removeClass("d-none").text(msg);
    }
    function clearError(){
        $("#ap_error").addClass("d-none").text("");
    }

    // Open modal
    $(document).on('click', '.btn-open-appointment', function () {
        let pid   = $(this).data("patient-id");
        let pname = $(this).data("patient-name");

        $('#ap_patient_id').val(pid);
        $('#ap_patient_label').text("Patient: " + pname);
        clearError();

        $('#ap_date').val('');
        $('#ap_time').val('');
        $('#ap_serial').prop("disabled", true).html('<option>Select date first</option>');

        $('#appointmentModal').modal({backdrop:'static'}).modal('show');
    });

    // Date change
    $('#ap_date').on('change', function () {

        clearError();
        let date = $(this).val();
        let pid  = $('#ap_patient_id').val();

        if(!date || !pid) return;

        $('#ap_serial').prop("disabled", true).html('<option>Checking...</option>');

        // Check if same patient same date
        $.get("{{ route('appointments.checkPatientDate') }}", {date:date, patient_id:pid}, function(r){

            if(r.exists){
                showError("This patient already has an appointment on this date.");
                $('#ap_serial').prop("disabled", true).html('<option>Not allowed</option>');
                return;
            }

            // Load available serials
            $.get("{{ route('appointments.availableSerials') }}", {date:date,limit:50}, function(res){

                $('#ap_serial').empty();

                if(res.ok && res.available.length){
                    $('#ap_serial').append('<option value="">Select Serial</option>');
                    res.available.forEach(num=>{
                        $('#ap_serial').append(`<option value="${num}">${num}</option>`);
                    });
                    $('#ap_serial').prop("disabled", false);
                } else {
                    $('#ap_serial').append('<option>No serials available</option>');
                    $('#ap_serial').prop("disabled", true);
                }

            });

        });

    });

    // Reset modal
    $('#appointmentModal').on('hidden.bs.modal', function () {
        $('#appointmentForm')[0].reset();
        $('#ap_serial').prop("disabled", true).html('<option>Select date first</option>');
        clearError();
    });

});
</script>
@endpush
