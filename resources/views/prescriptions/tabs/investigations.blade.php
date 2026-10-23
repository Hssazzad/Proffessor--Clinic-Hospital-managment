{{-- resources/views/prescriptions/tabs/investigations.blade.php --}}
@php
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

<div class="card">
  <div style="display:grid;grid-template-columns:2fr 2fr auto;gap:.75rem;align-items:end">
    <div>
      <label>Investigation *</label>
      <select id="inv_name" class="form-control select2" required>
        <option value="">-- Select Investigation --</option>
        @foreach(($commonInvestigations ?? collect()) as $ci)
          @if(($ci->active ?? 1) == 1)
            <option value="{{ $ci->name }}">{{ $ci->name }}</option>
          @endif
        @endforeach
      </select>
    </div>
    <div>
      <label>Note</label>
      <input id="inv_note" class="form-control" placeholder="optional">
    </div>
    <div>
      <button id="btnAddInv" class="btn" style="margin-top:1.6rem">Add</button>
    </div>
  </div>
  <div id="invMsg" style="margin-top:.5rem;font-size:.9rem;display:none"></div>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th style="width:45%">Investigation</th>
        <th>Note</th>
        <th style="width:160px">Actions</th>
      </tr>
    </thead>
    <tbody id="invRows">
      <tr><td colspan="3" class="text-muted">Loading…</td></tr>
    </tbody>
  </table>
</div>

<div style="display:flex;justify-content:space-between;gap:.75rem">
  <a class="btn secondary" href="{{ route($wz.'.wizard',['id'=>$pid,'patient'=>$patientId,'tab'=>'oe']) }}">← Back</a>
  <form method="get" action="{{ route($wz.'.wizard') }}" style="margin:0">
    <input type="hidden" name="id" value="{{ $pid }}">
    <input type="hidden" name="patient" value="{{ $patientId }}">
    <input type="hidden" name="tab" value="medicine">
    <button class="btn" type="submit">Next → Medicine</button>
  </form>
</div>

{{-- ========== MAIN AJAX SCRIPT ========== --}}
<script>
(function(){
  const $ = (s,ctx=document)=>ctx.querySelector(s);
  const pid = {{ $pid }};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const OPTIONS = @json(($commonInvestigations ?? collect())->filter(fn($x)=>($x->active ?? 1)==1)->pluck('name')->values());
  const rowsTbody = $('#invRows');
  const msg = $('#invMsg');

  function showMsg(text, ok=true){
    if (!msg) return;
    msg.style.display='block';
    msg.style.color = ok ? '#065f46' : '#b91c1c';
    msg.textContent = text;
    setTimeout(()=>{ msg.style.display='none'; }, 1600);
  }

  function buildOptions(selected){
    const esc = s=>String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
    let out = `<option value="">-- Select Investigation --</option>`;
    OPTIONS.forEach(n=>{
      out += `<option value="${esc(n)}"${n===selected?' selected':''}>${esc(n)}</option>`;
    });
    return out;
  }

  function renderList(rows){
    const esc = s=>String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
    if (!rows || rows.length===0) {
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-muted">No investigations added.</td></tr>`;
      return;
    }
    rowsTbody.innerHTML = rows.map(r=>`
      <tr data-id="${r.id}">
        <td><select class="form-control inv-name">${buildOptions(r.name)}</select></td>
        <td><input class="form-control inv-note" value="${esc(r.note||'')}"></td>
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
    const res = await fetch(`{{ route($wz.'.inv.ajax') }}`, {
      method: 'POST',
      headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: fd
    });
    const j = await res.json().catch(()=>({ok:false,error:'Bad JSON'}));
    if (!j.ok) throw new Error(j.error || 'Error');
    return j;
  }

  async function loadList(){
    try{
      const j = await call('list');
      renderList(j.rows || []);
    }catch(e){
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-danger">Load failed: ${e.message}</td></tr>`;
    }
  }

  $('#btnAddInv')?.addEventListener('click', async ()=>{
    const name = $('#inv_name')?.value || '';
    const note = $('#inv_note')?.value || '';
    if (!name) { showMsg('Select investigation', false); return; }
    const btn = $('#btnAddInv'); btn.disabled = true; btn.textContent = 'Saving...';
    try{
      await call('add', { name, note });
      $('#inv_name').value = '';
      $('#inv_note').value = '';
      await loadList();
      showMsg('Added.');
    }catch(e){
      showMsg(e.message || 'Save error', false);
    }finally{
      btn.disabled = false; btn.textContent = 'Add';
    }
  });

  rowsTbody?.addEventListener('click', async (e)=>{
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (e.target.classList.contains('btn-save')) {
      const name = tr.querySelector('.inv-name')?.value || '';
      const note = tr.querySelector('.inv-note')?.value || '';
      if (!name) { showMsg('Name required', false); return; }
      const btn = e.target; btn.disabled = true; btn.textContent = 'Saving...';
      try{
        await call('update', { id, name, note });
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
        if (!rowsTbody.children.length)
          rowsTbody.innerHTML = `<tr><td colspan="3" class="text-muted">No investigations added.</td></tr>`;
        showMsg('Deleted.');
      }catch(err){
        showMsg(err.message || 'Delete failed', false);
        btn.disabled = false; btn.textContent = 'Delete';
      }
    }
  });

  loadList();
})();
</script>

{{-- ============= SELECT2 INIT (RUNS AFTER PLUGINS) ============= --}}
@push('js')
<script>
jQuery(function($){

    if (!$.fn.select2) {
        console.warn('Select2 plugin not loaded.');
        return;
    }

    const $inv = $('#inv_name');

    $inv.select2({
        theme: 'bootstrap4',
        tags: true,
        placeholder: '-- Select / type Investigation --',
        width: '100%'
    });

    // Auto-focus search when opened
    $inv.on('select2:open', function(e){
        const id = e.target.id;
        const selector = ".select2-search__field[aria-controls='select2-" + id + "-results']";
        setTimeout(function(){
            const el = document.querySelector(selector);
            if (el) el.focus();
        }, 0);
    });

});
</script>
@endpush
