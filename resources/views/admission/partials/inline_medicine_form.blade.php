@php
  $companies = \App\Models\Company::where('active', 1)->orderBy('name')->get();
  $medicineTypes = \App\Models\MedicineType::where('active', 1)->orderBy('type_name')->get();
  $dosageForms = \App\Models\DosageForm::where('active', 1)->orderBy('dosage_name')->get();
@endphp

<style>
  .filter-label {
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #64748b;
    margin-bottom: .3rem;
  }
  .erp-select, .erp-input {
    border: 1.5px solid #d9e2ef;
    border-radius: 8px;
    padding: .45rem .75rem;
    font-size: .9rem;
    width: 100%;
    transition: border-color .2s;
  }
  .erp-select:focus, .erp-input:focus {
    border-color: #0ea5e9;
    outline: none;
    box-shadow: 0 0 0 3px rgba(14,165,233,.12);
  }
  .btn-erp {
    padding: .5rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: .85rem;
    border: none;
    cursor: pointer;
    transition: all .2s;
  }
  .btn-erp-success {
    background: #10b981;
    color: #fff;
  }
  .btn-erp-success:hover { background: #059669; }
  .btn-erp-ghost {
    background: transparent;
    color: #64748b;
    border: 1.5px solid #d9e2ef;
  }
  .btn-erp-ghost:hover { background: #f8fafc; }
  .btn-erp-sm { padding: .35rem .8rem; font-size: .8rem; }
  .filter-card {
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,.03);
  }
  .filter-card-title {
    font-size: .9rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: .85rem;
    padding-bottom: .6rem;
    border-bottom: 2px solid #eef2f7;
  }
  .filter-card-title i { color: #0ea5e9; margin-right: .5rem; }
  .med-search-wrapper .select2-container--bootstrap4 { width: 100% !important; }
  .med-search-wrapper .select2-container--bootstrap4 .select2-selection {
    border: 1.5px solid #d9e2ef !important;
    border-radius: 8px !important;
    min-height: 40px !important;
    padding: 2px 4px !important;
  }
  .med-search-wrapper .select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 0 3px rgba(14,165,233,.12) !important;
  }
</style>

{{-- ===================== FILTER SECTION ===================== --}}
<div class="filter-card no-print">
  <div class="filter-card-title"><i class="fas fa-sliders-h"></i> Filter Medicines</div>
  <div class="row">
    <div class="col-md-4 mb-3 mb-md-0">
      <div class="filter-label"><i class="fas fa-building mr-1"></i> Company</div>
      <select id="filterCompany" class="erp-select">
        <option value="">-- All Companies --</option>
        @foreach($companies as $c) <option value="{{ $c->name }}">{{ $c->name }}</option> @endforeach
      </select>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
      <div class="filter-label"><i class="fas fa-tag mr-1"></i> Medicine Type</div>
      <select id="filterType" class="erp-select">
        <option value="">-- All Types --</option>
        @foreach($medicineTypes as $t) <option value="{{ $t->id }}">{{ $t->type_name }}</option> @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <div class="filter-label"><i class="fas fa-pills mr-1"></i> Dosage Form</div>
      <select id="filterForm" class="erp-select">
        <option value="">-- All Forms --</option>
        @foreach($dosageForms as $f) <option value="{{ $f->id }}">{{ $f->dosage_name }}</option> @endforeach
      </select>
    </div>
  </div>
</div>

{{-- ===================== MEDICINE ENTRY FORM ===================== --}}
<form id="medicineFormInline">
  @csrf
  <input type="hidden" name="id"          id="inf_id">
  <input type="hidden" name="patientcode" id="inf_patientcode" value="{{ $patientcode }}">
  <input type="hidden" name="order_type"  id="inf_order_type"  value="{{ $orderType }}">
  <input type="hidden" name="templeteid"  id="inf_templeteid"  value="TPL-000001">

  {{-- Hidden fields for dose schedule --}}
  <input type="hidden" name="morning" id="inf_morning" value="">
  <input type="hidden" name="noon"    id="inf_noon"    value="">
  <input type="hidden" name="night"   id="inf_night"   value="">

  <div class="row">
    <div class="col-md-6 mb-3">
      <div class="filter-label"><i class="fas fa-capsules mr-1"></i> Medicine <span class="text-danger">*</span></div>
      <div class="med-search-wrapper">
        <select name="name" id="inf_name" class="form-control select2erp" style="width:100%" required>
          <option value="">-- Select Medicine --</option>
          @foreach($medicine as $med)
            <option value="{{ $med->name }}"
              data-company="{{ $med->company ?? '' }}"
              data-type="{{ $med->medicine_type_id ?? '' }}"
              data-form="{{ $med->dosage_form_id ?? '' }}"
              data-strength="{{ $med->strength ?? '' }}">
              {{ $med->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="filter-label"><i class="fas fa-flask mr-1"></i> Strength</div>
      <input type="text" name="strength" id="inf_strength" class="erp-input" placeholder="Auto-filled" readonly>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="filter-label"><i class="fas fa-hourglass-half mr-1"></i> Duration</div>
      <input type="text" name="duration" id="inf_duration" class="erp-input" placeholder="e.g. 7 days, 5 days, Continue">
    </div>
    <div class="col-md-4 mb-3">
      <div class="filter-label"><i class="fas fa-utensils mr-1"></i> Timing</div>
      <select name="timing" id="inf_timing" class="erp-select">
        <option value="">-- Select --</option>
        <option value="before">Before Meal</option>
        <option value="after">After Meal</option>
        <option value="with">With Meal</option>
        <option value="empty">Empty Stomach</option>
        <option value="bedtime">At Bedtime</option>
      </select>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="filter-label"><i class="fas fa-sticky-note mr-1"></i> Special Instructions</div>
      <input type="text" name="instruction" id="inf_instruction" class="erp-input" placeholder="Optional instructions for this medicine...">
    </div>
  </div>

  <div style="display:flex;gap:8px;justify-content:flex-end;">
    <button type="reset" class="btn-erp btn-erp-ghost btn-erp-sm">
      <i class="fas fa-undo"></i> Reset
    </button>
    <button type="submit" class="btn-erp btn-erp-success btn-erp-sm">
      <i class="fas fa-plus-circle"></i> Add Medicine
    </button>
  </div>
</form>

{{-- ===================== CLIENT-SIDE FILTER + FORM BEHAVIOR ===================== --}}
<script>
(function(){
  var $ = function(s,ctx){ return (ctx||document).querySelector(s); };
  var $$ = function(s,ctx){ return (ctx||document).querySelectorAll(s); };

  /* Auto-fill strength on medicine change */
  $('#inf_name')?.addEventListener('change', function(){
    var opt = this.options[this.selectedIndex];
    var strength = opt ? (opt.getAttribute('data-strength') || '') : '';
    $('#inf_strength').value = strength;
  });

  /* Client-side filtering */
  function applyFilters(){
    var company = ($('#filterCompany')?.value || '').toLowerCase();
    var typeId  = $('#filterType')?.value || '';
    var formId  = $('#filterForm')?.value || '';
    var opts    = $$('#inf_name option');

    for (var i = 0; i < opts.length; i++) {
      var opt = opts[i];
      if (!opt.value) continue;
      var c = (opt.getAttribute('data-company') || '').toLowerCase();
      var t = opt.getAttribute('data-type') || '';
      var f = opt.getAttribute('data-form') || '';
      var match = (!company || c === company) && (!typeId || t === typeId) && (!formId || f === formId);
      opt.style.display = match ? '' : 'none';
    }

    if (window.jQuery && jQuery.fn.select2) {
      jQuery('#inf_name').trigger('change.select2');
    }
  }

  var fc = $('#filterCompany'); if (fc) fc.addEventListener('change', applyFilters);
  var ft = $('#filterType');   if (ft) ft.addEventListener('change', applyFilters);
  var ff = $('#filterForm');   if (ff) ff.addEventListener('change', applyFilters);

  /* Reset handler */
  var resetBtn = document.querySelector('#medicineFormInline [type="reset"]');
  if (resetBtn) resetBtn.addEventListener('click', function(){
    setTimeout(function(){
      $('#inf_morning').value = '';
      $('#inf_noon').value    = '';
      $('#inf_night').value   = '';
      $('#inf_strength').value = '';
    }, 0);
  });
})();
</script>

@push('js')
<script>
jQuery(function($){

  function select2IfAvailable(sel, opts){
    if ($.fn.select2) {
      $(sel).select2($.extend({ theme: 'bootstrap4', width: '100%', allowClear: true }, opts || {}));
    }
  }

  select2IfAvailable('#inf_name', { placeholder: '-- Select Medicine --' });

  function bindAutoFocus($el){
    $el.on('select2:open', function(e){
      var id = e.target.id;
      var selector = ".select2-search__field[aria-controls='select2-" + id + "-results']";
      setTimeout(function(){
        var el = document.querySelector(selector);
        if (el) el.focus();
      }, 0);
    });
  }
  bindAutoFocus($('#inf_name'));

});
</script>
@endpush
