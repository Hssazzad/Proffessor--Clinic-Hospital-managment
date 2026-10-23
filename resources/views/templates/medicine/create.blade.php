@extends('adminlte::page')

@section('title', 'Template Medicine')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h4 class="mb-0"><i class="fas fa-pills mr-2"></i><b>Template Medicine</b></h4>
        <small class="text-muted">Add and manage template medicines</small>
    </div>
@stop

@section('content')
{{-- Tailwind CDN --}}
<script src="https://cdn.tailwindcss.com"></script>

<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- CARD 1 — ADD NEW MEDICINE --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title font-weight-bold text-primary mb-0">
                <i class="fas fa-plus-square mr-2"></i>Add New Medicine
            </h3>
            <button type="button" id="btnQuickAddOpen"
                    class="inline-flex items-center gap-1 h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-bolt"></i> + Quick Add
            </button>
        </div>

        <form id="medicineForm" method="POST" action="#" autocomplete="off">
            @csrf
            <div class="card-body">
                <div class="row">

                    {{-- Template --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="templateid">Template <span class="text-danger">*</span></label>
                            <select name="templateid" id="templateid"
                                    class="form-control select2bs4"
                                    data-placeholder="-- Select Template --">
                                <option value=""></option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->templateid }}">{{ $template->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Stage (Order Type) --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="order_type">Stage <span class="text-danger">*</span></label>
                            <select name="order_type" id="order_type"
                                    class="form-control select2bs4"
                                    data-placeholder="-- Select Stage --">
                                <option value=""></option>
                                @foreach($stages as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Medicine Type --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="medicine_type_id">Medicine Type</label>
                            <select name="medicine_type_id" id="medicine_type_id"
                                    class="form-control select2bs4"
                                    data-placeholder="-- Select Medicine Type --">
                                <option value=""></option>
                                @foreach($medicineTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Filters the medicine list (optional)</small>
                        </div>
                    </div>

                    {{-- Sub Medicine Type --}}
                    <div class="col-md-6 col-lg-4" id="subTypeWrapper" style="display:none;">
                        <div class="form-group">
                            <label for="medicine_subtype_id">
                                Sub Medicine Type <small class="text-muted font-weight-normal">(optional)</small>
                            </label>
                            <div class="input-group">
                                <select name="medicine_subtype_id" id="medicine_subtype_id"
                                        class="form-control select2bs4"
                                        data-placeholder="-- Select Sub Type --">
                                    <option value=""></option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary btn-open-subtype-modal"
                                            data-target-select="#medicine_subtype_id"
                                            data-type-select="#medicine_type_id"
                                            title="Add New Sub Medicine Type">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="subTypeEmptyHint" class="form-text text-muted" style="display:none;">
                                No sub types exist for this medicine type yet — click <b>+</b> to add one.
                            </small>
                        </div>
                    </div>

                    {{-- Medicine Name (primary search — type to find, no need to pick Company first) --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="medicine_id">Medicine Name <span class="text-danger">*</span></label>
                            <select name="medicine_id" id="medicine_id"
                                    class="form-control"
                                    data-placeholder="Type to search medicine name...">
                                <option value=""></option>
                            </select>
                            <small class="form-text text-muted">Start typing — Company/Type below auto-fill once you pick one.</small>
                        </div>
                    </div>

                    {{-- Company (auto-fills after picking a Medicine; can also be used as a filter) --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="company_id">Company <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id"
                                    class="form-control select2bs4"
                                    data-placeholder="-- Select or auto-filled --">
                                <option value=""></option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Strength (optional) --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="strength">Strength <small class="text-muted">(optional)</small></label>
                            <input type="text" name="strength" id="strength"
                                   class="form-control"
                                   placeholder="e.g. 500 mg" maxlength="50">
                        </div>
                    </div>

                    {{-- Dosage Form --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="dosage_form_id">Dosage Form</label>
                            <select name="dosage_form_id" id="dosage_form_id"
                                    class="form-control select2bs4"
                                    data-placeholder="-- Select Dosage Form (optional filter) --">
                                <option value=""></option>
                                @foreach($dosageForms as $form)
                                    <option value="{{ $form->id }}">{{ $form->dosage_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Generic Name --}}
                    @if($hasGenericName)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="generic_name">Generic Name</label>
                                <input type="text" name="generic_name" id="generic_name"
                                       class="form-control" placeholder="e.g. Paracetamol" maxlength="255" readonly>
                            </div>
                        </div>
                    @endif

                    {{-- Status --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="form-text text-muted">Status of this template entry</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                        <small class="text-muted font-weight-bold">Template Dosing Information</small>
                    </div>

                    {{-- Morning / Noon / Night --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="morning">সকাল</label>
                            <select name="morning" id="morning" class="form-control form-control-sm">
                                <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="noon">দুপুর</label>
                            <select name="noon" id="noon" class="form-control form-control-sm">
                                <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="night">রাত</label>
                            <select name="night" id="night" class="form-control form-control-sm">
                                <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                            </select>
                        </div>
                    </div>

                    {{-- Meal Timing --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="meal_timing">আহারের আগে/পরে</label>
                            <select name="meal_timing" id="meal_timing" class="form-control form-control-sm">
                                <option value="">-- Select --</option>
                                <option value="before">আগে (Before)</option>
                                <option value="after">পরে (After)</option>
                                <option value="with">সাথে (With)</option>
                                <option value="empty">খালি পেটে (Empty Stomach)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="duration_num">কতদিন?</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="duration_num" id="duration_num"
                                       class="form-control" placeholder="0" min="0">
                                <select name="duration_type" id="duration_type" class="form-control">
                                    <option value="">--</option>
                                    <option value="দিন">দিন (Days)</option>
                                    <option value="মাস">মাস (Month)</option>
                                    <option value="চলবে">চলবে (Ongoing)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Route --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="route">Route</label>
                            <select name="route" id="route" class="form-control form-control-sm">
                                <option value="Oral">Oral (মুখে)</option>
                                <option value="IV">IV (শিরায়)</option>
                                <option value="IM">IM (মাংসে)</option>
                                <option value="SC">SC (চামড়ার নিচে)</option>
                                <option value="Topical">Topical</option>
                                <option value="Inhalation">Inhalation</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Gel">Gel</option>
                                <option value="Injection">Injection</option>
                                <option value="Eye Drop">Eye Drop</option>
                                <option value="Ear Drop">Ear Drop</option>
                                <option value="Nasal Spray">Nasal Spray</option>
                            </select>
                        </div>
                    </div>

                    {{-- Special Instructions --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="instruction">Special Instructions</label>
                            <input type="text" name="instruction" id="instruction" class="form-control form-control-sm"
                                   placeholder="e.g. Before Food" maxlength="255">
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer bg-white text-right">
                <button type="reset" class="btn btn-outline-secondary mr-2" id="btnResetForm">
                    <i class="fas fa-undo mr-1"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary px-4" id="btnSaveMedicine">
                    <i class="fas fa-save mr-1"></i>Add to Template
                </button>
            </div>
        </form>
    </div>

    {{-- CARD 2 — MEDICINE LIST --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-list mr-2"></i>Medicine List
            </h3>
            <div class="card-tools">
                <span class="badge badge-info" id="listCountBadge">0 total</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label for="listCommonlyUsed" class="small font-weight-bold text-muted">Commonly Used</label>
                        <select name="listCommonlyUsed" id="listCommonlyUsed" class="form-control form-control-sm">
                            <option value="all">All</option>
                            <option value="yes">Commonly Used</option>
                            <option value="no">Normal Medicines</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-hover table-striped table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Medicine Name</th>
                        <th>Strength</th>
                        <th>Type</th>
                        <th>Sub Type</th>
                        <th>Company</th>
                        <th>Dosage Form</th>
                        @if($hasGenericName)
                            <th>Generic Name</th>
                        @endif
                        <th style="width:90px;">Status</th>
                        <th style="width:130px;">Commonly Used</th>
                        <th style="width:110px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="medicineListBody">
                    <tr>
                        <td colspan="{{ $hasGenericName ? 11 : 10 }}" class="text-center text-muted py-4">
                            <i class="fas fa-capsules fa-2x d-block mb-2"></i>
                            Select a template and a stage above to load its medicines.
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

{{-- QUICK ADD MODAL --}}
<div id="quickAddModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4">
        <div class="px-5 py-4 border-b flex justify-between items-center">
            <h5 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-bolt text-emerald-600"></i> Quick Add Medicine
            </h5>
            <button type="button" id="qaCloseBtn" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-4">
            <div id="quickAddAlert" class="hidden px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm"></div>
            <div id="quickAddContext" class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600">
                Adding under: <span id="qaContextText" class="font-semibold text-slate-800">--</span>
            </div>
            <div>
                <label for="qa_medicine_name" class="block text-xs font-semibold text-slate-600 mb-1">
                    Medicine Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="qa_medicine_name"
                       class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="e.g. Amoxicillin" maxlength="255" autocomplete="off">
            </div>
            <div>
                <label for="qa_dosage_form_id" class="block text-xs font-semibold text-slate-600 mb-1">
                    Dosage Form <span class="text-red-500">*</span>
                </label>
                <select id="qa_dosage_form_id"
                        class="w-full h-10 px-3 border border-slate-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Select Dosage Form --</option>
                    @foreach($dosageForms as $form)
                        <option value="{{ $form->id }}">{{ $form->dosage_name }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-slate-400">Strength optional. Same database insert as full form.</p>
        </div>
        <div class="px-5 py-3 border-t flex justify-end gap-2">
            <button type="button" id="qaCancelBtn"
                    class="h-9 px-4 border border-slate-300 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" id="btnQuickAddSave"
                    class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold">
                Save &amp; Use
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="qaToast" class="fixed bottom-6 right-6 z-50 min-w-[260px] px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-lg translate-y-32 opacity-0 transition-all duration-300"></div>

{{-- SUB TYPE MODAL --}}
<div class="modal fade" id="subTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Add Sub Medicine Type</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="subTypeModalAlert" class="alert alert-danger py-2" style="display:none;"></div>
                <div class="form-group">
                    <label>Medicine Type</label>
                    <input type="text" id="modalMedicineTypeName" class="form-control bg-light" readonly>
                    <input type="hidden" id="modalMedicineTypeId">
                </div>
                <div class="form-group">
                    <label for="modalSubtypeName">Sub Medicine Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="modalSubtypeName" class="form-control" placeholder="e.g. NSAID" maxlength="150">
                </div>
                <div class="form-group mb-0">
                    <label for="modalSubtypeStatus">Status</label>
                    <select id="modalSubtypeStatus" class="form-control">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSubType">
                    <i class="fas fa-save mr-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MEDICINE MODAL --}}
<div class="modal fade" id="editMedicineModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="editMedicineForm" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header bg-info">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_row_id">
                    <input type="hidden" id="edit_order_type">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_type_id">Medicine Type</label>
                                <select name="medicine_type_id" id="edit_medicine_type_id"
                                        class="form-control select2edit" data-placeholder="-- Select Medicine Type --" disabled>
                                    <option value=""></option>
                                    @foreach($medicineTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_subtype_id">
                                    Sub Medicine Type <small class="text-muted font-weight-normal">(optional)</small>
                                </label>
                                <select name="medicine_subtype_id" id="edit_medicine_subtype_id"
                                        class="form-control select2edit" data-placeholder="-- Select Sub Type --" disabled>
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_company_id">Company <span class="text-danger">*</span></label>
                                <select name="company_id" id="edit_company_id"
                                        class="form-control select2edit" data-placeholder="-- Select Company --">
                                    <option value=""></option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_id">Medicine Name <span class="text-danger">*</span></label>
                                <select name="medicine_id" id="edit_medicine_id"
                                        class="form-control select2edit" data-placeholder="-- Select Company First --" disabled>
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_strength">Strength <small class="text-muted">(optional)</small></label>
                                <input type="text" name="strength" id="edit_strength" class="form-control" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_dosage_form_id">Dosage Form</label>
                                <select name="dosage_form_id" id="edit_dosage_form_id"
                                        class="form-control select2edit" data-placeholder="-- Select Dosage Form --" disabled>
                                    <option value=""></option>
                                    @foreach($dosageForms as $form)
                                        <option value="{{ $form->id }}">{{ $form->dosage_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if($hasGenericName)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_generic_name">Generic Name</label>
                                    <input type="text" name="generic_name" id="edit_generic_name" class="form-control" maxlength="255" readonly>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>⭐ Commonly Used Medicine</label>
                                <div class="d-flex align-items-center mt-1">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="commonly_used"
                                               id="edit_commonly_used" value="1" role="switch" disabled>
                                        <label class="form-check-label text-muted small" for="edit_commonly_used" id="edit_commonly_used_label">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr class="my-2">
                            <small class="text-muted font-weight-bold">Template Dosing Information</small>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_morning">সকাল (Morning)</label>
                                <select name="morning" id="edit_morning" class="form-control form-control-sm">
                                    <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_noon">দুপুর (Noon)</label>
                                <select name="noon" id="edit_noon" class="form-control form-control-sm">
                                    <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_night">রাত (Night)</label>
                                <select name="night" id="edit_night" class="form-control form-control-sm">
                                    <option value="">-</option><option value="0">0</option><option value="1/2">½</option><option value="1">1</option><option value="1+1/2">1½</option><option value="2">2</option><option value="26u">26u</option><option value="30u">30u</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_meal_timing">আহারের আগে/পরে</label>
                                <select name="meal_timing" id="edit_meal_timing" class="form-control form-control-sm">
                                    <option value="">-- Select --</option>
                                    <option value="before">আগে (Before)</option>
                                    <option value="after">পরে (After)</option>
                                    <option value="with">সাথে (With)</option>
                                    <option value="empty">খালি পেটে (Empty Stomach)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_duration_num">কতদিন?</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="duration_num" id="edit_duration_num" class="form-control" placeholder="0" min="0">
                                    <select name="duration_type" id="edit_duration_type" class="form-control">
                                        <option value="">--</option>
                                        <option value="দিন">দিন (Days)</option>
                                        <option value="মাস">মাস (Month)</option>
                                        <option value="চলবে">চলবে (Ongoing)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_route">Route</label>
                                <select name="route" id="edit_route" class="form-control form-control-sm">
                                    <option value="Oral">Oral (মুখে)</option>
                                    <option value="IV">IV (শিরায়)</option>
                                    <option value="IM">IM (মাংসে)</option>
                                    <option value="SC">SC (চামড়ার নিচে)</option>
                                    <option value="Topical">Topical</option>
                                    <option value="Inhalation">Inhalation</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Gel">Gel</option>
                                    <option value="Injection">Injection</option>
                                    <option value="Eye Drop">Eye Drop</option>
                                    <option value="Ear Drop">Ear Drop</option>
                                    <option value="Nasal Spray">Nasal Spray</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_instruction">Special Instructions</label>
                                <input type="text" name="instruction" id="edit_instruction" class="form-control form-control-sm"
                                       placeholder="e.g. Before Food" maxlength="255">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-save mr-1"></i>Update Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" rel="stylesheet">
    <style>
        .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
        .input-group > .select2-container--bootstrap4 { flex: 1 1 auto; width: 1% !important; }
        .input-group > .select2-container--bootstrap4 .select2-selection--single {
            border-top-right-radius: 0; border-bottom-right-radius: 0;
        }
        .card-title { font-size: 1.05rem; }
        .form-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: #28a745; border-color: #28a745; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <script>
    $(function () {
        'use strict';

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        var ROUTES = {
            subTypeList:    "{{ route('settings.medicines.sub-types.ajax') }}",
            subTypeStore:   "{{ route('settings.medicines.sub-types.store') }}",
            quickAdd:       "{{ route('settings.medicines.quick-add') }}",
            medicineOptions:"{{ route('templates.medicine.options') }}",
            addMedicine:    "{{ route('templates.medicine.ajax.add') }}",
            listMedicines:  "{{ route('templates.medicine.ajax.list') }}",
            updateMedicine: "{{ route('templates.medicine.ajax.update', '__ID__') }}",
            deleteMedicine: "{{ route('templates.medicine.ajax.delete', '__ID__') }}"
        };

        var hasGenericName = @json($hasGenericName);

        var MEDICINE_MAP = {};
        var EDIT_MEDICINE_MAP = {};

        function escHtml(s) {
            return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function notifySuccess(msg) { window.toastr ? toastr.success(msg) : alert(msg); }
        function notifyError(msg)   { window.toastr ? toastr.error(msg)   : alert(msg); }

        function showToast(msg, type) {
            var t = document.getElementById('qaToast');
            if (!t) return;
            t.textContent = msg;
            t.className = 'fixed bottom-6 right-6 z-50 min-w-[260px] px-4 py-3 rounded-lg text-sm font-semibold text-white shadow-lg transition-all duration-300 ' +
                (type === 'error' ? 'bg-red-700' : 'bg-emerald-800');
            t.style.transform = 'translateY(0)';
            t.style.opacity = '1';
            setTimeout(function () {
                t.style.transform = 'translateY(8rem)';
                t.style.opacity = '0';
            }, 3000);
        }

        function firstError(xhr) {
            var msg = 'Server error (HTTP ' + xhr.status + ')';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.responseJSON.errors) {
                    var first = Object.keys(xhr.responseJSON.errors)[0];
                    msg = xhr.responseJSON.errors[first][0];
                }
            }
            return msg;
        }

        function updateToggleLabel($checkbox) {
            var $label = $('#' + $checkbox.attr('id') + '_label');
            if ($label.length) $label.text($checkbox.prop('checked') ? 'Yes' : 'No');
        }
        $(document).on('change', '.form-check-input[role="switch"][name="commonly_used"]', function () {
            updateToggleLabel($(this));
        });

        $('.select2bs4').each(function () {
            $(this).select2({
                theme: 'bootstrap4', width: '100%', allowClear: true,
                placeholder: $(this).data('placeholder') || ''
            });
        });

        /* ---------- MEDICINE NAME: live AJAX search (no Company required first) ---------- */
        var MEDICINE_SEARCH_MAP = {}; // id -> full medicine record, refreshed on each search
        $('#medicine_id').select2({
            theme: 'bootstrap4', width: '100%', allowClear: true,
            placeholder: $('#medicine_id').data('placeholder') || 'Type to search, or pick a filter above...',
            minimumInputLength: 0, // opening the box (even with no text) shows results based on current filters
            ajax: {
                url: ROUTES.medicineOptions,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        // Type/Sub Type/Company/Dosage Form act as optional filters if picked
                        medicine_type_id: $('#medicine_type_id').val() || '',
                        medicine_subtype_id: $('#medicine_subtype_id').val() || '',
                        company_id: $('#company_id').val() || '',
                        dosage_form_id: $('#dosage_form_id').val() || ''
                    };
                },
                processResults: function (res) {
                    var list = res.medicines || [];
                    var results = list.map(function (m) {
                        MEDICINE_SEARCH_MAP[m.id] = m;
                        var label = m.name + (m.strength ? ' (' + m.strength + ')' : '') + (m.company ? ' — ' + m.company : '');
                        return { id: m.id, text: label };
                    });
                    return { results: results };
                },
                cache: false // filters (Type/Company/...) change between searches, so don't cache by term alone
            }
        });

        // Re-query the Medicine Name list whenever a filter changes, so switching
        // Company/Type/SubType/Dosage Form refreshes what's shown immediately.
        $('#medicine_type_id, #company_id, #dosage_form_id').on('change', function () {
            $('#medicine_id').val(null).trigger('change');
        });
        $('#medicine_subtype_id').on('change.refreshMedicine', function () {
            if ($(this).data('loading')) return;
            $('#medicine_id').val(null).trigger('change');
        });
        $('.select2edit').each(function () {
            $(this).select2({
                theme: 'bootstrap4', width: '100%', allowClear: true,
                dropdownParent: $('#editMedicineModal'),
                placeholder: $(this).data('placeholder') || ''
            });
        });

        /* ---------- MEDICINE OPTIONS ---------- */
        function loadMedicineOptions($companySelect, $medicineSelect, filters, preselectId, preselectName, done) {
            var companyId = $companySelect.val();
            $medicineSelect.prop('disabled', true).html('<option value=""></option>').trigger('change');
            if (!companyId) {
                if (done) done(null);
                return;
            }
            var data = { company_id: companyId };
            $.extend(data, filters || {});
            $.get(ROUTES.medicineOptions, data)
                .done(function (res) {
                    var options = '<option value="">-- Select Medicine --</option>';
                    var map = {};
                    (res.medicines || []).forEach(function (m) {
                        options += '<option value="' + m.id + '">' + escHtml(m.name) + (m.strength ? ' (' + escHtml(m.strength) + ')' : '') + '</option>';
                        map[m.id] = m;
                    });
                    if (preselectId && !map[preselectId] && preselectName) {
                        options += '<option value="' + preselectId + '">' + escHtml(preselectName) + '</option>';
                    }
                    MEDICINE_MAP[companyId] = map;
                    $medicineSelect.html(options)
                        .prop('disabled', false)
                        .val(preselectId || '')
                        .trigger('change');
                    if (done) done(map[preselectId] || null);
                })
                .fail(function () {
                    $medicineSelect.prop('disabled', true)
                        .html('<option value="">-- Could not load medicines --</option>')
                        .trigger('change');
                    notifyError('Medicine list load করা যায়নি। Server/network সমস্যা — Console/Network ট্যাব চেক করুন।');
                    if (done) done(null);
                });
        }

        function currentFormFilters() {
            return {
                medicine_type_id: $('#medicine_type_id').val() || '',
                medicine_subtype_id: $('#medicine_subtype_id').val() || ''
            };
        }

        function reloadFormMedicines(preselectId, preselectName, done) {
            loadMedicineOptions($('#company_id'), $('#medicine_id'), currentFormFilters(), preselectId, preselectName, done);
        }

        function reloadEditMedicines(preselectId, preselectName, done) {
            var $company = $('#edit_company_id');
            var $medicine = $('#edit_medicine_id');
            var companyId = $company.val();
            if (!companyId) {
                $medicine.prop('disabled', true).html('<option value=""></option>').trigger('change');
                if (done) done(null);
                return;
            }
            $.get(ROUTES.medicineOptions, { company_id: companyId })
                .done(function (res) {
                    var options = '<option value="">-- Select Medicine --</option>';
                    var map = {};
                    (res.medicines || []).forEach(function (m) {
                        options += '<option value="' + m.id + '">' + escHtml(m.name) + (m.strength ? ' (' + escHtml(m.strength) + ')' : '') + '</option>';
                        map[m.id] = m;
                    });
                    if (preselectId && !map[preselectId] && preselectName) {
                        options += '<option value="' + preselectId + '">' + escHtml(preselectName) + '</option>';
                    }
                    EDIT_MEDICINE_MAP[companyId] = map;
                    $medicine.html(options)
                        .prop('disabled', false)
                        .val(preselectId || '')
                        .trigger('change');
                    if (done) done(map[preselectId] || null);
                })
                .fail(function () {
                    $medicine.prop('disabled', true)
                        .html('<option value="">-- Could not load medicines --</option>')
                        .trigger('change');
                    if (done) done(null);
                });
        }

        function autofillFromMedicine(med) {
            if (!med) return;
            $('#strength').val(med.strength || '');
            $('#generic_name').val(med.generic_name || '');
            $('#dosage_form_id').val(med.dosage_form_id || '').trigger('change.select2');
            $('#status').val('1');
            if (med.company_id) {
                $('#company_id').val(med.company_id).trigger('change.select2');
            }
            if (med.medicine_type_id) {
                $('#medicine_type_id').val(med.medicine_type_id).trigger('change.select2');
                loadSubTypes(med.medicine_type_id, $('#medicine_subtype_id'), med.medicine_sub_type_id || null, refreshAddFormSubTypeUI);
            }
        }

        function autofillEditFromMedicine(med) {
            if (!med) return;
            $('#edit_generic_name').val(med.generic_name || '');
            $('#edit_strength').val(med.strength || '');
            $('#edit_dosage_form_id').val(med.dosage_form_id || '').trigger('change.select2');
            $('#edit_medicine_type_id').val(med.medicine_type_id || '').trigger('change.select2');
            loadSubTypes(med.medicine_type_id || '', $('#edit_medicine_subtype_id'), med.medicine_sub_type_id || null);
            var commonlyUsed = med.commonly_used == 1;
            $('#edit_commonly_used').prop('checked', commonlyUsed);
            $('#edit_commonly_used_label').text(commonlyUsed ? 'Yes' : 'No');
        }

        // Company/Type/SubType are now optional filters for the Medicine Name
        // search (picked up automatically via the `data` function above) —
        // they no longer need to gate or reload the Medicine Name list.
        $('#medicine_type_id').on('change', function () {
            var typeId = $(this).val();
            if (!typeId) {
                $('#subTypeWrapper').slideUp(150);
                $('#medicine_subtype_id').val('').trigger('change');
                return;
            }
            loadSubTypes(typeId, $('#medicine_subtype_id'), null, refreshAddFormSubTypeUI);
        });

        $('#medicine_id').on('select2:select', function (e) {
            var med = MEDICINE_SEARCH_MAP[e.params.data.id];
            autofillFromMedicine(med);
        });
        $('#medicine_id').on('select2:clear', function () {
            $('#strength').val('');
            $('#generic_name').val('');
        });

        // Browser back-button/bfcache can restore Type visually without firing
        // 'change' — resync the Sub Type dropdown once on load if so.
        if ($('#medicine_type_id').val()) {
            loadSubTypes($('#medicine_type_id').val(), $('#medicine_subtype_id'), $('#medicine_subtype_id').val() || null, refreshAddFormSubTypeUI);
        }

        $('#edit_company_id').on('change', function () {
            reloadEditMedicines();
        });

        $('#edit_medicine_id').on('change', function () {
            var med = (EDIT_MEDICINE_MAP[$('#edit_company_id').val()] || {})[$(this).val()];
            autofillEditFromMedicine(med);
        });

        /* ---------- DURATION HELPERS ---------- */
        function buildDuration(numId, typeId) {
            var num = $('#' + numId).val();
            var type = $('#' + typeId).val();
            if (type === 'চলবে') return 'চলবে';
            if (num && type) return num + ' ' + type;
            if (type) return type;
            return '';
        }

        function splitDuration(value) {
            if (!value) return ['', ''];
            if (String(value).indexOf('চলবে') !== -1) return ['', 'চলবে'];
            var parts = String(value).split(' ');
            if (parts.length >= 2) return [parts[0], parts[1]];
            return ['', ''];
        }

        /* ---------- ADD MEDICINE ---------- */
        function buildDose() {
            return buildDoseFor('', '');
        }

        function buildDoseForEdit() {
            return buildDoseFor('edit_', 'edit_');
        }

        function buildDoseFor(morningPrefix, nightPrefix) {
            var morning = $('#' + morningPrefix + 'morning').val() || '-';
            var noon = $('#' + morningPrefix + 'noon').val() || '-';
            var night = $('#' + nightPrefix + 'night').val() || '-';
            return morning + '+' + noon + '+' + night;
        }

        $('#medicineForm').on('submit', function (e) {
            e.preventDefault();

            if (!$('#templateid').val()) { notifyError('Please select a Template.'); return; }
            if (!$('#order_type').val()) { notifyError('Please select a Stage.'); return; }
            if (!$('#company_id').val()) { notifyError('Please select a Company.'); return; }
            if (!$('#medicine_id').val()) { notifyError('Please select a Medicine.'); return; }

            var $btn = $('#btnSaveMedicine');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

            var payload = {
                templateid: $('#templateid').val(),
                order_type: $('#order_type').val(),
                company_id: $('#company_id').val(),
                medicine_id: $('#medicine_id').val(),
                strength: $('#strength').val(),
                status: $('#status').val(),
                morning: $('#morning').val(),
                noon: $('#noon').val(),
                night: $('#night').val(),
                dose: buildDose(),
                dosage: buildDose(),
                meal_timing: $('#meal_timing').val(),
                duration: buildDuration('duration_num', 'duration_type'),
                route: $('#route').val(),
                instruction: $('#instruction').val()
            };

            $.ajax({
                url: ROUTES.addMedicine,
                method: 'POST',
                data: payload
            })
            .done(function (res) {
                if (res.ok) {
                    notifySuccess(res.message || 'Medicine added successfully.');
                    resetMedicineFields();
                    loadList();
                } else {
                    notifyError(res.message || 'Save failed.');
                }
            })
            .fail(function (xhr) {
                notifyError(firstError(xhr));
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Add to Template');
            });
        });

        function resetMedicineFields() {
            $('#medicine_id').val(null).trigger('change');
            $('#company_id').val('').trigger('change.select2');
            $('#strength').val('');
            $('#generic_name').val('');
            $('#dosage_form_id').val('').trigger('change.select2');
            $('#morning').val('');
            $('#noon').val('');
            $('#night').val('');
            $('#meal_timing').val('');
            $('#duration_num').val('');
            $('#duration_type').val('');
            $('#route').val('Oral');
            $('#instruction').val('');
        }

        $('#btnResetForm').on('click', function () {
            setTimeout(function () {
                $('#medicineForm .select2bs4').val('').trigger('change.select2');
                $('#subTypeWrapper').hide();
                $('#medicine_id').val('').trigger('change');
                $('#strength').val('');
                $('#generic_name').val('');
                $('#status').val('1');
                $('#morning').val('');
                $('#noon').val('');
                $('#night').val('');
                $('#meal_timing').val('');
                $('#duration_num').val('');
                $('#duration_type').val('');
                $('#route').val('Oral');
                $('#instruction').val('');
                loadList();
            }, 0);
        });

        /* ---------- MEDICINE LIST ---------- */
        function loadList() {
            var templateId = $('#templateid').val();
            var stage = $('#order_type').val();
            if (!templateId || !stage) {
                $('#listCountBadge').text('0 total');
                $('#medicineListBody').html(
                    '<tr><td colspan="' + (hasGenericName ? 11 : 10) + '" class="text-center text-muted py-4">' +
                    '<i class="fas fa-capsules fa-2x d-block mb-2"></i>Select a template and a stage above to load its medicines.</td></tr>'
                );
                return;
            }

            $.get(ROUTES.listMedicines, {
                templateid: templateId,
                order_type: stage,
                commonly_used_filter: $('#listCommonlyUsed').val()
            })
            .done(function (res) {
                var rows = res.rows || [];
                $('#listCountBadge').text(rows.length + ' total');

                if (!rows.length) {
                    $('#medicineListBody').html(
                        '<tr><td colspan="' + (hasGenericName ? 11 : 10) + '" class="text-center text-muted py-4">' +
                        '<i class="fas fa-capsules fa-2x d-block mb-2"></i>No medicines found. Use the form above to add the first one.</td></tr>'
                    );
                    return;
                }

                var html = '';
                rows.forEach(function (row, index) {
                    html += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td class="font-weight-bold">' + escHtml(row.name) + '</td>' +
                        '<td>' + (row.strength || '—') + '</td>' +
                        '<td>' + (row.type_name || '—') + '</td>' +
                        '<td>' + (row.subtype_name || '—') + '</td>' +
                        '<td>' + (row.company || '—') + '</td>' +
                        '<td>' + (row.dosage_name || '—') + '</td>' +
                        (hasGenericName ? '<td>' + (row.generic_name || '—') + '</td>' : '') +
                        '<td>' + (row.active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>') + '</td>' +
                        '<td>' + (row.commonly_used ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>' : '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No</span>') + '</td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-xs btn-outline-info btn-edit-medicine" title="Edit"' +
                                ' data-id="' + row.id + '"' +
                                ' data-order-type="' + escHtml(row.order_type) + '"' +
                                ' data-name="' + escHtml(row.name) + '"' +
                                ' data-strength="' + escHtml(row.strength) + '"' +
                                ' data-company-id="' + (row.company_id || '') + '"' +
                                ' data-medicine-id="' + (row.medicine_id || '') + '"' +
                                ' data-type-id="' + (row.type_id || '') + '"' +
                                ' data-subtype-id="' + (row.subtype_id || '') + '"' +
                                ' data-dosage-id="' + (row.dosage_id || '') + '"' +
                                ' data-commonly-used="' + (row.commonly_used ? 1 : 0) + '"' +
                                ' data-status="' + (row.active ? 1 : 0) + '"' +
                                ' data-morning="' + escHtml(row.morning) + '"' +
                                ' data-noon="' + escHtml(row.noon) + '"' +
                                ' data-night="' + escHtml(row.night) + '"' +
                                ' data-meal-timing="' + escHtml(row.meal_timing) + '"' +
                                ' data-duration="' + escHtml(row.duration) + '"' +
                                ' data-route="' + escHtml(row.route) + '"' +
                                ' data-instruction="' + escHtml(row.instruction) + '"' +
                                (hasGenericName ? ' data-generic="' + escHtml(row.generic_name) + '"' : '') +
                            '><i class="fas fa-edit"></i></button>' +
                            '<button type="button" class="btn btn-xs btn-outline-danger btn-delete-medicine" title="Delete"' +
                                ' data-id="' + row.id + '" data-name="' + escHtml(row.name) + '">' +
                            '<i class="fas fa-trash-alt"></i></button>' +
                        '</td>' +
                    '</tr>';
                });
                $('#medicineListBody').html(html);
            })
            .fail(function () {
                notifyError('Could not load the medicine list.');
            });
        }

        $('#templateid').on('change', function () {
            loadList();
        });

        $('#order_type').on('change', function () {
            loadList();
        });

        $('#listCommonlyUsed').on('change', function () {
            loadList();
        });

        /* ---------- EDIT MEDICINE ---------- */
        $(document).on('click', '.btn-edit-medicine', function () {
            var d = $(this).data();
            $('#edit_row_id').val(d.id);
            $('#edit_medicine_type_id').val(d.typeId || '').trigger('change.select2');
            $('#edit_dosage_form_id').val(d.dosageId || '').trigger('change.select2');
            $('#edit_status').val(String(d.status));
            $('#edit_commonly_used').prop('checked', d.commonlyUsed == 1);
            $('#edit_commonly_used_label').text(d.commonlyUsed == 1 ? 'Yes' : 'No');
            @if($hasGenericName)
                $('#edit_generic_name').val(d.generic || '');
            @endif
            $('#edit_morning').val(d.morning || '');
            $('#edit_noon').val(d.noon || '');
            $('#edit_night').val(d.night || '');
            $('#edit_meal_timing').val(d.mealTiming || '');
            $('#edit_route').val(d.route || 'Oral');
            $('#edit_instruction').val(d.instruction || '');
            var dur = splitDuration(d.duration);
            $('#edit_duration_num').val(dur[0]);
            $('#edit_duration_type').val(dur[1]);
            loadSubTypes(d.typeId || '', $('#edit_medicine_subtype_id'), d.subtypeId || null);
            $('#edit_order_type').val(d.orderType || '');
            $('#edit_company_id').val(d.companyId || '').trigger('change.select2');
            reloadEditMedicines(d.medicineId || '', d.name || '', function () {});
            $('#editMedicineModal').modal('show');
        });

        $('#editMedicineForm').on('submit', function (e) {
            e.preventDefault();

            var id = $('#edit_row_id').val();
            if (!id) return;

            var orderType = $('#edit_order_type').val();
            if (!orderType) {
                notifyError('Cannot edit this medicine: its stage is missing.');
                return;
            }

            if (!$('#edit_company_id').val()) { notifyError('Please select a Company.'); return; }
            if (!$('#edit_medicine_id').val()) { notifyError('Please select a Medicine.'); return; }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

            var payload = {
                order_type: orderType,
                company_id: $('#edit_company_id').val(),
                medicine_id: $('#edit_medicine_id').val(),
                strength: $('#edit_strength').val(),
                status: $('#edit_status').val(),
                morning: $('#edit_morning').val(),
                noon: $('#edit_noon').val(),
                night: $('#edit_night').val(),
                dose: buildDoseForEdit(),
                dosage: buildDoseForEdit(),
                meal_timing: $('#edit_meal_timing').val(),
                duration: buildDuration('edit_duration_num', 'edit_duration_type'),
                route: $('#edit_route').val(),
                instruction: $('#edit_instruction').val()
            };

            $.ajax({
                url: ROUTES.updateMedicine.replace('__ID__', id),
                method: 'POST',
                data: $.extend({ _method: 'PUT' }, payload)
            })
            .done(function (res) {
                if (res.ok) {
                    notifySuccess(res.message || 'Medicine updated successfully.');
                    $('#editMedicineModal').modal('hide');
                    loadList();
                } else {
                    notifyError(res.message || 'Update failed.');
                }
            })
            .fail(function (xhr) {
                notifyError(firstError(xhr));
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Update Medicine');
            });
        });

        /* ---------- DELETE MEDICINE ---------- */
        $(document).on('click', '.btn-delete-medicine', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            if (!confirm('Delete medicine \'' + name + '\'? This cannot be undone.')) return;
            $.ajax({
                url: ROUTES.deleteMedicine.replace('__ID__', id),
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') }
            })
            .done(function (res) {
                if (res.ok) {
                    notifySuccess(res.message || 'Medicine deleted successfully.');
                    loadList();
                } else {
                    notifyError(res.message || 'Delete failed.');
                }
            })
            .fail(function (xhr) {
                notifyError(firstError(xhr));
            });
        });

        /* ---------- QUICK ADD ---------- */
        function openQuickAdd() {
            var typeId = $('#medicine_type_id').val();
            var companyId = $('#company_id').val();

            if (!typeId || !companyId) {
                notifyError('Quick Add এর আগে উপরে Medicine Type ও Company সিলেক্ট করুন — নতুন medicine সেই Type/Company এর আন্ডারেই যোগ হবে।');
                return;
            }

            var typeText = $('#medicine_type_id option:selected').text().trim();
            var subtypeId = $('#medicine_subtype_id').val();
            var subtypeText = subtypeId ? $('#medicine_subtype_id option:selected').text().trim() : '';
            var companyText = $('#company_id option:selected').text().trim();

            $('#qaContextText').text(typeText + (subtypeText ? ' → ' + subtypeText : '') + ' → ' + companyText);

            $('#qa_medicine_name').val('');
            $('#qa_dosage_form_id').val('');
            $('#quickAddAlert').addClass('hidden').text('');
            $('#quickAddModal').removeClass('hidden').addClass('flex');
            setTimeout(function () { $('#qa_medicine_name').focus(); }, 50);
        }
        function closeQuickAdd() {
            $('#quickAddModal').addClass('hidden').removeClass('flex');
        }

        $('#btnQuickAddOpen').on('click', openQuickAdd);
        $('#qaCloseBtn, #qaCancelBtn').on('click', closeQuickAdd);
        $('#quickAddModal').on('click', function (e) {
            if (e.target === this) closeQuickAdd();
        });

        $('#btnQuickAddSave').on('click', function () {
            var $btn = $(this);
            var name = $.trim($('#qa_medicine_name').val());
            var formId = $('#qa_dosage_form_id').val();
            var typeId = $('#medicine_type_id').val();
            var subtypeId = $('#medicine_subtype_id').val();
            var companyId = $('#company_id').val();

            if (!name) {
                $('#quickAddAlert').removeClass('hidden').text('Medicine Name is required.');
                $('#qa_medicine_name').focus();
                return;
            }
            if (!formId) {
                $('#quickAddAlert').removeClass('hidden').text('Dosage Form is required.');
                return;
            }
            if (!typeId || !companyId) {
                $('#quickAddAlert').removeClass('hidden').text('Medicine Type ও Company সিলেক্ট করা নেই — modal বন্ধ করে আবার খুলুন।');
                return;
            }

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: ROUTES.quickAdd,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    medicine_name: name,
                    dosage_form_id: formId,
                    medicine_type_id: typeId,
                    medicine_subtype_id: subtypeId || null,
                    company_id: companyId
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .done(function (data) {
                if (!data.ok) {
                    $('#quickAddAlert').removeClass('hidden').text(data.message || 'Save failed');
                    return;
                }
                closeQuickAdd();
                showToast(data.duplicate ? 'Already exists in database.' : 'Medicine added successfully.', 'success');

                // Inject the new medicine straight into the search widget and select it,
                // same as if the user had searched and picked it themselves.
                var med = data.medicine;
                MEDICINE_SEARCH_MAP[med.id] = med;
                var label = med.name + (med.strength ? ' (' + med.strength + ')' : '') + (med.company ? ' — ' + med.company : '');
                var $opt = new Option(label, med.id, true, true);
                $('#medicine_id').append($opt).trigger('change');
                autofillFromMedicine(med);
            })
            .fail(function (xhr) {
                $('#quickAddAlert').removeClass('hidden').text(firstError(xhr));
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save & Use');
            });
        });

        /* ---------- SUB TYPES ---------- */
        function loadSubTypes(typeId, $select, preselectId, done) {
            $select.data('loading', true);
            $select.empty().append('<option value=""></option>');
            if (!typeId) {
                $select.trigger('change');
                $select.data('loading', false);
                if (done) done([]);
                return;
            }
            $.get(ROUTES.subTypeList, { medicine_type_id: typeId })
                .done(function (response) {
                    var subTypes = (response && response.sub_types) ? response.sub_types : [];
                    subTypes.forEach(function (st) {
                        $select.append($('<option></option>').attr('value', st.id).text(st.subtype_name));
                    });
                    $select.val(preselectId || '').trigger('change');
                    $select.data('loading', false);
                    if (done) done(subTypes);
                })
                .fail(function () {
                    $select.data('loading', false);
                    notifyError('Could not load sub medicine types.');
                    if (done) done([]);
                });
        }

        function refreshAddFormSubTypeUI(subTypes) {
            var $wrapper = $('#subTypeWrapper');
            var $select2Box = $('#medicine_subtype_id').next('.select2');
            var hasAny = subTypes.length > 0;
            if (hasAny) {
                $wrapper.slideDown(150);
                $select2Box.show();
                $('#subTypeEmptyHint').hide();
            } else {
                $wrapper.slideUp(150);
            }
        }

        var subTypeTarget = null;
        $(document).on('click', '.btn-open-subtype-modal', function () {
            var $typeSelect = $($(this).data('type-select'));
            var typeId = $typeSelect.val();
            if (!typeId) { notifyError('Please select a Medicine Type first.'); return; }
            subTypeTarget = $(this).data('target-select');
            $('#modalMedicineTypeId').val(typeId);
            $('#modalMedicineTypeName').val($typeSelect.find('option:selected').text().trim());
            $('#modalSubtypeName').val('');
            $('#modalSubtypeStatus').val('1');
            $('#subTypeModalAlert').hide().empty();
            $('#subTypeModal').modal('show');
        });

        $('#subTypeModal').on('shown.bs.modal', function () {
            $('#modalSubtypeName').trigger('focus');
        });

        $('#btnSaveSubType').on('click', function () {
            var $btn = $(this);
            var typeId = $('#modalMedicineTypeId').val();
            var name = $.trim($('#modalSubtypeName').val());
            if (!name) {
                $('#subTypeModalAlert').text('Sub Medicine Type Name is required.').show();
                return;
            }
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
            $.post(ROUTES.subTypeStore, {
                medicine_type_id: typeId,
                subtype_name: name,
                active: $('#modalSubtypeStatus').val()
            })
            .done(function (response) {
                $('#subTypeModal').modal('hide');
                notifySuccess(response.message || 'Sub Medicine Type added successfully.');
                var $target = $(subTypeTarget);
                loadSubTypes(typeId, $target, response.id, function (subTypes) {
                    if (subTypeTarget === '#medicine_subtype_id') refreshAddFormSubTypeUI(subTypes);
                });
            })
            .fail(function (xhr) {
                $('#subTypeModalAlert').text(firstError(xhr)).show();
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save');
            });
        });
    });
    </script>
@stop