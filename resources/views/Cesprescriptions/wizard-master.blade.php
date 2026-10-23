{{-- resources/views/prescriptions/wizard-master.blade.php --}}
@extends('adminlte::page')

@section('title','Prescription Wizard')

@section('content_header')
  @php $active = $tab ?? request('tab') ?? 'patients'; @endphp
  @if($active !== 'preview')
    <h1 class="text-primary"><i class="fas fa-file-medical"></i> Cesarean Prescription</h1>
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
  
  /* Vitals Grid Styles */
  .vitals-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
  }
  
  .vital-item {
    text-align: center;
    padding: 0.5rem;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    background: #f8f9fa;
    transition: all 0.3s ease;
  }
  
  .vital-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #007bff;
  }
  
  .vital-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
  }
  
  .vital-value {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
    line-height: 1;
  }
  
  .vital-value i {
    margin-right: 0.25rem;
    opacity: 0.7;
  }
  
  .vital-unit {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 500;
  }
  
  .text-orange { color: #fd7e14 !important; }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .vitals-grid {
      grid-template-columns: 1fr;
    }
    
    .col-md-3 {
      margin-bottom: 1rem;
    }
  }
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
  $wz = 'ces';

  // nav helper
  $nav = fn($t) => route($wz.'.wizard', [
      'tab'     => $t,
      'id'      => $p_id,
      'patient' => $p_patient
  ]);
@endphp

<div class="wizard-tabs" role="tablist">
  @php 
    $steps = [
        'patients' => '1) Patients',
        'complain' => '2) Complain',
        'oe' => '3) O/E',
        'investigations' => '4) Investigations',
        'diagnosis' => '5) Diagnosis',
        'medicine' => '6) Medicine',
        'preview' => '7) Preview',
        'finish' => '8) Finish'
    ];
  @endphp

  @foreach($steps as $key => $label)
    <a class="tab-btn {{ $active === $key ? 'active' : '' }}" href="{{ $nav($key) }}">
        {{ $label }}
    </a>
  @endforeach
</div>

<div class="row">
  <!-- Patient Vitals Sidebar (hidden on the full-width Preview tab) -->
  @if($active !== 'preview')
  <div class="col-md-3">
    @if($patientId && $patient)
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h5 class="card-title">
            <i class="fas fa-heartbeat"></i> Patient Vitals
          </h5>
        </div>
        <div class="card-body p-2">
          <!-- Patient Info -->
          <div class="mb-3">
            <h6 class="text-bold text-primary">Patient Info</h6>
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width: 80px;">Name:</td>
                <td class="font-weight-bold">{{ $patient->patientname ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td class="text-muted">Code:</td>
                <td>{{ $patient->patientcode ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td class="text-muted">Age:</td>
                <td>{{ $patient->age ?? '--' }} years</td>
              </tr>
              <tr>
                <td class="text-muted">Gender:</td>
                <td>{{ $patient->gender ?? '--' }}</td>
              </tr>
            </table>
          </div>

          <!-- Vitals Section -->
          <div class="vitals-section">
            <h6 class="text-bold text-info">Latest Vitals</h6>
            
            @if(request()->has('debug'))
              <div class="alert alert-info">
                <h6>🔍 Debug Information:</h6>
                <strong>PatientId:</strong> {{ $patientId ?? 'NULL' }}<br>
                <strong>Patient Data:</strong> @if($patient) EXISTS @else NULL @endif<br>
                <strong>Vitals Data:</strong> @if($vitals) EXISTS @else NULL @endif<br>
                <strong>Vitals Count:</strong> {{ is_object($vitals) ? 'OBJECT' : 'NOT OBJECT' }}<br>
                
                @if($vitals)
                  <strong>Vitals Properties:</strong><br>
                  <pre>{{ json_encode(array_keys((array)$vitals), JSON_PRETTY_PRINT) }}</pre>
                  <strong>Full Vitals Data:</strong><br>
                  <pre>{{ json_encode($vitals, JSON_PRETTY_PRINT) }}</pre>
                @endif
                
                <strong>Database Check:</strong><br>
                @php
                  $directQuery = DB::table('patient_pre_assessments')->limit(5)->get();
                @endphp
                <strong>Sample Records:</strong> {{ $directQuery->count() }} found<br>
                @if($directQuery->isNotEmpty())
                  <pre>{{ json_encode($directQuery->first(), JSON_PRETTY_PRINT) }}</pre>
                @endif
              </div>
            @endif
            
            @if(request()->has('debug'))
              <div class="alert alert-warning">
                <h6>🔍 Debug Information:</h6>
                <strong>PatientId:</strong> {{ $patientId ?? 'NULL' }}<br>
                <strong>Patient Data:</strong> @if($patient) EXISTS @else NULL @endif<br>
                <strong>Vitals Data:</strong> @if($vitals) EXISTS @else NULL @endif<br>
                <strong>URL Parameters:</strong> {{ request()->getQueryString() }}<br>
                
                @if($vitals && is_array($vitals))
                  <strong>Vitals Array:</strong><br>
                  <pre>{{ json_encode($vitals, JSON_PRETTY_PRINT) }}</pre>
                @endif
                
                <strong>Full Request:</strong><br>
                <pre>{{ json_encode(request()->all(), JSON_PRETTY_PRINT) }}</pre>
              </div>
            @endif
            
            @if($vitals)
              <div class="vitals-grid">
                <div class="vital-item">
                  <div class="vital-label">BP</div>
                  <div class="vital-value text-danger">
                    <i class="fas fa-heart"></i>
                    {{ $vitals->bp_sys ?? '--' }}/{{ $vitals->bp_dia ?? '--' }}
                  </div>
                  <div class="vital-unit">mmHg</div>
                </div>
                
                <div class="vital-item">
                  <div class="vital-label">Weight</div>
                  <div class="vital-value text-success">
                    <i class="fas fa-weight"></i>
                    {{ $vitals->weight ?? '--' }}
                  </div>
                  <div class="vital-unit">kg</div>
                </div>
                
                <div class="vital-item">
                  <div class="vital-label">Height</div>
                  <div class="vital-value text-info">
                    <i class="fas fa-ruler-vertical"></i>
                    {{ $vitals->height ?? '--' }}
                  </div>
                  <div class="vital-unit">cm</div>
                </div>
                
                <div class="vital-item">
                  <div class="vital-label">Temperature</div>
                  <div class="vital-value text-orange">
                    <i class="fas fa-thermometer-half"></i>
                    {{ $vitals->temp ?? '--' }}
                  </div>
                  <div class="vital-unit">°F</div>
                </div>
                
                <div class="vital-item">
                  <div class="vital-label">SpO2</div>
                  <div class="vital-value text-info">
                    <i class="fas fa-lungs"></i>
                    {{ $vitals->spo2 ?? '--' }}
                  </div>
                  <div class="vital-unit">%</div>
                </div>
                
                <div class="vital-item">
                  <div class="vital-label">Pulse</div>
                  <div class="vital-value text-warning">
                    <i class="fas fa-heartbeat"></i>
                    {{ $vitals->pulse ?? '--' }}
                  </div>
                  <div class="vital-unit">bpm</div>
                </div>
              </div>
              
              @if($vitals->created_at)
              <div class="mt-2 text-muted small">
                <i class="fas fa-clock"></i>
                Recorded: {{ \Carbon\Carbon::parse($vitals->created_at)->format('M d, Y h:i A') }}
              </div>
              @endif
            @else
              <div class="text-center text-muted py-3">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <div class="small">No vitals recorded</div>
                <div class="text-xs">Please complete patient assessment</div>
              </div>
            @endif
          </div>
          
          <!-- Action Buttons -->
          @if($patientId)
          <div class="mt-3">
            <a href="{{ route('preconassessment.create', ['patient' => $patientId]) }}" 
               class="btn btn-sm btn-success btn-block">
              <i class="fas fa-plus"></i> Add Vitals
            </a>
            @if($vitals)
            <a href="{{ route('preconassessment.edit', $vitals->id) }}" 
               class="btn btn-sm btn-outline-primary btn-block mt-1">
              <i class="fas fa-edit"></i> Edit Vitals
            </a>
            @endif
          </div>
          @endif
        </div>
      </div>
    @else
      <div class="card card-warning card-outline">
        <div class="card-header">
          <h5 class="card-title">
            <i class="fas fa-user-md"></i> Patient Selection
          </h5>
        </div>
        <div class="card-body text-center py-3">
          <i class="fas fa-user-circle fa-3x text-muted mb-2"></i>
          <div class="text-muted">Please select a patient to view vitals</div>
        </div>
      </div>
    @endif
  </div>
  @endif

  <!-- Main Content (full width on the Preview tab) -->
  <div class="{{ $active === 'preview' ? 'col-12' : 'col-md-9' }}">
    <div class="card card-prescription">
      <div class="card-body">
         @php
             $tabView = 'prescriptions.tabs.' . $active;
             if ($active === 'preview' && view()->exists('Cesprescriptions.tabs.' . $active)) {
                 $tabView = 'Cesprescriptions.tabs.' . $active;
             }
         @endphp
         @include($tabView)
      </div>
    </div>
  </div>
</div>

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
            // YES → Continue (URL string template logic)
            window.location.href = "{{ route('ces.wizard') }}?tab=patients&id=" + draftId + "&patient=" + patientId + "&draft=continue";
        } else {
            // NO → Discard
            window.location.href = "{{ route('ces.wizard') }}?tab=patients&id=" + draftId + "&draft=discard";
        }
    });
    </script>
@endif

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
 $(document).ready(function() {
    // Initialize Select2 if exists
    $('.select2').select2({ theme: 'bootstrap4' });
});
</script>
@endpush