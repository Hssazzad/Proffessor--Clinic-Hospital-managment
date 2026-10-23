@php
  $wz = $wz ?? 'rx';
@endphp
<style>
  /* ===== Same look & feel as your Medicine tab ===== */
  .card{background:#fff;border:1px solid #e5eaf3;border-radius:.75rem;padding:1rem;margin-bottom:1rem}
  .card-header{padding:0 0 .75rem 0;border:0;background:transparent}
  .card-body{padding:0}

  .btn{padding:.5rem .9rem;border-radius:.5rem;border:1px solid #dbe3f0;background:#0ea5e9;color:#fff;cursor:pointer}
  .btn.secondary{background:#f8fafc;color:#0e1a2a}
  .btn.danger{background:#ef4444;border-color:#ef4444}
  .btn[disabled]{opacity:.6;cursor:not-allowed}

  .form-control{width:100%;padding:.45rem .55rem;border:1px solid #d9e2ef;border-radius:.5rem}
  label{display:block;margin-bottom:.35rem;font-weight:600}

  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid #e5eaf3;padding:.5rem .6rem;vertical-align:top}
  .table th{background:#f4f7fb}
  .nowrap{white-space:nowrap}

  /* ===== Add-new row grid (Doctor) ===== */
  .grid3{
    display:grid;
    grid-template-columns: 2fr 1fr 2fr auto; /* doctor, role, note, button */
    gap:.5rem;
    align-items:flex-end;
  }
  @media (max-width: 768px){
    .grid3{grid-template-columns:1fr}
    #btnAddDoctor{width:100%}
  }
  .grid3 label{font-size:.85rem;font-weight:500}

  /* ===== MOBILE: TABLE -> VERTICAL CARDS (Doctor list) ===== */
  @media (max-width: 768px){
    .doc-table-wrap{margin:0 -0.5rem}
    .doc-table-wrap .table{border:0}
    .doc-table-wrap thead{display:none}

    .doc-table-wrap tbody tr{
      display:block;
      border:1px solid #e5eaf3;
      border-radius:.75rem;
      margin-bottom:.75rem;
      padding:.35rem .5rem;
    }
    .doc-table-wrap tbody td{
      display:flex;
      align-items:center;
      border:0;
      border-bottom:1px dashed #e5eaf3;
      padding:.25rem .2rem;
    }
    .doc-table-wrap tbody td:last-child{
      border-bottom:0;
      justify-content:flex-end;
    }
    .doc-table-wrap tbody td::before{
      content:attr(data-label);
      flex:0 0 35%;
      font-weight:600;
      color:#4b5563;
      margin-right:.5rem;
      font-size:.85rem;
    }
    .doc-table-wrap tbody td .form-control{width:100%}

    .doc-table-wrap tbody td.nowrap{
      display:flex;
      gap:.25rem;
    }
    .doc-table-wrap tbody td.nowrap::before{
      content:"Actions";
      flex:0 0 auto;
      font-weight:600;
      color:#4b5563;
      margin-right:.5rem;
    }
    .doc-table-wrap .btn{
      padding:.3rem .6rem;
      font-size:.8rem;
    }
  }

  /* Optional: Select2 Bootstrap4 look (if you use select2 here too) */
  .select2-container--bootstrap4 .select2-selection {
      border: 1px solid #6b7280 !important;
      border-radius: 6px !important;
      min-height: 38px !important;
      padding: 4px !important;
  }
  .select2-container--bootstrap4.select2-container--focus .select2-selection {
      border-color: #2563eb !important;
      box-shadow: 0 0 0 1px #2563eb33 !important;
  }
  .select2-container--bootstrap4 .select2-dropdown {
      border: 1px solid #6b7280 !important;
      border-radius: 6px !important;
  }
  .select2-container--bootstrap4 .select2-results__option--highlighted {
      background: #0ea5e9 !important;
      color: #fff !important;
  }
  .select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
      background: #e0f2fe !important;
      color: #0e7490 !important;
  }
</style>
<div class="card mt-3">
  <div class="card-header"><b>Doctor</b></div>
  <div class="card-body">

    {{-- ADD NEW --}}
    <form method="post" action="{{ route($wz.'.doctor.store') }}" class="mb-3">
      @csrf
      <input type="hidden" name="prescription_id" value="{{ $pid }}">
      <input type="hidden" name="patient_id" value="{{ $patientId }}">
      <input type="hidden" name="next" value="doctor">

      <div class="grid3">
        <div>
          <label>Doctor *</label>
          <select name="doctor_id" class="form-control select2" style="width:100%" required>
            <option value="">-- Select Doctor --</option>
            @foreach($commonDoctors ?? [] as $d)
              <option value="{{ $d->id }}">{{ $d->doctor_name }}</option>
            @endforeach
          </select>
          @error('doctor_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        
        <div>
          <label>&nbsp;</label>
          <button id="btnAddDoctor" class="btn" type="submit">+ Add</button>
        </div>
      </div>
    </form>

    {{-- LIST --}}
    <div class="card doc-table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Doctor</th>
            
            <th style="width:160px" class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($rxDoctors ?? []) as $i => $row)
            <tr>
              <td data-label="#">{{ $i+1 }}</td>
              <td data-label="Doctor">
                <b>{{ $row->doctor_name }}</b>
                @if(!empty($row->speciality))
                  <small class="text-muted">({{ $row->speciality }})</small>
                @endif
              </td>
             
              <td class="nowrap">
                <form method="post" action="{{ route($wz.'.doctor.destroy', $row->id) }}"
                      onsubmit="return confirm('Remove doctor?')" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="prescription_id" value="{{ $pid }}">
                  <input type="hidden" name="patient_id" value="{{ $patientId }}">
                  <input type="hidden" name="next" value="doctor">
                  <button class="btn danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">No doctor added yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

<div style="display:flex;justify-content:space-between;gap:.75rem">
  <a class="btn secondary" href="{{ route($wz.'.wizard',['id'=>$pid,'patient'=>$patientId,'tab'=>'investigations']) }}">← Back</a>
  <form method="get" action="{{ route($wz.'.wizard') }}" style="margin:0">
    <input type="hidden" name="id" value="{{ $pid }}">
    <input type="hidden" name="patient" value="{{ $patientId }}">
    <input type="hidden" name="tab" value="preview">
    <button class="btn" type="submit">Next → Preview</button>
  </form>
</div>