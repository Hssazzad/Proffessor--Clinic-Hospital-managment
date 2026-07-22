{{-- resources/views/prescriptions/partials/admission_stage_preview.blade.php --}}
<div class="card" style="border-radius:.75rem;border:1px solid #e5e7eb;margin-top:0;">
  <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
    <strong>
      <i class="fas fa-file-medical mr-1"></i>
      {{ $patient->patientname ?? 'Patient' }}
      @if($admission)
        — Admission #{{ $admission->admission_no ?? $admission->id }}
      @endif
    </strong>
    <span class="badge badge-{{ $stageInfo['color'] }} ml-2" style="font-size:.8rem;">
      {{ $stageInfo['label'] }}
    </span>
  </div>

  <div class="card-body p-0">
    @if($medicines->isEmpty())
      <div class="p-3 text-muted">এই stage এ কোনো medicine নেই।</div>
    @else
      <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
        <thead class="thead-light">
          <tr>
            <th style="width:35px;">#</th>
            <th>Medicine</th>
            <th style="width:70px;">সকাল</th>
            <th style="width:70px;">দুপুর</th>
            <th style="width:70px;">রাত</th>
            <th style="width:90px;">Duration</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
        @foreach($medicines as $i => $med)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>
              <strong>{{ $med->medicine_name ?? $med->name ?? '—' }}</strong>
              @if(!empty($med->strength))
                <small class="text-muted"> ({{ $med->strength }})</small>
              @endif
            </td>
            <td>{{ $med->morning   ?? $med->sokal   ?? $med->dose_morning   ?? '—' }}</td>
            <td>{{ $med->afternoon ?? $med->dupur   ?? $med->dose_afternoon ?? '—' }}</td>
            <td>{{ $med->night     ?? $med->raat    ?? $med->dose_night     ?? '—' }}</td>
            <td>{{ $med->duration  ?? '—' }}</td>
            <td class="text-muted">{{ $med->note ?? '' }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
