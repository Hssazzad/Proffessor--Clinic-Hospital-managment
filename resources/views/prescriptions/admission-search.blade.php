@extends('adminlte::page')

@section('title', 'Admission Stage Search')

@section('content_header')
  <h1>Admission Stage Search</h1>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
  .adm-search-card{
      background:#fff;
      border-radius:.75rem;
      box-shadow:0 1px 3px rgba(15,23,42,.06);
      border:1px solid #e5e7eb;
      margin-bottom:1rem;
  }
  .adm-search-card .card-body{ padding:1rem 1.2rem; }
  #previewArea{ margin-top:1rem; }
</style>
@endpush

@section('content')

<div class="card adm-search-card">
  <div class="card-body">
    <form method="GET" action="{{ route('admission.search') }}">
      <div class="form-group">
        <label>Patient</label>
        <select name="patient" class="form-control select2" id="patientSelect">
          <option value="">-- Select Patient --</option>
          @foreach($patients as $p)
            <option value="{{ $p->id }}" {{ (int)$patientId === (int)$p->id ? 'selected' : '' }}>
              {{ $p->patientname }} ({{ $p->mobile_no ?? '' }})
            </option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-primary">Search</button>
    </form>
  </div>
</div>

@if($selectedPatient)
  <div class="card adm-search-card">
    <div class="card-body">
      <h4 class="mb-3">Admissions of {{ $selectedPatient->patientname }}</h4>

      @if($admissions->isEmpty())
        <p>No admissions found.</p>
      @else
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th style="width:50px;">#</th>
                <th>Admission No</th>
                <th>Admit Date</th>
                <th>Discharge Date</th>
                <th>Stage</th>
                <th style="width:140px;">Action</th>
              </tr>
            </thead>
            <tbody>
            @foreach($admissions as $i => $adm)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $adm->admission_no ?? $adm->id }}</td>
                <td>{{ $adm->admission_date ? \Carbon\Carbon::parse($adm->admission_date)->format('d-m-Y') : '—' }}</td>
                <td>{{ $adm->discharge_date ? \Carbon\Carbon::parse($adm->discharge_date)->format('d-m-Y') : '—' }}</td>
                <td>
                  @php
                    $stage = $adm->stage ?? null;
                    $stageBadge = match($stage) {
                      'on_admission' => 'badge-primary',
                      'pre_surgery'  => 'badge-warning',
                      'post_surgery' => 'badge-info',
                      'round'        => 'badge-secondary',
                      'fresh'        => 'badge-success',
                      'discharge'    => 'badge-danger',
                      default        => 'badge-dark',
                    };
                    $stageLabel = $stage ? ucwords(str_replace('_', ' ', $stage)) : 'All Stages';
                  @endphp
                  <span class="badge {{ $stageBadge }}">{{ $stageLabel }}</span>
                </td>
                <td>
                  <button type="button"
                          class="btn btn-sm btn-success btn-preview-adm"
                          data-id="{{ $adm->id }}">
                    সব Stage দেখুন
                  </button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
@endif

<div id="previewArea"></div>

@stop

@push('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script>
$(function(){
  $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

  $(document).on('click', '.btn-preview-adm', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var url = '{{ url("admission-stage-preview") }}/' + id;

    $('#previewArea').html(
      '<div class="card adm-search-card">'+
        '<div class="card-body text-center">'+
          '<div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">' +
            '<span class="sr-only">Loading...</span>' +
          '</div>'+
          '<div class="mt-2">Loading stage preview...</div>'+
        '</div>'+
      '</div>'
    );

    $.ajax({
      url: url,
      type: 'GET',
      success: function(html){
        $('#previewArea').hide().html(html).fadeIn(200);
        $('html, body').animate({
          scrollTop: $('#previewArea').offset().top - 70
        }, 400);
      },
      error: function(xhr){
        var msg = 'Failed to load stage preview.';
        if (xhr.status === 404) msg = 'Admission record পাওয়া যায়নি।';
        else if (xhr.responseText && xhr.responseText.indexOf('<') !== 0) msg = xhr.responseText;
        $('#previewArea').html('<div class="alert alert-danger mb-0">'+msg+'</div>');
      }
    });
  });
});
</script>
@endpush
