@php
  $pid = (int)($pid ?? request('id'));
  $patientId = (int)($patientId ?? request('patient'));
  $wz = $wz ?? 'rx';
  $pName = $patient->patientname ?? '--';
  $pAge = $patient->age ?? '--';
  $pGender = $patient->gender ?? '--';
  $pCode = $patient->patientcode ?? '--';
  $pDoctor = $rx->doctor_name ?? '--';
  $pType = $patient->patient_type ?? '--';
  $pWeight = $vitals->weight ?? '--';
  $pBP = '--';
  if (isset($vitals)) {
    if (!empty($vitals->bp)) {
      $pBP = $vitals->bp;
    } elseif (!empty($vitals->bp_sys)) {
      $pBP = $vitals->bp_sys . '/' . ($vitals->bp_dia ?? '--');
    }
  }
@endphp
@if (!$pid)
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    No prescription selected. Save Patient first.
  </div>
  @php return; @endphp
@endif
{{-- Tailwind CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<div class="max-w-full bg-white text-slate-800">
  {{-- Patient Summary --}}
  <div class="flex flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3 mb-5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
    <div><span class="text-slate-500 font-semibold text-xs uppercase">Patient</span> <strong class="ml-1">{{ $pName }}</strong></div>
    <div><span class="text-slate-500 font-semibold text-xs uppercase">Age/Sex</span> <strong class="ml-1">{{ $pAge }}/{{ $pGender && $pGender !== '--' ? substr($pGender,0,1) : $pGender }}</strong></div>
    <div><span class="text-slate-500 font-semibold text-xs uppercase">UHID</span> <strong class="ml-1">{{ $pCode }}</strong></div>
    <div><span class="text-slate-500 font-semibold text-xs uppercase">Doctor</span> <strong class="ml-1">{{ $pDoctor }}</strong></div>
    <div><span class="text-slate-500 font-semibold text-xs uppercase">Wt</span> <strong class="ml-1">{{ $pWeight }}{{ is_numeric($pWeight) ? 'kg' : '' }}</strong></div>
    <div><span class="text-slate-500 font-semibold text-xs uppercase">BP</span> <strong class="ml-1">{{ $pBP }}</strong></div>
  </div>
  {{-- Medicine Entry --}}
  <div class="mb-6">
    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-4 pb-2 border-b-2 border-slate-200">Medicine Entry</h3>
    {{-- Search + Common + Quick Add --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4">
      <div class="md:col-span-5">
        <label for="medSearchInput" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Search Medicine</label>
        <input type="text" id="medSearchInput" name="medSearchInput" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Type medicine name..." autocomplete="off" autofocus>
      </div>
      <div class="md:col-span-3 relative">
        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">&nbsp;</label>
        <div id="commonMedSection" style="display:none;">
          <button type="button" id="commonMedBtn" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white text-left flex justify-between items-center">
            <span id="commonMedBtnText">Common Medicines</span>
            <span class="text-slate-400 text-xs">▼</span>
          </button>
          <div id="commonMedPanel" class="absolute top-full left-0 right-0 z-50 hidden bg-white border border-slate-200 rounded-b-lg shadow-lg">
            <div class="p-2 border-b">
              <input type="text" id="commonMedSearch" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm" placeholder="Search common..." autocomplete="off">
            </div>
            <div class="max-h-60 overflow-y-auto">
              <table class="w-full text-sm">
                <thead class="bg-slate-50 sticky top-0">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Medicine</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Strength</th>
                    <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Type</th>
                  </tr>
                </thead>
                <tbody id="commonMedList">
                  <tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">&nbsp;</label>
        <button type="button" id="quickAddBtn" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
          + Quick Add
        </button>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">&nbsp;</label>
        <input type="text" id="quickAddInput" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm" placeholder="Quick add..." style="display:none;" autocomplete="off">
      </div>
    </div>
    {{-- Filters --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      <div>
        <label for="filterType" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Type</label>
        <select id="filterType" name="filterType" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- All Types --</option>
          @foreach($medicineTypes as $t)
            <option value="{{ $t->id }}">{{ $t->type_name }}</option>
          @endforeach
        </select>
      </div>
      <div id="subTypeFilterWrap" style="display:none;">
        <label for="filterSubType" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Sub Type</label>
        <select id="filterSubType" name="filterSubType" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white">
          <option value="">-- All Sub Types --</option>
        </select>
      </div>
      <div>
        <label for="filterCompany" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Company</label>
        <select id="filterCompany" name="filterCompany" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white">
          <option value="">-- All Companies --</option>
          @foreach($companies as $c)
            <option value="{{ $c->name }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label for="filterForm" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Form</label>
        <select id="filterForm" name="filterForm" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white">
          <option value="">-- All Forms --</option>
          @foreach($dosageForms as $f)
            <option value="{{ $f->id }}">{{ $f->dosage_name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    {{-- Results --}}
    <div class="mb-4">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-xs font-semibold text-slate-500 uppercase">Matching Medicines</span>
        <span id="medResultCount" class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs font-semibold rounded">0</span>
      </div>
      <div class="border border-slate-200 rounded-lg max-h-56 overflow-y-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 sticky top-0">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-500 w-10">#</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Medicine</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Strength</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Group</th>
              <th class="px-3 py-2 text-left text-xs font-bold text-slate-500">Company</th>
              <th class="px-3 py-2 w-20"></th>
            </tr>
          </thead>
          <tbody id="medResults">
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                <div class="text-2xl mb-2">🔍</div>
                <p>Type medicine name or select filters above.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    {{-- Selected + Form --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
        <div class="md:col-span-5">
          <label for="selectedMedText" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Selected Medicine</label>
          <input type="hidden" id="med_name" name="med_name" value="">
          <input type="hidden" id="med_route" name="med_route" value="">
          <div id="selectedMedBox" class="h-10 px-3 flex items-center border border-slate-300 rounded-lg bg-white text-sm font-semibold text-slate-400">
            <span id="selectedMedText">Pick a medicine from the list above</span>
          </div>
        </div>
        <div class="md:col-span-2">
          <label for="med_strength" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Strength</label>
          <input type="text" id="med_strength" name="med_strength" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 500mg" autocomplete="off">
        </div>
        <div class="md:col-span-2">
          <label for="med_dose" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Dose</label>
          <select id="med_dose" name="med_dose" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Select --</option>
            <option value="½ Tablet">1/2 Tablet</option>
            <option value="1 Tablet">1 Tablet</option>
            <option value="2 Tablet">2 Tablet</option>
            <option value="3 Tablet">3 Tablet</option>
            <option value="1 Capsule">1 Capsule</option>
            <option value="2 Capsule">2 Capsule</option>
            <option value="1 Tsp">1 Tsp</option>
            <option value="2 Tsp">2 Tsp</option>
            <option value="1 Ampoule">1 Ampoule</option>
            <option value="2 Ampoule">2 Ampoule</option>
            <option value="As directed">As directed</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label for="med_frequency" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Frequency</label>
          <select id="med_frequency" name="med_frequency" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Select --</option>
            <option value="1+0+0">1+0+0 (Once Daily)</option>
            <option value="1+0+1">1+0+1 (Morning + Night)</option>
            <option value="1+1+0">1+1+0 (Morning + Noon)</option>
            <option value="1+1+1">1+1+1 (Three Times)</option>
            <option value="0+0+1">0+0+1 (At Night)</option>
            <option value="0+1+0">0+1+0 (At Noon)</option>
            <option value="0+1+1">0+1+1 (Noon + Night)</option>
            <option value="Stat">Stat</option>
            <option value="SOS">SOS</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-2">
          <label for="med_duration" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Duration</label>
          <input type="text" id="med_duration" name="med_duration" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 7 days" autocomplete="off">
        </div>
        <div class="md:col-span-2">
          <label for="med_timing" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Timing</label>
          <select id="med_timing" name="med_timing" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="After Meal">After Meal</option>
            <option value="Before Meal">Before Meal</option>
            <option value="With Meal">With Meal</option>
            <option value="Empty Stomach">Empty Stomach</option>
            <option value="At Bedtime">At Bedtime</option>
          </select>
        </div>
        <div class="md:col-span-5">
          <label for="med_note" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Instruction</label>
          <input type="text" id="med_note" name="med_note" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional instructions..." autocomplete="off">
        </div>
        <div class="md:col-span-3">
          <label class="block text-xs font-semibold text-slate-500 uppercase mb-1 invisible">Add</label>
          <button type="button" id="btnAddMed" class="w-full h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm flex items-center justify-center gap-2">
            + Add Medicine
          </button>
        </div>
      </div>
      <div id="medMsg" class="mt-3 text-sm hidden"></div>
    </div>
  </div>
  {{-- Prescribed List --}}
  <div class="border-t border-slate-200 pt-5 mb-6">
    <div class="flex items-center gap-2 mb-3">
      <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Prescribed Medicines</h3>
      <span id="medCount" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">0</span>
    </div>
    <div class="border border-slate-200 rounded-lg overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500 w-10">#</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Medicine</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Strength</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Dose</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Frequency</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Duration</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Timing</th>
            <th class="px-3 py-2.5 text-left text-xs font-bold text-slate-500">Remarks</th>
            <th class="px-3 py-2.5 w-16"></th>
          </tr>
        </thead>
        <tbody id="medRows">
          <tr>
            <td colspan="9" class="px-4 py-10 text-center text-slate-400">
              <div class="text-3xl mb-2">💊</div>
              <p>No medicines added yet.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  {{-- Navigation --}}
  <div class="flex justify-between items-center pt-4 border-t border-slate-200">
    <a href="{{ route($wz.'.wizard',['id'=>$pid,'patient'=>$patientId,'tab'=>'investigations']) }}" class="h-10 px-5 border border-slate-300 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-2">
      ← Back to Investigations
    </a>
    <form method="get" action="{{ route($wz.'.wizard') }}">
      <input type="hidden" name="id" value="{{ $pid }}">
      <input type="hidden" name="patient" value="{{ $patientId }}">
      <input type="hidden" name="tab" value="doctor">
      <button type="submit" class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm">
        Next: Doctor →
      </button>
    </form>
  </div>
</div>
{{-- Toast --}}
<div id="qaToast" class="fixed bottom-6 right-6 z-50 min-w-[260px] px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-lg translate-y-32 opacity-0 transition-all duration-300"></div>
{{-- Quick Add Modal --}}
<div id="quickAddModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4">
    <div class="px-5 py-4 border-b flex justify-between items-center">
      <h5 class="font-bold text-slate-800">Quick Add Medicine</h5>
      <button type="button" id="qaCloseBtn" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
    </div>
    <div class="p-5 space-y-4">
      <div>
        <label for="qaMedName" class="block text-xs font-semibold text-slate-600 mb-1">Medicine Name <span class="text-red-500">*</span></label>
        <input type="text" id="qaMedName" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm" placeholder="e.g. Amoxicillin" autocomplete="off">
      </div>
      <div>
        <label for="qaForm" class="block text-xs font-semibold text-slate-600 mb-1">Type of Medicine <span class="text-red-500">*</span></label>
        <select id="qaForm" class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white">
          <option value="">-- Select Type --</option>
          @foreach($dosageForms as $f)
            <option value="{{ $f->id }}">{{ $f->dosage_name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="px-5 py-3 border-t flex justify-end gap-2">
      <button type="button" id="qaCancelBtn" class="h-9 px-4 border border-slate-300 rounded-lg text-sm font-semibold text-slate-600">Cancel</button>
      <button type="button" id="qaSaveBtn" class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">Save & Use</button>
    </div>
  </div>
</div>
<script>
(function(){
  var pid = {{ $pid }};
  var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  var afterCreateId = null;
  var searchTimer = null;
  function $(id){ return document.getElementById(id); }
  function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function showMsg(text, ok){
    var el = $('medMsg');
    if(!el) return;
    el.classList.remove('hidden');
    el.className = 'mt-3 text-sm ' + (ok !== false ? 'text-emerald-700' : 'text-red-600');
    el.textContent = text;
    setTimeout(function(){ el.classList.add('hidden'); }, 3000);
  }
  function showToast(msg, type){
    var t = $('qaToast');
    t.textContent = msg;
    t.className = 'fixed bottom-6 right-6 z-50 min-w-[260px] px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-lg transition-all duration-300 ' + (type==='error'?'bg-red-700':'bg-emerald-800');
    t.style.transform = 'translateY(0)';
    t.style.opacity = '1';
    setTimeout(function(){ t.style.transform='translateY(8rem)'; t.style.opacity='0'; }, 3000);
  }
  // ===== List =====
  async function call(action, payload){
    var fd = new FormData();
    fd.append('action', action);
    fd.append('prescription_id', pid);
    fd.append('_token', csrf);
    if(payload) Object.keys(payload).forEach(function(k){ fd.append(k, payload[k]??''); });
    var res = await fetch('{{ route($wz.".med.ajax") }}', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body:fd });
    if(res.status===422){ var j=await res.json(); throw new Error(j.message||'Validation error'); }
    var j = await res.json().catch(function(){ return {ok:false,error:'Bad JSON'}; });
    if(j.ok===false && j.error) throw new Error(j.error);
    return j;
  }
  function renderList(rows){
    var tbody = $('medRows');
    var count = $('medCount');
    if(!rows || !rows.length){
      tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-10 text-center text-slate-400"><div class="text-3xl mb-2">💊</div><p>No medicines added yet.</p></td></tr>';
      if(count) count.textContent = '0';
      return;
    }
    if(count) count.textContent = rows.length;
    tbody.innerHTML = rows.map(function(r,i){
      return '<tr class="border-t border-slate-100 hover:bg-slate-50" data-id="'+r.id+'">'+
        '<td class="px-3 py-2.5">'+(i+1)+'</td>'+
        '<td class="px-3 py-2.5 font-semibold">'+esc(r.name)+'</td>'+
        '<td class="px-3 py-2.5">'+esc(r.strength||'')+'</td>'+
        '<td class="px-3 py-2.5">'+esc(r.dose||'')+'</td>'+
        '<td class="px-3 py-2.5"><span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded">'+esc(r.frequency||'')+'</span></td>'+
        '<td class="px-3 py-2.5">'+esc(r.duration||'')+'</td>'+
        '<td class="px-3 py-2.5">'+esc(r.timing||'')+'</td>'+
        '<td class="px-3 py-2.5">'+esc(r.note||'')+'</td>'+
        '<td class="px-3 py-2.5"><button type="button" class="btn-del h-8 w-8 rounded bg-red-50 text-red-600 hover:bg-red-100 text-sm">🗑</button></td>'+
      '</tr>';
    }).join('');
  }
  async function loadList(){
    try{ var j = await call('list'); renderList(j.rows||[]); }
    catch(e){ $('medRows').innerHTML = '<tr><td colspan="9" class="px-4 py-6 text-center text-red-500">Load failed</td></tr>'; }
  }
  // ===== Select medicine (note from Medicine Master is prefilled into Instruction) =====
  function fillMedicine(name, strength, note){
    $('med_name').value = name;
    $('med_strength').value = strength || '';
    if($('med_note')) $('med_note').value = note || '';
    var box = $('selectedMedBox');
    box.className = 'h-10 px-3 flex items-center border border-blue-400 rounded-lg bg-blue-50 text-sm font-semibold text-slate-800';
    $('selectedMedText').textContent = name + (strength ? ' — ' + strength : '');
  }
  function clearSelection(){
    $('med_name').value = '';
    $('med_strength').value = '';
    var box = $('selectedMedBox');
    box.className = 'h-10 px-3 flex items-center border border-slate-300 rounded-lg bg-white text-sm font-semibold text-slate-400';
    $('selectedMedText').textContent = 'Pick a medicine from the list above';
  }
  // ===== Results =====
  function renderResults(rows){
    var tbody = $('medResults');
    var count = $('medResultCount');
    if(count) count.textContent = rows.length;
    if(!rows.length){
      tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400"><p>No medicines match.</p><button type="button" id="quickAddFromEmpty" class="mt-2 text-sm text-blue-600 font-semibold">+ Quick Add</button></td></tr>';
      var btn = $('quickAddFromEmpty');
      if(btn) btn.onclick = function(){ openQuickAdd($('medSearchInput')?.value || ''); };
      return;
    }
    tbody.innerHTML = rows.map(function(m,i){
      return '<tr class="border-t border-slate-100 hover:bg-blue-50 cursor-pointer med-result-row" data-name="'+esc(m.name)+'" data-strength="'+esc(m.strength||'')+'" data-note="'+esc(m.note||'')+'" data-id="'+m.id+'">'+
        '<td class="px-3 py-2">'+(i+1)+'</td>'+
        '<td class="px-3 py-2 font-semibold">'+esc(m.name)+'</td>'+
        '<td class="px-3 py-2">'+esc(m.strength||'—')+'</td>'+
        '<td class="px-3 py-2">'+esc(m.GroupName||'—')+'</td>'+
        '<td class="px-3 py-2">'+esc(m.company||'—')+'</td>'+
        '<td class="px-3 py-2"><button type="button" class="text-xs px-2 py-1 border border-slate-300 rounded hover:bg-white">Select</button></td>'+
      '</tr>';
    }).join('');
  }
  // ===== AJAX Search + Filters (type name → show results) =====
  async function loadMedicines(){
    var q = ($('medSearchInput')?.value || '').trim();
    var typeId = $('filterType')?.value || '';
    var subTypeId = $('filterSubType')?.value || '';
    var company = $('filterCompany')?.value || '';
    var formId = $('filterForm')?.value || '';
    clearSelection();
    if(!q && !typeId && !subTypeId && !company && !formId){
      $('medResultCount').textContent = '0';
      $('medResults').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400"><div class="text-2xl mb-2">🔍</div><p>Type medicine name or select filters above.</p></td></tr>';
      return;
    }
    var params = new URLSearchParams();
    if(q) params.set('q', q);
    if(company) params.set('company', company);
    if(typeId) params.set('medicine_type_id', typeId);
    if(subTypeId) params.set('medicine_sub_type_id', subTypeId);
    if(formId) params.set('dosage_form_id', formId);
    $('medResults').innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Searching...</td></tr>';
    try{
      var res = await fetch('{{ route("ajax.filtered.medicines") }}?'+params.toString());
      var data = await res.json();
      var rows = Array.isArray(data) ? data : (data.medicines || data.data || data.rows || []);
      renderResults(rows);
    }catch(e){
      $('medResults').innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-red-500">Could not load</td></tr>';
    }
  }
  // Filter change
  ['filterType','filterSubType','filterCompany','filterForm'].forEach(function(id){
    var el = $(id);
    if(el) el.addEventListener('change', function(){
      if(id==='filterType'){
        var wrap = $('subTypeFilterWrap');
        var sel = $('filterSubType');
        sel.innerHTML = '<option value="">-- All Sub Types --</option>';
        wrap.style.display = 'none';
        if(this.value){
          fetch('{{ route("ajax.medicine.subtypes") }}?medicine_type_id='+this.value)
            .then(r=>r.json()).then(function(data){
              if(Array.isArray(data) && data.length){
                data.forEach(function(st){
                  var o = document.createElement('option');
                  o.value = st.id; o.textContent = st.subtype_name;
                  sel.appendChild(o);
                });
                wrap.style.display = '';
              }
            });
        }
      }
      loadMedicines();
    });
  });
  // Click result
  $('medResults').addEventListener('click', function(e){
    var tr = e.target.closest('.med-result-row');
    if(!tr) return;
    fillMedicine(tr.getAttribute('data-name'), tr.getAttribute('data-strength'), tr.getAttribute('data-note'));
  });
  // AJAX search on type (debounce 300ms) — 1 letter thekei start
  $('medSearchInput').addEventListener('input', function(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function(){ loadMedicines(); }, 300);
  });
  // Add medicine
  $('btnAddMed').addEventListener('click', async function(){
    var payload = {
      name: $('med_name').value,
      strength: $('med_strength').value,
      dose: $('med_dose').value,
      frequency: $('med_frequency').value,
      duration: $('med_duration').value,
      timing: $('med_timing').value,
      note: $('med_note').value,
      route: ''
    };
    if(!payload.name){ showMsg('Please select a medicine first.', false); return; }
    this.disabled = true;
    this.textContent = 'Saving...';
    try{
      await call('add', payload);
      ['med_dose','med_frequency','med_duration','med_timing','med_note'].forEach(function(id){ if($(id)) $(id).value=''; });
      clearSelection();
      await loadList();
      showMsg('Medicine added successfully.');
      $('medSearchInput').focus();
    }catch(e){ showMsg(e.message||'Save error', false); }
    finally{ this.disabled=false; this.innerHTML='+ Add Medicine'; }
  });
  // Delete
  $('medRows').addEventListener('click', async function(e){
    var btn = e.target.closest('.btn-del');
    if(!btn) return;
    var tr = btn.closest('tr[data-id]');
    if(!tr || !confirm('Delete this medicine?')) return;
    try{
      await call('delete', {id: tr.getAttribute('data-id')});
      showMsg('Deleted.');
      await loadList();
    }catch(err){ showMsg(err.message||'Delete failed', false); }
  });
  // Common medicines
  var cmMedicines = [];
  async function loadCommonMedicines(){
    try{
      // The endpoint paginates (12 per page) for "lazy loading", but the panel
      // does client-side search, so load ALL pages once and join them.
      var all = [], offset = 0;
      for (;;) {
        var res = await fetch('{{ route("ajax.commonly.used.medicines") }}?offset='+offset);
        var data = await res.json();
        var page = data.medicines || [];
        if (!page.length) break;
        all = all.concat(page);
        offset = (data.loaded != null) ? data.loaded : (offset + page.length);
        if (data.total != null && offset >= data.total) break;
      }
      cmMedicines = all;
      if(!cmMedicines.length){ $('commonMedSection').style.display='none'; return; }
      $('commonMedSection').style.display = '';
      renderCmList(cmMedicines);
    }catch(e){ $('commonMedSection').style.display='none'; }
  }
  function renderCmList(items){
    var tbody = $('commonMedList');
    if(!items.length){ tbody.innerHTML='<tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">No medicines</td></tr>'; return; }
    tbody.innerHTML = items.map(function(m){
      return '<tr class="hover:bg-blue-50 cursor-pointer" data-name="'+esc(m.name)+'" data-strength="'+esc(m.strength||'')+'" data-note="'+esc(m.note||'')+'">'+
        '<td class="px-3 py-2 font-semibold">'+esc(m.name)+'</td>'+
        '<td class="px-3 py-2 text-slate-500">'+esc(m.strength||'—')+'</td>'+
        '<td class="px-3 py-2 text-slate-500">'+esc(m.dosage_form||m.form_name||'—')+'</td></tr>';
    }).join('');
  }
  $('commonMedBtn').addEventListener('click', function(e){
    e.stopPropagation();
    var panel = $('commonMedPanel');
    panel.classList.toggle('hidden');
  });
  document.addEventListener('click', function(e){
    if(!$('commonMedSection').contains(e.target)) $('commonMedPanel').classList.add('hidden');
  });
  $('commonMedSearch').addEventListener('input', function(){
    var q = this.value.toLowerCase();
    renderCmList(cmMedicines.filter(function(m){
      return !q || (m.name||'').toLowerCase().indexOf(q)!==-1 || (m.strength||'').toLowerCase().indexOf(q)!==-1;
    }));
  });
  $('commonMedList').addEventListener('click', function(e){
    var tr = e.target.closest('tr[data-name]');
    if(!tr) return;
    fillMedicine(tr.getAttribute('data-name'), tr.getAttribute('data-strength'), tr.getAttribute('data-note'));
    $('commonMedPanel').classList.add('hidden');
    showMsg('"'+tr.getAttribute('data-name')+'" selected');
  });
  // Quick Add Modal
  function openQuickAdd(name){
    $('qaMedName').value = name||'';
    $('qaForm').value = '';
    $('quickAddModal').classList.remove('hidden');
    $('quickAddModal').classList.add('flex');
    $('qaMedName').focus();
  }
  function closeQuickAdd(){
    $('quickAddModal').classList.add('hidden');
    $('quickAddModal').classList.remove('flex');
  }
  $('quickAddBtn').onclick = function(){ openQuickAdd(''); };
  $('qaCloseBtn').onclick = closeQuickAdd;
  $('qaCancelBtn').onclick = closeQuickAdd;
  window.openQuickAdd = openQuickAdd;
  $('qaSaveBtn').addEventListener('click', async function(){
    var name = $('qaMedName').value.trim();
    var formId = $('qaForm').value;
    if(!name){ $('qaMedName').focus(); return; }
    if(!formId){ $('qaForm').focus(); return; }
    this.disabled = true;
    this.textContent = 'Saving...';
    try{
      var res = await fetch('{{ route("ajax.quick.add.medicine") }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({medicine_name:name, dosage_form_id:formId})
      });
      var data = await res.json();
      if(!data.ok) throw new Error(data.message||'Save failed');
      closeQuickAdd();
      afterCreateId = data.medicine?.id;
      loadMedicines();
      showToast(data.duplicate ? 'Already exists, selected.' : 'Medicine added.', 'success');
    }catch(e){ showToast(e.message||'Failed', 'error'); }
    finally{ this.disabled=false; this.textContent='Save & Use'; }
  });
  // Init
  loadList();
  loadMedicines();
  loadCommonMedicines();
})();
</script>