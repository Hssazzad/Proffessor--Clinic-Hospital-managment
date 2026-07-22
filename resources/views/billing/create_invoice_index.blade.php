@extends('adminlte::page')

@section('title', 'Create Invoice')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            Create Invoice
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.invoice.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Create Invoice</li>
        </ol>
    </div>
@stop

@section('content')

<div style="display:flex; gap:16px; padding:4px 0; align-items:flex-start;">

    {{-- LEFT COLUMN --}}
    <div style="flex:0 0 45%; border:2px solid #00bfa5; border-radius:6px; background:#fff; overflow:hidden;">

        <div style="padding:10px 14px; border-bottom:1px solid #e0e0e0; font-size:15px; font-weight:600; color:#222; display:flex; justify-content:space-between; align-items:center;">
            <span>Create Invoice</span>

            <select id="billTypeSelect"
                    style="border:1px solid #b2dfdb; border-radius:5px; font-size:12px; font-weight:600;
                           color:#00695c; background:#e0f2f1; padding:5px 10px; outline:none;
                           cursor:pointer; min-width:160px;">
                <option value="">Loading...</option>
            </select>
        </div>

        <div style="padding:12px 14px; display:flex; flex-direction:column; gap:9px;">

            <div class="ci-row" style="align-items:flex-start;">
                <div class="ci-label" style="padding-top:6px;">Patient</div>
                <div class="ci-colon" style="padding-top:6px;">:</div>
                <div class="ci-control">
                    <select id="patientSelect" style="width:100%;">
                        <option value="">- Type to search patient -</option>
                    </select>
                </div>
            </div>

            <div id="patientInfoBox" style="display:none; background:#e8f5e9; border-radius:5px; padding:8px 10px; font-size:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                    <div>
                        <span style="background:#00bfa5;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;font-weight:600;" id="ptCode"></span>
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
            <input type="hidden" id="selectedPatientAge">
            <input type="hidden" id="selectedMobile">
            <input type="hidden" id="selectedAdmissionId">

            <div style="border-top:1px solid #eee; margin:2px 0;"></div>

            <div id="patientRequiredNotice"
                 style="background:#fff3e0; border:1px solid #ffcc80; border-radius:4px;
                        padding:6px 10px; font-size:12px; color:#e65100; display:block;">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Please select a patient first to add items.
            </div>

            <div id="actionFormsWrapper" style="display:flex; flex-direction:column; gap:15px;">

                <div id="doctorVisitBlock" style="background:#f8f9fa; border:1px solid #ddd; padding:10px; border-radius:5px;">
                    <div style="font-size:12px; font-weight:bold; color:#1a3a5c; margin-bottom:8px;">
                        <i class="fas fa-user-md"></i> Doctor Consultation
                    </div>
                    <div class="ci-row">
                        <div class="ci-label">Select Doctor</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="doctorSelect" class="ci-select">
                                <option value="">Loading...</option>
                            </select>
                        </div>
                    </div>
                    <div class="ci-row" id="docFeeGroup" style="display:none; margin-top:8px;">
                        <div class="ci-label">Visit Fee</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="number" id="doctorFeeInput" class="ci-input"
                                   placeholder="Enter visit fee" min="0" step="1">
                        </div>
                    </div>
                </div>

                <div id="investigationBlock" style="background:#f4fdfb; border:1px solid #b2dfdb; padding:10px; border-radius:5px;">
                    <div style="font-size:12px; font-weight:bold; color:#00695c; margin-bottom:8px;" id="invBlockTitle">
                        <i class="fas fa-microscope"></i> Add Investigation
                    </div>

                    <div id="mainCategoryRow" class="ci-row" style="display:none;">
                        <div class="ci-label">Type</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="mainCategorySelect" class="ci-select" style="width:100%;"></select>
                        </div>
                    </div>

                    <div class="ci-row">
                        <div class="ci-label">Investigation</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="subCategorySelect" class="ci-select" disabled>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                    </div>

                    <div class="ci-row" id="amountInputGroup" style="display:none; margin-top:8px;">
                        <div class="ci-label">Price</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="number" id="customAmountInput" class="ci-input"
                                   placeholder="Enter price" min="0" step="1">
                        </div>
                    </div>

                    <div class="ci-row" id="commentInputGroup" style="display:none; align-items:flex-start; margin-top:8px;">
                        <div class="ci-label" style="padding-top:5px;">Remarks</div>
                        <div class="ci-colon" style="padding-top:5px;">:</div>
                        <div class="ci-control">
                            <textarea id="commentInput" class="ci-input" rows="2"
                                      placeholder="Optional note"
                                      style="height:52px; resize:none;"></textarea>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div style="padding:4px 14px 14px;">
            <button type="button" id="btnAddItem" disabled
                    style="width:100%;background:#2979ff;color:#fff;border:none;border-radius:4px;
                           padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;">
                + Add to Bill
            </button>
        </div>

    </div>


    {{-- RIGHT COLUMN --}}
    <div style="flex:1; border:2px solid #00bfa5; border-radius:6px; background:#fff; overflow:hidden;">

        <div style="padding:10px 14px; border-bottom:1px solid #e0e0e0; font-size:15px; font-weight:600; color:#222;">
            Invoice
        </div>

        <div style="padding:10px 14px 0;">

            <div style="text-align:center; font-size:22px; font-weight:600; color:#222; padding:6px 0 10px;">
                Invoice
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; gap:10px;">
                <div style="font-size:13px; color:#555; font-weight:500;" id="invoicePatientName">- No patient selected -</div>
                <div style="font-size:12px; color:#777; display:flex; align-items:center; gap:6px; white-space:nowrap;">
                    Bill No:
                    <span style="border:1px solid #ccc; border-radius:3px; padding:2px 8px; font-weight:600; font-size:13px; color:#222;" id="invoiceBillNo"></span>
                </div>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:12px;" id="billTable">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th class="inv-th" style="width:28px;">SL</th>
                        <th class="inv-th" style="width:115px;">Item Type</th>
                        <th class="inv-th">Service Name</th>
                        <th class="inv-th" style="width:74px; text-align:right;">Price</th>
                        <th class="inv-th" style="width:80px;">Remarks</th>
                        <th class="inv-th" style="width:44px; text-align:center;">Del</th>
                    </tr>
                </thead>
                <tbody id="billBody">
                    <tr id="billEmptyRow">
                        <td colspan="6" style="text-align:center; color:#aaa; padding:20px; font-size:12px; border:1px solid #ddd;">
                            No items added yet
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#fafafa;">
                        <td colspan="3" style="text-align:right; padding:6px 8px; font-size:12px; color:#777; border:1px solid #ddd; font-weight:500;">Total</td>
                        <td style="text-align:right; padding:6px 8px; border:1px solid #ddd; font-weight:600; color:#222;" id="billTotalFoot">0</td>
                        <td colspan="2" style="border:1px solid #ddd;"></td>
                    </tr>
                </tfoot>
            </table>

        </div>

        <div id="invoiceBottomSection" style="border-top:2px solid #e8f5e9; padding:12px 14px 0;">

            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                <div style="flex:1; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#888; margin-bottom:3px;">Sub Total</div>
                    <div style="font-size:15px; font-weight:600; color:#222;" id="subTotalDisplay">0</div>
                </div>
                <div style="flex:1; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#888; margin-bottom:3px;">Discount</div>
                    <div style="font-size:15px; font-weight:600; color:#e53935;" id="lessDisplay">0</div>
                </div>
                <div style="flex:1; background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#555; margin-bottom:3px;">Net Payable</div>
                    <div style="font-size:15px; font-weight:700; color:#00695c;" id="grandTotalDisplay">0</div>
                </div>
                <div style="flex:1; background:#fff3e0; border:1px solid #ffcc80; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#888; margin-bottom:3px;">Min 25%</div>
                    <div style="font-size:15px; font-weight:600; color:#e65100;" id="min25Display">0</div>
                </div>
                <div style="flex:1; background:#ffebee; border:1px solid #ef9a9a; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#c62828; margin-bottom:3px;">Due Amount</div>
                    <div style="font-size:15px; font-weight:700; color:#c62828;" id="dueDisplay">0</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 14px; margin-bottom:10px;">
                <div class="ci-row">
                    <div class="ci-label">Discount</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="number" id="discountInput" class="ci-input" value="0" min="0" step="1">
                    </div>
                </div>
                <div class="ci-row">
                    <div class="ci-label">Payment Date</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="date" id="payDateInput" class="ci-input" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="ci-row">
                    <div class="ci-label">Paid Amount</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="number" id="paidInput" class="ci-input" placeholder="0" min="0" step="1">
                        <div id="paidError" style="display:none;color:#e53935;font-size:11px;margin-top:2px;"></div>
                    </div>
                </div>
                <div class="ci-row">
                    <div class="ci-label">Pay Method</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <select id="payMethodInput" class="ci-select">
                            <option value="cash">Cash</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>
                <div class="ci-row" style="grid-column:1/-1;">
                    <div class="ci-label">Collected By</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <select id="collectedByInput" style="width:100%;">
                            <option value="">- Select Staff -</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding:10px 14px 14px;">
            <button type="button" id="btnSaveInvoice"
                    style="width:100%;background:#00bfa5;color:#fff;border:none;border-radius:4px;
                           padding:9px 0;font-size:13px;font-weight:600;cursor:pointer;letter-spacing:.3px;">
                Create Invoice
            </button>
        </div>

    </div>

</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .content-wrapper { background: #f4f6f8 !important; }
    .select2-container--default .select2-selection--single { border:1px solid #ccc !important; border-radius:4px !important; height:30px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:28px !important; font-size:12px !important; color:#222 !important; padding-left:8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:28px !important; }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single { border-color:#00bfa5 !important; box-shadow:0 0 0 2px rgba(0,191,165,.15) !important; outline:none !important; }
    .select2-dropdown { border:1px solid #b2dfdb !important; border-radius:5px !important; box-shadow:0 6px 20px rgba(0,0,0,.12) !important; font-size:12px !important; z-index:99999 !important; }
    .select2-container--default .select2-search--dropdown .select2-search__field { border:1px solid #ccc !important; border-radius:4px !important; font-size:12px !important; padding:5px 8px !important; }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus { border-color:#00bfa5 !important; outline:none !important; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color:#e0f2f1 !important; color:#004d40 !important; }
    .select2-results__option { padding:6px 10px !important; }
    .pt-opt-wrap { display:flex; align-items:center; gap:9px; }
    .pt-opt-avatar { width:28px; height:28px; border-radius:50%; background:#b2dfdb; color:#004d40; font-weight:700; font-size:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .pt-opt-info { flex:1; min-width:0; }
    .pt-opt-name { font-weight:600; color:#1a3c5e; font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .pt-opt-meta { color:#888; font-size:11px; margin-top:1px; }
    .pt-opt-code { background:#00bfa5; color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:10px; flex-shrink:0; }
    .ci-row { display:flex; align-items:center; gap:0; min-height:28px; }
    .ci-label { flex:0 0 110px; font-size:12px; color:#555; }
    .ci-colon { flex:0 0 12px; color:#888; font-size:12px; }
    .ci-control { flex:1; }
    .ci-select, .ci-input { width:100%; border:1px solid #ccc; border-radius:4px; padding:5px 8px; font-size:12px; color:#222; background:#fff; height:30px; outline:none; }
    .ci-select:focus, .ci-input:focus { border-color:#00bfa5; }
    .ci-select:disabled { background:#f5f5f5; color:#aaa; cursor:not-allowed; }
    textarea.ci-input { height:auto; }
    .inv-th { border:1px solid #ccc; padding:6px 8px; font-weight:600; font-size:11px; color:#333; }
    #billBody td { border:1px solid #ddd; padding:5px 8px; font-size:12px; color:#222; vertical-align:middle; }
    .inv-type-cell { display:inline-block; background:#e0f2f1; color:#00695c; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; white-space:nowrap; }
    .doc-type-cell { display:inline-block; background:#e3f2fd; color:#1565c0; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; white-space:nowrap; }
    .btn-del { background:#e53935; color:#fff; border:none; border-radius:3px; padding:2px 8px; font-size:12px; cursor:pointer; line-height:1.4; }
    .btn-del:hover { background:#b71c1c; }
    #btnAddItem:disabled { background:#90caf9 !important; cursor:not-allowed !important; }
    #btnSaveInvoice:disabled { background:#80cbc4 !important; cursor:not-allowed !important; }
    #billTypeSelect:focus { border-color:#00796b !important; box-shadow:0 0 0 2px rgba(0,121,107,.15); }
</style>
@stop

@prepend('js')
<script>
if (typeof jQuery === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"><\/script>');
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endprepend

@section('js')
<script>
toastr.options = { positionClass:'toast-top-right', timeOut:3000, progressBar:true };
</script>
<script>
(function () {
'use strict';

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

let billItems      = [];
let selectedSub    = null;
let selectedDoctor = null;
const DOCTOR_MAIN_CODE = 9999;

// ============================================================
// BILL TYPE HELPERS
// ============================================================
function getBillType() {
    return $('#billTypeSelect').val();
}

let billTypeMap = {};

function isInvType(type) {
    const bt = billTypeMap[type];
    return bt ? !!bt.requires_category : false;
}

function getMainCodeFromType(type) {
    const bt = billTypeMap[type];
    return bt && bt.main_code ? parseInt(bt.main_code) : null;
}

function getMainNameFromType(type) {
    const bt = billTypeMap[type] || {};
    return bt.label || $('#billTypeSelect option[value="' + type + '"]').text().trim();
}

function getMappedBillTypeLabel(mainCode) {
    let label = '';

    $.each(billTypeMap, function (_, bt) {
        if (
            !label &&
            Number(bt.main_code) === Number(mainCode) &&
            !!bt.requires_category &&
            !bt.free_text_items
        ) {
            label = bt.label || '';
        }
    });

    return label;
}

// ============================================================
// Load Bill Types
// ============================================================
function loadBillTypes() {
    const iconMap = { 'doctor_visit': '🩺', 'investigation': '🔬', 'full_bill': '📋' };

    $.get('{{ route("billing.invoice.getBillTypes") }}', function (data) {
        const $sel = $('#billTypeSelect').empty();

        data.forEach(function (bt) {
            billTypeMap[bt.value] = bt;
            const opt = new Option((iconMap[bt.value] || '📄') + ' ' + bt.label, bt.value);
            if (bt.main_code) {
                $(opt).attr('data-maincode', bt.main_code);
            }
            $sel.append(opt);
        });

        if (data.length) {
            $sel.prop('selectedIndex', 0);
        }

        billTypesLoaded = true;

        if (mainCategoriesLoaded) {
            applyBillTypeView(getBillType());
        }
    }).fail(function () {
        toastr.error('Failed to load bill types.');
    });
}

// ============================================================
// Load Collectors
// ============================================================
function loadOfficeEmployees() {
    $.get('{{ route("billing.invoice.getCollectors") }}', function (data) {
        const $sel = $('#collectedByInput').empty().append('<option value="">- Select Staff -</option>');
        data.forEach(function (emp) {
            $sel.append(new Option(emp.Name, emp.Name));
        });
        $sel.trigger('change');
    }).fail(function () {
        toastr.error('Failed to load staff list.');
    });
}

// ============================================================
// Helpers
// ============================================================
function initials(name) {
    if (!name) return '-';
    const p = name.trim().split(/\s+/);
    return p.length === 1
        ? p[0].charAt(0).toUpperCase()
        : (p[0].charAt(0) + p[p.length - 1].charAt(0)).toUpperCase();
}

function isPatientSelected() {
    return !!$('#selectedPatientId').val();
}

function checkAddButtonState() {
    const type = getBillType();
    const bt   = billTypeMap[type] || {};
    let canAdd = false;

    const docReady = selectedDoctor && selectedDoctor.fee > 0;
    const invReady = !!selectedSub;

    if (bt.requires_doctor && docReady) canAdd = true;
    if (bt.requires_category && invReady) canAdd = true;
    if (bt.free_text_items && (docReady || invReady)) canAdd = true;

    $('#btnAddItem').prop('disabled', !canAdd || !isPatientSelected());
}

function resetInvForm() {
    $('#mainCategoryRow').hide();
    $('#mainCategorySelect').val('');
    $('#subCategorySelect').html('<option value="">- Select -</option>').prop('disabled', true);
    $('#amountInputGroup, #commentInputGroup').hide();
    $('#customAmountInput, #commentInput').val('');
    selectedSub = null;
}

function resetDoctorForm() {
    $('#doctorSelect').val('').trigger('change');
    $('#doctorFeeInput').val('');
    selectedDoctor = null;
}

function resetAllForms() {
    resetInvForm();
    resetDoctorForm();
    $('#docFeeGroup').hide();
}

// ============================================================
// Bill Type View
// ============================================================
function applyBillTypeView(type) {
    if (!type) {
        checkAddButtonState();
        return;
    }

    const bt = billTypeMap[type] || {};

    if (bt.requires_category) {
        if (bt.main_code) {
            $('#invBlockTitle').html('<i class="fas fa-microscope"></i> ' + getMainNameFromType(type));
            loadSubsForMainCode(bt.main_code);
        } else {
            $('#invBlockTitle').html('<i class="fas fa-microscope"></i> Add Investigation');
            showMainCategoryDropdown();
        }
    } else if (bt.free_text_items) {
        $('#invBlockTitle').html('<i class="fas fa-microscope"></i> Add Investigation');
        showMainCategoryDropdown();
    }

    checkAddButtonState();
}

function loadSubsForMainCode(mainCode) {
    $('#mainCategoryRow').hide();
    $('#mainCategorySelect').val(String(mainCode));
    $('#amountInputGroup, #commentInputGroup').hide();
    $('#customAmountInput, #commentInput').val('');
    selectedSub = null;

    $('#subCategorySelect').html('<option value="">Loading...</option>').prop('disabled', true);

    $.get('{{ route("billing.invoice.getSub") }}', { main_code: mainCode }, function (data) {
        const $sub = $('#subCategorySelect');
        $sub.empty().append(new Option('- Select -', ''));

        if (data.length) {
            data.forEach(function (s) {
                const opt = new Option(s.Name + ' (' + parseInt(s.Amount).toLocaleString() + ')', s.Code);
                $(opt).attr('data-name', s.Name)
                    .attr('data-amount', s.Amount)
                    .attr('data-maincode', mainCode);
                $sub.append(opt);
            });
        }

        $sub.prop('disabled', false);
    }).fail(function () {
        $('#subCategorySelect').html('<option value="">Failed to load</option>').prop('disabled', false);
        toastr.error('Failed to load investigations.');
    });
}

function showMainCategoryDropdown() {
    $('#mainCategoryRow').show();
    $('#mainCategorySelect').val('');
    $('#subCategorySelect').html('<option value="">Select Type First</option>').prop('disabled', true);
}

// ============================================================
// Change Handlers
// ============================================================
$('#billTypeSelect').on('change', function () {
    const newType = $(this).val();
    resetInvForm();
    resetDoctorForm();
    applyBillTypeView(newType);
});

$('#mainCategorySelect').on('change', function () {
    const mainCode = $(this).val();
    const $sub     = $('#subCategorySelect');

    selectedSub = null;
    checkAddButtonState();
    $('#amountInputGroup, #commentInputGroup').hide();
    $('#customAmountInput, #commentInput').val('');

    if (!mainCode) {
        $sub.html('<option value="">Select Type First</option>').prop('disabled', true);
        return;
    }

    const curBt = billTypeMap[getBillType()] || {};
    if (curBt.free_text_items || (curBt.requires_category && !curBt.main_code)) {
        loadSubForFullBill(mainCode);
    }
});

function loadSubForFullBill(mainCode) {
    const $sub = $('#subCategorySelect');
    $sub.html('<option value="">Loading...</option>').prop('disabled', true);

    $.get('{{ route("billing.invoice.getSub") }}', { main_code: mainCode }, function (data) {
        $sub.empty().append(new Option('- Select Investigation -', ''));

        if (data.length) {
            data.forEach(function (s) {
                const opt = new Option(s.Name + ' (' + parseInt(s.Amount).toLocaleString() + ')', s.Code);
                $(opt).attr('data-name', s.Name)
                    .attr('data-amount', s.Amount)
                    .attr('data-maincode', mainCode);
                $sub.append(opt);
            });
        }

        $sub.prop('disabled', false);
    }).fail(function () {
        $sub.html('<option value="">Failed to load</option>').prop('disabled', false);
    });
}

$('#subCategorySelect').on('change', function () {
    const $opt = $(this).find(':selected');
    const code = $(this).val();

    if (!code) {
        selectedSub = null;
        $('#amountInputGroup, #commentInputGroup').hide();
        $('#customAmountInput, #commentInput').val('');
    } else {
        const mainCode = parseInt($opt.data('maincode'));
        const curBt = billTypeMap[getBillType()] || {};
        const useMainDropdown = curBt.free_text_items || (curBt.requires_category && !curBt.main_code);
        const mainName = useMainDropdown
            ? $('#mainCategorySelect option:selected').text()
            : getMainNameFromType(getBillType());

        selectedSub = {
            Code     : parseInt(code),
            MainCode : mainCode,
            MainName : mainName,
            Name     : $opt.data('name'),
            Amount   : parseInt($opt.data('amount')) || 0
        };

        $('#customAmountInput').val(selectedSub.Amount);
        $('#commentInput').val('');
        $('#amountInputGroup, #commentInputGroup').show();
    }

    checkAddButtonState();
});

$('#customAmountInput').on('input', function () {
    if (selectedSub) {
        selectedSub.Amount = parseInt($(this).val()) || 0;
        checkAddButtonState();
    }
});

// ============================================================
// Doctors
// ============================================================
$.get('{{ route("billing.invoice.getDoctors") }}', function (data) {
    const $sel = $('#doctorSelect');
    $sel.empty().append(new Option('- Select Doctor -', ''));

    data.forEach(function (doc) {
        const opt = new Option(doc.name, doc.id);
        $(opt).attr('data-name', doc.name);
        $sel.append(opt);
    });
}).fail(function () {
    toastr.error('Failed to load doctors.');
});

$('#doctorSelect').on('change', function () {
    const opt = $(this).find(':selected');

    if ($(this).val()) {
        selectedDoctor = {
            id   : parseInt($(this).val()),
            name : opt.data('name'),
            fee  : 0
        };
        $('#doctorFeeInput').val('');
        $('#docFeeGroup').show();
    } else {
        selectedDoctor = null;
        $('#doctorFeeInput').val('');
        $('#docFeeGroup').hide();
    }

    checkAddButtonState();
});

$('#doctorFeeInput').on('input', function () {
    if (selectedDoctor) {
        selectedDoctor.fee = parseInt($(this).val()) || 0;
        checkAddButtonState();
    }
});

// ============================================================
// Main Category Load Coordination
// ============================================================
let billTypesLoaded      = false;
let mainCategoriesLoaded = false;

$.get('{{ route("billing.invoice.getMain") }}', function (data) {
    const $sel = $('#mainCategorySelect');
    $sel.empty().append(new Option('Select Investigation Type', ''));

    data.forEach(function (m) {
        $sel.append(new Option(m.Name, m.Code));
    });

    mainCategoriesLoaded = true;

    if (billTypesLoaded) {
        applyBillTypeView(getBillType());
    }
}).fail(function () {
    toastr.error('Failed to load investigation types.');
    mainCategoriesLoaded = true;
    if (billTypesLoaded) {
        applyBillTypeView(getBillType());
    }
});

loadBillTypes();

// ============================================================
// Add Item
// ============================================================
$('#btnAddItem').on('click', function () {
    if (!isPatientSelected()) {
        toastr.warning('Please select a patient first.');
        return;
    }

    const type        = getBillType();
    const patientCode = $('#selectedPatientCode').val();
    let requests      = [];
    const bt          = billTypeMap[type] || {};

    if ((bt.requires_doctor || bt.free_text_items) && selectedDoctor) {
        selectedDoctor.fee = parseInt($('#doctorFeeInput').val()) || 0;

        if (selectedDoctor.fee <= 0) {
            toastr.warning('Please enter visit fee.');
            if (bt.requires_doctor && !bt.free_text_items) return;
        } else if (billItems.some(i => i.item_type === 'doctor' && Number(i.subCode) === Number(selectedDoctor.id))) {
            toastr.warning('Dr. ' + selectedDoctor.name + ' already added.');
        } else {
            requests.push({
                item_type    : 'doctor',
                patient_code : patientCode,
                main_code    : DOCTOR_MAIN_CODE,
                sub_code     : selectedDoctor.id,
                name         : 'Dr. ' + selectedDoctor.name,
                mainName     : 'Doctor Consultation',
                amount       : selectedDoctor.fee,
                comment      : ''
            });
        }
    }

    if ((bt.requires_category || bt.free_text_items) && selectedSub) {
        selectedSub.Amount = parseInt($('#customAmountInput').val()) || 0;

        if (billItems.some(i =>
            i.item_type === 'investigation' &&
            Number(i.mainCode) === Number(selectedSub.MainCode) &&
            Number(i.subCode) === Number(selectedSub.Code)
        )) {
            toastr.warning(selectedSub.Name + ' already added.');
        } else {
            requests.push({
                item_type    : 'investigation',
                patient_code : patientCode,
                main_code    : selectedSub.MainCode,
                sub_code     : selectedSub.Code,
                name         : selectedSub.Name,
                mainName     : selectedSub.MainName,
                amount       : selectedSub.Amount,
                comment      : $('#commentInput').val().trim()
            });
        }
    }

    if (requests.length === 0) {
        return;
    }

    $('#btnAddItem').prop('disabled', true);

    const csrfToken = '{{ csrf_token() }}';

    const promises = requests.map(function (req) {
        return $.ajax({
            url    : '{{ route("billing.invoice.addTmp") }}',
            method : 'POST',
            data   : {
                _token       : csrfToken,
                patient_code : req.patient_code,
                main_code    : req.main_code,
                sub_code     : req.sub_code,
                name         : req.name,
                amount       : req.amount
            }
        }).then(function (res) {
            if (res && res.success) {
                billItems.push({
                    item_type : req.item_type,
                    subCode   : req.sub_code,
                    mainCode  : req.main_code,
                    mainName  : req.mainName,
                    name      : req.name,
                    amount    : req.amount,
                    comment   : req.comment,
                    tmpId     : res.tmp_id
                });
            } else {
                toastr.error('Server rejected item: ' + req.name + (res && res.message ? ' — ' + res.message : ''));
            }
            return res;
        }, function (xhr) {
            const msg = xhr.responseJSON
                ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON))
                : ('HTTP ' + xhr.status + ' — ' + xhr.statusText);
            toastr.error('Add item failed: ' + msg);
            return { success: false };
        });
    });

    Promise.all(promises).then(function (results) {
        const anyAdded = results.some(function (r) { return r && r.success; });

        renderBill();

        if (anyAdded) {
            toastr.success('Item(s) added to bill.');
        } else {
            toastr.error('No items were added. Check console for details.');
        }

        const bt2 = billTypeMap[type] || {};

        if (bt2.requires_doctor && !bt2.free_text_items) {
            resetDoctorForm();
        }

        if (bt2.requires_category && !bt2.free_text_items) {
            resetInvForm();
            loadSubsForMainCode(getMainCodeFromType(type));
        }

        if (bt2.free_text_items) {
            resetDoctorForm();
            resetInvForm();
            $('#mainCategorySelect').val('');
            $('#subCategorySelect').html('<option value="">Select Type First</option>').prop('disabled', true);
        }

        checkAddButtonState();
    }).catch(function () {
        toastr.error('Unexpected error. Check console.');
        checkAddButtonState();
    })['finally'](function () {
        checkAddButtonState();
    });
});

// ============================================================
// Render Bill
// ============================================================
function renderBill() {
    const $body = $('#billBody');
    $body.empty();

    if (!billItems.length) {
        $body.html(`<tr id="billEmptyRow">
            <td colspan="6" style="text-align:center;color:#aaa;padding:20px;font-size:12px;border:1px solid #ddd;">
                No items added yet
            </td>
        </tr>`);
        $('#billTotalFoot').text('0');
        return;
    }

    let total = 0;

    billItems.forEach(function (item, idx) {
        total += item.amount;
        const badgeClass = (item.item_type === 'doctor') ? 'doc-type-cell' : 'inv-type-cell';

        $body.append(`
            <tr>
                <td style="color:#888; text-align:center;">${idx + 1}</td>
                <td><span class="${badgeClass}">${item.mainName || ''}</span></td>
                <td style="font-weight:500;">${item.name}</td>
                <td style="text-align:right; font-weight:600;">${item.amount.toLocaleString()}</td>
                <td style="font-size:11px; color:#777; font-style:italic;">${item.comment || '-'}</td>
                <td style="text-align:center;">
                    <button type="button" class="btn-del btnRemove" data-idx="${idx}" data-tmpid="${item.tmpId || 0}">x</button>
                </td>
            </tr>
        `);
    });

    $('#billTotalFoot').text(total.toLocaleString());
    recalcPayment();
}

$(document).on('click', '.btnRemove', function () {
    const idx   = parseInt($(this).data('idx'));
    const tmpId = parseInt($(this).data('tmpid'));

    if (tmpId > 0) {
        $.ajax({
            url    : '{{ route("billing.invoice.removeTmp") }}',
            method : 'DELETE',
            data   : { tmp_id: tmpId }
        });
    }

    billItems.splice(idx, 1);
    renderBill();
});

// ============================================================
// Payment Recalc
// ============================================================
function recalcPayment() {
    const total    = billItems.reduce((s, i) => s + i.amount, 0);
    const discount = parseInt($('#discountInput').val()) || 0;
    const net      = Math.max(0, total - discount);
    const min25    = Math.ceil(net * 0.25);
    const paid     = parseInt($('#paidInput').val()) || 0;
    const due      = Math.max(0, net - paid);

    $('#subTotalDisplay').text(total.toLocaleString());
    $('#lessDisplay').text(discount.toLocaleString());
    $('#min25Display').text(min25.toLocaleString());
    $('#grandTotalDisplay').text(net.toLocaleString());
    $('#dueDisplay').text(due.toLocaleString());

    if (paid > 0 && paid < min25) {
        $('#paidError').text('Minimum 25% required: ' + min25.toLocaleString()).show();
        $('#paidInput').css('border-color', '#e53935');
    } else {
        $('#paidError').hide();
        $('#paidInput').css('border-color', '#ccc');
    }
}

$('#discountInput, #paidInput').on('input', recalcPayment);

// ============================================================
// Patient Select2
// ============================================================
$('#patientSelect').select2({
    placeholder        : 'Type name / code / mobile...',
    allowClear         : true,
    minimumInputLength : 0,
    width              : '100%',
    dropdownParent     : $('#patientSelect').parent(),
    ajax: {
        url           : '{{ route("billing.invoice.searchPatient") }}',
        dataType      : 'json',
        delay         : 250,
        data          : function (params) {
            return { q: params.term || '' };
        },
        processResults: function (data) {
            return {
                results: data.map(function (p) {
                    return { id: p.id, text: p.patientname, data: p };
                })
            };
        },
        cache: false
    },
    templateResult: function (item) {
        if (item.loading) return $('<span style="color:#00897b;font-size:12px;">Searching...</span>');
        if (!item.data) return item.text;

        const p = item.data;
        const av = initials(p.patientname);
        const meta = [p.age ? p.age + ' yrs' : '', p.gender || '', p.mobile_no || ''].filter(Boolean).join(' · ');

        return $(`
            <div class="pt-opt-wrap">
                <div class="pt-opt-avatar">${av}</div>
                <div class="pt-opt-info">
                    <div class="pt-opt-name">${p.patientname}</div>
                    <div class="pt-opt-meta">${meta}</div>
                </div>
                <span class="pt-opt-code">${p.patientcode || ''}</span>
            </div>
        `);
    },
    templateSelection: function (item) {
        if (!item.data) return item.text;
        return item.data.patientname;
    },
    language: {
        noResults: function () { return 'No patients found'; },
        searching: function () { return 'Searching...'; }
    }
});

$('#patientSelect').on('select2:select', function (e) {
    const p = e.params.data.data;

    $('#ptCode').text(p.patientcode || '');
    $('#ptName').text(p.patientname || '');
    $('#ptAge').text((p.age || '') + ' / ' + (p.gender || ''));
    $('#ptMobile').text(p.mobile_no || '');
    $('#selectedPatientId').val(p.id);
    $('#selectedPatientCode').val(p.patientcode);
    $('#selectedPatientName').val(p.patientname);
    $('#selectedPatientAge').val(p.age || '');
    $('#selectedMobile').val(p.mobile_no || '');
    $('#selectedAdmissionId').val(p.admission_id || '');
    $('#invoicePatientName').text(p.patientname);
    $('#patientInfoBox').show();

    updateFieldsVisibility();
    loadTmpItems(p.patientcode);
});

$('#patientSelect').on('select2:unselect select2:clear', function () {
    $('#patientInfoBox').hide();
    $('#selectedPatientId,#selectedPatientCode,#selectedPatientName,#selectedPatientAge,#selectedMobile,#selectedAdmissionId').val('');
    $('#invoicePatientName').text('- No patient selected -');
    billItems = [];
    renderBill();
    updateFieldsVisibility();
});

$('#btnClearPatient').on('click', function () {
    $('#patientSelect').val(null).trigger('change');
});

function updateFieldsVisibility() {
    if (isPatientSelected()) {
        $('#patientRequiredNotice').hide();
        applyBillTypeView(getBillType());
    } else {
        $('#patientRequiredNotice').show();
        resetAllForms();
        $('#btnAddItem').prop('disabled', true);
    }
}

// ============================================================
// Load Tmp Items
// ============================================================
function loadTmpItems(patientCode) {
    $.get('{{ route("billing.invoice.getTmp") }}', { patient_code: patientCode }, function (data) {
        billItems = [];

        if (!data.length) {
            renderBill();
            return;
        }

        const mainCodes = [...new Set(data.map(r => Number(r.MainCode)).filter(mc => mc !== DOCTOR_MAIN_CODE))];
        let nameMap = {};
        const mainNameMap = {};

        $('#mainCategorySelect option').each(function () {
            if ($(this).val()) {
                mainNameMap[$(this).val()] = $(this).text();
            }
        });

        function resolveDocName(id) {
            const $opt = $('#doctorSelect option[value="' + id + '"]');
            return $opt.length ? ('Dr. ' + $opt.data('name')) : ('Doctor #' + id);
        }

        function buildItems() {
            data.forEach(function (row) {
                const isDoc = Number(row.MainCode) === DOCTOR_MAIN_CODE;
                const itemKey = row.MainCode + '_' + row.SubCode;

                billItems.push({
                    item_type : isDoc ? 'doctor' : 'investigation',
                    subCode   : row.SubCode,
                    mainCode  : row.MainCode,
                    mainName  : isDoc ? 'Doctor Consultation' : (getMappedBillTypeLabel(row.MainCode) || mainNameMap[row.MainCode] || ''),
                    name      : isDoc ? resolveDocName(row.SubCode) : (row.Name || nameMap[itemKey] || ('Item #' + row.SubCode)),
                    amount    : parseInt(row.Amount) || 0,
                    comment   : '',
                    tmpId     : row.ID
                });
            });

            renderBill();
        }

        if (mainCodes.length === 0) {
            buildItems();
            return;
        }

        let pending = mainCodes.length;

        mainCodes.forEach(function (mc) {
            $.get('{{ route("billing.invoice.getSub") }}', { main_code: mc }, function (subs) {
                subs.forEach(function (s) {
                    nameMap[mc + '_' + s.Code] = s.Name;
                });

                pending--;
                if (pending === 0) {
                    buildItems();
                }
            }).fail(function () {
                pending--;
                if (pending === 0) {
                    buildItems();
                }
            });
        });
    });
}

// ============================================================
// Save Invoice
// ============================================================
$('#btnSaveInvoice').on('click', function () {
    const patientId = $('#selectedPatientId').val();

    if (!patientId) {
        toastr.warning('Please select a patient first.');
        return;
    }

    if (!billItems.length) {
        toastr.warning('Please add at least one item.');
        return;
    }

    const total    = billItems.reduce((s, i) => s + i.amount, 0);
    const discount = parseInt($('#discountInput').val()) || 0;
    const net      = Math.max(0, total - discount);
    const min25    = Math.ceil(net * 0.25);
    const paid     = parseInt($('#paidInput').val()) || 0;

    if (paid < min25) {
        $('#paidError').text('Minimum 25% required: ' + min25.toLocaleString()).show();
        $('#paidInput').focus();
        toastr.error('Paid amount must be at least 25% (' + min25.toLocaleString() + ').');
        return;
    }

    const payload = {
        patient_id     : patientId,
        admission_id   : $('#selectedAdmissionId').val() || null,
        patient_name   : $('#selectedPatientName').val(),
        patient_code   : $('#selectedPatientCode').val(),
        patient_age    : $('#selectedPatientAge').val(),
        mobile_no      : $('#selectedMobile').val(),
        total_bill     : total,
        discount       : discount,
        paid_amount    : paid,
        payment_date   : $('#payDateInput').val(),
        payment_method : $('#payMethodInput').val(),
        collected_by   : $('#collectedByInput').val(),
        invoice_type   : getBillType(),
        items          : billItems.map(function (i) {
            return {
                category      : i.mainCode,
                category_name : i.mainName,
                service_name  : i.name,
                unit_price    : i.amount,
                quantity      : 1,
                amount        : i.amount,
                remarks       : i.comment || ''
            };
        }),
        _token : '{{ csrf_token() }}'
    };

    const $btn = $(this);
    $btn.prop('disabled', true).text('Saving...');

    $.ajax({
        url         : '{{ route("billing.invoice.store") }}',
        method      : 'POST',
        contentType : 'application/json',
        data        : JSON.stringify(payload),
        success: function (res) {
            if (res.success) {
                toastr.success('Invoice saved!');
                $.post('{{ route("billing.invoice.clearTmp") }}', {
                    patient_code : $('#selectedPatientCode').val(),
                    _token       : '{{ csrf_token() }}'
                });

                // ✅ FIX: notun tab e open kore — browser title = invoice title → unique filename
                window.open('{{ url("Billing/CreateInvoice") }}/' + res.payment_id + '/print', '_blank');

                billItems = [];
                renderBill();
                $('#patientSelect').val(null).trigger('change');
            } else {
                toastr.error(res.message || 'Failed to save.');
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON ? xhr.responseJSON.message : 'Server error.');
        },
        complete: function () {
            $btn.prop('disabled', false).text('Create Invoice');
        }
    });
});

// ============================================================
// Init
// ============================================================
$('#collectedByInput').select2({
    placeholder    : '- Select Staff -',
    allowClear     : true,
    dropdownParent : $('#invoiceBottomSection'),
    width          : '100%'
});

loadOfficeEmployees();
updateFieldsVisibility();

})();
</script>
@stop