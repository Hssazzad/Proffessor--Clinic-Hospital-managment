@extends('adminlte::page')

@section('title', 'Create Appointment')

@section('content_header')
    <h1 class="text-primary">Create Appointment</h1>
@stop

@section('content')
@php $today = now()->toDateString(); @endphp

<div class="container mt-3">
    {{-- Success/Error Alerts --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="card shadow-sm">
      <div class="card-body">
        {{-- Route Name Fixed: এখানে .page বাদ দিয়ে আপনার web.php এর সাথে মিলানো হয়েছে --}}
        <form id="apForm" action="{{ route('appointments.store') }}" method="POST">
          @csrf

          <div class="form-group">
            <label>Patient <span class="text-danger">*</span></label>
            <select name="patient_id" id="patient_id" class="form-control" required>
              <option value="">Select Patient</option>
              @foreach($patients as $p)
                <option value="{{ $p->id }}">{{ $p->patientname }} ({{ $p->patientcode }})</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="date" name="appointment_date" id="appointment_date"
                   class="form-control" min="{{ $today }}" value="{{ old('appointment_date') }}" required>
          </div>

          <div class="form-group">
            <label>Time <span class="text-danger">*</span></label>
            <input type="time" name="appointment_time" id="appointment_time"
                   class="form-control" value="{{ old('appointment_time') }}" required>
          </div>

          <div class="form-group">
            <label>Serial Number <span class="text-danger">*</span></label>
            <select name="serial_no" id="serial_no" class="form-control" disabled required>
              <option value="">Select date first</option>
            </select>
          </div>

          <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
          </div>

          <button type="submit" class="btn btn-primary px-4">Save Appointment</button>
        </form>
      </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function(){

  function resetSerial(){
    $("#serial_no").prop("disabled", true).html('<option value="">Select date first</option>');
  }

  // Date change triggers serial check
  $("#appointment_date").on("change", function(){
    var date = $(this).val();
    var pid  = $("#patient_id").val();
    
    resetSerial();
    if(!date || !pid) return;

    // AJAX Check: Is patient already booked?
    $.get("{{ route('appointments.checkPatientDate') }}", {date:date, patient_id:pid}, function(r){
      if(r.exists){
        alert("This patient already has an appointment on this date.");
        resetSerial();
        return;
      }
      
      // AJAX Load: Get available serials
      $.get("{{ route('appointments.availableSerials') }}", {date:date, limit:50}, function(res){
        $("#serial_no").empty();
        if(res.ok && res.available.length){
          $("#serial_no").append('<option value="">Select Serial</option>');
          res.available.forEach(function(n){
            $("#serial_no").append('<option value="'+n+'">'+n+'</option>');
          });
          $("#serial_no").prop("disabled", false);
        } else {
          $("#serial_no").append('<option value="">No serials available</option>');
          $("#serial_no").prop("disabled", true);
        }
      });
    });
  });

  // Re-trigger serial check if patient selection changes after date pick
  $("#patient_id").on("change", function(){
      if($("#appointment_date").val()){
          $("#appointment_date").trigger("change");
      }
  });

});
</script>
@endpush
