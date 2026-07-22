@extends('adminlte::page')

@section('title', 'Template Medicine')

@section('content_header')
<div class="cc-appbar">
    <div class="cc-appbar-inner">
        <div class="cc-appbar-brand">
            <i class="fas fa-notes-medical"></i>
            <span>ClinicBilling</span>
        </div>
        <div class="cc-appbar-subtitle">Template Medicine — Manage medicine templates for patient care orders</div>
    </div>
</div>
@stop

@section('content')
<section class="cc-section">
    <div class="container-fluid" style="max-width:1200px;">

        {{-- Template Selection --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="cc-card">
                    <div class="cc-card-header">
                        <i class="fas fa-file-medical-alt mr-2"></i>Select Template
                    </div>
                    <div class="cc-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="cc-label">Template <span class="text-danger">*</span></label>
                                <select id="templateid" class="form-control select2">
                                    <option value="">-- Select Template --</option>
                                    @foreach($templates as $temp)
                                        <option value="{{ $temp->templateid }}">{{ $temp->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert Messages --}}
        <div id="alertMessage"></div>

        {{-- Step Navigation --}}
        <div class="row mb-3" id="stepNavigation" style="display:none;">
            <div class="col-md-12">
                <div class="cc-tab-bar">
                    <button type="button" class="cc-tab-btn stepBtn" id="stepBtn_admit">
                        <i class="fas fa-user-check mr-1"></i> AT Admission
                        <span class="cc-badge" id="admitCount">0</span>
                    </button>
                    <button type="button" class="cc-tab-btn stepBtn" id="stepBtn_preorder">
                        <i class="fas fa-syringe mr-1"></i> Pre-Operation
                        <span class="cc-badge" id="preorderCount">0</span>
                    </button>
                    <button type="button" class="cc-tab-btn stepBtn" id="stepBtn_postorder">
                        <i class="fas fa-heartbeat mr-1"></i> Post-Operation
                        <span class="cc-badge" id="postorderCount">0</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div class="modal fade" id="editMedicineModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content cc-modal">
                    <div class="modal-header cc-modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Medicine</h5>
                        <button type="button" class="close cc-modal-close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-3">
                        <input type="hidden" id="edit_medicine_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">Medicine Name <span class="text-danger">*</span></label>
                                    <div class="med-autocomplete-wrap" id="edit_med_wrap">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="edit_medicine" class="form-control cc-input med-ac-input" placeholder="Type medicine name..." autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary btn-sm med-ac-clear" onclick="clearMedInput('edit_medicine','edit_med_wrap')" tabindex="-1"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="med-ac-dropdown" id="edit_med_dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">Company Name</label>
                                    <div class="med-autocomplete-wrap" id="edit_company_wrap">
                                        <input type="text" id="edit_company" class="form-control cc-input med-ac-input" placeholder="Enter company name..." autocomplete="off">
                                        <div class="med-ac-dropdown" id="edit_company_dropdown"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="cc-label">সকাল (Morning)</label>
                                    <select id="edit_morning" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="cc-label">দুপুর (Noon)</label>
                                    <select id="edit_noon" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="cc-label">রাত (Night)</label>
                                    <select id="edit_night" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">আহারের আগে/পরে</label>
                                    <select id="edit_meal_timing" class="form-control cc-select form-control-sm">
                                        <option value="">-- Select --</option><option value="before">আগে (Before)</option><option value="after">পরে (After)</option><option value="with">সাথে (With)</option><option value="empty">খালি পেটে (Empty Stomach)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">কতদিন?</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="edit_duration_num" class="form-control cc-input" placeholder="0" min="0" style="max-width:70px;">
                                        <select id="edit_duration_type" class="form-control cc-select">
                                            <option value="">--</option><option value="দিন">দিন (Days)</option><option value="মাস">মাস (Month)</option><option value="চলবে">চলবে (Ongoing)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">Route</label>
                                    <select id="edit_route" class="form-control cc-select form-control-sm">
                                        <option value="Oral">Oral (মুখে)</option><option value="IV">IV (শিরায়)</option><option value="IM">IM (মাংসে)</option><option value="SC">SC (চামড়ার নিচে)</option><option value="Topical">Topical</option><option value="Inhalation">Inhalation</option><option value="Tablet">Tablet</option><option value="Gel">Gel</option><option value="Injection">Injection</option><option value="Eye Drop">Eye Drop</option><option value="Ear Drop">Ear Drop</option><option value="Nasal Spray">Nasal Spray</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="cc-label">Special Instructions / Notes</label>
                                    <select id="edit_instruction" class="form-control cc-select form-control-sm">
                                        <option value="">-- None --</option><option value="Before Food">Before Food</option><option value="After Food">After Food</option><option value="Empty Stomach">Empty Stomach</option><option value="With Food">With Food</option><option value="At Bed Time">At Bed Time</option><option value="With Water">With Water</option><option value="With Milk">With Milk</option><option value="As Directed">As Directed</option><option value="Chew Before Swallow">Chew Before Swallow</option><option value="Do Not Crush">Do Not Crush</option><option value="Swallow Whole">Swallow Whole</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2" style="border-top:1px solid #d9d9e2;">
                        <button type="button" class="btn cc-btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cancel</button>
                        <button type="button" class="btn cc-btn-warning btn-sm" id="btnUpdateMedicine"><i class="fas fa-save mr-1"></i>Update Medicine</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- /EDIT MODAL --}}

        {{-- ১. AT ADMISSION --}}
        <div class="row" id="admitSection" style="display:none;">
            <div class="col-lg-5">
                <div class="cc-card cc-card-accent-blue">
                    <div class="cc-card-header cc-header-blue">
                        <i class="fas fa-user-check mr-2"></i>AT Admission — Add Medicine
                    </div>
                    <form id="admitForm">
                        @csrf
                        <div class="cc-card-body">
                            <div class="form-group mb-2">
                                <label class="cc-label">Medicine Name <span class="text-danger">*</span></label>
                                <div class="med-autocomplete-wrap" id="admit_med_wrap">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="admit_medicine" class="form-control cc-input med-ac-input" placeholder="Type or select medicine..." autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm med-ac-clear" onclick="clearMedInput('admit_medicine','admit_med_wrap')" tabindex="-1"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="med-ac-dropdown" id="admit_med_dropdown"></div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="cc-label">Company Name</label>
                                <div class="med-autocomplete-wrap" id="admit_company_wrap">
                                    <input type="text" id="admit_company" class="form-control cc-input med-ac-input" placeholder="Enter company name..." autocomplete="off">
                                    <div class="med-ac-dropdown" id="admit_company_dropdown"></div>
                                </div>
                            </div>
                            <div class="cc-dosage-row">
                                <div>
                                    <label class="cc-label">সকাল</label>
                                    <select id="admit_morning" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="cc-label">দুপুর</label>
                                    <select id="admit_noon" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="cc-label">রাত</label>
                                    <select id="admit_night" class="form-control cc-select form-control-sm">
                                        <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="cc-label">আহারের আগে/পরে</label>
                                        <select id="admit_meal_timing" class="form-control cc-select form-control-sm">
                                            <option value="">-- Select --</option><option value="before">আগে (Before)</option><option value="after">পরে (After)</option><option value="with">সাথে (With)</option><option value="empty">খালি পেটে (Empty Stomach)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="cc-label">কতদিন?</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="admit_duration_num" class="form-control cc-input" placeholder="0" min="0" style="max-width:60px;">
                                            <select id="admit_duration_type" class="form-control cc-select">
                                                <option value="">--</option><option value="দিন">দিন (Days)</option><option value="মাস">মাস (Month)</option><option value="চলবে">চলবে (Ongoing)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="cc-label">Route</label>
                                        <select id="admit_route" class="form-control cc-select form-control-sm">
                                            <option value="Oral">Oral (মুখে)</option><option value="IV">IV (শিরায়)</option><option value="IM">IM (মাংসে)</option><option value="SC">SC (চামড়ার নিচে)</option><option value="Topical">Topical</option><option value="Inhalation">Inhalation</option><option value="Tablet">Tablet</option><option value="Gel">Gel</option><option value="Injection">Injection</option><option value="Eye Drop">Eye Drop</option><option value="Ear Drop">Ear Drop</option><option value="Nasal Spray">Nasal Spray</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="cc-label">Special Instructions</label>
                                        <select id="admit_instruction" class="form-control cc-select form-control-sm">
                                            <option value="">-- None --</option><option value="Before Food">Before Food</option><option value="After Food">After Food</option><option value="Empty Stomach">Empty Stomach</option><option value="With Food">With Food</option><option value="At Bed Time">At Bed Time</option><option value="With Water">With Water</option><option value="With Milk">With Milk</option><option value="As Directed">As Directed</option><option value="Chew Before Swallow">Chew Before Swallow</option><option value="Do Not Crush">Do Not Crush</option><option value="Swallow Whole">Swallow Whole</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cc-card-footer">
                            <button type="button" class="btn cc-btn-primary btn-sm btn-block btnAddMedicine" data-type="admit">
                                <i class="fas fa-plus mr-1"></i> Add Medicine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="cc-card">
                    <div class="cc-card-header cc-header-blue">
                        <i class="fas fa-list mr-2"></i>Added Medicines — AT Admission
                    </div>
                    <div class="cc-card-body p-0">
                        <div class="table-responsive">
                            <table class="table cc-table m-0">
                                <thead><tr><th>#</th><th>Medicine</th><th>সকাল</th><th>দুপুর</th><th>রাত</th><th>আগে/পরে</th><th>কতদিন</th><th>Action</th></tr></thead>
                                <tbody id="admitTableBody"><tr><td colspan="8" class="cc-empty">No medicines added</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ২. PRE-OPERATION --}}
        <div class="row" id="preorderSection" style="display:none;">
            <div class="col-lg-5">
                <div class="cc-card cc-card-accent-amber">
                    <div class="cc-card-header cc-header-amber">
                        <i class="fas fa-syringe mr-2"></i>Pre-Operation — Add Medicine
                    </div>
                    <form id="preorderForm">
                        @csrf
                        <div class="cc-card-body">
                            <div class="form-group mb-2">
                                <label class="cc-label">Medicine Name <span class="text-danger">*</span></label>
                                <div class="med-autocomplete-wrap" id="preorder_med_wrap">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="preorder_medicine" class="form-control cc-input med-ac-input" placeholder="Type or select medicine..." autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm med-ac-clear" onclick="clearMedInput('preorder_medicine','preorder_med_wrap')" tabindex="-1"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="med-ac-dropdown" id="preorder_med_dropdown"></div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="cc-label">Company Name</label>
                                <div class="med-autocomplete-wrap" id="preorder_company_wrap">
                                    <input type="text" id="preorder_company" class="form-control cc-input med-ac-input" placeholder="Enter company name..." autocomplete="off">
                                    <div class="med-ac-dropdown" id="preorder_company_dropdown"></div>
                                </div>
                            </div>
                            <div class="cc-dosage-row">
                                <div><label class="cc-label">সকাল</label><select id="preorder_morning" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                                <div><label class="cc-label">দুপুর</label><select id="preorder_noon" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                                <div><label class="cc-label">রাত</label><select id="preorder_night" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">আহারের আগে/পরে</label><select id="preorder_meal_timing" class="form-control cc-select form-control-sm"><option value="">-- Select --</option><option value="before">আগে (Before)</option><option value="after">পরে (After)</option><option value="with">সাথে (With)</option><option value="empty">খালি পেটে (Empty Stomach)</option></select></div></div>
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">কতদিন?</label><div class="input-group input-group-sm"><input type="number" id="preorder_duration_num" class="form-control cc-input" placeholder="0" min="0" style="max-width:60px;"><select id="preorder_duration_type" class="form-control cc-select"><option value="">--</option><option value="দিন">দিন (Days)</option><option value="মাস">মাস (Month)</option><option value="চলবে">চলবে (Ongoing)</option></select></div></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">Route</label><select id="preorder_route" class="form-control cc-select form-control-sm"><option value="Oral">Oral (মুখে)</option><option value="IV">IV (শিরায়)</option><option value="IM">IM (মাংসে)</option><option value="SC">SC (চামড়ার নিচে)</option><option value="Topical">Topical</option><option value="Inhalation">Inhalation</option><option value="Tablet">Tablet</option><option value="Gel">Gel</option><option value="Injection">Injection</option><option value="Eye Drop">Eye Drop</option><option value="Ear Drop">Ear Drop</option><option value="Nasal Spray">Nasal Spray</option></select></div></div>
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">Special Instructions</label><select id="preorder_instruction" class="form-control cc-select form-control-sm"><option value="">-- None --</option><option value="Before Food">Before Food</option><option value="After Food">After Food</option><option value="Empty Stomach">Empty Stomach</option><option value="With Food">With Food</option><option value="At Bed Time">At Bed Time</option><option value="With Water">With Water</option><option value="With Milk">With Milk</option><option value="As Directed">As Directed</option></select></div></div>
                            </div>
                        </div>
                        <div class="cc-card-footer">
                            <button type="button" class="btn cc-btn-amber btn-sm btn-block btnAddMedicine" data-type="preorder"><i class="fas fa-plus mr-1"></i> Add Medicine</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="cc-card">
                    <div class="cc-card-header cc-header-amber"><i class="fas fa-list mr-2"></i>Added Medicines — Pre-Operation</div>
                    <div class="cc-card-body p-0">
                        <div class="table-responsive">
                            <table class="table cc-table m-0">
                                <thead><tr><th>#</th><th>Medicine</th><th>সকাল</th><th>দুপুর</th><th>রাত</th><th>আগে/পরে</th><th>কতদিন</th><th>Action</th></tr></thead>
                                <tbody id="preorderTableBody"><tr><td colspan="8" class="cc-empty">No medicines added</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ৩. POST-OPERATION --}}
        <div class="row" id="postorderSection" style="display:none;">
            <div class="col-lg-5">
                <div class="cc-card cc-card-accent-green">
                    <div class="cc-card-header cc-header-green"><i class="fas fa-heartbeat mr-2"></i>Post-Operation — Add Medicine</div>
                    <form id="postorderForm">
                        @csrf
                        <div class="cc-card-body">
                            <div class="form-group mb-2">
                                <label class="cc-label">Medicine Name <span class="text-danger">*</span></label>
                                <div class="med-autocomplete-wrap" id="postorder_med_wrap">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="postorder_medicine" class="form-control cc-input med-ac-input" placeholder="Type or select medicine..." autocomplete="off">
                                        <div class="input-group-append"><button type="button" class="btn btn-outline-secondary btn-sm med-ac-clear" onclick="clearMedInput('postorder_medicine','postorder_med_wrap')" tabindex="-1"><i class="fas fa-times"></i></button></div>
                                    </div>
                                    <div class="med-ac-dropdown" id="postorder_med_dropdown"></div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="cc-label">Company Name</label>
                                <div class="med-autocomplete-wrap" id="postorder_company_wrap">
                                    <input type="text" id="postorder_company" class="form-control cc-input med-ac-input" placeholder="Enter company name..." autocomplete="off">
                                    <div class="med-ac-dropdown" id="postorder_company_dropdown"></div>
                                </div>
                            </div>
                            <div class="cc-dosage-row">
                                <div><label class="cc-label">সকাল</label><select id="postorder_morning" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                                <div><label class="cc-label">দুপুর</label><select id="postorder_noon" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                                <div><label class="cc-label">রাত</label><select id="postorder_night" class="form-control cc-select form-control-sm"><option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option></select></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">আহারের আগে/পরে</label><select id="postorder_meal_timing" class="form-control cc-select form-control-sm"><option value="">-- Select --</option><option value="before">আগে (Before)</option><option value="after">পরে (After)</option><option value="with">সাথে (With)</option><option value="empty">খালি পেটে (Empty Stomach)</option></select></div></div>
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">কতদিন?</label><div class="input-group input-group-sm"><input type="number" id="postorder_duration_num" class="form-control cc-input" placeholder="0" min="0" style="max-width:60px;"><select id="postorder_duration_type" class="form-control cc-select"><option value="">--</option><option value="দিন">দিন (Days)</option><option value="মাস">মাস (Month)</option><option value="চলবে">চলবে (Ongoing)</option></select></div></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">Route</label><select id="postorder_route" class="form-control cc-select form-control-sm"><option value="Oral">Oral (মুখে)</option><option value="IV">IV (শিরায়)</option><option value="IM">IM (মাংসে)</option><option value="SC">SC (চামড়ার নিচে)</option><option value="Topical">Topical</option><option value="Inhalation">Inhalation</option><option value="Tablet">Tablet</option><option value="Gel">Gel</option><option value="Injection">Injection</option><option value="Eye Drop">Eye Drop</option><option value="Ear Drop">Ear Drop</option><option value="Nasal Spray">Nasal Spray</option></select></div></div>
                                <div class="col-md-6"><div class="form-group mb-2"><label class="cc-label">Special Instructions</label><select id="postorder_instruction" class="form-control cc-select form-control-sm"><option value="">-- None --</option><option value="Before Food">Before Food</option><option value="After Food">After Food</option><option value="Empty Stomach">Empty Stomach</option><option value="With Food">With Food</option><option value="At Bed Time">At Bed Time</option><option value="With Water">With Water</option><option value="With Milk">With Milk</option><option value="As Directed">As Directed</option></select></div></div>
                            </div>
                        </div>
                        <div class="cc-card-footer">
                            <button type="button" class="btn cc-btn-green btn-sm btn-block btnAddMedicine" data-type="postorder"><i class="fas fa-plus mr-1"></i> Add Medicine</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="cc-card">
                    <div class="cc-card-header cc-header-green"><i class="fas fa-list mr-2"></i>Added Medicines — Post-Operation</div>
                    <div class="cc-card-body p-0">
                        <div class="table-responsive">
                            <table class="table cc-table m-0">
                                <thead><tr><th>#</th><th>Medicine</th><th>সকাল</th><th>দুপুর</th><th>রাত</th><th>আগে/পরে</th><th>কতদিন</th><th>Action</th></tr></thead>
                                <tbody id="postorderTableBody"><tr><td colspan="8" class="cc-empty">No medicines added</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Bottom Navigation --}}
<nav class="cc-bottom-nav">
    <a href="{{ route('billing.payment.index') }}" class="cc-nav-item">
        <i class="fas fa-file-invoice"></i>
        <span>Invoices</span>
    </a>
    <a href="{{ route('billing.paylist.index') }}" class="cc-nav-item">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-user-injured"></i>
        <span>Patients</span>
    </a>
    <a href="{{ route('templates.addmedicine') }}" class="cc-nav-item active">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
    </a>
</nav>
@stop

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --cc-bg: #faf8ff;
    --cc-primary: #0f52ba;
    --cc-primary-dark: #0b3d8a;
    --cc-primary-light: #e8edff;
    --cc-white: #ffffff;
    --cc-border: #d9d9e2;
    --cc-text: #1a1a2e;
    --cc-muted: #6b7280;
    --cc-green: #10b981;
    --cc-green-bg: #ecfdf5;
    --cc-red: #ef4444;
    --cc-red-bg: #fef2f2;
    --cc-amber: #f59e0b;
    --cc-amber-bg: #fffbeb;
    --cc-radius: 4px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

body {
    background: var(--cc-bg);
    font-family: var(--cc-font);
    padding-bottom: 64px;
}

/* ── App Bar ── */
.cc-appbar {
    background: var(--cc-white);
    border-bottom: 1px solid var(--cc-border);
    padding: 12px 24px;
    margin: -8px -15px 0 -15px;
}
.cc-appbar-inner {
    display: flex;
    align-items: center;
    gap: 16px;
    max-width: 1200px;
    margin: 0 auto;
}
.cc-appbar-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--cc-primary);
    letter-spacing: -0.01em;
}
.cc-appbar-brand i { font-size: 1.1rem; }
.cc-appbar-subtitle {
    font-size: 0.82rem;
    color: var(--cc-muted);
    font-weight: 400;
}

/* ── Section Wrapper ── */
.cc-section {
    padding-top: 16px;
}

/* ── Base Card ── */
.cc-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.cc-card-accent-blue  { border-top: 3px solid var(--cc-primary); }
.cc-card-accent-amber { border-top: 3px solid var(--cc-amber); }
.cc-card-accent-green { border-top: 3px solid var(--cc-green); }

.cc-card-header {
    padding: 10px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
}
.cc-header-blue  { background: var(--cc-primary-light); color: var(--cc-primary-dark); }
.cc-header-amber { background: var(--cc-amber-bg); color: #92400e; }
.cc-header-green { background: var(--cc-green-bg); color: #065f46; }

.cc-card-body   { padding: 14px 16px; }
.cc-card-footer { padding: 10px 16px; background: #f8fafc; border-top: 1px solid var(--cc-border); }

/* ── Labels & Inputs ── */
.cc-label {
    display: block;
    font-size: 0.76rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.cc-input, .cc-select {
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    font-size: 0.84rem;
    color: var(--cc-text);
    background: var(--cc-white);
    transition: border-color 0.15s;
}
.cc-input:focus, .cc-select:focus {
    border-color: var(--cc-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(15,82,186,0.12);
}

/* ── Dosage 3-col row ── */
.cc-dosage-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    margin-bottom: 8px;
}

/* ── Pill Tab Bar ── */
.cc-tab-bar {
    display: flex;
    gap: 8px;
}
.cc-tab-btn {
    flex: 1;
    padding: 8px 12px;
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--cc-text);
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.cc-tab-btn:hover { background: var(--cc-primary-light); color: var(--cc-primary); border-color: var(--cc-primary); }
.cc-tab-btn.active { background: var(--cc-primary); color: var(--cc-white); border-color: var(--cc-primary); }
.cc-tab-btn.active_other { background: var(--cc-primary-light); color: var(--cc-primary); border-color: var(--cc-primary); }

.cc-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    background: rgba(255,255,255,0.3);
    border-radius: var(--cc-radius);
    font-size: 0.72rem;
    font-weight: 700;
}
.cc-tab-btn.active .cc-badge { background: rgba(255,255,255,0.25); color: var(--cc-white); }
.cc-tab-btn:not(.active) .cc-badge { background: var(--cc-primary-light); color: var(--cc-primary); }

/* ── Table ── */
.cc-table { font-size: 0.82rem; border-collapse: collapse; width: 100%; }
.cc-table thead tr { background: var(--cc-primary-light); }
.cc-table thead th {
    padding: 8px 10px;
    font-size: 0.74rem;
    font-weight: 700;
    color: var(--cc-primary-dark);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--cc-border);
    white-space: nowrap;
    text-align: center;
}
.cc-table thead th:nth-child(2) { text-align: left; }
.cc-table tbody td {
    padding: 7px 10px;
    border-bottom: 1px solid #f1f5fb;
    color: var(--cc-text);
    vertical-align: middle;
    text-align: center;
}
.cc-table tbody td:nth-child(2) { text-align: left; }
.cc-table tbody tr:last-child td { border-bottom: none; }
.cc-table tbody tr:hover { background: #f8fafc; }
.cc-empty { padding: 20px; text-align: center; color: #9ca3af; font-style: italic; font-size: 0.83rem; }

/* ── Meal badge pills ── */
.cc-meal-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: var(--cc-radius);
    font-size: 0.72rem;
    font-weight: 600;
}
.cc-meal-before { background: var(--cc-amber-bg); color: #92400e; }
.cc-meal-after  { background: var(--cc-primary-light); color: var(--cc-primary-dark); }
.cc-meal-with   { background: #f3f4f6; color: #374151; }
.cc-meal-empty  { background: var(--cc-green-bg); color: #065f46; }

/* ── Action buttons ── */
.cc-btn-action-group { display: flex; gap: 4px; justify-content: center; }
.btn-xs { padding: 3px 8px; font-size: 0.72rem; border-radius: var(--cc-radius); line-height: 1.4; }
.cc-btn-edit   { background: var(--cc-amber-bg); border: 1px solid var(--cc-amber); color: #92400e; }
.cc-btn-edit:hover { background: #fde68a; }
.cc-btn-del    { background: var(--cc-white); border: 1px solid var(--cc-border); color: #9ca3af; }
.cc-btn-del:hover  { background: var(--cc-red-bg); border-color: var(--cc-red); color: var(--cc-red); }

/* ── Buttons ── */
.cc-btn-primary { background: var(--cc-primary); border-color: var(--cc-primary); color: var(--cc-white); }
.cc-btn-primary:hover { background: var(--cc-primary-dark); border-color: var(--cc-primary-dark); color: var(--cc-white); }
.cc-btn-amber   { background: var(--cc-amber); border-color: var(--cc-amber); color: var(--cc-white); }
.cc-btn-amber:hover { background: #d97706; color: var(--cc-white); }
.cc-btn-green   { background: var(--cc-green); border-color: var(--cc-green); color: var(--cc-white); }
.cc-btn-green:hover { background: #059669; color: var(--cc-white); }
.cc-btn-warning { background: var(--cc-amber); border-color: var(--cc-amber); color: var(--cc-white); }
.cc-btn-warning:hover { background: #d97706; color: var(--cc-white); }
.cc-btn-secondary { background: #f9fafb; border-color: var(--cc-border); color: #374151; }
.cc-btn-secondary:hover { background: #f3f4f6; }

/* ── Modal ── */
.cc-modal { border: none; border-radius: var(--cc-radius); overflow: hidden; }
.cc-modal-header { background: var(--cc-primary); color: var(--cc-white); padding: 12px 16px; border-bottom: none; border-radius: var(--cc-radius) var(--cc-radius) 0 0; }
.cc-modal-header .modal-title { font-size:0.92rem; font-weight:600; }
.cc-modal-close { color: var(--cc-white); opacity:0.85; font-size:1.3rem; }
.cc-modal-close:hover { opacity:1; color: var(--cc-white); }

/* ── Alerts ── */
.alert { border-radius: var(--cc-radius); font-size: 0.86rem; padding: 10px 16px; border: none; }
.cc-alert-success { background: var(--cc-green-bg); color: #065f46; border-left: 4px solid var(--cc-green); }
.cc-alert-danger  { background: var(--cc-red-bg); color: #991b1b; border-left: 4px solid var(--cc-red); }

/* ── Autocomplete ── */
.med-autocomplete-wrap { position: relative; }
.med-ac-dropdown {
    display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
    background: var(--cc-white); border: 1px solid var(--cc-border); border-top: none;
    border-radius: 0 0 var(--cc-radius) var(--cc-radius);
    max-height: 210px; overflow-y: auto;
}
.med-ac-dropdown.show { display: block; }
.med-ac-item {
    padding: 7px 12px; font-size: 0.82rem; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid #f1f5f9; transition: background 0.1s;
}
.med-ac-item:last-child { border-bottom: none; }
.med-ac-item:hover, .med-ac-item.focused { background: var(--cc-primary-light); color: var(--cc-primary); }
.med-ac-cat { font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: var(--cc-radius); text-transform: uppercase; flex-shrink: 0; }
.cat-db      { background: var(--cc-primary-light); color: var(--cc-primary-dark); }
.cat-company { background: #fce7f3; color: #9d174d; }
.med-ac-item mark { background: #fef08a; color: inherit; padding: 0; font-weight: 700; }
.med-ac-no-result, .med-ac-loading { padding: 10px 14px; font-size: 0.8rem; color: #9ca3af; text-align: center; }
.med-ac-clear { border-left: 0 !important; color: #9ca3af; padding: 0 8px !important; }
.med-ac-clear:hover { color: var(--cc-red); background: var(--cc-red-bg) !important; }

/* ── Bottom Navigation ── */
.cc-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 56px;
    background: var(--cc-white);
    border-top: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 1050;
}
.cc-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 6px 12px;
    color: var(--cc-muted);
    text-decoration: none;
    font-size: 0.65rem;
    font-weight: 600;
    transition: color 0.15s;
    border-radius: var(--cc-radius);
}
.cc-nav-item i { font-size: 1.15rem; }
.cc-nav-item:hover { color: var(--cc-primary); text-decoration: none; }
.cc-nav-item.active { color: var(--cc-primary); }

/* ── Select2 overrides ── */
.select2-container--default .select2-selection--single {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    height: 35px !important;
    font-size: 0.84rem;
    font-family: var(--cc-font);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 33px !important;
    padding-left: 10px;
    color: var(--cc-text);
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 33px !important;
}
.select2-container--default .select2-results__option--highlighted {
    background: var(--cc-primary) !important;
}
.select2-dropdown {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    font-family: var(--cc-font);
    font-size: 0.84rem;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/* ════════════════════════════════════════════════════════════
   MEDICINE LIST — loaded from common_medicine table via Ajax
════════════════════════════════════════════════════════════ */
var MEDICINE_LIST = [];
var MEDICINE_LIST_LOADED = false;
var COMPANY_LIST = [
    'Square','Beximco','Incepta','Opsonin','ACI','Eskayef','Renata',
    'General Pharma','Drug International','Novo Nordisk','Sanofi',
    'Aristopharma','Acme','Healthcare Pharma','Orion Pharma',
];

function loadCommonMedicines() {
    $.get("{{ route('common.medicine.list') }}", function(res) {
        if (res && res.medicines) {
            MEDICINE_LIST = res.medicines.map(function(m) {
                return { id: m.id, name: m.name, group: m.GroupName || '', strength: m.strength || '' };
            });
        }
        MEDICINE_LIST_LOADED = true;
    }).fail(function() {
        MEDICINE_LIST_LOADED = true;
        console.warn('Could not load common medicines from server.');
    });
}

/* ════════════════════════════════════════════════════════════
   CUSTOM AUTOCOMPLETE ENGINE
════════════════════════════════════════════════════════════ */
function highlightMatch(text, query) {
    if (!query) return escHtml(text);
    var re = new RegExp('(' + escRe(query) + ')', 'gi');
    return escHtml(text).replace(re, '<mark>$1</mark>');
}
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escRe(s)   { return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); }

function showMedDropdown(inputId, dropId, query) {
    var drop = document.getElementById(dropId);
    if (!drop) return;
    if (!MEDICINE_LIST_LOADED) {
        drop.innerHTML = '<div class="med-ac-loading"><i class="fas fa-spinner fa-spin mr-1"></i>Loading medicines...</div>';
        drop.classList.add('show'); return;
    }
    query = (query || '').trim();
    var lower = query.toLowerCase();
    var results = query.length === 0
        ? MEDICINE_LIST.slice(0, 30)
        : MEDICINE_LIST.filter(function(m) {
            return m.name.toLowerCase().indexOf(lower) !== -1 || (m.group && m.group.toLowerCase().indexOf(lower) !== -1);
          });
    if (results.length === 0) {
        drop.innerHTML = '<div class="med-ac-no-result"><i class="fas fa-search mr-1"></i>No match — you can still type freely</div>';
        drop.classList.add('show'); return;
    }
    drop.innerHTML = results.map(function(m, idx) {
        var groupBadge = m.group ? '<span class="med-ac-cat cat-db">' + escHtml(m.group) + '</span>' : '<span class="med-ac-cat cat-db">Medicine</span>';
        return '<div class="med-ac-item" data-val="' + escHtml(m.name) + '" data-idx="' + idx + '">' + groupBadge +
            '<span>' + highlightMatch(m.name, query) + (m.strength ? ' <small style="color:#9ca3af;">(' + escHtml(m.strength) + ')</small>' : '') + '</span></div>';
    }).join('');
    drop.querySelectorAll('.med-ac-item').forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById(inputId).value = this.dataset.val;
            hideDrop(dropId);
        });
    });
    drop.classList.add('show');
}

function showCompanyDropdown(inputId, dropId, query) {
    var drop = document.getElementById(dropId);
    if (!drop) return;
    query = (query || '').trim();
    var lower = query.toLowerCase();
    var results = query.length === 0 ? COMPANY_LIST : COMPANY_LIST.filter(function(c) { return c.toLowerCase().indexOf(lower) !== -1; });
    if (results.length === 0) {
        drop.innerHTML = '<div class="med-ac-no-result">No match — type freely</div>';
        drop.classList.add('show'); return;
    }
    drop.innerHTML = results.map(function(c) {
        return '<div class="med-ac-item" data-val="' + escHtml(c) + '"><span class="med-ac-cat cat-company">Co.</span><span>' + highlightMatch(c, query) + '</span></div>';
    }).join('');
    drop.querySelectorAll('.med-ac-item').forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById(inputId).value = this.dataset.val;
            hideDrop(dropId);
        });
    });
    drop.classList.add('show');
}

function hideDrop(dropId)  { var d = document.getElementById(dropId); if (d) d.classList.remove('show'); }
function hideAllDrops()    { document.querySelectorAll('.med-ac-dropdown').forEach(function(d) { d.classList.remove('show'); }); }

function initMedAC(inputId, dropId) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('focus',  function() { showMedDropdown(inputId, dropId, this.value); });
    input.addEventListener('input',  function() { showMedDropdown(inputId, dropId, this.value); });
    input.addEventListener('blur',   function() { setTimeout(function() { hideDrop(dropId); }, 180); });
    input.addEventListener('keydown', function(e) {
        var drop = document.getElementById(dropId);
        if (!drop || !drop.classList.contains('show')) return;
        var items = drop.querySelectorAll('.med-ac-item');
        var focused = drop.querySelector('.med-ac-item.focused');
        var idx = -1;
        items.forEach(function(it, i) { if (it === focused) idx = i; });
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (idx < items.length - 1) { if (focused) focused.classList.remove('focused'); items[idx+1].classList.add('focused'); items[idx+1].scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (idx > 0) { if (focused) focused.classList.remove('focused'); items[idx-1].classList.add('focused'); items[idx-1].scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'Enter') {
            if (focused) { e.preventDefault(); input.value = focused.dataset.val; hideDrop(dropId); }
        } else if (e.key === 'Escape') { hideDrop(dropId); }
    });
}

function initCompanyAC(inputId, dropId) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('focus', function() { showCompanyDropdown(inputId, dropId, this.value); });
    input.addEventListener('input', function() { showCompanyDropdown(inputId, dropId, this.value); });
    input.addEventListener('blur',  function() { setTimeout(function() { hideDrop(dropId); }, 180); });
}

window.clearMedInput = function(inputId) {
    var inp = document.getElementById(inputId);
    if (inp) { inp.value = ''; inp.focus(); }
};

document.addEventListener('click', function(e) { if (!e.target.closest('.med-autocomplete-wrap')) hideAllDrops(); });

/* ════════════════════════════════════════════════════════════
   MAIN APP LOGIC (all logic unchanged)
════════════════════════════════════════════════════════════ */
if (typeof jQuery !== 'undefined') {
    jQuery(function($) {

        loadCommonMedicines();

        let currentStep = 'admit';
        let medicines = { admit: [], preorder: [], postorder: [] };

        $('#templateid').select2({ width: '100%' });

        initMedAC('admit_medicine',     'admit_med_dropdown');
        initMedAC('preorder_medicine',  'preorder_med_dropdown');
        initMedAC('postorder_medicine', 'postorder_med_dropdown');
        initMedAC('edit_medicine',      'edit_med_dropdown');

        initCompanyAC('admit_company',     'admit_company_dropdown');
        initCompanyAC('preorder_company',  'preorder_company_dropdown');
        initCompanyAC('postorder_company', 'postorder_company_dropdown');
        initCompanyAC('edit_company',      'edit_company_dropdown');

        $('#templateid').on('change', function() {
            let tid = $(this).val();
            if (tid) {
                currentStep = 'admit';
                medicines = { admit: [], preorder: [], postorder: [] };
                $('#stepNavigation').show();
                loadAllMedicines();
                showStep('admit');
            } else { hideAllSteps(); }
        });

        $('#stepBtn_admit').on('click',     function() { currentStep = 'admit';     showStep('admit');     });
        $('#stepBtn_preorder').on('click',  function() { currentStep = 'preorder';  showStep('preorder');  });
        $('#stepBtn_postorder').on('click', function() { currentStep = 'postorder'; showStep('postorder'); });

        $('.btnAddMedicine').on('click',  function() { addMedicine($(this).data('type')); });
        $('#btnUpdateMedicine').on('click', function() { updateMedicine(); });

        function showStep(step) {
            $('#admitSection, #preorderSection, #postorderSection').hide();
            $('.stepBtn').removeClass('active');
            if      (step === 'admit')     { $('#admitSection').show();     $('#stepBtn_admit').addClass('active');     }
            else if (step === 'preorder')  { $('#preorderSection').show();  $('#stepBtn_preorder').addClass('active');  }
            else if (step === 'postorder') { $('#postorderSection').show(); $('#stepBtn_postorder').addClass('active'); }
            updateButtonStates();
        }

        function updateButtonStates() {
            $('.stepBtn').removeClass('active_other');
            if (medicines.admit.length > 0    && currentStep !== 'admit')     $('#stepBtn_admit').addClass('active_other');
            if (medicines.preorder.length > 0  && currentStep !== 'preorder')  $('#stepBtn_preorder').addClass('active_other');
            if (medicines.postorder.length > 0 && currentStep !== 'postorder') $('#stepBtn_postorder').addClass('active_other');
        }

        function hideAllSteps() {
            $('#admitSection, #preorderSection, #postorderSection').hide();
            $('#stepNavigation').hide();
        }

        function buildDosage(type) {
            let m  = $('#' + type + '_morning').val() || '-';
            let n  = $('#' + type + '_noon').val()    || '-';
            let ni = $('#' + type + '_night').val()   || '-';
            return m + '+' + n + '+' + ni;
        }

        function buildDuration(type) {
            let num   = $('#' + type + '_duration_num').val();
            let dtype = $('#' + type + '_duration_type').val();
            if (dtype === 'চলবে') return 'চলবে';
            if (num && dtype) return num + ' ' + dtype;
            if (dtype) return dtype;
            return '';
        }

        function addMedicine(type) {
            let tid      = $('#templateid').val();
            let medicine = $('#' + type + '_medicine').val();
            if (!tid)                          { showAlert('Please select a template!', 'error'); return; }
            if (!medicine || !medicine.trim()) { showAlert('Please enter medicine name!', 'error'); return; }

            let dosage      = buildDosage(type);
            let duration    = buildDuration(type);
            let meal_timing = $('#' + type + '_meal_timing').val();
            let instruction = $('#' + type + '_instruction').val();
            let route       = $('#' + type + '_route').val();
            let company     = $('#' + type + '_company').val();
            let morning     = $('#' + type + '_morning').val();
            let noon        = $('#' + type + '_noon').val();
            let night       = $('#' + type + '_night').val();
            let order_type  = type;

            let btn = $('.btnAddMedicine[data-type="' + type + '"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('templates.medicine.ajax.add') }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", templateid: tid, name: medicine, dosage, morning, noon, night, meal_timing, duration, instruction, route, order_type, company },
                success: function(res) {
                    if (res.ok) { showAlert('Medicine added successfully.', 'success'); clearForm(type); loadAllMedicines(); }
                    else { showAlert('Error: ' + res.message, 'error'); }
                },
                error: function() { showAlert('Server error!', 'error'); },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i> Add Medicine'); }
            });
        }

        function clearForm(type) {
            $('#' + type + '_medicine').val('');
            $('#' + type + '_company').val('');
            $('#' + type + '_morning').val('');
            $('#' + type + '_noon').val('');
            $('#' + type + '_night').val('');
            $('#' + type + '_meal_timing').val('');
            $('#' + type + '_duration_num').val('');
            $('#' + type + '_duration_type').val('');
            $('#' + type + '_instruction').val('');
            $('#' + type + '_route').val('Oral');
            $('#' + type + '_medicine').focus();
        }

        window.editMedicine = function(id) {
            let row = null;
            ['admit','preorder','postorder'].forEach(function(t) {
                let found = medicines[t].find(function(m) { return m.id == id; });
                if (found) row = found;
            });
            if (!row) { showAlert('Medicine data not found!', 'error'); return; }

            $('#edit_medicine_id').val(row.id);
            $('#edit_medicine').val(row.name   || '');
            $('#edit_company').val(row.company  || '');
            $('#edit_route').val(row.route      || 'Oral');
            $('#edit_meal_timing').val(row.meal_timing || '');
            $('#edit_instruction').val(row.instruction || '');

            let morning = row.morning || (row.dose ? (row.dose.split('+')[0] || '') : '');
            let noon    = row.noon    || (row.dose ? (row.dose.split('+')[1] || '') : '');
            let night   = row.night   || (row.dose ? (row.dose.split('+')[2] || '') : '');
            $('#edit_morning').val(morning);
            $('#edit_noon').val(noon);
            $('#edit_night').val(night);

            let dur = row.duration || '';
            if (dur === 'চলবে' || dur.toLowerCase().includes('চলবে')) {
                $('#edit_duration_num').val(''); $('#edit_duration_type').val('চলবে');
            } else {
                let parts = dur.split(' ');
                if (parts.length >= 2) { $('#edit_duration_num').val(parts[0]); $('#edit_duration_type').val(parts[1]); }
                else { $('#edit_duration_num').val(''); $('#edit_duration_type').val(''); }
            }
            $('#editMedicineModal').modal('show');
        };

        function updateMedicine() {
            let id = $('#edit_medicine_id').val();
            if (!id) return;

            let name        = $('#edit_medicine').val().trim();
            let company     = $('#edit_company').val().trim();
            let morning     = $('#edit_morning').val();
            let noon        = $('#edit_noon').val();
            let night       = $('#edit_night').val();
            let dosage      = (morning||'-') + '+' + (noon||'-') + '+' + (night||'-');
            let meal_timing = $('#edit_meal_timing').val();
            let instruction = $('#edit_instruction').val();
            let route       = $('#edit_route').val();

            let order_type = '';
            ['admit','preorder','postorder'].forEach(function(t) {
                let found = medicines[t].find(function(m) { return m.id == id; });
                if (found) order_type = found.order_type || t;
            });

            let dnum  = $('#edit_duration_num').val();
            let dtype = $('#edit_duration_type').val();
            let duration = '';
            if      (dtype === 'চলবে') duration = 'চলবে';
            else if (dnum && dtype)    duration = dnum + ' ' + dtype;
            else if (dtype)            duration = dtype;

            if (!name) { showAlert('Medicine name is required!', 'error'); return; }

            let btn = $('#btnUpdateMedicine');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

            $.ajax({
                url: `/templates/medicine/ajax/${id}`,
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'PUT', name, company, dosage, morning, noon, night, meal_timing, duration, instruction, route, order_type },
                success: function(res) {
                    if (res.ok || res.success) {
                        showAlert('Medicine updated successfully.', 'success');
                        $('#editMedicineModal').modal('hide');
                        loadAllMedicines();
                    } else { showAlert('Update failed: ' + (res.message || 'Unknown error'), 'error'); }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server error (HTTP ' + xhr.status + ')';
                    showAlert('Error: ' + msg, 'error');
                },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Medicine'); }
            });
        }

        function loadAllMedicines() {
            let tid = $('#templateid').val();
            if (!tid) return;
            $.get("{{ route('templates.medicine.ajax.list') }}", { templateid: tid }, function(res) {
                medicines = { admit: [], preorder: [], postorder: [] };
                let admitHtml = '', preorderHtml = '', postorderHtml = '';
                let cntA = 1, cntPre = 1, cntPost = 1;

                if (res.rows && res.rows.length > 0) {
                    res.rows.forEach(function(row) {
                        let type = row.order_type || 'admit';
                        if (!medicines[type]) medicines[type] = [];
                        medicines[type].push(row);

                        let morning = row.morning || (row.dose ? row.dose.split('+')[0] : '-');
                        let noon    = row.noon    || (row.dose ? row.dose.split('+')[1] : '-');
                        let night   = row.night   || (row.dose ? row.dose.split('+')[2] : '-');
                        let meal    = row.meal_timing || '';
                        let dur     = row.duration || '';
                        let cnt     = type === 'admit' ? cntA++ : type === 'preorder' ? cntPre++ : cntPost++;

                        let mealLabel = '';
                        if      (meal === 'before') mealLabel = '<span class="cc-meal-pill cc-meal-before">আগে</span>';
                        else if (meal === 'after')  mealLabel = '<span class="cc-meal-pill cc-meal-after">পরে</span>';
                        else if (meal === 'with')   mealLabel = '<span class="cc-meal-pill cc-meal-with">সাথে</span>';
                        else if (meal === 'empty')  mealLabel = '<span class="cc-meal-pill cc-meal-empty">খালি</span>';
                        else                        mealLabel = '<span style="color:#9ca3af;">—</span>';

                        let rowHtml = `<tr>
                            <td style="font-weight:600;color:#6b7280;">${cnt}</td>
                            <td>
                                <span style="font-weight:600;color:#1f2937;">${row.name}</span>
                                ${row.company ? '<br><small style="color:#9ca3af;font-size:0.72rem;">' + row.company + '</small>' : ''}
                            </td>
                            <td>${morning || '—'}</td>
                            <td>${noon    || '—'}</td>
                            <td>${night   || '—'}</td>
                            <td>${mealLabel}</td>
                            <td style="font-size:0.8rem;color:#374151;">${dur || '—'}</td>
                            <td>
                                <div class="cc-btn-action-group">
                                    <button onclick="editMedicine(${row.id})" class="btn btn-xs cc-btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button onclick="deleteMedicine(${row.id})" class="btn btn-xs cc-btn-del" title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;

                        if      (type === 'admit')    admitHtml     += rowHtml;
                        else if (type === 'preorder') preorderHtml  += rowHtml;
                        else                          postorderHtml += rowHtml;
                    });
                }

                let empty = '<tr><td colspan="8" class="cc-empty">No medicines added</td></tr>';
                $('#admitTableBody').html(admitHtml     || empty);
                $('#preorderTableBody').html(preorderHtml  || empty);
                $('#postorderTableBody').html(postorderHtml || empty);

                $('#admitCount').text(medicines.admit.length);
                $('#preorderCount').text(medicines.preorder.length);
                $('#postorderCount').text(medicines.postorder.length);
                updateButtonStates();
            });
        }

        function showAlert(message, type) {
            let cls  = type === 'success' ? 'cc-alert-success' : 'cc-alert-danger';
            let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            $('#alertMessage').html(`
                <div class="alert ${cls} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} mr-2"></i>${message}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            `);
            setTimeout(function() { $('.alert').fadeOut('slow', function() { $(this).remove(); }); }, 3000);
        }

        window.deleteMedicine = function(id) {
            if (confirm('Are you sure you want to delete this medicine?')) {
                $.ajax({
                    url: `/templates/medicine/ajax/${id}`,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.ok || res.success) { showAlert('Medicine deleted.', 'success'); loadAllMedicines(); }
                    },
                    error: function() { showAlert('Delete failed!', 'error'); }
                });
            }
        };

    }); // end jQuery ready
}
</script>
@stop