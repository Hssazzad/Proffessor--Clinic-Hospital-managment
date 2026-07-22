{{-- resources/views/prescriptions/wizard-multi.blade.php --}}
@extends('adminlte::page')

@section('title','Prescription Wizard (Separate Forms)')

@section('content_header')
  <h1 class="text-primary">Prescription Wizard � Separate Forms</h1>
@stop

@section('content')
@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger">
    <strong>Fix the following:</strong>
    <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
  </div>
@endif

<style>
  .tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
  .tab-btn{padding:.55rem 1rem;border:1px solid #dbe3f0;background:#f8fafc;cursor:pointer;border-radius:.5rem}
  .tab-btn.active{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
  .panel{display:none}
  .panel.active{display:block}
  .card{background:#fff;border:1px solid #e5eaf3;border-radius:.75rem;padding:1rem}
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid #e5eaf3;padding:.5rem .6rem}
  .table th{background:#f4f7fb;font-weight:600}
  .btn{padding:.5rem .9rem;border-radius:.5rem;border:1px solid #dbe3f0;background:#0ea5e9;color:#fff;cursor:pointer}
  .btn.secondary{background:#f8fafc;color:#0e1a2a}
  .btn.danger{background:#ef4444}
  .row-actions{display:flex;gap:.5rem;margin:.5rem 0}
  .muted{color:#64748b;font-size:.9rem}
  .footer-actions{display:flex;gap:.5rem;justify-content:space-between;margin-top:1rem}
</style>

@php
  $active = $tab ?? 'patients';
  $pid = $data['prescription_id'] ?? null;
@endphp

{{-- Tabs --}}
<div class="tabs" role="tablist" aria-label="Steps">
  @foreach (['patients'=>'1) Patients','investigations'=>'2) Investigations','diagnosis'=>'3) Diagnosis','medicine'=>'4) Medicine'] as $key=>$label)
    <a href="{{ route('rx.wizard',['id'=>$pid,'tab'=>$key]) }}"
       class="tab-btn {{ $active === $key ? 'active':'' }}"
       role="tab" aria-controls="panel-{{ $key }}">{{ $label }}</a>
  @endforeach
</div>


{{-- Patients FORM (saves to patients table) --}}
<section id="panel-patients" class="panel {{ ($tab ?? 'patients')==='patients'?'active':'' }}">
  <div class="card">
    <form method="post" action="{{ route('rx.save.patient') }}">
      @csrf
      <input type="hidden" name="prescription_id" value="{{ $data['prescription_id'] ?? '' }}">
      <input type="hidden" name="patient_id" value="{{ $data['patient']->id ?? '' }}">

      <div class="grid-2">
        <div>
          <label>Patient Code</label>
          <input class="form-control" name="patientcode" value="{{ old('patientcode', $data['patient']->patientcode ?? '') }}" placeholder="Auto if blank">
        </div>
        <div>
          <label>Patient Name *</label>
          <input class="form-control" name="patientname" required value="{{ old('patientname', $data['patient']->patientname ?? '') }}">
        </div>

        <div>
          <label>Mobile No</label>
          <input class="form-control" name="mobile_no" value="{{ old('mobile_no', $data['patient']->mobile_no ?? '') }}">
        </div>
        <div>
          <label>NID Number</label>
          <input class="form-control" name="nid_number" value="{{ old('nid_number', $data['patient']->nid_number ?? '') }}">
        </div>

        <div>
          <label>Age</label>
          <input type="number" class="form-control" name="age" value="{{ old('age', $data['patient']->age ?? '') }}">
        </div>
        <div>
          <label>Date of Birth</label>
          <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $data['patient']->date_of_birth ?? '') }}">
        </div>

        <div>
          <label>Gender</label>
          <select class="form-control" name="gender">
            @php $g = old('gender', $data['patient']->gender ?? ''); @endphp
            <option value="">-- select --</option>
            <option value="Male"   {{ $g==='Male'?'selected':'' }}>Male</option>
            <option value="Female" {{ $g==='Female'?'selected':'' }}>Female</option>
            <option value="Other"  {{ $g==='Other'?'selected':'' }}>Other</option>
          </select>
        </div>
        <div>
          <label>Blood Group</label>
          <input class="form-control" name="blood_group" value="{{ old('blood_group', $data['patient']->blood_group ?? '') }}" placeholder="A+, O-, ...">
        </div>

        <div style="grid-column:1 / -1">
          <label>Address</label>
          <input class="form-control" name="address" value="{{ old('address', $data['patient']->address ?? '') }}">
        </div>

        <div>
          <label>Father�s Name</label>
          <input class="form-control" name="patientfather" value="{{ old('patientfather', $data['patient']->patientfather ?? '') }}">
        </div>
        <div>
          <label>Husband/Wife</label>
          <input class="form-control" name="patienthusband" value="{{ old('patienthusband', $data['patient']->patienthusband ?? '') }}">
        </div>

        <div>
          <label>Alternate Contact</label>
          <input class="form-control" name="contact_no" value="{{ old('contact_no', $data['patient']->contact_no ?? '') }}">
        </div>
        <div>
          <label>Email</label>
          <input type="email" class="form-control" name="email" value="{{ old('email', $data['patient']->email ?? '') }}">
        </div>

        <div style="grid-column:1 / -1">
          <label>Notes</label>
          <textarea class="form-control" rows="2" name="notes">{{ old('notes', $data['patient']->notes ?? '') }}</textarea>
        </div>
      </div>

      <div class="footer-actions" style="margin-top:1rem">
        <span class="muted">Tip: leave Patient Code empty to auto-generate (PT-YYYYMM-####).</span>
        <button class="btn" type="submit">Save & Next &rarr;</button>
      </div>
    </form>
  </div>
</section>


{{-- Investigations FORM --}}
<section id="panel-investigations" class="panel {{ $active==='investigations'?'active':'' }}">
  <div class="card">
    <form method="post" action="{{ route('rx.save.investigations') }}" id="invForm">
      @csrf
      <input type="hidden" name="prescription_id" value="{{ $pid }}">
      <div class="row-actions">
        <button type="button" class="btn secondary" id="btnAddInv">+ Add Row</button>
      </div>
      <table class="table" id="invTable">
        <thead><tr><th style="width:35%">Investigation</th><th>Note</th><th style="width:80px">Action</th></tr></thead>
        <tbody></tbody>
      </table>
      <div class="footer-actions">
        <a class="btn secondary" href="{{ route('rx.wizard',['id'=>$pid,'tab'=>'patients']) }}">&larr; Back</a>
        <button class="btn" type="submit">Save & Next &rarr;</button>
      </div>
    </form>
  </div>
</section>

{{-- Diagnosis FORM --}}
<section id="panel-diagnosis" class="panel {{ $active==='diagnosis'?'active':'' }}">
  <div class="card">
    <form method="post" action="{{ route('rx.save.diagnosis') }}">
      @csrf
      <input type="hidden" name="prescription_id" value="{{ $pid }}">
      <div class="grid-2">
        <div>
          <label>Chief Complaint *</label>
          <textarea class="form-control" rows="3" name="diagnosis[chief_complaint]" required>{{ old('diagnosis.chief_complaint') }}</textarea>
        </div>
        <div>
          <label>Diagnosis *</label>
          <textarea class="form-control" rows="3" name="diagnosis[diagnosis]" required>{{ old('diagnosis.diagnosis') }}</textarea>
        </div>
        <div style="grid-column:1 / -1">
          <label>Advice / Notes</label>
          <textarea class="form-control" rows="3" name="diagnosis[advice]">{{ old('diagnosis.advice') }}</textarea>
        </div>
      </div>
      <div class="footer-actions">
        <a class="btn secondary" href="{{ route('rx.wizard',['id'=>$pid,'tab'=>'investigations']) }}">&larr; Back</a>
        <button class="btn" type="submit">Save & Next &rarr;</button>
      </div>
    </form>
  </div>
</section>

{{-- Medicine FORM --}}
<section id="panel-medicine" class="panel {{ $active==='medicine'?'active':'' }}">
  <div class="card">
    <form method="post" action="{{ route('rx.save.medicine') }}" id="medForm">
      @csrf
      <input type="hidden" name="prescription_id" value="{{ $pid }}">
      <div class="row-actions">
        <button type="button" class="btn secondary" id="btnAddMed">+ Add Row</button>
      </div>
      <table class="table" id="medTable">
        <thead>
          <tr>
            <th style="width:22%">Medicine Name *</th>
            <th style="width:10%">Strength</th>
            <th style="width:12%">Dose</th>
            <th style="width:12%">Route</th>
            <th style="width:12%">Frequency</th>
            <th style="width:12%">Duration</th>
            <th style="width:12%">Timing</th>
            <th style="width:80px">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <div class="grid-2" style="margin-top:1rem">
        <div>
          <label>Prescribed On</label>
          <input type="date" class="form-control" name="meta[prescribed_on]" value="{{ date('Y-m-d') }}">
        </div>
        <div>
          <label>Doctor Name</label>
          <input class="form-control" name="meta[doctor_name]">
        </div>
        <div>
          <label>Doctor Reg No</label>
          <input class="form-control" name="meta[doctor_reg_no]">
        </div>
      </div>

      <div class="footer-actions">
        <a class="btn secondary" href="{{ route('rx.wizard',['id'=>$pid,'tab'=>'diagnosis']) }}">&larr; Back</a>
        <button class="btn" type="submit">Save Medicine</button>
      </div>
    </form>
  </div>
</section>

<script>
(function(){
  const $ = (s,ctx=document)=>ctx.querySelector(s);
  const $$ = (s,ctx=document)=>Array.from(ctx.querySelectorAll(s));

  // Investigations add/remove
  const invBody = $('#invTable tbody');
  if (invBody) {
    $('#btnAddInv').addEventListener('click', ()=> addInvRow());
    function addInvRow(data={name:'',note:''}){
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input class="form-control" name="investigations[][name]" value="${esc(data.name)}"></td>
        <td><input class="form-control" name="investigations[][note]" value="${esc(data.note)}"></td>
        <td><button type="button" class="btn danger btn-del">Del</button></td>`;
      tr.querySelector('.btn-del').addEventListener('click', ()=> tr.remove());
      invBody.appendChild(tr);
    }
    // start one row
    addInvRow();
  }

  // Medicines add/remove
  const medBody = $('#medTable tbody');
  if (medBody) {
    $('#btnAddMed').addEventListener('click', ()=> addMedRow());
    function addMedRow(d={}) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input class="form-control" required name="medicines[][name]" value="${esc(d.name||'')}"></td>
        <td><input class="form-control" name="medicines[][strength]" value="${esc(d.strength||'')}"></td>
        <td><input class="form-control" name="medicines[][dose]" value="${esc(d.dose||'')}"></td>
        <td><input class="form-control" name="medicines[][route]" value="${esc(d.route||'')}"></td>
        <td><input class="form-control" name="medicines[][frequency]" value="${esc(d.frequency||'')}"></td>
        <td><input class="form-control" name="medicines[][duration]" value="${esc(d.duration||'')}"></td>
        <td><input class="form-control" name="medicines[][timing]" value="${esc(d.timing||'')}"></td>
        <td><button type="button" class="btn danger btn-del">Del</button></td>`;
      tr.querySelector('.btn-del').addEventListener('click', ()=> tr.remove());
      medBody.appendChild(tr);
    }
    // start one row
    addMedRow();
  }

  function esc(s){ return String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;'); }
})();
</script>
@endsection
