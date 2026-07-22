<h4 class="mb-3">Prescriptions of {{ $patientName }}</h4>

@if($prescriptions->isEmpty())
  <p>No prescriptions found.</p>
@else
  <table class="table table-sm table-bordered">
    <thead>
      <tr>
        <th style="width:60px;">#</th>
        <th style="width:140px;">Date</th>
        <th>Type / Doctor</th>
        <th style="width:150px;">Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($prescriptions as $i => $rx)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ \Carbon\Carbon::parse($rx->display_date ?? $rx->created_at)->format('d-m-Y') }}</td>
        <td>
          @if($rx->source === 'prescription')
            {{ $rx->doctor_name ?? '—' }}
          @elseif($rx->source === 'surgery')
            <span class="badge badge-info">Surgery</span>
            {{ $rx->label }}
          @else
            <span class="badge badge-secondary">Admission</span>
            {{ $rx->label ?? '' }}
          @endif
        </td>
        <td>
          @if($rx->source === 'prescription')
            <button type="button"
                    class="btn btn-sm btn-success btn-preview"
                    data-id="{{ $rx->id }}">
              Preview & Inline PDF
            </button>
          @else
            <span class="text-muted">—</span>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
@endif
