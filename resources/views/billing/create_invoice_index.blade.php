@extends('adminlte::page')

@section('title', $pageTitle ?? 'Create Invoice')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            {{ $pageTitle ?? 'Create Invoice' }}
            @if(($paymentSource ?? null) === 'manager')
                <span class="badge" style="background:#7b1fa2;color:#fff;font-size:11px;padding:2px 10px;border-radius:10px;font-weight:600;vertical-align:middle;margin-left:8px;">Manager Collection</span>
            @elseif(($paymentSource ?? null) === 'reception')
                <span class="badge" style="background:#00897b;color:#fff;font-size:11px;padding:2px 10px;border-radius:10px;font-weight:600;vertical-align:middle;margin-left:8px;">Reception Collection</span>
            @endif
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.invoice.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">{{ $pageTitle ?? 'Create Invoice' }}</li>
        </ol>
    </div>
@stop

@section('content')

<div style="display:flex; gap:16px; padding:4px 0; align-items:flex-start;">

    {{-- LEFT COLUMN --}}
    <div style="flex:0 0 45%; border:2px solid #00bfa5; border-radius:6px; background:#fff; overflow:hidden;">

        <div style="padding:10px 14px; border-bottom:1px solid #e0e0e0; font-size:15px; font-weight:600; color:#222; display:flex; justify-content:space-between; align-items:center;">
            <span>{{ $pageTitle ?? 'Create Invoice' }}
                @if(($paymentSource ?? null) === 'manager')
                    <span class="badge" style="background:#7b1fa2;color:#fff;font-size:11px;padding:2px 10px;border-radius:10px;font-weight:600;vertical-align:middle;margin-left:8px;">Manager Collection</span>
                @elseif(($paymentSource ?? null) === 'reception')
                    <span class="badge" style="background:#00897b;color:#fff;font-size:11px;padding:2px 10px;border-radius:10px;font-weight:600;vertical-align:middle;margin-left:8px;">Reception Collection</span>
                @endif
            </span>

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

                    <div class="ci-row" id="chamberTypeGroup" style="display:none; margin-top:8px;">
                        <div class="ci-label">Chamber Type</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <select id="chamber_type" class="ci-select">
                                <option value="">Loading...</option>
                            </select>
                        </div>
                    </div>

                    <div class="ci-row" id="docFeeGroup" style="display:none; margin-top:8px;">
                        <div class="ci-label">Visit Fee</div>
                        <div class="ci-colon">:</div>
                        <div class="ci-control">
                            <input type="number" id="doctorFeeInput" class="ci-input" readonly
                                   placeholder="Auto-filled from chamber selection" min="0" step="1"
                                   title="Fee is fixed by the selected chamber type and cannot be edited.">
                        </div>
                    </div>

                    <div class="ci-row" id="visitInfoGroup" style="display:none; margin-top:6px;">
                        <div class="ci-label"></div>
                        <div class="ci-colon"></div>
                        <div class="ci-control" style="font-size:11px; color:#555;">
                            Visit Type: <span id="visitType" style="font-weight:600;">-</span>
                            &nbsp;|&nbsp;
                            Last Visit: <span id="lastVisit" style="font-weight:600;">-</span>
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
                                   placeholder="Auto-filled from selection" min="0" step="1"
                                   title="Change the price if needed.">
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
                    <div style="font-size:11px; color:#888; margin-bottom:3px;">Min 50%</div>
                    <div style="font-size:15px; font-weight:600; color:#e65100;" id="min50Display">0</div>
                </div>
                <div style="flex:1; background:#ffebee; border:1px solid #ef9a9a; border-radius:5px; padding:8px 12px;">
                    <div style="font-size:11px; color:#c62828; margin-bottom:3px;">Due Amount</div>
                    <div style="font-size:15px; font-weight:700; color:#c62828;" id="dueDisplay">0</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 14px; margin-bottom:10px;">
                <div class="ci-row ci-row-narrow">
                    <div class="ci-label ci-label-narrow" id="discountLabel">Discount</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control" style="display:flex; gap:6px; align-items:center;">
                        <select id="discountTypeSelect" style="border:1px solid #ccc; border-radius:4px; font-size:12px; padding:6px 6px; outline:none; cursor:pointer;">
                            <option value="percent">%</option>
                            <option value="flat">৳</option>
                        </select>
                        <input type="number" id="discountInput" class="ci-input" value="0" min="0" step="0.01" style="flex:1;">
                    </div>
                </div>
                <div class="ci-row ci-row-narrow">
                    <div class="ci-label ci-label-narrow">Payment Date</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="date" id="payDateInput" class="ci-input" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="ci-row ci-row-narrow">
                    <div class="ci-label ci-label-narrow">Delivery Date/Time</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="datetime-local" id="deliveryInput" class="ci-input" value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                </div>
                <div class="ci-row ci-row-narrow">
                    <div class="ci-label ci-label-narrow">Paid Amount</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <input type="number" id="paidInput" class="ci-input" placeholder="0" min="0" step="1">
                        <div id="paidError" style="display:none;color:#e53935;font-size:11px;margin-top:2px;"></div>
                    </div>
                </div>
                <div class="ci-row ci-row-narrow">
                    <div class="ci-label ci-label-narrow">Pay Method</div>
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
                <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                    <div class="ci-label ci-label-narrow">Reference By</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <select id="referenceNameInput" style="width:100%;">
                            <option value="">- Select Member -</option>
                            @foreach ($references as $ref)
                                <option value="{{ $ref->Name }}">{{ $ref->Name }} ({{ $ref->ref_type }})</option>
                            @endforeach
                            <option value="__others__">Others (type name)</option>
                        </select>
                        <input type="text" id="referenceNameOther" class="ci-input"
                               placeholder="Type reference name" style="display:none; margin-top:4px;">
                        <div id="referenceNote" style="display:none;color:#e65100;font-size:11px;margin-top:2px;"></div>
                    </div>
                </div>
                <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                    <div class="ci-label ci-label-narrow">Ref by Doctor</div>
                    <div class="ci-colon">:</div>
                    <div class="ci-control">
                        <select id="invoiceRefDoctorSelect" class="ci-select">
                            <option value="">- Select Doctor -</option>
                        </select>
                    </div>
                </div>
                <div class="ci-row ci-row-narrow" style="grid-column:1/-1;">
                    <div class="ci-label ci-label-narrow">Collected By</div>
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
            {{-- Success panel: invoice is created ONCE; copies below are read-only print actions --}}
            <div id="invoiceResultPanel" style="display:none;margin-top:10px;padding:12px;border:1px solid #b2dfdb;border-radius:4px;background:#e0f2f1;">
                <div style="font-size:13px;font-weight:700;color:#00695c;">
                    Invoice Created Successfully — <span id="createdBillNo"></span>
                </div>
                <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="btn-copy-print" data-copy="patient"
                            style="flex:1;min-width:100px;background:#00bfa5;color:#fff;border:none;border-radius:4px;padding:7px 0;font-size:12px;font-weight:600;cursor:pointer;">
                        Patient Copy
                    </button>
                    <button type="button" class="btn-copy-print" data-copy="office"
                            style="flex:1;min-width:100px;background:#00897b;color:#fff;border:none;border-radius:4px;padding:7px 0;font-size:12px;font-weight:600;cursor:pointer;">
                        Office Copy
                    </button>
                    <button type="button" class="btn-copy-print" data-copy="reprint"
                            style="flex:1;min-width:100px;background:#546e7a;color:#fff;border:none;border-radius:4px;padding:7px 0;font-size:12px;font-weight:600;cursor:pointer;">
                        Reprint
                    </button>
                </div>
            </div>
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
    .ci-row { display:flex; align-items:center; gap:0; min-height:28px; min-width:0; }
    .ci-label { flex:0 0 110px; font-size:12px; color:#555; }
    .ci-colon { flex:0 0 12px; color:#888; font-size:12px; }
    .ci-control { flex:1; min-width:0; }
    .ci-select, .ci-input { width:100%; max-width:100%; min-width:0; border:1px solid #ccc; border-radius:4px; padding:5px 8px; font-size:12px; color:#222; background:#fff; height:30px; outline:none; box-sizing:border-box; }
    input[type="datetime-local"].ci-input { font-size:11px; padding:5px 4px; }
    .ci-select:focus, .ci-input:focus { border-color:#00bfa5; }
    .ci-select:disabled { background:#f5f5f5; color:#aaa; cursor:not-allowed; }
    .ci-input[readonly] { background:#f5f5f5; color:#555; cursor:not-allowed; font-weight:600; }
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
    .ci-row-narrow { min-height:24px; }
    .ci-label-narrow { flex:0 0 80px; font-size:12px; color:#555; }
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
let selectedDoctor   = null;
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
     //Route Name: billing
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
	//Route Name: billing
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
// Chamber Types (dynamic — from chamber_fee_configs table)
// Fully ID-driven: option VALUE is the config id, never the name.
// ============================================================
function loadChamberTypes() {
	 //Route Name: billing
    $.get('{{ route("billing.invoice.getChamberTypes") }}', function (data) {
        const $sel = $('#chamber_type');
        $sel.empty().append(new Option('- Select -', ''));

        data.forEach(function (c) {
            $sel.append(new Option(c.chamber_name, c.id));
        });
    }).fail(function () {
        $('#chamber_type').html('<option value="">Failed to load</option>');
        toastr.error('Failed to load chamber types.');
    });
}

function getSelectedChamberConfigId() {
    const id = parseInt($('#chamber_type').val());
    return id > 0 ? id : null;
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


function resetInvForm(soft) {
    if (soft) {
        
        $('#subCategorySelect').val('');
    } else {
        
        $('#mainCategoryRow').hide();
        $('#mainCategorySelect').val('');
        $('#subCategorySelect').html('<option value="">- Select -</option>').prop('disabled', true);
    }
    $('#amountInputGroup, #commentInputGroup').hide();
    $('#customAmountInput, #commentInput').val('');
    selectedSub = null;
}

// ============================================================
// ★ ফিক্স ২: resetDoctorForm — soft parameter
// ============================================================
function resetDoctorForm(soft) {
    if (soft) {
        // শুধু selection, fee, chamber type clear
        $('#doctorSelect').val('');
        $('#doctorFeeInput').val('');
        $('#docFeeGroup').hide();
        $('#chamberTypeGroup, #visitInfoGroup').hide();
        $('#chamber_type').val('');
        $('#visitType, #lastVisit').text('-');
        selectedDoctor = null;
    } else {
        // trigger('change') সহ পুরো রিসেট
        $('#doctorSelect').val('').trigger('change');
        $('#doctorFeeInput').val('');
        $('#chamberTypeGroup, #visitInfoGroup').hide();
        $('#chamber_type').val('');
        $('#visitType, #lastVisit').text('-');
        selectedDoctor = null;
    }
}

function resetAllForms() {
    resetInvForm(false);
    resetDoctorForm(false);
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
    //Route:billing
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
    applyMainCategoryFilter();
}

// ============================================================
// Canonical investigation categories — keeps CategoryName clean
// (ECG / X-Ray / Pathology / USG) for the Investigation bill type
// ============================================================
const INVESTIGATION_CATEGORY_NAMES = ['ECG', 'X-Ray', 'Pathology', 'USG'];

function applyMainCategoryFilter() {
    const $sel = $('#mainCategorySelect');
    const all  = window.__allMainCategories || [];

    if (!isInvType(getBillType())) {
        if (all.length && $sel.children('option').length !== all.length + 1) {
            rebuildMainCategorySelect(all);
        }
        return;
    }

    const canonical = all.filter(function (m) {
        return INVESTIGATION_CATEGORY_NAMES.indexOf(m.Name) !== -1;
    });

    rebuildMainCategorySelect(canonical);
}

function rebuildMainCategorySelect(items) {
    const $sel = $('#mainCategorySelect');
    $sel.empty().append(new Option('Select Investigation Type', ''));
    items.forEach(function (m) {
        $sel.append(new Option(m.Name, m.Code));
    });
}

// ============================================================
// Change Handlers
// ============================================================
 $('#billTypeSelect').on('change', function () {
    const newType = $(this).val();
    resetInvForm(false);  // ★ hard reset
    resetDoctorForm(false); // ★ hard reset
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
    //Route:billing
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

// Keep the selected item's price in sync when it is edited before adding it.
$('#customAmountInput').on('input', function () {
    if (selectedSub) {
        selectedSub.Amount = parseInt($(this).val(), 10) || 0;
        checkAddButtonState();
    }
});

// ============================================================
// Doctors
// ============================================================
//Route:billing
 $.get('{{ route("billing.invoice.getDoctors") }}', function (data) {
    const $sel = $('#doctorSelect');
    $sel.empty().append(new Option('- Select Doctor -', ''));

    const $refInvSel = $('#invoiceRefDoctorSelect');
    $refInvSel.empty().append(new Option('- Select Doctor -', ''));

    data.forEach(function (doc) {
        const opt = new Option(doc.name, doc.id);
        $(opt).attr('data-name', doc.name);
        $sel.append(opt);

        const refInvOpt = new Option(doc.name, doc.id);
        $(refInvOpt).attr('data-name', doc.name);
        $refInvSel.append(refInvOpt);
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
        $('#chamberTypeGroup').show();
    } else {
        selectedDoctor = null;
        $('#doctorFeeInput').val('');
        $('#docFeeGroup').hide();
        $('#chamberTypeGroup, #visitInfoGroup').hide();
        $('#chamber_type').val('');
        $('#visitType, #lastVisit').text('-');
    }

    checkAddButtonState();
});

// ============================================================
// Chamber Type -> auto-fetch consultation fee
// ============================================================
 $('#chamber_type').on('change', function () {
    const patient_id   = $('#selectedPatientId').val();
    const doctor_id    = $('#doctorSelect').val();
    const chamber_fee_config_id = getSelectedChamberConfigId();

    if (!patient_id || !doctor_id || !chamber_fee_config_id) {
        return;
    }

    $.ajax({
		//Route:billing
        url  : '{{ route("billing.check.consultation.fee") }}',
        type : 'POST',
        data : {
            _token               : '{{ csrf_token() }}',
            patient_id           : patient_id,
            doctor_id            : doctor_id,
            chamber_fee_config_id : chamber_fee_config_id
        },
        success: function (res) {
            if (res.success) {
                $('#doctorFeeInput').val(res.fee);
                $('#visitType').text(res.visit_type);
                $('#lastVisit').text(res.last_visit_date ?? '-');
                $('#visitInfoGroup').show();

                if (selectedDoctor) {
                    selectedDoctor.fee = parseInt(res.fee) || 0;
                }

                checkAddButtonState();
            } else {
                toastr.warning(res.message || 'Could not fetch fee.');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON ? (xhr.responseJSON.message || 'Validation error.') : 'Failed to check consultation fee.';
            toastr.error(msg);
        }
    });
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
//Roue:billing
 $.get('{{ route("billing.invoice.getMain") }}', function (data) {
    const $sel = $('#mainCategorySelect');
    $sel.empty().append(new Option('Select Investigation Type', ''));

    window.__allMainCategories = data;

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
            toastr.warning(selectedDoctor.name + ' already added.');
        } else {
            requests.push({
                item_type    : 'doctor',
                patient_code : patientCode,
                main_code    : DOCTOR_MAIN_CODE,
                sub_code     : selectedDoctor.id,
                name         : selectedDoctor.name,
                mainName     : 'Doctor Consultation',
                amount       : selectedDoctor.fee,
                comment      : '',
                doctor_id    : selectedDoctor.id,
                chamber_fee_config_id : getSelectedChamberConfigId()
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
			//Route:billing
            url    : '{{ route("billing.invoice.addTmp") }}',
            method : 'POST',
            data   : {
                _token       : csrfToken,
                patient_code : req.patient_code,
                main_code    : req.main_code,
                sub_code     : req.sub_code,
                name         : req.name,
                amount       : req.amount,
                doctor_id    : req.doctor_id || null,
                chamber_fee_config_id : req.chamber_fee_config_id || null
            }
        }).then(function (res) {
            if (res && res.success) {
                billItems.push({
                    item_type  : req.item_type,
                    subCode    : req.sub_code,
                    mainCode   : req.main_code,
                    mainName   : req.mainName,
                    name       : req.name,
                    amount     : req.amount,
                    comment    : req.comment,
                    doctorId   : req.doctor_id || null,
                    doctorName : req.doctor_name || null,
                    chamberFeeConfigId : req.chamber_fee_config_id || null,
                    tmpId      : res.tmp_id
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
            resetDoctorForm(true);  
        }

        if (bt2.requires_category && !bt2.free_text_items) {
            resetInvForm(true);  
            
        }

        if (bt2.free_text_items) {
            resetDoctorForm(true);  
            resetInvForm(true);    
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
        recalcPayment();
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
                <td style="font-weight:500;">
                    ${item.name}
                    ${item.doctorName ? '<div style="font-size:10px;color:#00695c;font-weight:400;">Ref: ' + item.doctorName + '</div>' : ''}
                </td>
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
			//Route:billing
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
function getReferenceName() {
    const selected = $('#referenceNameInput').val();

    if (selected === '__others__') {
        return $('#referenceNameOther').val().trim();
    }

    return selected ? selected.trim() : '';
}

function computeDiscount(total) {
    const discType  = $('#discountTypeSelect').val() || 'percent';
    const discValue = parseFloat($('#discountInput').val()) || 0;

    if (discType === 'flat') {
        return Math.min(Math.round(discValue), total);
    }
    return Math.round(total * discValue / 100);
}

function recalcPayment() {
    const total    = billItems.reduce((s, i) => s + i.amount, 0);
    const discount = computeDiscount(total);
    const net      = Math.max(0, total - discount);
    // Min 50% is ALWAYS based on the ORIGINAL total (pre-discount) — a
    // discount must never lower this threshold. Matches backend formula
    // exactly (CreateInvoiceRequest.php / InvoiceService.php).
    const min50    = Math.ceil(total * 0.5);
    const paid     = parseInt($('#paidInput').val()) || 0;
    const due      = Math.max(0, net - paid);
    const referenceName = getReferenceName();

    $('#subTotalDisplay').text(total.toLocaleString());
    $('#lessDisplay').text(discount.toLocaleString());
    $('#min50Display').text(min50.toLocaleString());
    $('#grandTotalDisplay').text(net.toLocaleString());
    $('#dueDisplay').text(due.toLocaleString());

    // ------------------------------------------------------------
    // Discount + Reference rule takes priority over the plain 50%
    // check: ANY discount > 0 requires a reference_name, and (when a
    // reference IS present) the invoice always goes to pending —
    // regardless of how much was paid. So the ordinary "below 50%"
    // warning is not relevant once a discount is applied; a different
    // message is shown instead.
    // ------------------------------------------------------------
    if (discount > 0) {
        if (!referenceName) {
            $('#paidError').text('Discount দিতে হলে Reference By নাম বাধ্যতামূলক।').show();
            $('#paidInput').css('border-color', '#e53935');
        } else if (paid > net) {
            $('#paidError').text('Paid cannot exceed net payable: ' + net.toLocaleString()).show();
            $('#paidInput').css('border-color', '#e53935');
        } else {
            $('#paidError').hide();
            $('#paidInput').css('border-color', '#ccc');
        }
    } else if (paid > net) {
        $('#paidError').text('Paid cannot exceed net payable: ' + net.toLocaleString()).show();
        $('#paidInput').css('border-color', '#e53935');
    } else if (paid > 0 && paid < min50) {
        if (referenceName) {
            $('#paidError').text('Below 50% (' + min50.toLocaleString() + ') — will be saved as pending admin approval.').show();
            $('#paidInput').css('border-color', '#e65100');
        } else {
            $('#paidError').text('Minimum 50% required: ' + min50.toLocaleString()).show();
            $('#paidInput').css('border-color', '#e53935');
        }
    } else {
        $('#paidError').hide();
        $('#paidInput').css('border-color', '#ccc');
    }

    updateReferenceNote(discount, paid, min50, referenceName);
}

function updateReferenceNote(discount, paid, min50, referenceName) {
    if (discount > 0 && referenceName) {
        $('#referenceNote')
            .text('Discount প্রয়োগ করা হয়েছে — এই invoice সবসময় admin approval-এর জন্য pending থাকবে।')
            .show();
    } else if (discount === 0 && referenceName && paid > 0 && paid < min50) {
        $('#referenceNote')
            .text('This invoice will be submitted for admin approval since the paid amount is below 50%.')
            .show();
    } else {
        $('#referenceNote').hide();
    }
}

 $('#discountTypeSelect').on('change', function () {
    const isFlat = $(this).val() === 'flat';
    $('#discountLabel').text(isFlat ? 'Discount (৳)' : 'Discount (%)');
    if (isFlat) {
        $('#discountInput').removeAttr('max');
    } else {
        $('#discountInput').attr('max', 100);
        const total = billItems.reduce((s, i) => s + i.amount, 0);
        if ((parseFloat($('#discountInput').val()) || 0) > 100) {
            $('#discountInput').val(100);
        }
    }
    recalcPayment();
});

 $('#discountInput, #paidInput').on('input', recalcPayment);
 $('#referenceNameOther').on('input', recalcPayment);

 $('#referenceNameInput').on('select2:select', function (e) {
    if (e.params.data.id === '__others__') {
        $('#referenceNameOther').show().focus();
    } else {
        $('#referenceNameOther').val('').hide();
    }
    recalcPayment();
 });

 $('#referenceNameInput').on('select2:clear', function () {
    $('#referenceNameOther').val('').hide();
    recalcPayment();
 });

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
		//Route:billing
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
	//Route:billing
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
            return $opt.length ? $opt.data('name') : ('Doctor #' + id);
        }

        function buildItems() {
            data.forEach(function (row) {
                const isDoc = Number(row.MainCode) === DOCTOR_MAIN_CODE;
                const itemKey = row.MainCode + '_' + row.SubCode;

                billItems.push({
                    item_type  : isDoc ? 'doctor' : 'investigation',
                    subCode    : row.SubCode,
                    mainCode   : row.MainCode,
                    mainName   : isDoc ? 'Doctor Consultation' : (getMappedBillTypeLabel(row.MainCode) || mainNameMap[row.MainCode] || ''),
                    name       : isDoc ? resolveDocName(row.SubCode) : (row.Name || nameMap[itemKey] || ('Item #' + row.SubCode)),
                    amount     : parseInt(row.Amount) || 0,
                    comment    : '',
                    doctorId   : row.DoctorId || null,
                    doctorName : row.DoctorId ? resolveDocName(row.DoctorId) : null,
                    chamberFeeConfigId : row.chamber_fee_config_id ? parseInt(row.chamber_fee_config_id) : null,
                    tmpId      : row.ID
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
			//Route:billing
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
    const discType  = $('#discountTypeSelect').val() || 'percent';
    const discValue = parseFloat($('#discountInput').val()) || 0;
    const discount = computeDiscount(total);
    const net      = Math.max(0, total - discount);
    // Min 50% is ALWAYS based on the ORIGINAL total (pre-discount) —
    // matches the server-side formula exactly.
    const min50    = Math.ceil(total * 0.5);
    const paid     = parseInt($('#paidInput').val()) || 0;
    const referenceName = getReferenceName();

    if (paid > net) {
        $('#paidError').text('Paid amount cannot exceed net payable: ' + net.toLocaleString()).show();
        $('#paidInput').focus();
        toastr.error('Paid amount cannot be more than the bill total (' + net.toLocaleString() + ').');
        return;
    }

    // ------------------------------------------------------------
    // Discount + Reference rule — checked BEFORE the plain 50% rule.
    // Any discount > 0 requires a reference_name, full stop. When a
    // reference IS present alongside a discount, the invoice always
    // goes to pending — the 50% check below is skipped entirely for
    // this case (paid amount doesn't matter once a discount+reference
    // combination is confirmed).
    // ------------------------------------------------------------
    if (discount > 0) {
        if (!referenceName) {
            $('#paidError').text('Discount দিতে হলে Reference By নাম বাধ্যতামূলক।').show();
            $('#referenceNameInput').focus();
            toastr.error('Discount প্রয়োগ করতে হলে অবশ্যই একটি Reference By নাম দিতে হবে।');
            return;
        }

        toastr.info('Discount প্রয়োগ করা হয়েছে — এই invoice admin approval-এর জন্য pending থাকবে।');
    } else {
        if (paid < min50 && !referenceName) {
            $('#paidError').text('Minimum 50% required: ' + min50.toLocaleString()).show();
            $('#paidInput').focus();
            toastr.error('Paid amount must be at least 50% (' + min50.toLocaleString() + '), or provide a Reference By name for admin approval.');
            return;
        }

        if (paid < min50 && referenceName) {
            toastr.info('This invoice will be saved as pending admin approval since paid amount is below 50%.');
        }
    }

    const payload = {
        patient_id     : patientId,
        admission_id   : $('#selectedAdmissionId').val() || null,
        patient_name   : $('#selectedPatientName').val(),
        patient_code   : $('#selectedPatientCode').val(),
        patient_age    : $('#selectedPatientAge').val(),
        mobile_no      : $('#selectedMobile').val(),
        total_bill      : total,
        discount_type   : discType,
        discount_value  : discValue,
        discount        : discount,
        paid_amount     : paid,
        payment_date   : $('#payDateInput').val(),
        delivery_datetime : $('#deliveryInput').val() || null,
        payment_method : $('#payMethodInput').val(),
        collected_by   : $('#collectedByInput').val(),
        ref_doctor_id  : $('#invoiceRefDoctorSelect').val() || null,
        reference_name : referenceName || null,
        invoice_type   : getBillType(),
        items          : billItems.map(function (i) {
            return {
                category      : i.mainCode,
                category_name : i.mainName,
                service_name  : i.name,
                unit_price    : i.amount,
                quantity      : 1,
                amount        : i.amount,
                remarks       : i.comment || '',
                doctor_id     : i.doctorId || null,
                chamber_fee_config_id : i.chamberFeeConfigId || null
            };
        }),
        _token : '{{ csrf_token() }}'
    };

    const $btn = $(this);
    $btn.prop('disabled', true).text('Saving...');

    $.ajax({
		//Route:billing (manager page overrides via $storeRoute)
        url         : '{{ $storeRoute ?? route("billing.invoice.store") }}',
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

                window.open('{{ url("Billing/CreateInvoice") }}/' + res.payment_id + '/pdf?copy=patient', '_blank');

                // Show read-only copy buttons for the SAME invoice (no re-creation).
                window.lastCreatedInvoiceId = res.payment_id;
                $('#createdBillNo').text(res.bill_no || '');
                $('#invoiceResultPanel').show();

                billItems = [];
                renderBill();
                $('#referenceNameInput').val(null).trigger('change');
                $('#referenceNameOther').val('').hide();
                $('#referenceNote').hide();
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
// Copy print buttons — READ-ONLY: load the SAME invoice, never re-create.
// ============================================================
$(document).on('click', '.btn-copy-print', function () {
    var id = window.lastCreatedInvoiceId;
    if (! id) {
        toastr.error('No invoice created yet.');
        return;
    }
    var copy = $(this).data('copy') || 'patient';
    window.open('{{ url("Billing/CreateInvoice") }}/' + id + '/pdf?copy=' + copy, '_blank');
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

 $('#referenceNameInput').select2({
    placeholder    : '- Select Member -',
    allowClear     : true,
    dropdownParent : $('#invoiceBottomSection'),
    width          : '100%'
});

loadOfficeEmployees();
loadChamberTypes();
updateFieldsVisibility();

})();
</script>
@stop
