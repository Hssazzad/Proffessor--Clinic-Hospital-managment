@extends('adminlte::page')

@section('title', 'Doctors')

@section('content_header')
    <h1 class="text-primary">Add Doctor</h1>
@stop

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    window.APP = {
        csrf: '{{ csrf_token() }}',
        dataUrl: '{{ route('settings.doctors.data') }}',
        storeUrl: '{{ route('settings.doctors.store') }}',
        itemBase: '{{ url('settings/doctors') }}'
    };
    window.SPECIALITIES = @json($specialities->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]));
</script>

<div class="max-w-6xl mx-auto py-6">

    {{-- Flash for non-AJAX fallback --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div id="alertBox" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

    {{-- Add form --}}
    <div class="bg-white rounded-xl shadow mb-8 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-800">+ Add New Doctor</h3>
        </div>
        <form id="addForm" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Doctor Name <span class="text-red-500">*</span></label>
                <input type="text" id="addDoctorName" name="doctor_name" required maxlength="50"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter doctor name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reg No</label>
                <input type="text" id="addRegNo" name="reg_no" maxlength="12"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Registration no">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Speciality</label>
                <select id="addSpeciality" name="speciality"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Speciality --</option>
                    @foreach ($specialities as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                <input type="text" id="addContact" name="contact" maxlength="12"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contact no">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posting</label>
                <input type="text" id="addPosting" name="Posting" maxlength="25"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Posting / Chamber">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rate Code</label>
                <input type="text" id="addRateCode" name="RateCode" maxlength="12"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Rate code">
            </div>
            <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="addActive" name="active" value="1" checked class="h-4 w-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg shadow transition">
                    Save Doctor
                </button>
            </div>
        </form>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input type="text" id="searchBox" placeholder="Search by name, reg no, contact or speciality..."
               class="w-full max-w-md border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Doctor List</h3>
            <span id="itemCount" class="text-xs text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reg No</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Speciality</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Posting</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rate Code</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div id="emptyState" class="hidden px-5 py-10 text-center text-sm text-gray-500">No doctors found.</div>
    </div>
</div>

{{-- Edit modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Doctor</h3>
        <input type="hidden" id="editId">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Doctor Name <span class="text-red-500">*</span></label>
                <input type="text" id="editDoctorName" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required maxlength="50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reg No</label>
                <input type="text" id="editRegNo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="12">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Speciality</label>
                <select id="editSpeciality" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                <input type="text" id="editContact" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="12">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posting</label>
                <input type="text" id="editPosting" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="25">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rate Code</label>
                <input type="text" id="editRateCode" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="12">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="editActive" value="1" class="h-4 w-4 text-blue-600 rounded">
                <label for="editActive" class="text-sm text-gray-700">Active</label>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button id="editCancel" class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700">Cancel</button>
            <button id="editSave" class="px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">Update</button>
        </div>
    </div>
</div>

@stop

@push('js')
<script>
$(function () {
    var CSRF = window.APP.csrf;
    var itemUrl = function (id) { return window.APP.itemBase + '/' + id; };
    var escape = function (s) { return $('<div>').text(s == null ? '' : String(s)).html(); };

    function specialityOptions(selected) {
        var html = '<option value="">-- Select Speciality --</option>';
        (window.SPECIALITIES || []).forEach(function (s) {
            html += '<option value="' + s.id + '"' + (String(selected) === String(s.id) ? ' selected' : '') + '>' + escape(s.name) + '</option>';
        });
        return html;
    }

    function showAlert(msg, ok) {
        var box = $('#alertBox');
        box.removeClass('hidden')
            .toggleClass('bg-green-100 border border-green-300 text-green-800', ok)
            .toggleClass('bg-red-100 border border-red-300 text-red-800', !ok)
            .text(msg);
        setTimeout(function () { box.addClass('hidden'); }, 3000);
    }

    function render(data) {
        var tbody = $('#tableBody');
        tbody.empty();
        $('#itemCount').text(data.length + ' item(s)');
        if (!data.length) {
            $('#emptyState').removeClass('hidden');
            return;
        }
        $('#emptyState').addClass('hidden');
        data.forEach(function (item, i) {
            var badge = item.active
                ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>'
                : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>';
            var speciality = escape(item.speciality_name || item.speciality);
            var row = '<tr>' +
                '<td class="px-5 py-3 text-sm text-gray-600">' + (i + 1) + '</td>' +
                '<td class="px-5 py-3 text-sm text-gray-700">' + escape(item.reg_no) + '</td>' +
                '<td class="px-5 py-3 text-sm font-medium text-gray-800">' + escape(item.doctor_name) + '</td>' +
                '<td class="px-5 py-3 text-sm text-gray-700">' + speciality + '</td>' +
                '<td class="px-5 py-3 text-sm text-gray-700">' + escape(item.contact) + '</td>' +
                '<td class="px-5 py-3 text-sm text-gray-700">' + escape(item.Posting) + '</td>' +
                '<td class="px-5 py-3 text-sm text-gray-700">' + escape(item.RateCode) + '</td>' +
                '<td class="px-5 py-3">' + badge + '</td>' +
                '<td class="px-5 py-3 text-right whitespace-nowrap">' +
                    '<button class="btn-edit mr-2 inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-semibold bg-yellow-500 hover:bg-yellow-600 text-white" data-id="' + item.id + '">Edit</button>' +
                    '<button class="btn-delete inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-semibold bg-red-500 hover:bg-red-600 text-white" data-id="' + item.id + '">Delete</button>' +
                '</td></tr>';
            tbody.append(row);
            tbody.find('tr:last .btn-edit').data('item', item);
        });
    }

    function loadList() {
        $.ajax({
            url: window.APP.dataUrl,
            method: 'GET',
            data: { search: $('#searchBox').val() },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) { render(res.data || []); },
            error: function () { showAlert('Failed to load data.', false); }
        });
    }

    // Add
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#addDoctorName').val().trim()) return;
        $.ajax({
            url: window.APP.storeUrl,
            method: 'POST',
            data: {
                reg_no: $('#addRegNo').val().trim(),
                doctor_name: $('#addDoctorName').val().trim(),
                speciality: $('#addSpeciality').val(),
                contact: $('#addContact').val().trim(),
                Posting: $('#addPosting').val().trim(),
                RateCode: $('#addRateCode').val().trim(),
                active: $('#addActive').is(':checked') ? 1 : 0
            },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                showAlert(res.message || 'Saved.', true);
                $('#addForm')[0].reset();
                $('#addActive').prop('checked', true);
                loadList();
            },
            error: function (xhr) {
                var msg = 'Failed to save.';
                if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).join(' ');
                showAlert(msg, false);
            }
        });
    });

    // Edit modal open
    $(document).on('click', '.btn-edit', function () {
        var item = $(this).data('item');
        if (!item) return;
        $('#editId').val(item.id);
        $('#editDoctorName').val(item.doctor_name);
        $('#editRegNo').val(item.reg_no || '');
        $('#editSpeciality').html(specialityOptions(item.speciality));
        $('#editContact').val(item.contact || '');
        $('#editPosting').val(item.Posting || '');
        $('#editRateCode').val(item.RateCode || '');
        $('#editActive').prop('checked', item.active == 1);
        $('#editModal').removeClass('hidden').addClass('flex');
        $('#editDoctorName').focus();
    });

    $('#editCancel').on('click', function () {
        $('#editModal').addClass('hidden').removeClass('flex');
    });

    // Update
    $('#editSave').on('click', function () {
        var id = $('#editId').val();
        if (!$('#editDoctorName').val().trim()) return;
        $.ajax({
            url: itemUrl(id),
            method: 'POST',
            data: {
                reg_no: $('#editRegNo').val().trim(),
                doctor_name: $('#editDoctorName').val().trim(),
                speciality: $('#editSpeciality').val(),
                contact: $('#editContact').val().trim(),
                Posting: $('#editPosting').val().trim(),
                RateCode: $('#editRateCode').val().trim(),
                active: $('#editActive').is(':checked') ? 1 : 0,
                _method: 'PUT'
            },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                $('#editModal').addClass('hidden').removeClass('flex');
                showAlert(res.message || 'Updated.', true);
                loadList();
            },
            error: function (xhr) {
                var msg = 'Failed to update.';
                if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).join(' ');
                showAlert(msg, false);
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        if (!confirm('Are you sure to delete this doctor?')) return;
        $.ajax({
            url: itemUrl(id),
            method: 'POST',
            data: { _method: 'DELETE' },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                showAlert(res.message || 'Deleted.', true);
                loadList();
            },
            error: function () { showAlert('Failed to delete.', false); }
        });
    });

    // Search
    var searchTimer;
    $('#searchBox').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadList, 300);
    });

    loadList();
});
</script>
@endpush
