<script>
$(function () {
    'use strict';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var ROUTES = {
        index:          "{{ route('settings.medicines.index') }}",
        listHtml:       "{{ route('settings.medicines.list-html') }}",
        subTypeList:    "{{ route('settings.medicines.sub-types.ajax') }}",
        subTypeStore:   "{{ route('settings.medicines.sub-types.store') }}",
        companyStore:   "{{ route('settings.medicines.companies.store') }}",
        typeStore:      "{{ route('settings.medicines.types.store') }}",
        dosageStore:    "{{ route('settings.medicines.dosage-forms.store') }}",
        medicineUpdate: "{{ route('settings.medicines.update', '__ID__') }}",
        quickAdd:       "{{ route('settings.medicines.quick-add') }}",
        checkDuplicate: "{{ route('settings.medicines.check-duplicate') }}"
    };

    function notifySuccess(msg) { window.toastr ? toastr.success(msg) : alert(msg); }
    function notifyError(msg)   { window.toastr ? toastr.error(msg)   : alert(msg); }
    function notifyWarning(msg) { window.toastr ? toastr.warning(msg) : alert(msg); }

    /* ---------- SELECT2 INIT ---------- */
    $('.select2bs4').each(function () {
        $(this).select2({
            theme: 'bootstrap4', width: '100%', allowClear: true,
            placeholder: $(this).data('placeholder') || ''
        });
    });
    $('.select2edit').each(function () {
        $(this).select2({
            theme: 'bootstrap4', width: '100%', allowClear: true,
            dropdownParent: $('#editMedicineModal'),
            placeholder: $(this).data('placeholder') || ''
        });
    });

    /* ---------- CURRENT LIST QUERY (for AJAX list refresh) ---------- */
    function currentListQuery() {
        var params = {};
        var qs = new URLSearchParams(window.location.search);
        if (qs.get('search')) params.search = qs.get('search');
        if (qs.get('commonly_used_filter')) params.commonly_used_filter = qs.get('commonly_used_filter');
        return params;
    }

    function refreshList(params, done) {
        $.get(ROUTES.listHtml, params || {})
            .done(function (res) {
                if (res && res.ok && res.html) {
                    $('#medicineListRegion').html(res.html);
                }
                if (done) done();
            })
            .fail(function () { if (done) done(); });
    }

    /* ---------- STICKY TYPE/COMPANY (sessionStorage) ---------- */
    var STICKY_KEY = 'medicine_master_sticky_v1';
    var stickySubtypeId = null;

    function getSticky() {
        try {
            var raw = sessionStorage.getItem(STICKY_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) { return null; }
    }

    function saveSticky() {
        var data = {
            medicine_type_id: $('#medicine_type_id').val() || '',
            medicine_subtype_id: $('#medicine_subtype_id').val() || '',
            company_id: $('#company_id').val() || ''
        };
        if (!data.medicine_type_id && !data.company_id) {
            sessionStorage.removeItem(STICKY_KEY);
            hideStickyBar();
            return;
        }
        sessionStorage.setItem(STICKY_KEY, JSON.stringify(data));
        showStickyBar();
    }

    function showStickyBar() {
        $('#stickyCompanyName').text($('#company_id option:selected').text() || '—');
        $('#stickyTypeName').text($('#medicine_type_id option:selected').text() || '—');
        $('#stickyBar').show();
    }

    function hideStickyBar() {
        $('#stickyBar').hide();
    }

    function focusSelect($el) {
        if ($el.hasClass('select2-hidden-accessible')) { $el.select2('focus'); }
        else { $el.trigger('focus'); }
    }

    /* Fast entry: focus the first empty required field (Company → Type → Dosage → Name). */
    function focusFirstEmptyField() {
        var ids = ['#company_id', '#medicine_type_id', '#dosage_form_id', '#medicine_name'];
        for (var i = 0; i < ids.length; i++) {
            if (!$(ids[i]).val()) { focusSelect($(ids[i])); return; }
        }
        $('#medicine_name').trigger('focus');
    }

    function applySticky() {
        var s = getSticky();
        if (!s) { focusFirstEmptyField(); return; }
        var typeId = s.medicine_type_id || '';
        var companyId = s.company_id || '';
        if (companyId && $('#company_id option[value="' + companyId + '"]').length) {
            $('#company_id').val(companyId).trigger('change.select2');
        }
        if (typeId && $('#medicine_type_id option[value="' + typeId + '"]').length) {
            stickySubtypeId = s.medicine_subtype_id || null;
            $('#medicine_type_id').val(typeId).trigger('change.select2').trigger('change');
        }
        showStickyBar();
        // Batch-entry: jump to the first empty field so the next item can be typed immediately.
        setTimeout(focusFirstEmptyField, 200);
    }

    $('#btnChangeSticky').on('click', function () {
        sessionStorage.removeItem(STICKY_KEY);
        hideStickyBar();
        $('#medicine_type_id').val('').trigger('change.select2').trigger('change');
        $('#medicine_subtype_id').val('');
        $('#company_id').val('').trigger('change.select2');
        notifySuccess('Sticky Type/Company cleared. Select new values.');
        focusSelect($('#company_id'));
    });

    $('#company_id').on('change', function () { saveSticky(); });
    $('#medicine_subtype_id').on('change', function () { saveSticky(); });

    /* ---------- LIVE DUPLICATE CHECK (Medicine Name) ---------- */
    var dupTimer = null;
    function escHtml(s) { return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function clearDupState() {
        $('#dupWarning').hide().empty();
        $('#btnSaveMedicine').prop('disabled', false);
    }

    $('#medicine_name').on('input', function () {
        var $box = $('#dupWarning');
        var term = $.trim($(this).val());
        clearTimeout(dupTimer);
        if (term.length < 2) { clearDupState(); return; }
        dupTimer = setTimeout(function () {
            $.get(ROUTES.checkDuplicate, { name: term })
                .done(function (res) {
                    var matches = res.matches || [];
                    if (!matches.length) { clearDupState(); return; }
                    var exact = matches.some(function (m) { return m.exact; });
                    var html = '<b>' +
                        (exact ? 'এই নামে medicine already আছে — duplicate save করা যাবে না:' : 'Similar medicine already exists:') + '</b><br>';
                    matches.slice(0, 3).forEach(function (m) {
                        html += '&bull; ' + escHtml(m.name) + (m.strength ? ' (' + escHtml(m.strength) + ')' : '') +
                                (m.company ? ' — ' + escHtml(m.company) : '') + '<br>';
                    });
                    if (exact) {
                        $box.css({ background: '#fdecea', borderColor: '#f5c6cb' });
                        $('#btnSaveMedicine').prop('disabled', true);
                    } else {
                        $box.css({ background: '#fff8e1', borderColor: '#ffe082' });
                        $('#btnSaveMedicine').prop('disabled', false);
                    }
                    $box.html(html).show();
                })
                .fail(function () { clearDupState(); });
        }, 350);
    });

    /* ---------- SUB TYPES ---------- */
    function loadSubTypes(typeId, $select, preselectId, done) {
        $select.empty().append('<option value=""></option>');
        if (!typeId) {
            $select.trigger('change');
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
                if (done) done(subTypes);
            })
            .fail(function () {
                notifyError('Could not load sub medicine types.');
                if (done) done([]);
            });
    }

    function refreshAddFormSubTypeUI(subTypes) {
        var $wrapper = $('#subTypeWrapper');
        var $select2Box = $('#medicine_subtype_id').next('.select2');
        var hasAny = subTypes.length > 0;
        $wrapper.slideDown(150);
        $select2Box.toggle(hasAny);
        $('#subTypeEmptyHint').toggle(!hasAny);
    }

    $('#medicine_type_id').on('change', function () {
        var typeId = $(this).val();
        if (!typeId) {
            $('#subTypeWrapper').slideUp(150);
            $('#medicine_subtype_id').val('').trigger('change');
            saveSticky();
            return;
        }
        var preselect = stickySubtypeId;
        stickySubtypeId = null;
        loadSubTypes(typeId, $('#medicine_subtype_id'), preselect, refreshAddFormSubTypeUI);
        saveSticky();
    });

    $('#edit_medicine_type_id').on('change', function () {
        loadSubTypes($(this).val(), $('#edit_medicine_subtype_id'), null);
    });

    function loadQaSubTypes(typeId, preselectId) {
        var $wrapper = $('#qa_subTypeWrapper');
        var $select = $('#qa_medicine_subtype_id');
        $select.empty().append('<option value=""></option>');
        if (!typeId) {
            $wrapper.hide();
            return;
        }
        $.get(ROUTES.subTypeList, { medicine_type_id: typeId })
            .done(function (response) {
                var subTypes = (response && response.sub_types) ? response.sub_types : [];
                if (!subTypes.length) {
                    $select.hide().val('');
                    $('#qa_subTypeEmptyHint').show();
                    $wrapper.show();
                    return;
                }
                subTypes.forEach(function (st) {
                    $select.append($('<option></option>').attr('value', st.id).text(st.subtype_name));
                });
                $select.val(preselectId || '').show();
                $('#qa_subTypeEmptyHint').hide();
                $wrapper.show();
            })
            .fail(function () {
                $select.hide().val('');
                $('#qa_subTypeEmptyHint').show();
                $wrapper.show();
            });
    }

    $('#qa_medicine_type_id').on('change', function () {
        loadQaSubTypes($(this).val(), '');
    });

    /* ---------- UNIFIED MASTER-ADD MODAL (Company / Type / Dosage / Sub Type) ---------- */
    var MASTER_ADD = {
        company: {
            title: 'Add New Company', label: 'Company Name', placeholder: 'e.g. Square Pharma',
            route: ROUTES.companyStore, field: 'name', responseName: 'name',
            selects: ['#company_id', '#edit_company_id', '#qa_company_id']
        },
        type: {
            title: 'Add New Medicine Type', label: 'Medicine Type Name', placeholder: 'e.g. Antibiotic',
            route: ROUTES.typeStore, field: 'type_name', responseName: 'type_name',
            selects: ['#medicine_type_id', '#edit_medicine_type_id', '#qa_medicine_type_id']
        },
        dosage: {
            title: 'Add New Dosage Form', label: 'Dosage Form Name', placeholder: 'e.g. Capsule',
            route: ROUTES.dosageStore, field: 'dosage_name', responseName: 'dosage_name',
            selects: ['#dosage_form_id', '#edit_dosage_form_id', '#qa_dosage_form_id']
        },
        subtype: {
            title: 'Add Sub Medicine Type', label: 'Sub Medicine Type Name', placeholder: 'e.g. NSAID',
            route: ROUTES.subTypeStore, field: 'subtype_name', responseName: 'subtype_name',
            needsParent: true
        }
    };

    var masterKind = null;
    var masterTarget = null;
    var masterTypeSelect = null;

    $(document).on('click', '.btn-open-type-modal', function () {
        var kind = $(this).data('master-kind');
        var cfg = MASTER_ADD[kind];
        if (!cfg) return;

        if (kind === 'subtype') {
            var $typeSelect = $($(this).data('type-select'));
            var typeId = $typeSelect.val();
            if (!typeId) { notifyError('Please select a Medicine Type first.'); return; }
            $('#masterAddParentGroup').show();
            $('#masterAddParentName').val($typeSelect.find('option:selected').text().trim());
        } else {
            $('#masterAddParentGroup').hide();
            $('#masterAddParentName').val('');
        }

        masterKind = kind;
        masterTarget = $(this).data('target-select');
        masterTypeSelect = $(this).data('type-select') || null;

        $('#masterAddTitle').text(cfg.title);
        $('#masterAddNameLabel').html(cfg.label + ' <span class="text-danger">*</span>');
        $('#masterAddName').attr('placeholder', cfg.placeholder).val('');
        $('#masterAddAlert').hide().text('');
        $('#masterAddModal').modal('show');
    });

    $('#masterAddModal').on('shown.bs.modal', function () {
        $('#masterAddName').trigger('focus');
    });

    $('#masterAddModal').on('keydown', 'input', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#btnMasterAddSave').trigger('click'); }
    });

    $('#btnMasterAddSave').on('click', function () {
        var cfg = MASTER_ADD[masterKind];
        if (!cfg) return;
        var $btn = $(this);
        var name = $.trim($('#masterAddName').val());
        if (!name) {
            $('#masterAddAlert').text(cfg.label + ' is required.').show();
            return;
        }

        $btn.prop('disabled', true).text('Saving...');

        var payload = {};
        payload[cfg.field] = name;
        if (cfg.needsParent) {
            payload.medicine_type_id = $(masterTypeSelect).val();
            payload.active = 1;
        }

        $.post(cfg.route, payload)
            .done(function (response) {
                if (response.success === false) {
                    $('#masterAddAlert').text(response.message || 'Could not save.').show();
                    return;
                }
                $('#masterAddModal').modal('hide');
                notifySuccess(response.message || (cfg.label + ' added successfully.'));

                if (masterKind === 'subtype') {
                    var typeId = $(masterTypeSelect).val();
                    var $target = $(masterTarget);
                    loadSubTypes(typeId, $target, response.id, function (subTypes) {
                        if (masterTarget === '#medicine_subtype_id') refreshAddFormSubTypeUI(subTypes);
                    });
                } else {
                    var displayName = response[cfg.responseName];
                    cfg.selects.forEach(function (sel) {
                        var $sel = $(sel);
                        if (!$sel.length) return;
                        if ($sel.find('option[value="' + response.id + '"]').length) return;
                        $sel.append($('<option></option>').attr('value', response.id).text(displayName));
                    });
                    var $t = $(masterTarget || cfg.selects[0]);
                    $t.val(response.id).trigger('change');
                }
            })
            .fail(function (xhr) {
                var msg = 'Could not save.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    if (xhr.responseJSON.errors) {
                        var first = Object.keys(xhr.responseJSON.errors)[0];
                        msg = xhr.responseJSON.errors[first][0];
                    }
                }
                $('#masterAddAlert').text(msg).show();
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save');
            });
    });

    /* ---------- QUICK ADD (Bootstrap modal + AJAX, no page reload) ---------- */
    function focusFirstQaField() {
        if (!$('#qa_company_id').val()) { $('#qa_company_id').trigger('focus'); return; }
        if (!$('#qa_medicine_type_id').val()) { $('#qa_medicine_type_id').trigger('focus'); return; }
        if (!$('#qa_dosage_form_id').val()) { $('#qa_dosage_form_id').trigger('focus'); return; }
        $('#qa_medicine_name').trigger('focus');
    }

    function openQuickAdd() {
        $('#qa_medicine_name').val('');
        $('#qa_strength').val('');
        $('#qa_note').val('');
        $('#qa_dosage_form_id').val('');
        $('#quickAddAlert').hide().text('');

        // Company/Type pre-fill: main form → sticky → empty
        var typeId = $('#medicine_type_id').val() || '';
        var subtypeId = '';
        var companyId = $('#company_id').val() || '';
        var s = getSticky();
        if (s) {
            if (!typeId) typeId = s.medicine_type_id || '';
            subtypeId = s.medicine_subtype_id || '';
            if (!companyId) companyId = s.company_id || '';
        }
        $('#qa_company_id').val(companyId);
        $('#qa_medicine_type_id').val(typeId);
        loadQaSubTypes(typeId, subtypeId);

        $('#quickAddModal').modal('show');
    }

    $('#btnQuickAddOpen').on('click', openQuickAdd);

    $('#quickAddModal').on('shown.bs.modal', focusFirstQaField);

    $('#quickAddModal').on('keydown', 'input', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#btnQuickAddSave').trigger('click'); }
    });

    function qaError(msg) {
        $('#quickAddAlert').text(msg).show();
    }

    $('#btnQuickAddSave').on('click', function () {
        var $btn = $(this);
        var companyId = $('#qa_company_id').val();
        var typeId = $('#qa_medicine_type_id').val();
        var subtypeId = $('#qa_medicine_subtype_id').val() || '';
        var formId = $('#qa_dosage_form_id').val();
        var name = $.trim($('#qa_medicine_name').val());
        var strength = $.trim($('#qa_strength').val());
        var noteVal = $.trim($('#qa_note').val());

        $('#quickAddAlert').hide();

        if (!companyId) { qaError('Company is required.'); $('#qa_company_id').focus(); return; }
        if (!typeId) { qaError('Medicine Type is required.'); $('#qa_medicine_type_id').focus(); return; }
        if (!formId) { qaError('Dosage Form is required.'); $('#qa_dosage_form_id').focus(); return; }
        if (!name) { qaError('Medicine Name is required.'); $('#qa_medicine_name').focus(); return; }

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: ROUTES.quickAdd,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                medicine_name: name,
                dosage_form_id: formId,
                medicine_type_id: typeId,
                medicine_subtype_id: subtypeId,
                company_id: companyId,
                strength: strength,
                note: noteVal
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .done(function (data) {
            if (!data.ok) {
                qaError(data.message || 'Save failed');
                return;
            }

            // Sticky: same Company/Type for the next entry (can be changed any time)
            sessionStorage.setItem(STICKY_KEY, JSON.stringify({
                medicine_type_id: typeId,
                medicine_subtype_id: subtypeId,
                company_id: companyId
            }));

            if (data.duplicate) {
                notifyWarning('"' + (data.medicine ? data.medicine.name : name) + '" already exists in the database.');
            } else {
                notifySuccess('Medicine "' + (data.medicine ? data.medicine.name : name) + '" added successfully.');
            }

            // AJAX list refresh — jump to the page where the medicine appears (no page reload)
            var q = currentListQuery();
            if (data.page && data.page > 1) q.page = data.page;
            refreshList(q);

            // Stay open for the next medicine (fast batch entry): clear only the per-medicine fields
            $('#qa_medicine_name').val('');
            $('#qa_strength').val('');
            $('#qa_note').val('');
            $('#medicine_name').val('').trigger('input');
            clearDupState();
            $('#qa_medicine_name').trigger('focus');
        })
        .fail(function (xhr) {
            var msg = 'Could not save medicine.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.responseJSON.errors) {
                    var first = Object.keys(xhr.responseJSON.errors)[0];
                    msg = xhr.responseJSON.errors[first][0];
                }
            }
            qaError(msg);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Save & Use');
        });
    });

    /* ---------- EDIT MEDICINE ---------- */
    $(document).on('click', '.btn-edit-medicine', function () {
        var d = $(this).data();
        $('#editMedicineForm').attr('action', ROUTES.medicineUpdate.replace('__ID__', d.id));
        $('#edit_medicine_name').val(d.name);
        $('#edit_strength').val(d.strength || '');
        $('#edit_note').val(d.note || '');
        $('#edit_status').val(String(d.status));
        @if($hasGenericName)
            $('#edit_generic_name').val(d.generic || '');
        @endif
        var $cu = $('#edit_commonly_used');
        $cu.prop('checked', d.commonlyUsed == 1);
        $('#edit_commonly_used_label').text(d.commonlyUsed == 1 ? 'Yes' : 'No');
        $('#edit_company_id').val(d.companyId || '').trigger('change');
        $('#edit_dosage_form_id').val(d.dosageId || '').trigger('change');
        $('#edit_medicine_type_id').val(d.typeId || '').trigger('change.select2');
        loadSubTypes(d.typeId, $('#edit_medicine_subtype_id'), d.subtypeId || null);
        $('#editMedicineModal').modal('show');
    });

    $('#editMedicineForm').on('submit', function (e) {
        if (!$('#edit_medicine_type_id').val() || !$('#edit_company_id').val() ||
            !$('#edit_dosage_form_id').val() ||
            !$.trim($('#edit_medicine_name').val())) {
            e.preventDefault();
            notifyError('Please fill in all required fields (Type, Company, Name, Dosage Form).');
        }
    });

    /* ---------- COMMONLY USED TOGGLE LABEL ---------- */
    function updateToggleLabel($checkbox) {
        var $label = $('#' + $checkbox.attr('id') + '_label');
        if ($label.length) $label.text($checkbox.prop('checked') ? 'Yes' : 'No');
    }
    $(document).on('change', '.form-check-input[role="switch"][name="commonly_used"]', function () {
        updateToggleLabel($(this));
    });

    /* ---------- RESET ---------- */
    $('#btnResetForm').on('click', function () {
        setTimeout(function () {
            $('#medicineForm .select2bs4').val('').trigger('change.select2');
            $('#subTypeWrapper').hide();
            $('#status').val('1');
            $('#commonly_used').prop('checked', false);
            $('#commonly_used_label').text('No');
            clearDupState();
            focusFirstEmptyField();
        }, 0);
    });

    /* ---------- INIT: old input wins over sticky ---------- */
    @if(old('medicine_type_id'))
        loadSubTypes(
            '{{ old('medicine_type_id') }}',
            $('#medicine_subtype_id'),
            '{{ old('medicine_subtype_id') }}',
            refreshAddFormSubTypeUI
        );
    @else
        applySticky();
    @endif
});
</script>
