@php
    $pc = trim((string) ($resPatientCode ?? ($patientcode ?? ($patient->patientcode ?? ''))));
    $base = url('admission/print-options');
    $ccOptions = \Illuminate\Support\Facades\Schema::hasTable('common_complain')
        ? \Illuminate\Support\Facades\DB::table('common_complain')->where('active', 1)->orderBy('name')->pluck('name')
        : collect();
    $invOptions = \Illuminate\Support\Facades\Schema::hasTable('common_investigation')
        ? \Illuminate\Support\Facades\DB::table('common_investigation')->where('active', 1)->orderBy('name')->get(['name', 'category'])
        : collect();
    $oeFields = \Illuminate\Support\Facades\Schema::hasTable('oe_master')
        ? \Illuminate\Support\Facades\DB::table('oe_master')->where('active', 1)->orderBy('sort_order')->orderBy('id')->get(['id', 'name', 'unit'])
        : collect();
@endphp

<button type="button" id="prpPickBtn" class="btn btn-sm btn-outline-primary shadow"
        style="position:fixed;right:16px;bottom:90px;z-index:1040">
    Select C/C · Inv · O/E
</button>

<div class="modal fade" id="prpPickModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select C/C · Inv · O/E</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">C/C <small class="text-muted">(common)</small></h6>
                        <div style="max-height:300px;overflow:auto;border:1px solid #e5e7eb;border-radius:.5rem;padding:.5rem">
                            @forelse($ccOptions as $name)
                                <label class="d-block mb-1 font-weight-normal">
                                    <input type="checkbox" class="prp-cc" value="{{ $name }}"> {{ $name }}
                                </label>
                            @empty
                                <div class="text-muted small">No common complaints.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">Inv</h6>
                        <div style="max-height:300px;overflow:auto;border:1px solid #e5e7e5;border-radius:.5rem;padding:.5rem">
                            @foreach($invOptions as $i)
                                <label class="d-block mb-1 font-weight-normal">
                                    <input type="checkbox" class="prp-inv" value="{{ $i->name }}"> {{ $i->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-weight-bold">O/E</h6>
                        <div class="form-group mb-2">
                            <select id="prpRecord" class="form-control form-control-sm">
                                <option value="">Latest</option>
                            </select>
                        </div>
                        <div id="prpOe" style="max-height:280px;overflow:auto">
                            @foreach($oeFields as $f)
                                <div class="d-flex mb-1" style="align-items:center">
                                    <span style="width:9rem;flex-shrink:0;font-size:.85rem">{{ $f->name }}</span>
                                    <input type="text" class="form-control form-control-sm prp-oe-val"
                                           data-field-id="{{ $f->id }}" data-label="{{ $f->name }}"
                                           data-unit="{{ $f->unit }}" placeholder="{{ $f->unit ?: '—' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="prpApply">Apply to Print</button>
            </div>
        </div>
    </div>
</div>

<script>
var PRP_AJAX_URL = @if($pc !== '') @json($base . '?patientcode=' . rawurlencode($pc)) @else null @endif;
var PRP_SAVE_URL = @json(url('admission/print-selection'));
var prpData = null;

$(function () {
    $('#prpPickBtn').on('click', function () {
        $('#prpPickModal').modal('show');
        if (!prpData && PRP_AJAX_URL) prpLoad();
    });
    $('#prpApply').on('click', function () {
        var ccList = [], invList = [], oeMap = {};
        $('.prp-cc:checked').each(function () { ccList.push($(this).val()); });
        $('.prp-inv:checked').each(function () { invList.push($(this).val()); });
        $('#prpOe .prp-oe-val').each(function () {
            var v = $(this).val().trim();
            if (!v) return;
            oeMap[$(this).data('label')] = v + ($(this).data('unit') ? ' ' + $(this).data('unit') : '');
        });
        if (ccList.length) window.admissionPrintDefaults.cc = '• ' + ccList.join('\n• ');
        if (invList.length) window.admissionPrintDefaults.inv = '• ' + invList.join('\n• ');
        var oe = $.map(oeMap, function (v, k) { return k + ': ' + v; });
        if (oe.length) window.admissionPrintDefaults.oe = oe.join('\n');
        if (PRP_AJAX_URL) {
            $.ajax({
                url: PRP_SAVE_URL, type: "POST",
                data: { _token: $('meta[name="csrf-token"]').attr('content'), patientcode: @json($pc), cc: ccList, inv: invList, oe: oeMap },
                dataType: "json"
            });
        }
        $('#prpPickModal').modal('hide');
    });
});

function prpLoad() {
    $.get(PRP_AJAX_URL, function (d) {
        if (!d || !d.ok) return;
        prpData = d;
        var rec = $('#prpRecord');
        rec.append($.map(d.history, function (h) {
            return '<option value="' + h.id + '">' + h.datetime + ' — P ' + (h.pulse || '—') +
                ' | BP ' + (h.bp || '—') + ' | T ' + (h.temp || '—') + '</option>';
        }).join(''));
        prpFill(d.history && d.history[0] ? d.history[0].values : d.field_values);
        rec.on('change', function () {
            var m = null;
            $.each(d.history, function (i, h) { if (String(h.id) === rec.val()) m = h.values; });
            if (m) prpFill(m);
        });
    });
}

function prpFill(v) {
    $('#prpOe .prp-oe-val').each(function () {
        var val = v ? v[$(this).data('field-id')] : '';
        $(this).val(val != null ? val : '');
    });
}
</script>