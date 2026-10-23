@extends('adminlte::page')

@section('title', 'Create Appointment')

@section('content_header')
    <h1 class="text-primary">Create Appointment</h1>
@stop

@section('content')
@php $today = now()->toDateString(); @endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/hospital-erp.css') }}">
@endpush

<div class="container mt-3 hospital-page">
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

    <div class="card hospital-box">
      <div class="card-header with-border">
        <h3 class="card-title">Appointment Booking</h3>
      </div>
      <div class="card-body">
        {{-- Route Name Fixed: এখানে .page বাদ দিয়ে আপনার web.php এর সাথে মিলানো হয়েছে --}}
        <form id="apForm" action="{{ route('appointments.store') }}" method="POST">
          @csrf

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Patient <span class="text-danger">*</span></label>
                <select name="patient_id" id="patient_id" class="form-control" required>
                  <option value="">Select Patient</option>
                  @foreach($patients as $p)
                    <option value="{{ $p->id }}">{{ $p->patientname }} ({{ $p->patientcode }})</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" name="appointment_date" id="appointment_date"
                       class="form-control" min="{{ $today }}" value="{{ old('appointment_date') }}" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Time <span class="text-danger">*</span></label>
                <input type="time" name="appointment_time" id="appointment_time"
                       class="form-control" value="{{ old('appointment_time') }}" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Serial Number <span class="text-danger">*</span></label>
                <input type="number" name="serial_no" id="serial_no" class="form-control"
                       readonly min="1" max="50" placeholder="Auto (select date)">
                <small class="text-muted">Date নির্বাচন করলে automatically তৈরি হবে</small>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
              </div>
            </div>

            <div class="col-md-12">
              <button type="submit" class="btn btn-success">Save Appointment</button>
            </div>
          </div>
        </form>
      </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function(){

  function resetSerial(){
    $("#serial_no").val('');
  }

  // Date change triggers automatic serial assignment
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
      
      // AJAX: Auto-assign next serial for the date
      $.get("{{ route('appointments.nextSerial') }}", {date:date}, function(res){
        if(res.ok){
          if(res.next > 50){
            alert("No serials available for this date.");
            resetSerial();
          } else {
            $("#serial_no").val(res.next);
          }
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
