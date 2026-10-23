{{-- resources/views/prescriptions/wizard-master.blade.php --}}
@extends('adminlte::page')

@section('title','Prescription Wizard')

@section('content_header')
  @php $active = $tab ?? request('tab') ?? 'patients'; @endphp
  @if($active !== 'preview')
    <h1 class="text-primary"><i class="fas fa-file-medical"></i> Prescription Wizard</h1>
  @endif
@stop

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css">
<style>
  .wizard-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
  .tab-btn { padding: 0.6rem 1.2rem; border: 1px solid #dee2e6; background: #fff; border-radius: 50px; text-decoration: none; color: #495057; font-weight: 500; transition: 0.3s; }
  .tab-btn:hover { background: #f8f9fa; color: #007bff; }
  .tab-btn.active { background: #007bff; color: #fff; border-color: #007bff; box-shadow: 0 4px 6px rgba(0,123,255,0.2); }
  .card-prescription { border-radius: 0.75rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border-top: 3px solid #007bff; }
</style>
@endpush

@section('content')

@if (session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check"></i> {{ session('success') }}
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <b><i class="icon fas fa-ban"></i> Fix Errors:</b>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php
  $active = $tab ?? 'patients';
  $p_id = $pid ?? request('id');
  $p_patient = $patientId ?? request('patient');
  $wz = $wizard ?? 'rx';

  // nav helper
  $backTab = request('back_tab', $active === 'preview' ? 'medicine' : $active);
  $nav = fn($t) => route($wz.'.wizard', [
      'tab'      => $t,
      'id'       => $p_id,
      'patient'  => $p_patient,
      'back_tab' => $t === 'preview' ? ($active !== 'preview' ? $active : $backTab) : $backTab
  ]);
@endphp


<div class="wizard-tabs" role="tablist">
  @php
   $steps = [
    'patients'       => '1) Patients',
    'complain'       => '2) Complain',
    'oe'             => '3) O/E',
    'investigations' => '4) Investigations',
    'medicine'       => '5) Medicine',
    'doctor'         => '6) Doctor',      
    'preview'        => '7) Preview',
    'finish'         => '8) Finish'
];
  @endphp

  @foreach($steps as $key => $label)
    <a class="tab-btn {{ $active === $key ? 'active' : '' }}" href="{{ $nav($key) }}">
        {{ $label }}
    </a>
  @endforeach
</div>

<div class="row">
  <!-- Main Content -->
  <div class="col-12">
    <div class="card card-prescription">
      <div class="card-body">
         
    @php
    $allowedTabs = [
        'patients',
        'complain',
        'oe',
        'investigations',
        'medicine',
        'doctor',     
        'preview',
        'finish'
    ];

    if (!in_array($active, $allowedTabs)) {
        $active = 'patients';
    }
@endphp

@if($active === 'oe')
    @include('prescriptions.tabs.oe')
@elseif(view()->exists('prescriptions.tabs.'.$active))
    @include('prescriptions.tabs.'.$active)
@else
    <div class="alert alert-danger">
        ট্যাব পাওয়া যায়নি: <strong>{{ $active }}</strong>
    </div>
@endif

{{-- 🔔 Draft Recovery Popup --}}
@if (!empty($draftForPopup) && $active === 'patients')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const draftId = "{{ $draftForPopup->id }}";
        const patientId = "{{ $draftForPopup->patient_id }}";

        let msg = "একটি অসম্পূর্ণ প্রেসক্রিপশন পাওয়া গেছে।\n" +
                  "ID: " + draftId + "\n" +
                  "Patient ID: " + patientId + "\n\n" +
                  "আপনি কি আগের ড্রাফট থেকে কাজ চালিয়ে যেতে চান?";

        if (confirm(msg)) {
            window.location.href = "{{ route($wz.'.wizard') }}?tab=patients&id=" + draftId + "&patient=" + patientId + "&draft=continue";
        } else {
            window.location.href = "{{ route($wz.'.wizard') }}?tab=patients&id=" + draftId + "&draft=discard";
        }
    });
    </script>
@endif

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
 $(document).ready(function() {
    // Skip already-initialized selects so tab-specific options (tags, width) stick.
    // Also exclude .select2-container spans: re-initializing them creates a second,
    // invisible 1px-wide dropdown (select2 copies .select2-hidden-accessible onto it).
    $('.select2:not(.select2-container):not(.select2-hidden-accessible)').select2({ theme: 'bootstrap4' });
});
</script>
@endpush
