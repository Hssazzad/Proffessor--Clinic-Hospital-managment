@php
  $filterCompanies = $medicine->pluck('company')->filter()->unique()->values()->sort()->all();
  $filterGroups    = $medicine->pluck('GroupName')->filter()->unique()->values()->sort()->all();
@endphp

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
.med-filter-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:600px){.med-filter-row{grid-template-columns:1fr;}}
.med-filter-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:var(--txt-secondary,#64748b);margin-bottom:6px;}
.med-filter-label i{color:var(--primary,#0f52ba);margin-right:5px;}
.med-search-wrapper .select2-container{width:100% !important;}
.med-search-wrapper .select2-container .select2-selection--single{
    height:40px;border:1.5px solid var(--bdr,#d9e2ef);border-radius:var(--r-sm,6px);padding:3px 0 0 2px;
}
.med-search-wrapper .select2-container--focus .select2-selection--single{border-color:var(--primary,#0f52ba);box-shadow:0 0 0 3px rgba(15,82,186,.12);}
.med-search-wrapper .select2-selection__rendered{line-height:32px !important;font-size:14px;color:var(--txt,#1e293b);}
.med-search-wrapper .select2-container .select2-selection__arrow{height:38px !important;}
</style>
@endpush

{{-- ▸ Company / Medicine Group filter row (full width) ▸ --}}
<div class="mr-form-group span-3 no-print">
    <div class="med-filter-row">
        <div>
            <div class="med-filter-label"><i class="fas fa-building"></i> Company</div>
            <select id="filterCompany" class="mr-input mr-select">
                <option value="">-- All Companies --</option>
                @foreach($filterCompanies as $fc)
                    <option value="{{ $fc }}">{{ $fc }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <div class="med-filter-label"><i class="fas fa-tags"></i> Medicine Group / Category</div>
            <select id="filterGroup" class="mr-input mr-select">
                <option value="">-- All Groups --</option>
                @foreach($filterGroups as $g)
                    <option value="{{ $g }}">{{ $g }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- ▸ Medicine Name (searchable Select2) ▸ --}}
<div class="mr-form-group span-2">
    <label class="mr-label">Medicine Name <span class="req">*</span></label>
    <div class="med-search-wrapper">
        <select name="name" id="inf_name" class="mr-input" required>
            <option value="">-- Select Medicine --</option>
            @foreach($medicine as $med)
                <option value="{{ $med->name }}"
                    data-company="{{ $med->company ?? '' }}"
                    data-group="{{ $med->GroupName ?? '' }}"
                    data-strength="{{ $med->strength ?? '' }}">
                    {{ $med->name }}{{ $med->strength ? ' (' . $med->strength . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
jQuery(function($){
    if (!$.fn.select2) return;

    function initMedSelect2(){
        if ($('#inf_name').hasClass('select2-hidden-accessible')) return;
        $('#inf_name').select2({
            placeholder: '-- Select Medicine --',
            allowClear: true,
            width: '100%',
            matcher: function(params, data){
                if ($.trim(params.term) === '') return data;
                if (typeof data.text === 'undefined') return null;
                var t = data.text.toLowerCase();
                var meta = data.element ? ((data.element.dataset.company || '') + ' ' + (data.element.dataset.group || '')).toLowerCase() : '';
                return (t.indexOf(params.term.toLowerCase()) > -1 || meta.indexOf(params.term.toLowerCase()) > -1) ? data : null;
            }
        });
    }
    initMedSelect2();

    $('#inf_name').on('change', function(){
        var opt = this.selectedOptions && this.selectedOptions[0];
        var strength = opt ? (opt.getAttribute('data-strength') || '') : '';
        $('#inf_strength').val(strength);
    });

    function applyFilters(){
        var company = ($('#filterCompany').val() || '').toLowerCase();
        var group   = ($('#filterGroup').val() || '').toLowerCase();
        $('#inf_name option').each(function(){
            if (!this.value) { this.style.display = ''; return; }
            var c = (this.getAttribute('data-company') || '').toLowerCase();
            var g = (this.getAttribute('data-group') || '').toLowerCase();
            this.style.display = (!company || c === company) && (!group || g === group) ? '' : 'none';
        });
        var cur = $('#inf_name').val();
        if (cur) {
            var curOpt = $('#inf_name option').filter(function(){ return this.value === cur; });
            if (curOpt.length && curOpt[0].style.display === 'none') {
                $('#inf_name').val('').trigger('change');
            }
        }
        $('#inf_name').trigger('change.select2');
    }

    $('#filterCompany').on('change', applyFilters);
    $('#filterGroup').on('change', applyFilters);
});
</script>
@endpush
