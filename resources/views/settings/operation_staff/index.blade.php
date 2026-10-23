@extends('adminlte::page')

@section('title', $title)

@section('content_header')
    <h1 class="text-primary">{{ $heading }}</h1>
@stop

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    window.APP = {
        csrf: '{{ csrf_token() }}',
        dataUrl: '{{ route($data) }}',
        storeUrl: '{{ route($store) }}',
        type: '{{ $type }}',
        singular: '{{ $singular }}'
    };
</script>

<div class="max-w-5xl mx-auto py-6">
    {{-- Tab switch between the two lists --}}
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('settings.assistant_surgeons.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'assistant_surgeon' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Assistant Surgeons
        </a>
        <a href="{{ route('settings.anesthesiologists.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'anesthesiologist' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Anesthesiologists
        </a>
        <a href="{{ route('settings.operation_names.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'operation_name' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Operation Names
        </a>
        <a href="{{ route('settings.discharge_diagnoses.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'discharge_diagnosis' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Discharge Diagnoses
        </a>
        <a href="{{ route('settings.discharge_instructions.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'discharge_instruction' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Discharge Instructions
        </a>
        <a href="{{ route('settings.operation_methods.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold {{ $type === 'operation_method' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Operation Methods
        </a>
    </div>

    {{-- Flash for non-AJAX fallback --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div id="alertBox" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

    {{-- Add form --}}
    <div class="bg-white rounded-xl shadow mb-8 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-800">+ Add New {{ $singular }}</h3>
        </div>
        <form id="addForm" class="p-5 flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="addName" name="name" required maxlength="255"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter {{ $singular }} name">
            </div>
            <div class="flex items-center gap-2 pb-2">
                <input type="checkbox" id="addActive" name="active" value="1" checked class="h-4 w-4 text-blue-600 rounded">
                <label for="addActive" class="text-sm text-gray-700">Active</label>
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg shadow transition">
                Save
            </button>
        </form>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input type="text" id="searchBox" placeholder="Search by name..."
               class="w-full max-w-sm border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">{{ $title }}</h3>
            <span id="itemCount" class="text-xs text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div id="emptyState" class="hidden px-5 py-10 text-center text-sm text-gray-500">No records found.</div>
    </div>
</div>

{{-- Edit modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit {{ $singular }}</h3>
        <input type="hidden" id="editId">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" id="editName" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="flex items-center gap-2 mb-6">
            <input type="checkbox" id="editActive" value="1" class="h-4 w-4 text-blue-600 rounded">
            <label for="editActive" class="text-sm text-gray-700">Active</label>
        </div>
        <div class="flex justify-end gap-2">
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
    var singular = window.APP.singular;
    var itemUrl = function (id) { return window.APP.storeUrl + '/' + id; };

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
            var row = '<tr>' +
                '<td class="px-5 py-3 text-sm text-gray-600">' + (i + 1) + '</td>' +
                '<td class="px-5 py-3 text-sm font-medium text-gray-800">' + $('<div>').text(item.name).html() + '</td>' +
                '<td class="px-5 py-3">' + badge + '</td>' +
                '<td class="px-5 py-3 text-right whitespace-nowrap">' +
                    '<button class="btn-edit mr-2 inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-semibold bg-yellow-500 hover:bg-yellow-600 text-white" data-id="' + item.id + '" data-name="' + $('<div>').text(item.name).html() + '" data-active="' + item.active + '">Edit</button>' +
                    '<button class="btn-delete inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-semibold bg-red-500 hover:bg-red-600 text-white" data-id="' + item.id + '">Delete</button>' +
                '</td></tr>';
            tbody.append(row);
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
        var name = $('#addName').val().trim();
        if (!name) return;
        $.ajax({
            url: window.APP.storeUrl,
            method: 'POST',
            data: { name: name, active: $('#addActive').is(':checked') ? 1 : 0 },
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
        $('#editId').val($(this).data('id'));
        $('#editName').val($(this).data('name'));
        $('#editActive').prop('checked', $(this).data('active') == 1);
        $('#editModal').removeClass('hidden').addClass('flex');
        $('#editName').focus();
    });

    $('#editCancel').on('click', function () {
        $('#editModal').addClass('hidden').removeClass('flex');
    });

    // Update
    $('#editSave').on('click', function () {
        var id = $('#editId').val();
        var name = $('#editName').val().trim();
        if (!name) return;
        $.ajax({
            url: itemUrl(id),
            method: 'POST',
            data: { name: name, active: $('#editActive').is(':checked') ? 1 : 0, _method: 'PUT' },
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
        if (!confirm('Are you sure to delete this ' + singular + '?')) return;
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
