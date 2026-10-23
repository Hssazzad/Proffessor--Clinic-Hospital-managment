@extends('adminlte::page')

@section('title', 'Add Doctor')

@section('content_header')
  <h1 class="text-primary">Add Doctor</h1>
@stop

@section('content')

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-body">
    <form action="{{ route('settings.doctors.store') }}" method="post">
      @csrf

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Reg No</label>
          <input type="text" name="reg_no" class="form-control"
                 value="{{ old('reg_no') }}" maxlength="12">
        </div>

        <div class="form-group col-md-8">
          <label>Doctor Name</label>
          <input type="text" name="doctor_name" class="form-control"
                 value="{{ old('doctor_name') }}" maxlength="50">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Speciality</label>

          <select name="speciality" class="form-control select2">
              <option value="">-- Select Speciality --</option>

              @foreach($specialities as $sp)
                  <option value="{{ $sp->id }}"
                      {{ old('speciality') == $sp->id ? 'selected' : '' }}>
                      {{ $sp->name }}
                  </option>
              @endforeach
          </select>
        </div>

        <div class="form-group col-md-4">
          <label>Contact</label>
          <input type="text" name="contact" class="form-control"
                 value="{{ old('contact') }}" maxlength="12">
        </div>

        <div class="form-group col-md-4">
          <label>Posting</label>
          <input type="text" name="Posting" class="form-control"
                 value="{{ old('Posting') }}" maxlength="25">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Rate Code</label>
          <input type="text" name="RateCode" class="form-control"
                 value="{{ old('RateCode') }}" maxlength="12">
        </div>
      </div>

      <button class="btn btn-primary">Save Doctor</button>
      <a href="{{ route('settings.doctors.index') }}" class="btn btn-secondary">
        Back to List
      </a>
    </form>
  </div>
</div>

@stop

@push('css')
{{-- Select2 CSS Push to CSS Stack --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: '-- Select Speciality --',
        allowClear: true
    });
});
</script>
@endpush