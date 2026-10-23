{{-- CARD 1 — ADD NEW MEDICINE (fast entry: Company → Type → Sub Type → Dosage Form → Name) --}}
<div class="card pm-gov-card mb-4 pm-compact">
    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <h3 class="card-title font-weight-bold mb-0 pm-card-title">Add New Medicine</h3>
        <div class="d-flex align-items-center">
            <small class="text-muted mr-3 d-none d-md-inline">
                <i class="fas fa-bolt mr-1"></i>Fast entry: fill once, Type &amp; Company stay sticky — just type the next name.
            </small>
            <button type="button" id="btnQuickAddOpen" class="pm-qabtn">
                Quick Add
            </button>
        </div>
    </div>

    <form id="medicineForm" method="POST" action="{{ route('settings.medicines.store') }}" autocomplete="off">
        @csrf

        {{-- STICKY TYPE/COMPANY BAR --}}
        <div class="px-3 pt-2">
            <div id="stickyBar" class="pm-sticky-bar d-flex align-items-center justify-content-between" style="display:none;">
                <span class="small text-muted">
                    Sticky: <b id="stickyCompanyName" class="text-dark">—</b>
                    <span> • </span>
                    <b id="stickyTypeName" class="text-dark">—</b>
                </span>
                <button type="button" id="btnChangeSticky" class="btn btn-sm btn-outline-warning">
                    Change Type/Company
                </button>
            </div>
        </div>

        <div class="card-body pm-card-body">
            <div class="row pm-form-row">

                {{-- Company --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label for="company_id">Company <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="company_id" id="company_id"
                                    class="form-control select2bs4 @error('company_id') is-invalid @enderror"
                                    data-placeholder="-- Select Company --">
                                <option value=""></option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                        data-master-kind="company"
                                        data-target-select="#company_id"
                                        title="Add New Company">
Add
                                </button>
                            </div>
                        </div>
                        @error('company_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Medicine Type --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label for="medicine_type_id">Medicine Type <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="medicine_type_id" id="medicine_type_id"
                                    class="form-control select2bs4 @error('medicine_type_id') is-invalid @enderror"
                                    data-placeholder="-- Select Medicine Type --">
                                <option value=""></option>
                                @foreach($medicineTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('medicine_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->type_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                        data-master-kind="type"
                                        data-target-select="#medicine_type_id"
                                        title="Add New Medicine Type">
Add
                                </button>
                            </div>
                        </div>
                        @error('medicine_type_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Sub Medicine Type --}}
                <div class="col-md-6 col-xl-3" id="subTypeWrapper" style="display:none;">
                    <div class="form-group">
                        <label for="medicine_subtype_id">
                            Sub Medicine Type <small class="text-muted font-weight-normal">(optional)</small>
                        </label>
                        <div class="input-group">
                            <select name="medicine_subtype_id" id="medicine_subtype_id"
                                    class="form-control select2bs4 @error('medicine_subtype_id') is-invalid @enderror"
                                    data-placeholder="-- Select Sub Type --">
                                <option value=""></option>
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                        data-master-kind="subtype"
                                        data-target-select="#medicine_subtype_id"
                                        data-type-select="#medicine_type_id"
                                        title="Add New Sub Medicine Type">
Add
                                </button>
                            </div>
                        </div>
                        <small id="subTypeEmptyHint" class="form-text text-muted" style="display:none;">
                            No sub types exist for this medicine type yet — click <b>Add</b> to create one.
                        </small>
                        @error('medicine_subtype_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Dosage Form --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label for="dosage_form_id">Dosage Form <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="dosage_form_id" id="dosage_form_id"
                                    class="form-control select2bs4 @error('dosage_form_id') is-invalid @enderror"
                                    data-placeholder="-- Capsule / Tablet / Injection --">
                                <option value=""></option>
                                @foreach($dosageForms as $form)
                                    <option value="{{ $form->id }}" {{ old('dosage_form_id') == $form->id ? 'selected' : '' }}>
                                        {{ $form->dosage_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                        data-master-kind="dosage"
                                        data-target-select="#dosage_form_id"
                                        title="Add New Dosage Form">
Add
                                </button>
                            </div>
                        </div>
                        @error('dosage_form_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Medicine Name --}}
                <div class="col-md-6 col-xl-4">
                    <div class="form-group">
                        <label for="medicine_name">Medicine Name <span class="text-danger">*</span></label>
                        <input type="text" name="medicine_name" id="medicine_name"
                               class="form-control @error('medicine_name') is-invalid @enderror"
                               value="{{ old('medicine_name') }}" placeholder="e.g. Napa" maxlength="255" autocomplete="off">
                        <div id="dupWarning" class="mt-1 p-2 rounded" style="display:none;background:#fff8e1;border:1px solid #ffe082;font-size:12px;"></div>
                        @error('medicine_name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Strength (optional) --}}
                <div class="col-md-6 col-xl-2">
                    <div class="form-group">
                        <label for="strength">Strength <small class="text-muted">(optional)</small></label>
                        <input type="text" name="strength" id="strength"
                               class="form-control @error('strength') is-invalid @enderror"
                               value="{{ old('strength') }}" placeholder="e.g. 500 mg" maxlength="50">
                        @error('strength')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Note (shows on prescription) --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label for="note">Note <small class="text-muted">(shows on prescription)</small></label>
                        <input type="text" name="note" id="note"
                               class="form-control @error('note') is-invalid @enderror"
                               value="{{ old('note') }}" placeholder="e.g. Take after meal" maxlength="255">
                        @error('note')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Generic Name --}}
                @if($hasGenericName)
                    <div class="col-md-6 col-xl-3">
                        <div class="form-group">
                            <label for="generic_name">Generic Name</label>
                            <input type="text" name="generic_name" id="generic_name"
                                   class="form-control @error('generic_name') is-invalid @enderror"
                                   value="{{ old('generic_name') }}" placeholder="e.g. Paracetamol" maxlength="255">
                            @error('generic_name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Commonly Used --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label>⭐ Commonly Used Medicine</label>
                        <div class="d-flex align-items-center pm-commonly-used">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="commonly_used"
                                       id="commonly_used" value="1" role="switch"
                                       {{ old('commonly_used') ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small" for="commonly_used" id="commonly_used_label">
                                    {{ old('commonly_used') ? 'Yes' : 'No' }}
                                </label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Appears in the <b>Commonly Used Medicines</b> section on the Prescription page.
                        </small>
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-md-6 col-xl-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
        </div>
        <div class="card-footer bg-white text-right pm-card-footer">
            <button type="reset" class="btn btn-outline-secondary mr-2 pm-btn-footer" id="btnResetForm">
                Reset
            </button>
            <button type="submit" id="btnSaveMedicine" class="btn pm-btn-footer pm-save-btn px-4">
                Save Medicine
            </button>
        </div>
    </form>
</div>
