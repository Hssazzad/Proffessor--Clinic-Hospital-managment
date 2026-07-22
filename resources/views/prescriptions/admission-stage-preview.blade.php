<style>
  .asp-header{ background:#343a40; color:#fff; padding:12px 16px; border-radius:.5rem; margin-bottom:1rem; }
  .asp-header h5{ margin:0; font-size:1rem; }
  .asp-header small{ opacity:.8; }
  .asp-card{ border:1px solid #e5e7eb; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
  .asp-card .card-header{ padding:10px 14px; font-weight:600; font-size:.9rem; display:flex; align-items:center; gap:8px; }
  .asp-card .card-body{ padding:10px 14px; }
  .asp-table{ width:100%; border-collapse:collapse; font-size:.82rem; }
  .asp-table th, .asp-table td{ border:1px solid #dee2e6; padding:6px 8px; text-align:left; }
  .asp-table th{ background:#f8f9fa; font-weight:600; white-space:nowrap; }
  .badge-count{ font-size:.72rem; padding:2px 7px; border-radius:10px; background:rgba(0,0,0,.15); color:#fff; }
</style>

<div class="asp-header">
  <h5>{{ $patient->patientname ?? 'Unknown' }}
    <small>| Admit: {{ isset($admission->admission_date) ? \Carbon\Carbon::parse($admission->admission_date)->format('d-m-Y') : '—' }}
    | No: {{ $admission->admission_no ?? $admission->id }}</small>
  </h5>
</div>

@php
  $stages = [
    ['key' => 'onAdmissionMeds', 'label' => 'On Admission',     'color' => '#007bff', 'bg' => '#e7f1ff'],
    ['key' => 'preSurgeryMeds',  'label' => 'Pre-Surgery',       'color' => '#ffc107', 'bg' => '#fff9e6'],
    ['key' => 'postSurgeryMeds', 'label' => 'Post-Surgery',      'color' => '#17a2b8', 'bg' => '#e7f7fb'],
    ['key' => 'roundMeds',       'label' => 'Round',             'color' => '#6c757d', 'bg' => '#f2f2f2'],
    ['key' => 'freshMeds',       'label' => 'Fresh Rx',          'color' => '#28a745', 'bg' => '#e8f5ed'],
    ['key' => 'dischargeMeds',   'label' => 'Discharge',         'color' => '#dc3545', 'bg' => '#fce8ea'],
  ];
@endphp

@foreach($stages as $st)
  @php $meds = ${$st['key']}; @endphp
  <div class="asp-card">
    <div class="card-header" style="background:{{ $st['color'] }};color:#fff;">
      <span>{{ $st['label'] }}</span>
      <span class="badge-count">{{ $meds->count() }}</span>
    </div>
    <div class="card-body">
      @if($meds->isEmpty())
        <p class="text-muted mb-0" style="font-size:.85rem;">কোনো medicine নেই।</p>
      @else
        <table class="asp-table">
          <thead>
            <tr>
              <th style="width:35px;">#</th>
              <th>Medicine Name</th>
              <th style="width:55px;">সকাল</th>
              <th style="width:55px;">দুপুর</th>
              <th style="width:55px;">রাত</th>
              <th style="width:70px;">Duration</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            @foreach($meds as $j => $m)
              <tr>
                <td>{{ $j + 1 }}</td>
                <td>{{ $m->medicine_name ?? $m->name ?? '—' }}
                  @if(!empty($m->strength))
                    <br><small class="text-muted">{{ $m->strength }}</small>
                  @endif
                </td>
                <td>{{ $m->morning ?? $m->sokal ?? '—' }}</td>
                <td>{{ $m->afternoon ?? $m->dupure ?? $m->dupur ?? $m->noon ?? '—' }}</td>
                <td>{{ $m->night ?? $m->raat ?? '—' }}</td>
                <td>{{ $m->duration ?? '—' }}</td>
                <td>{{ $m->note ?? $m->instruction ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
@endforeach
