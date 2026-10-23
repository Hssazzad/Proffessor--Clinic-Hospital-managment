{{-- resources/views/prescriptions/tabs/complain.blade.php --}}
@php
  // Expected: $pid, $patientId, $commoncomplain (optional)
  $pid       = (int)($pid ?? request('id'));
  $patientId = (int)($patientId ?? request('patient'));
  $wz        = $wz ?? 'rx';
@endphp

@if (!$pid)
  <div class="alert alert-danger">No prescription selected. Save Patient first.</div>
  @php return; @endphp
@endif


<style>
  .card{background:#fff;border:1px solid #e5eaf3;border-radius:.75rem;padding:1rem;margin-bottom:1rem}
  .btn{padding:.5rem .9rem;border-radius:.5rem;border:1px solid #dbe3f0;background:#0ea5e9;color:#fff;cursor:pointer}
  .btn.secondary{background:#f8fafc;color:#0e1a2a}
  .btn.danger{background:#ef4444}
  .btn[disabled]{opacity:.6;cursor:not-allowed}
  .form-control{width:100%;padding:.45rem .55rem;border:1px solid #d9e2ef;border-radius:.5rem}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{border:1px solid #e5eaf3;padding:.5rem .6rem;vertical-align:top}
  .table th{background:#f4f7fb}
  label{display:block;margin-bottom:.35rem;font-weight:600}
  
  /* Main visible Select2 box (the one you click) */
.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #6b7280 !important;   /* gray border */
    border-radius: 6px !important;
}

/* On focus */
.select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #2563eb !important;  /* blue border on focus */
    box-shadow: 0 0 0 1px #2563eb33 !important;
}

/* Dropdown area (the list of options) */
.select2-container--bootstrap4 .select2-dropdown {
    border: 1px solid #6b7280 !important;
    border-radius: 6px !important;
}

/* Dropdown option highlight */
.select2-container--bootstrap4 .select2-results__option--highlighted {
    background: #0ea5e9 !important;
    color: #fff !important;
}

/* Selected option in results */
.select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
    background: #e0f2fe !important;
    color: #0e7490 !important;
}

</style>

<div class="card">
  <div style="display:grid;grid-template-columns:2fr 2fr auto;gap:.75rem;align-items:end">
    <div>
      <label>Complain *</label>
      <select id="cmp_text" class="form-control select2" required>
        <option value="">-- Select / type complain --</option>
        @foreach(($commoncomplain ?? collect()) as $cc)
          @if(($cc->active ?? 1) == 1)
            <option value="{{ $cc->name }}">{{ $cc->name }}</option>
          @endif
        @endforeach
      </select>
    </div>
    <div>
      <label>Note</label>
      <input id="cmp_note" class="form-control" placeholder="optional">
    </div>
    <div>
      <button id="btnAddCmp" class="btn" style="margin-top:1.6rem">Add</button>
    </div>
  </div>
  <div id="cmpMsg" style="margin-top:.5rem;font-size:.9rem;display:none"></div>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th style="width:45%">Complain</th>
        <th>Note</th>
        <th style="width:160px">Actions</th>
      </tr>
    </thead>
    <tbody id="cmpRows">
      <tr><td colspan="3" class="text-muted">Loading…</td></tr>
    </tbody>
  </table>
</div>

<div style="display:flex;justify-content:space-between;gap:.75rem">
  <a class="btn secondary" href="{{ route($wz.'.wizard',['id'=>$pid,'patient'=>$patientId,'tab'=>'patients']) }}">← Back</a>
  <form method="get" action="{{ route($wz.'.wizard') }}" style="margin:0">
    <input type="hidden" name="id" value="{{ $pid }}">
    <input type="hidden" name="patient" value="{{ $patientId }}">
    <input type="hidden" name="tab" value="oe">
    <button class="btn" type="submit">Next → O/E</button>
  </form>
</div>

<script>
(function(){
  const $ = (s,ctx=document)=>ctx.querySelector(s);
  const pid = {{ $pid }};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const OPTIONS = @json(($commoncomplain ?? collect())->filter(fn($x)=>($x->active ?? 1)==1)->pluck('name')->values());

  const rowsTbody = $('#cmpRows');
  const msg = $('#cmpMsg');

  function showMsg(text, ok=true){
    msg.style.display='block';
    msg.style.color = ok ? '#065f46' : '#b91c1c';
    msg.textContent = text;
    setTimeout(()=>{ msg.style.display='none'; }, 1600);
  }

  function buildOptions(selected){
    const esc = s=>String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
    let out = `<option value="">-- Select / type complain --</option>`;
    (OPTIONS || []).forEach(n=>{
      const sel = (n===selected)?' selected':'';
      out += `<option value="${esc(n)}"${sel}>${esc(n)}</option>`;
    });
    return out;
  }

  function renderList(rows){
    const esc = s=>String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
    if (!rows || rows.length===0) {
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-muted">No complaints added.</td></tr>`;
      return;
    }
    rowsTbody.innerHTML = rows.map(r=>`
      <tr data-id="${r.id}">
        <td>
          <select class="form-control cmp-text">${buildOptions(r.complaint)}</select>
        </td>
        <td>
          <input class="form-control cmp-note" value="${esc(r.note||'')}">
        </td>
        <td style="white-space:nowrap">
          <button type="button" class="btn btn-save">Save</button>
          <button type="button" class="btn danger btn-del">Delete</button>
        </td>
      </tr>
    `).join('');
  }

  async function call(action, payload={}){
    const fd = new FormData();
    fd.append('action', action);
    fd.append('prescription_id', pid);
    fd.append('_token', csrf);
    for (const [k,v] of Object.entries(payload)) fd.append(k, v ?? '');
    const res = await fetch(`{{ route($wz.'.complain.ajax') }}`, {
      method: 'POST',
      headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: fd
    });
    if (res.status === 422) {
      const j = await res.json();
      throw new Error(j?.message || 'Validation error');
    }
    const j = await res.json().catch(()=>({ok:false,error:'Bad JSON'}));
    if (j.ok === false && j.error) throw new Error(j.error);
    return j;
  }

  async function loadList(){
    try{
      const j = await call('list');
      renderList(j.rows||[]);
    }catch(e){
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-danger">Load failed: ${e.message}</td></tr>`;
    }
  }

  // Add

  $('#btnAddCmp')?.addEventListener('click', async ()=>{
    const complaint = $('#cmp_text')?.value || '';
    const note = $('#cmp_note')?.value || '';
    if (!complaint) { showMsg('Select / enter a complaint', false); return; }
    const btn = $('#btnAddCmp'); btn.disabled = true; btn.textContent = 'Saving...';
    try{
      await call('add', { complaint, note });
      // reset
      $('#cmp_text').value = '';
      $('#cmp_note').value = '';
      await loadList();
      showMsg('Added.');
    }catch(e){
      showMsg(e.message || 'Save error', false);
    }finally{
      btn.disabled = false; btn.textContent = 'Add';
    }
  });


  // Inline Save/Delete
  $('#cmpRows')?.addEventListener('click', async (e)=>{
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (e.target.classList.contains('btn-save')) {
      const complaint = tr.querySelector('.cmp-text')?.value || '';
      const note = tr.querySelector('.cmp-note')?.value || '';
      if (!complaint) { showMsg('Complain is required', false); return; }
      const btn = e.target; btn.disabled = true; btn.textContent = 'Saving...';
      try{
        await call('update', { id, complaint, note });
        showMsg('Updated.');
      }catch(err){
        showMsg(err.message || 'Update failed', false);
      }finally{
        btn.disabled = false; btn.textContent = 'Save';
      }
    }

    if (e.target.classList.contains('btn-del')) {
      if (!confirm('Delete this item?')) return;
      const btn = e.target; btn.disabled = true; btn.textContent = 'Deleting...';
      try{
        await call('delete', { id });
        tr.remove();
        if (!$('#cmpRows').children.length) $('#cmpRows').innerHTML = `<tr><td colspan="3" class="text-muted">No complaints added.</td></tr>`;
        showMsg('Deleted.');
      }catch(err){
        showMsg(err.message || 'Delete failed', false);
        btn.disabled = false; btn.textContent = 'Delete';
      }
    }
  });

  // initial load
  loadList();

  // ------------------------------------------------------------
  // Select2 init — jQuery loads AFTER this script tag, so retry
  // until it is available. dropdownParent keeps the dropdown
  // anchored right below the box (no detached/overflowing list).
  // ------------------------------------------------------------
  function initComplainSelect2(){
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#cmp_text').select2({
        theme: 'bootstrap4',
        tags: true, // allow typing new complaints
        placeholder: '-- Select / type complain --',
        width: '100%',
        dropdownParent: jQuery('#cmp_text').parent()
      });
    } else {
      setTimeout(initComplainSelect2, 200);
    }
  }
  initComplainSelect2();
})();
</script>
