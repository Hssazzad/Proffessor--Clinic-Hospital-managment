function admissionInitMedicineNameSelect(selectSelector, modalSelector) {
    var $select = $(selectSelector);

    if (!$select.length || typeof $select.select2 !== 'function') {
        return;
    }

    $select.select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: $(modalSelector),
        placeholder: '-- Search or type medicine name',
        allowClear: true,
        tags: true,
        createTag: function (params) {
            var term = $.trim(params.term);

            if (!term) {
                return null;
            }

            return {
                id: term,
                text: term,
                newTag: true
            };
        }
    });
}

function admissionSetMedicineName(selectSelector, medicineName) {
    var $select = $(selectSelector);
    medicineName = medicineName || '';

    if (!medicineName) {
        $select.val('').trigger('change');
        return;
    }

    var hasOption = $select.find('option').filter(function () {
        return $(this).val() === medicineName;
    }).length > 0;

    if (!hasOption) {
        $select.append(new Option(medicineName, medicineName, true, true));
    }

    $select.val(medicineName).trigger('change');
}
