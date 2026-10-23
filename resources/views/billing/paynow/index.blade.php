@extends('adminlte::page')

@section('title', 'Billing — Pay Now')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            Pay Now
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.paynow.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Pay Now</li>
        </ol>
    </div>
@stop

@section('content')

<div style="max-width:700px; margin:8px auto; padding:4px 0;">

    <div style="border:2px solid #11b8a6; border-radius:6px; background:#fff; overflow:hidden;">

        <div style="padding:10px 14px; border-bottom:1px solid #e0e0e0; font-size:15px; font-weight:600; color:#222; display:flex; justify-content:space-between; align-items:center; background:#f8fafb; flex-wrap:wrap; gap:6px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span><i class="fas fa-money-bill-wave mr-2"></i> Pay Now</span>
                <div style="display:inline-flex; align-items:center; gap:4px;">
                    <label for="paynowSource" style="margin:0; font-size:11px; font-weight:600; color:#555;">Source:</label>
                    <select id="paynowSource" style="border:1px solid #ccc; border-radius:4px; font-size:11px; padding:2px 6px; height:26px; outline:none; cursor:pointer;" onchange="onSourceChange()">
                        <option value="all">All</option>
                        <option value="reception">Reception</option>
                        <option value="manager">Manager</option>
                        <option value="legacy">Legacy</option>
                    </select>
                </div>
            </div>
            <span style="font-size:12px; color:#6d7a82;" id="invoiceCount">Select patient to load invoices</span>
        </div>

        <div style="padding:12px 14px; display:flex; flex-direction:column; gap:9px;">

            {{-- Patient Search --}}
            <div class="ci-row" style="align-items:flex-start;">
                <div class="ci-label" style="padding-top:6px;">Patient</div>
                <div class="ci-colon" style="padding-top:6px;">:</div>
                <div class="ci-control">
                    <input type="text" id="patientSearchInput" class="ci-input"
                           style="width:100%;" placeholder="Type patient name / code / mobile...">
                    <div id="patientList" style="display:none;background:#fff;border:1px solid #ccc;border-radius:4px;margin-top:2px;max-height:180px;overflow-y:auto;position:absolute;z-index:999;min-width:300px;"></div>
                </div>
            </div>

            {{-- Selected Patient Info --}}
            <div id="patientInfoBox" style="display:none; background:#e8f5e9; border-radius:5px; padding:8px 10px; font-size:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                    <div>
                        <span style="background:#11b8a6;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;font-weight:600;" id="ptCode"></span>
                        <strong id="ptName" style="margin-left:6px; font-size:13px;"></strong>
                    </div>
                    <div style="color:#555;" id="ptAge"></div>
                    <div style="color:#555;" id="ptMobile"></div>
                    <button type="button" id="btnClearPatient"
                            style="background:none;border:1px solid #e57373;color:#e57373;border-radius:3px;font-size:11px;padding:1px 8px;cursor:pointer;">
                        X Clear
                    </button>
                </div>
            </div>

            <input type="hidden" id="selectedPatientId">
            <input type="hidden" id="selectedPatientCode">
            <input type="hidden" id="selectedPatientName">

            <div style="border-top:1px solid #eee; margin:2px 0;"></div>

            {{-- Due Invoices List --}}
            <div id="dueListContainer" style="border:1px solid #e0e0e0; border-radius:4px; max-height:200px; overflow-y:auto;">
                <div id="dueListEmpty" style="text-align:center; color:#aaa; padding:20px; font-size:12px;">
                    <i class="fas fa-user-injured"></i> Select a patient to load due invoices.
                </div>
                <div id="dueListContent" style="display:none;"></div>
            </div>

            {{-- Selected Invoice Summary --}}
            <div id="invoiceBox" style="display:none; background:#eef8ef; border:1px solid #d9ead8; border-radius:5px; padding:8px 10px; font-size:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px; margin-bottom:6px;">
                    <div>
                        <span style="background:#11b8a6;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;font-weight:600;" id="invBillNo"></span>
                        <strong id="invPatientName" style="margin-left:6px; font-size:13px;"></strong>
                    </div>
                    <button type="button" id="btnClearInvoice"
                            style="background:none;border:1px solid #e57373;color:#e57373;border-radius:3px;font-size:11px;padding:1px 8px;cursor:pointer;">
                        X Clear
                    </button>
                </div>
                {{-- Create Invoice Style Summary Strip --}}
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <div style="flex:1;min-width:55px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#888;margin-bottom:2px;">Sub Total</div>
                        <div style="font-size:14px;font-weight:600;color:#222;" id="invSubTotal">0</div>
                    </div>
                    <div style="flex:1;min-width:55px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#888;margin-bottom:2px;">Old Discount</div>
                        <div style="font-size:14px;font-weight:600;color:#e53935;" id="invOldDiscount">0</div>
                    </div>
                    <div style="flex:1;min-width:55px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#888;margin-bottom:2px;">New Discount</div>
                        <div style="font-size:14px;font-weight:600;color:#e53935;" id="invNewDiscount">0</div>
                    </div>
                    <div style="flex:1;min-width:55px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#555;margin-bottom:2px;">Net Payable</div>
                        <div style="font-size:14px;font-weight:700;color:#00695c;" id="invNetPayable">0</div>
                    </div>
                    <div style="flex:1;min-width:55px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#888;margin-bottom:2px;">Already Paid</div>
                        <div style="font-size:14px;font-weight:600;color:#2f7d32;" id="invAlreadyPaid">0</div>
                    </div>
                    <div style="flex:1;min-width:55px;background:#ffebee;border:1px solid #ef9a9a;border-radius:5px;padding:6px 8px;text-align:center;">
                        <div style="font-size:9px;color:#c62828;margin-bottom:2px;">New Due</div>
                        <div style="font-size:14px;font-weight:700;color:#c62828;" id="invNewDue">0</div>
                    </div>
                </div>
                <div id="payError" style="display:none;color:#e53935;font-size:11px;margin-top:4px;"></div>
            </div>

            <input type="hidden" id="selectedInvoiceId">

            {{-- PAYMENT + DISCOUNT FIELDS --}}
            <div style="border-top:2px solid #e8f5e9; padding-top:8px;">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 14px;">

                    {{-- Pay Amount --}}
                    <div class="ci-row ci-row-narrow">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Pay Amount</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="number" id="payingAmountInput" class="ci-input" placeholder="0" min="0" step="1">
                        </div>
                    </div>

                    {{-- Discount --}}
                    <div class="ci-row ci-row-narrow">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;" id="discLabel">Discount</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control" style="display:flex; gap:4px; align-items:center;">
                            <select id="discTypeSelect" style="border:1px solid #ccc; border-radius:4px; font-size:12px; padding:5px 6px; height:30px; outline:none; cursor:pointer;">
                                <option value="percent">%</option>
                                <option value="flat">৳</option>
                            </select>
                            <input type="number" id="discAmountInput" class="ci-input" value="0" min="0" step="0.01" style="flex:1;">
                        </div>
                    </div>

                    {{-- Reference By --}}
                    <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Reference</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="discReferenceSelect" style="width:100%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:12px; height:30px; outline:none;">
                                <option value="">- Select Member -</option>
                                @foreach($references as $ref)
                                    <option value="{{ $ref->ID }}">{{ $ref->Name }} ({{ $ref->ref_type }})</option>
                                @endforeach
                                <option value="__others__">Others (type name)</option>
                            </select>
                            <input type="text" id="discReferenceOther" class="ci-input"
                                   placeholder="Type reference name" style="display:none; margin-top:3px;">
                        </div>
                    </div>

                    {{-- Payment Date --}}
                    <div class="ci-row ci-row-narrow">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Date</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="date" id="payDateInput" class="ci-input" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Pay Method --}}
                    <div class="ci-row ci-row-narrow">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Method</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="payMethodInput" class="ci-input" style="height:30px;">
                                <option value="cash">Cash</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    {{-- Collected By --}}
                    <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Collected By</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="collectedByInput" style="width:100%; border:1px solid #ccc; border-radius:4px; padding:4px 8px; font-size:12px; height:30px; outline:none;">
                                <option value="">- Select Staff -</option>
                                @foreach($collectors as $staff)
                                    <option value="{{ $staff->Name }}">{{ $staff->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                        <div class="ci-label ci-label-narrow" style="flex:0 0 70px;">Note</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="text" id="discNoteInput" class="ci-input" placeholder="Optional note">
                        </div>
                    </div>

                </div>



            </div>

        </div>

        <div style="padding:4px 14px 14px;">
            <button type="button" id="btnPayNow" disabled
                    style="width:100%;background:#11b8a6;color:#fff;border:none;border-radius:4px;padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;">
                <i class="fas fa-check-circle mr-1"></i> Pay Now
            </button>
        </div>

    </div>

</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .content-wrapper { background: #f4f6f8 !important; }
    .ci-row { display:flex; align-items:center; gap:0; min-height:28px; }
    .ci-label { flex:0 0 100px; font-size:12px; color:#555; }
    .ci-colon { flex:0 0 12px; color:#888; font-size:12px; text-align:center; }
    .ci-control { flex:1; }
    .ci-input { width:100%; border:1px solid #ccc; border-radius:4px; padding:5px 8px; font-size:12px; color:#222; background:#fff; height:30px; outline:none; }
    .ci-input:focus { border-color:#11b8a6; }
    .ci-row-narrow { min-height:24px; }
    .ci-label-narrow { flex:0 0 70px; font-size:12px; color:#555; }
    #btnPayNow:disabled { background:#90d8d0 !important; cursor:not-allowed !important; }
    #btnPayNow:hover:not(:disabled) { background:#0d9485; }
    #patientList .patient-item { padding:6px 10px;cursor:pointer;border-bottom:1px solid #eee;font-size:12px; }
    #patientList .patient-item:hover { background:#e8f8f5; }
    #patientList .patient-item .name { font-weight:600;color:#1a2433; }
    #patientList .patient-item .meta { color:#6d7a82;font-size:11px; }
    .due-item { padding:6px 8px;cursor:pointer;border-bottom:1px solid #eee;font-size:12px;display:flex;justify-content:space-between;align-items:center; }
    .due-item:hover { background:#e8f8f5; }
    .due-item.active { background:#d4f5f2; }
    .due-item .bill { font-weight:600;color:#0fa897; }
    .due-item .due-amount { color:#d77900;font-weight:700; }
    .due-item .status { font-size:10px;padding:1px 6px;border-radius:8px; }
    .due-item .status.unpaid { background:#fce8e7;color:#c0392b; }
    .due-item .status.partial { background:#fff7e7;color:#d77900; }
    .disc-h-item { display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;background:#fff;border:1px solid #eee2c8;border-radius:4px;padding:5px 8px;margin-bottom:4px;font-size:11px; }
    .disc-h-item .amt { font-weight:700;color:#b7791f; }
    .disc-h-item .meta { color:#7a8790;font-size:10px; }
    .disc-h-empty { text-align:center;color:#a99a7c;font-size:11px;padding:6px; }
    #patientSearchInput:focus, select:focus { border-color:#11b8a6 !important; box-shadow:0 0 0 2px rgba(17,184,166,.12) !important; outline:none !important; }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = { positionClass:'toast-top-right', timeOut:3000, progressBar:true, closeButton:true };
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

// ─── Variables ──────────────────────────────────────────────────
var selectedPatient = null;
var selectedInvoice = null;
var loadedInvoices = [];
var searchTimeout = null;
var payAutoMode = true; // true = Pay Amount এখনো auto-calculated (ইউজার নিজে হাতে টাইপ করেনি)

// ─── Helpers ────────────────────────────────────────────────────
function fmt(v) { return (parseFloat(v)||0).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0}); }

// ─── Patient Search ────────────────────────────────────────────
function loadPatientSearch(q) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        var src = $('#paynowSource').val();
        $.get('{{ route("billing.paynow.searchPatient") }}', { q: q, payment_source: src }, function(res) {
            var html = '';
            if (res.data && res.data.length) {
                for (var i=0; i<res.data.length; i++) {
                    var p = res.data[i];
                    html += '<div class="patient-item" data-id="'+p.id+'" data-code="'+(p.patientcode||'')+'" data-name="'+(p.patientname||'')+'" data-age="'+(p.age||'')+'" data-mobile="'+(p.mobile_no||'')+'">'+
                        '<div class="name">'+(p.patientname||'-')+'</div>'+
                        '<div class="meta">'+(p.patientcode||'')+' | '+(p.age||'')+' yrs | '+(p.mobile_no||'')+'</div>'+
                        '<div class="meta" style="color:#d77900;font-weight:600;">Due: ৳ '+fmt(p.due_total||0)+'</div>'+
                        '</div>';
                }
            } else { html = '<div style="padding:8px;text-align:center;color:#999;font-size:12px;">No patient found</div>'; }
            $('#patientList').html(html).show();
        });
    }, q.length ? 300 : 0);
}

function onSourceChange() {
    if (selectedPatient) {
        loadDueInvoices();
    } else {
        var q = $('#patientSearchInput').val().trim();
        loadPatientSearch(q);
    }
}

function renderSourceBadge(source) {
    var s = (source || '').toLowerCase().trim();
    if (s === 'manager') {
        return '<span class="badge badge-primary" style="font-size:9px;font-weight:600;padding:2px 5px;vertical-align:middle;">Manager</span>';
    }
    if (s === 'reception') {
        return '<span class="badge badge-info" style="font-size:9px;font-weight:600;padding:2px 5px;vertical-align:middle;">Reception</span>';
    }
    return '<span class="badge badge-secondary" style="font-size:9px;font-weight:500;padding:2px 5px;vertical-align:middle;">Legacy</span>';
}

$('#patientSearchInput').on('keyup', function() {
    var q = $(this).val().trim();
    if (q.length < 1) { $('#patientList').hide(); return; }
    loadPatientSearch(q);
});

// Click / Focus করলেই dropdown খুলবে — যাদের Due আছে তাদেরই দেখাবে
$('#patientSearchInput').on('click focus', function() {
    if ($('#patientList').is(':visible') && $('#patientList').children().length) return;
    loadPatientSearch('');
});

$(document).on('click', '.patient-item', function() {
    selectedPatient = {
        id: $(this).data('id'),
        patientcode: $(this).data('code'),
        patientname: $(this).data('name'),
        age: $(this).data('age'),
        mobile_no: $(this).data('mobile')
    };
    $('#selectedPatientId').val(selectedPatient.id);
    $('#selectedPatientCode').val(selectedPatient.patientcode);
    $('#selectedPatientName').val(selectedPatient.patientname);
    $('#ptCode').text(selectedPatient.patientcode||'-');
    $('#ptName').text(selectedPatient.patientname||'-');
    $('#ptAge').text((selectedPatient.age||'-')+' yrs');
    $('#ptMobile').text(selectedPatient.mobile_no||'-');
    $('#patientInfoBox').show();
    $('#patientList').hide();
    $('#patientSearchInput').val(selectedPatient.patientname);
    loadDueInvoices();
});

$('#btnClearPatient').on('click', function() {
    selectedPatient = null; selectedInvoice = null; loadedInvoices = [];
    $('#selectedPatientId,#selectedPatientCode,#selectedPatientName').val('');
    $('#patientInfoBox').hide();
    $('#patientSearchInput').val('');
    $('#patientList').hide();
    resetInvoice();
    $('#dueListEmpty').show();
    $('#dueListContent').hide().empty();
    if ($('#historyEmpty').length) $('#historyEmpty').show();
    if ($('#historyContent').length) $('#historyContent').hide().empty();
    $('#btnPayNow').prop('disabled', true);
    $('#invoiceCount').text('Select patient to load invoices');
});

// ─── Load Due Invoices ─────────────────────────────────────────
function loadDueInvoices() {
    if (!selectedPatient) return;
    var pid = selectedPatient.id;
    var pcode = selectedPatient.patientcode||'';
    var src = $('#paynowSource').val();
    $('#dueListEmpty').hide();
    $('#dueListContent').show().html('<div style="text-align:center;color:#aaa;padding:10px;font-size:12px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $.get('{{ url("Billing/Paynow/ajax/due-invoices") }}/'+pid, { patient_code: pcode, per_page: 100, payment_source: src }, function(res) {
        loadedInvoices = res.data||[];
        renderDueList();
        if (loadedInvoices.length) {
            $('#invoiceCount').text(loadedInvoices.length + ' due invoice(s) found');
            setSelectedInvoice(loadedInvoices[0].ID);
        } else {
            resetInvoice();
            $('#invoiceCount').text('No due invoice found');
            $('#dueListContent').html('<div style="text-align:center;color:#aaa;padding:10px;font-size:12px;">No due invoice found.</div>');
        }
    });
}

// ─── Render Due List ───────────────────────────────────────────
function renderDueList() {
    if (!loadedInvoices.length) {
        $('#dueListContent').html('<div style="text-align:center;color:#aaa;padding:10px;font-size:12px;">No due invoice found.</div>');
        return;
    }
    var html = '';
    for (var i=0; i<loadedInvoices.length; i++) {
        var inv = loadedInvoices[i];
        var active = (selectedInvoice && String(selectedInvoice.ID)===String(inv.ID)) ? 'active' : '';
        var status = parseFloat(inv.PaidAmount||0)>0 ? 'partial' : 'unpaid';
        var statusLabel = parseFloat(inv.PaidAmount||0)>0 ? 'Partial' : 'Unpaid';
        html += '<div class="due-item '+active+'" data-id="'+inv.ID+'">'+
            '<div><span class="bill">'+(inv.BillNo||'-')+'</span> ' + renderSourceBadge(inv.payment_source) + ' <span style="color:#555;font-size:11px;">'+(inv.PatientName||'')+'</span></div>'+
            '<div><span class="due-amount">৳ '+fmt(inv.DueAmount||0)+'</span> <span class="status '+status+'">'+statusLabel+'</span></div>'+
            '</div>';
    }
    $('#dueListContent').html(html);
}

// ─── Set Selected Invoice ──────────────────────────────────────
function setSelectedInvoice(invoiceId) {
    var found = null;
    for (var i=0; i<loadedInvoices.length; i++) {
        if (String(loadedInvoices[i].ID)===String(invoiceId)) { found = loadedInvoices[i]; break; }
    }
    if (!found) return;
    selectedInvoice = found;
    fillInvoiceBox(found);
    $('.due-item').removeClass('active');
    $('.due-item[data-id="'+found.ID+'"]').addClass('active');
    loadDiscountHistory(found.ID);
    $('#discAmountInput').val(0);
    recalcPayment();
}

// ─── Fill Invoice Box ──────────────────────────────────────────
function fillInvoiceBox(inv) {
    $('#selectedInvoiceId').val(inv.ID);
    $('#invBillNo').text(inv.BillNo||'-');
    $('#invPatientName').text(inv.PatientName||'-');
    $('#invoiceBox').show();
    var due = parseFloat(inv.DueAmount||0);
    payAutoMode = true;
    $('#payingAmountInput').val(due>0 ? Math.round(due) : 0);
    recalcPayment();
}

// ─── Reset Invoice ─────────────────────────────────────────────
function resetInvoice() {
    selectedInvoice = null;
    payAutoMode = true;
    $('#invoiceBox').hide();
    $('#selectedInvoiceId').val('');
    $('#payingAmountInput').val('');
    $('#discAmountInput').val(0);
    $('#payError').hide();
    $('#btnPayNow').prop('disabled', true);
    if ($('#historyEmpty').length) $('#historyEmpty').show();
    if ($('#historyContent').length) $('#historyContent').hide().empty();
}

$(document).on('click', '.due-item', function() {
    setSelectedInvoice($(this).data('id'));
});

$('#btnClearInvoice').on('click', function() {
    resetInvoice();
    $('.due-item').removeClass('active');
});

// ─── Live Recalculation (Create Invoice Style) ─────────────────
$('#payingAmountInput').on('input', function() {
    payAutoMode = false;
    recalcPayment();
});
$('#discTypeSelect, #discAmountInput, #discReferenceSelect, #discReferenceOther').on('change input', function() {
    recalcPayment();
});

function getReference() {
    var val = $('#discReferenceSelect').val();
    if (val === '__others__') { return $('#discReferenceOther').val().trim()||null; }
    return val||null;
}

$('#discReferenceSelect').on('change', function() {
    if ($(this).val() === '__others__') {
        $('#discReferenceOther').show().focus();
    } else {
        $('#discReferenceOther').hide().val('');
    }
    recalcPayment();
});

// 2 দশমিক পর্যন্ত round — PHP round(...,2)-এর সাথে মিলবে
function round2(v) { return Math.round((parseFloat(v)||0) * 100) / 100; }

// ─── recalcPayment — Create Invoice মতো live summary ───────────
function recalcPayment() {
    if (!selectedInvoice) { $('#btnPayNow').prop('disabled', true); return; }

    var totalBill   = round2(selectedInvoice.TotalBill||0);
    var oldDiscount = round2(selectedInvoice.Discount||0);
    var oldPaid     = round2(selectedInvoice.PaidAmount||0);
    var due         = round2(selectedInvoice.DueAmount||0);

    var discType = $('#discTypeSelect').val();
    var discVal  = parseFloat($('#discAmountInput').val())||0;
    var ref      = getReference();

    // New discount calculation (same as backend PaynowController@store)
    var newDiscountRequested = 0;
    if (discVal > 0) {
        newDiscountRequested = round2(discType==='flat' ? discVal : totalBill*discVal/100);
    }

    // Pay Amount auto-mode: discount বদলালে pay auto-adjust হবে
    var paying = round2($('#payingAmountInput').val())||0;
    if (payAutoMode) {
        paying = round2(Math.max(0, due - newDiscountRequested));
        $('#payingAmountInput').val(paying > 0 ? paying : 0);
    }

    // Discount cap: Pay + Discount can't exceed Due
    var maxDiscount    = round2(Math.max(0, due - paying));
    var newDiscountAmt = Math.min(newDiscountRequested, maxDiscount);
    if (newDiscountRequested > due) newDiscountAmt = 0; // server reject করবে

    var totalDiscount = round2(oldDiscount + newDiscountAmt);
    var netPayable    = round2(Math.max(0, totalBill - totalDiscount));
    var newPaid       = round2(oldPaid + paying);
    var newDue        = round2(Math.max(0, due - paying - newDiscountAmt));

    // Update summary strip
    $('#invSubTotal').text(fmt(totalBill));
    $('#invOldDiscount').text(fmt(oldDiscount));
    $('#invNewDiscount').text(fmt(newDiscountAmt));
    $('#invNetPayable').text(fmt(netPayable));
    $('#invAlreadyPaid').text(fmt(oldPaid));
    $('#invNewDue').text(fmt(newDue));

    // Validation + error message
    var error = '';
    var canPay = true;

    if (newDiscountRequested > due) {
        error = 'ডিসকাউন্ট (' + fmt(newDiscountRequested) + ') Due (' + fmt(due) + ') এর বেশি দেওয়া যাবে না।';
        canPay = false;
    } else if (paying <= 0 && newDiscountAmt <= 0) {
        error = '';
        canPay = false;
    } else if (paying > due) {
        error = 'Pay Amount Due (' + fmt(due) + ') এর বেশি হতে পারবে না।';
        canPay = false;
    } else if (paying + newDiscountAmt > due) {
        error = 'Pay + Discount মিলে Due (' + fmt(due) + ') এর বেশি — Pay Amount কমান অথবা Discount কমান।';
        canPay = false;
    } else if (discVal > 0 && !ref) {
        error = 'Discount দিতে Reference নির্বাচন করুন।';
        canPay = false;
    }

    if (error) {
        $('#payError').text(error).show();
        $('#payingAmountInput').css('border-color', '#e53935');
    } else {
        $('#payError').hide();
        $('#payingAmountInput').css('border-color', '#ccc');
    }

    $('#btnPayNow').prop('disabled', !canPay);
}

// ─── Load Discount History ─────────────────────────────────────
function loadDiscountHistory(invoiceId) {
    if ($('#historyEmpty').length) $('#historyEmpty').hide();
    if ($('#historyContent').length) {
        $('#historyContent').show().html('<div style="text-align:center;color:#aaa;padding:10px;font-size:12px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    }
    $.get('{{ url("Billing/Paynow/discount-history") }}/'+invoiceId, function(res) {
        var data = res.data||[];
        if (!data.length) {
            if ($('#historyContent').length) $('#historyContent').html('<div class="disc-h-empty">No discount history found.</div>');
            return;
        }
        var html = '';
        for (var i=0; i<data.length; i++) {
            var r = data[i];
            var date = (r.created_at||'').replace('T',' ').slice(0,16);
            html += '<div class="disc-h-item">'+
                '<div><span class="amt">৳ '+fmt(r.adjustment_amount)+'</span>'+
                (r.reference_name ? ' — '+r.reference_name : '')+
                ' <span class="meta">Due: '+fmt(r.old_due)+' → '+fmt(r.new_due)+'</span></div>'+
                '<div class="meta">'+date+(r.note ? ' — '+r.note : '')+'</div>'+
                '</div>';
        }
        if ($('#historyContent').length) $('#historyContent').html(html);
    }).fail(function(){
        if ($('#historyContent').length) $('#historyContent').html('<div class="disc-h-empty">Failed to load history.</div>');
    });
}

$('#btnPayNow').on('click', function() {
    if (!selectedInvoice) return;

    var paying = parseFloat($('#payingAmountInput').val())||0;
    var due = parseFloat(selectedInvoice.DueAmount||0);
    var discVal = parseFloat($('#discAmountInput').val())||0;
    var total = parseFloat(selectedInvoice.TotalBill||0);
    var discType = $('#discTypeSelect').val();
    var ref = getReference();

    var payload = {
        invoice_id: selectedInvoice.ID,
        paying_amount: paying,
        payment_date: $('#payDateInput').val(),
        payment_method: $('#payMethodInput').val(),
        collected_by: $('#collectedByInput').val(),
        discount_type: discType,
        discount_value: discVal,
        note: $('#discNoteInput').val().trim()||null
    };

    if (discVal > 0) {
        if (ref === '__others__' || isNaN(ref)) {
            payload.reference_new_name = $('#discReferenceOther').val().trim();
        } else {
            payload.reference_id = parseInt(ref);
        }
    }

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

    // User gesture-এর সময়েই window open — pop-up blocker bypass
    var pdfWindow = window.open('', '_blank');

    $.ajax({
        url: '{{ route("billing.paynow.store") }}',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            if (!res.success) {
                toastr.error(res.message||'Failed.');
                if (pdfWindow) pdfWindow.close();
                return;
            }

            toastr.success('Payment & Discount applied successfully!');

            // Auto Open PDF - CreateInvoice PDF
            if (pdfWindow) {
                pdfWindow.location.href = '{{ url("Billing/CreateInvoice") }}/' + res.invoice_id + '/pdf';
            }

            // Reload invoices
            loadDueInvoices();
        },
        error: function(xhr) {
            var r = xhr.responseJSON;
            toastr.error((r&&r.message)||'Server error.');
            if (pdfWindow) pdfWindow.close();
        },
        complete: function() {
            $btn.html('<i class="fas fa-check-circle mr-1"></i> Pay Now');
            if (selectedInvoice) { recalcPayment(); } else { $btn.prop('disabled', true); }
        }
    });
});

// ─── Click outside ─────────────────────────────────────────────
$(document).on('click', function(e) {
    if (!$(e.target).closest('#patientSearchInput, #patientList').length) {
        $('#patientList').hide();
    }
});

$(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var src = urlParams.get('payment_source');
    if (src && ['all', 'reception', 'manager', 'legacy'].includes(src.toLowerCase())) {
        $('#paynowSource').val(src.toLowerCase());
    }
});

</script>
@stop
