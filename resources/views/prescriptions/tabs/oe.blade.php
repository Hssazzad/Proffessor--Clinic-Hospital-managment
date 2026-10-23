@php
  // Expected inputs (same idea as your diagnosis tab):
  // $pid, $patientId, and $masterOptions (collection of {id, name, unit})
  $pid        = (int)($pid ?? request('id'));
  $patientId  = (int)($patientId ?? request('patient'));
  $wz         = $wz ?? 'rx';
  $OE_OPTIONS = collect($masterOptions ?? [])->values();
  // If your master O/E list also has an active flag like commonDiagnoses,
  // chain ->filter(fn($x)=>($x['active'] ?? 1)==1) before ->values() above.
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
      border: 1px solid #6b7280 !important;
      border-radius: 6px !important;
      min-height: 38px !important;
      padding: 4px !important;
  }
  /* On focus */
  .select2-container--bootstrap4.select2-container--focus .select2-selection {
      border-color: #2563eb !important;
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

{{-- ADD NEW (AJAX) --}}
<div class="card">
  <div style="display:grid;grid-template-columns:2fr 1fr auto;gap:.75rem;align-items:end">
    <div>
      <label>O/E Field *</label>
      <select id="oe_field" class="form-control select2" required>
        <option value="">-- Select O/E Field --</option>
        @foreach($OE_OPTIONS as $m)
          <option value="{{ $m['id'] }}">{{ $m['name'] }}{{ $m['unit'] !== '' ? ' ('.$m['unit'].')' : '' }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label>Value *</label>
      <input id="oe_value" class="form-control" placeholder="e.g. 78, 120/80, Normal">
    </div>
    <div>
      <button id="btnAddOe" class="btn" style="margin-top:1.6rem">Add</button>
    </div>
  </div>
  <div id="oeMsg" style="margin-top:.5rem;font-size:.9rem;display:none"></div>
</div>

{{-- LIST (AJAX) --}}
<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th style="width:40%">Field</th>
        <th>Value</th>
        <th style="width:160px">Actions</th>
      </tr>
    </thead>
    <tbody id="oeRows">
      <tr><td colspan="3" class="text-muted">Loading…</td></tr>
    </tbody>
  </table>
</div>

@php
  $tabOrder = ['patients', 'complain', 'oe', 'investigations', 'medicine', 'preview'];
  $currentTab = 'oe';
  $currentIndex = array_search($currentTab, $tabOrder);
  $prevTab = $tabOrder[$currentIndex - 1] ?? 'patients';
  $nextTab = $tabOrder[$currentIndex + 1] ?? 'preview';
@endphp

<div style="display:flex;justify-content:space-between;gap:.75rem">
  <a class="btn secondary"
     href="{{ route($wz.'.wizard', ['id'=>$pid, 'patient'=>$patientId, 'tab'=>$prevTab]) }}">
     ← Back
  </a>
  <form method="get" action="{{ route($wz.'.wizard') }}" style="margin:0">
    <input type="hidden" name="id" value="{{ $pid }}">
    <input type="hidden" name="patient" value="{{ $patientId }}">
    <input type="hidden" name="tab" value="{{ $nextTab }}">
    <button class="btn" type="submit">Next →</button>
  </form>
</div>

<script>
(function(){
  const $ = (s,ctx=document)=>ctx.querySelector(s);
  const pid = {{ $pid }};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const OE_OPTIONS = @json($OE_OPTIONS);

  const rowsTbody = $('#oeRows');
  const msg = $('#oeMsg');
  let usedFieldIds = new Set();

  function showMsg(text, ok=true){
    if (!msg) return;
    msg.style.display='block';
    msg.style.color = ok ? '#065f46' : '#b91c1c';
    msg.textContent = text;
    setTimeout(()=>{ msg.style.display='none'; }, 1600);
  }

  function esc(s){ return String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;'); }

  function fieldLabel(fieldId){
    const opt = OE_OPTIONS.find(o => String(o.id) === String(fieldId));
    if (!opt) return '—';
    return opt.name + (opt.unit ? ` (${opt.unit})` : '');
  }

  function renderList(rows){
    usedFieldIds = new Set((rows||[]).map(r => String(r.field_id)));
    if (!rows || rows.length===0) {
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-muted">No O/E fields added.</td></tr>`;
      return;
    }
    rowsTbody.innerHTML = rows.map(r=>`
      <tr data-id="${r.id}" data-field-id="${r.field_id}">
        <td>${esc(fieldLabel(r.field_id))}</td>
        <td>
          <input class="form-control oe-value" value="${esc(r.value||'')}">
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
    const res = await fetch(`{{ route($wz.'.oe.ajax') }}`, {
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
      rowsTbody.innerHTML = `<tr><td colspan="3" class="text-danger">Load failed: ${esc(e.message)}</td></tr>`;
    }
  }

  // Auto-fill latest PreCon value when an O/E field is selected.
  // NOTE: jQuery & Select2 are loaded at the bottom of the page AFTER this inline
  // script runs, so window.jQuery is still undefined here. Only bind once the
  // document is ready (jQuery available) and ALSO attach a native 'change'
  // listener, because Select2 dispatches the change on the underlying element.
  var oeFieldEl = $('#oe_field');
  var oeValueEl = $('#oe_value');

  function bindOeAutoFill(){
    var fieldEl = oeFieldEl;
    if (!fieldEl) return;
    function onFieldChange(){
      var fieldId = fieldEl.value || '';
      if (!fieldId){ if (oeValueEl) oeValueEl.value=''; return; }
      try{
        call('latest', { field_id: fieldId }).then(function(j){
          if (oeValueEl) oeValueEl.value = (j && j.ok && j.value != null) ? String(j.value) : '';
        }).catch(function(){
          if (oeValueEl) oeValueEl.value = '';
        });
      }catch(e){
        if (oeValueEl) oeValueEl.value = '';
      }
    }
    if (window.jQuery && typeof jQuery !== 'undefined') jQuery(oeFieldEl).on('change', onFieldChange);
    oeFieldEl.addEventListener('change', onFieldChange);
  }

  if (document.readyState !== 'loading') bindOeAutoFill();
  else document.addEventListener('DOMContentLoaded', bindOeAutoFill);

  // Add
  $('#btnAddOe')?.addEventListener('click', async ()=>{
    const fieldEl = $('#oe_field');
    const fieldId = fieldEl?.value || '';
    const value = $('#oe_value')?.value || '';
    if (!fieldId) { showMsg('O/E field select koro', false); return; }
    if (!value) { showMsg('Value likho', false); return; }
    if (usedFieldIds.has(String(fieldId))) { showMsg('Ei field already add kora ache', false); return; }

    const btn = $('#btnAddOe'); btn.disabled = true; btn.textContent = 'Saving...';
    try{
      await call('add', { field_id: fieldId, value });
      $('#oe_value').value = '';
      if (window.jQuery && jQuery.fn.select2) {
        jQuery('#oe_field').val('').trigger('change');
      } else if (fieldEl) {
        fieldEl.value = '';
      }
      await loadList();
      showMsg('Added.');
    }catch(e){
      showMsg(e.message || 'Save error', false);
    }finally{
      btn.disabled = false; btn.textContent = 'Add';
    }
  });

  // Inline Save/Delete
  rowsTbody?.addEventListener('click', async (e)=>{
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (e.target.classList.contains('btn-save')) {
      const value = tr.querySelector('.oe-value')?.value || '';
      if (!value) { showMsg('Value required', false); return; }
      const btn = e.target; btn.disabled = true; btn.textContent = 'Saving...';
      try{
        await call('update', { id, value });
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
        if (!rowsTbody.children.length) rowsTbody.innerHTML = `<tr><td colspan="3" class="text-muted">No O/E fields added.</td></tr>`;
        showMsg('Deleted.');
      }catch(err){
        showMsg(err.message || 'Delete failed', false);
        btn.disabled = false; btn.textContent = 'Delete';
      }
    }
  });

  // initial load
  loadList();
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Use jQuery explicitly so it doesn't conflict with your const $ helper
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('#oe_field').select2({
            theme: 'bootstrap4',
            placeholder: '-- Select O/E Field --',
            width: 'resolve'
        });
    }
});
</script>