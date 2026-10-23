{{-- resources/views/prescriptions/tabs/oe.blade.php --}}
@php
    $pid       = (int)($pid ?? request('id') ?? 0);
    $patientId = (int)($patientId ?? request('patient') ?? 0);
@endphp

<div class="card">
    <div class="card-header fw-bold">O/E (Vitals)</div>
    <div class="card-body">

        @if(!$pid || !$patientId)
            <div class="alert alert-warning mb-3">
                Please select a patient first from the <strong>Patients</strong> tab.
            </div>
        @else

            {{-- QUICK ADD PANEL --}}
            <div class="card mb-3 p-2">
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-bottom:.5rem">
                    <div>
                        <label class="form-label mb-1">Pulse</label>
                        <input id="oe_pulse"    class="form-control vital-input" placeholder="e.g. 80">
                    </div>
                    <div>
                        <label class="form-label mb-1">BP</label>
                        <input id="oe_bp"       class="form-control vital-input" placeholder="e.g. 120/80">
                    </div>
                    <div>
                        <label class="form-label mb-1">Temp</label>
                        <input id="oe_temp"     class="form-control vital-input" placeholder="e.g. 98.6">
                    </div>
                    <div>
                        <label class="form-label mb-1">Weight</label>
                        <input id="oe_weight"   class="form-control vital-input" placeholder="kg">
                    </div>
                </div>

                <div style="display:flex;gap:.5rem;align-items:center">
                    <input id="oe_notes" class="form-control" placeholder="Notes (optional)" style="min-width:240px">
                    <button id="btnAddOe" class="btn btn-primary">Add</button>
                </div>
                <div id="oeMsg" class="mt-2" style="font-size:.9rem;display:none"></div>
            </div>

            {{-- TABLE LIST --}}
            <div>
                <table class="table table-sm table-bordered mb-0">
                    <thead style="background:#f4f7fb;">
                        <tr>
                            <th style="width:160px">Date / Time</th>
                            <th style="width:80px">Pulse</th>
                            <th style="width:110px">BP</th>
                            <th style="width:80px">Temp</th>
                            <th style="width:80px">Weight</th>
                            <th>Notes</th>
                            <th style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="oeRows">
                        <tr><td colspan="7" class="text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3" style="display:flex;justify-content:space-between;gap:.75rem">
                <a class="btn btn-outline-secondary" href="{{ route('rx.wizard',['id'=>$pid,'patient'=>$patientId,'tab'=>'patients']) }}">← Back</a>

                <form method="get" action="{{ route('rx.wizard') }}" style="margin:0">
                    <input type="hidden" name="id" value="{{ $pid }}">
                    <input type="hidden" name="patient" value="{{ $patientId }}">
                    <input type="hidden" name="tab" value="investigations">
                    <button class="btn btn-primary" type="submit">Next → Investigations</button>
                </form>
            </div>

        @endif
    </div>
</div>

@push('css')
<style>
/* small z-index help for extensions (not guaranteed) */
.content-wrapper, .card, .card-body { position: relative; z-index: 1; }
.form-control { position: relative; z-index: 2; }

/* consistent small styling like investigations.blade */
.form-control{padding:.45rem .55rem;border:1px solid #d9e2ef;border-radius:.5rem}
.btn{padding:.45rem .8rem;border-radius:.5rem}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border:1px solid #e5eaf3;padding:.45rem .6rem;vertical-align:middle}
#oeMsg{transition:opacity .2s}
</style>
@endpush

@push('js')
<script>
(function(){
  const $ = (s, ctx=document) => ctx.querySelector(s);
  const pid = {{ $pid }};
  const patientId = {{ $patientId }};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const rowsTbody = $('#oeRows');
  const msg = $('#oeMsg');

  function showMsg(text, ok=true){
    if (!msg) return;
    msg.style.display = 'block';
    msg.style.color = ok ? '#065f46' : '#b91c1c';
    msg.textContent = text;
    setTimeout(()=>{ msg.style.display='none'; }, 2000);
  }

  async function call(action, payload={}){
    const fd = new FormData();
    fd.append('action', action);
    fd.append('prescription_id', pid);
    fd.append('patient_id', patientId);
    fd.append('_token', csrf);
    for (const [k,v] of Object.entries(payload)) fd.append(k, v ?? '');
    const res = await fetch(`{{ route('rx.oe.ajax') }}`, {
      method: 'POST',
      headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: fd
    });
    const j = await res.json().catch(()=>({ok:false,error:'Bad JSON'}));
    if (!j.ok) throw new Error(j.error || 'Error');
    return j;
  }

  function esc(s){ return String(s??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

  function renderList(rows){
    if (!rows || rows.length===0) {
      rowsTbody.innerHTML = `<tr><td colspan="7" class="text-muted">No O/E records found.</td></tr>`;
      return;
    }

    rowsTbody.innerHTML = rows.map(r=>`
      <tr data-id="${r.id}">
        <td style="white-space:nowrap">${esc(r.datetime ?? r.created_at ?? '')}</td>
        <td><input class="form-control oe-pulse" value="${esc(r.pulse ?? '')}"></td>
        <td><input class="form-control oe-bp" value="${esc(r.bp ?? '')}"></td>
        <td><input class="form-control oe-temp" value="${esc(r.temp ?? '')}"></td>
        <td><input class="form-control oe-weight" value="${esc(r.weight ?? '')}"></td>
        <td><input class="form-control oe-notes" value="${esc(r.notes ?? r.note ?? '')}"></td>
        <td style="white-space:nowrap">
          <button type="button" class="btn btn-sm btn-save">Save</button>
          <button type="button" class="btn btn-sm btn-danger btn-del">Delete</button>
        </td>
      </tr>
    `).join('');
  }

  async function loadList(){
    rowsTbody.innerHTML = `<tr><td colspan="7" class="text-muted">Loading…</td></tr>`;
    try{
      const j = await call('list');
      renderList(j.rows || []);
    }catch(e){
      rowsTbody.innerHTML = `<tr><td colspan="7" class="text-danger">Load failed: ${esc(e.message)}</td></tr>`;
    }
  }

  // Add new record
  $('#btnAddOe')?.addEventListener('click', async ()=>{
    const pulse = $('#oe_pulse')?.value || '';
    const bp    = $('#oe_bp')?.value || '';
    const temp  = $('#oe_temp')?.value || '';
    const weight= $('#oe_weight')?.value || '';
    const notes = $('#oe_notes')?.value || '';

    if (!pulse && !bp && !temp && !weight && !notes) {
      showMsg('Enter at least one field to add.', false);
      return;
    }

    const btn = $('#btnAddOe'); btn.disabled = true; btn.textContent = 'Saving...';
    try{
      await call('add', { pulse, bp, temp, weight, notes });
      $('#oe_pulse').value = $('#oe_bp').value = $('#oe_temp').value = $('#oe_weight').value = $('#oe_notes').value = '';
      await loadList();
      showMsg('Added.');
    }catch(e){
      showMsg(e.message || 'Add failed', false);
    }finally{
      btn.disabled = false; btn.textContent = 'Add';
    }
  });

  // Row-level save / delete
  rowsTbody?.addEventListener('click', async (ev)=>{
    const tr = ev.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (ev.target.classList.contains('btn-save')) {
      const pulse = tr.querySelector('.oe-pulse')?.value || '';
      const bp    = tr.querySelector('.oe-bp')?.value || '';
      const temp  = tr.querySelector('.oe-temp')?.value || '';
      const weight= tr.querySelector('.oe-weight')?.value || '';
      const notes = tr.querySelector('.oe-notes')?.value || '';

      const btn = ev.target; btn.disabled = true; btn.textContent = 'Saving...';
      try{
        await call('update', { id, pulse, bp, temp, weight, notes });
        showMsg('Updated.');
      }catch(e){
        showMsg(e.message || 'Update failed', false);
      }finally{
        btn.disabled = false; btn.textContent = 'Save';
      }
    }

    if (ev.target.classList.contains('btn-del')) {
      if (!confirm('Delete this record?')) return;
      const btn = ev.target; btn.disabled = true; btn.textContent = 'Deleting...';
      try{
        await call('delete', { id });
        tr.remove();
        showMsg('Deleted.');
        if (!rowsTbody.querySelector('tr')) {
          rowsTbody.innerHTML = `<tr><td colspan="7" class="text-muted">No O/E records found.</td></tr>`;
        }
      }catch(e){
        showMsg(e.message || 'Delete failed', false);
        btn.disabled = false; btn.textContent = 'Delete';
      }
    }
  });

  // prevent e/E and negative from numeric inputs where used in other blades
  document.addEventListener('keydown', function(e){
    if (e.target && e.target.classList && e.target.classList.contains('vital-input')) {
      if (e.key === '-' || e.key === 'e' || e.key === 'E') e.preventDefault();
    }
  });

  // initial load
  loadList();

})();
</script>
@endpush