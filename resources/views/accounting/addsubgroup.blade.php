@extends('adminlte::page')

{{-- Load AdminLTE's Select2 plugin (CSS + JS) on this page --}}
@section('plugins.Select2', true)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6 col-lg-6">

            <h6 class="parameter" style="font-size:2vw;">{{ isset($subgroup) ? 'Edit Sub Head' : 'Add Sub Head' }}</h6>

            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form id="comment" method="POST" action="{{ isset($subgroup) ? route('accounting.updatesubgroup', $subgroup->ID) : route('accounting.addsubgroupaction') }}">
                        @csrf
                        @isset($subgroup)
                            @method('PUT')
                        @endisset

                        {{-- Ledger Type --}}
                        <div class="form-group row">
                            <label for="gl_type" class="col-sm-4 control-label">
                                Ledger Types
                            </label>

                            <div class="col-sm-1">:</div>

                            <div class="col-sm-4">
                                <select name="gl_type" id="gl_type" class="select2" style="width:250px;">
                                    <option value="">Select Ledger Type</option>

                                    @foreach ($gltype as $row)
                                        <option value="{{ $row->code }}"
                                            @selected((isset($subgroup) ? $subgroup->gl_type : old('gl_type', $gl_type ?? '')) == $row->code)>
                                            {{ $row->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('gl_type')
                                    <div class="error1 text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- Main Head Name (populated via AJAX) --}}
                        <div class="form-group row">
                            <label for="gl_group" class="col-sm-4 control-label">Main Head Name</label>
                            <div class="col-sm-1">:</div>
                            <div class="col-sm-4">
                                <select name="gl_group" id="gl_group" class="select2 form-control input-lg" style="width:240px;" required>
                                    <option value="">Select Main Head</option>
                                    @isset($subgroup)
                                        <option value="{{ $subgroup->gl_group }}" selected>{{ $subgroup->gl_group }}</option>
                                    @endisset
                                </select>
                                @error('gl_group')
                                    <div class="error1 text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Sub Head Code (auto-generated) --}}
                        <div class="form-group row">
                            <label for="gl_sub_group" class="col-sm-4 control-label">Sub Head Code</label>
                            <div class="col-sm-1">:</div>
                            <div class="col-sm-4">
                                <input type="text" id="gl_sub_group" name="gl_sub_group" class="form-control"
                                       value="{{ isset($subgroup) ? $subgroup->gl_sub_group : old('gl_sub_group') }}" readonly>
                                @error('gl_sub_group')
                                    <div class="error1 text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Sub Head Name --}}
                        <div class="form-group row">
                            <label for="gl_sub_group_name" class="col-sm-4 control-label">Sub Head Name</label>
                            <div class="col-sm-1">:</div>
                            <div class="col-sm-4">
                                <input type="text" id="gl_sub_group_name" name="gl_sub_group_name" class="form-control"
                                       value="{{ isset($subgroup) ? $subgroup->gl_sub_group_name : old('gl_sub_group_name') }}" required>
                                @error('gl_sub_group_name')
                                    <div class="error1 text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary">{{ isset($subgroup) ? 'Update' : 'Add Now' }}</button>
                        </div>
                    </form>

                </div>{{-- /.card-body --}}
            </div>{{-- /.main-card --}}

            <div class="row mt-2">
                <div class="col-sm-9">
                    <span id="result"></span>
                </div>
            </div>

        </div>{{-- /.col --}}
    </div>{{-- /.row --}}
</div>{{-- /.container-fluid --}}
@endsection

@push('js')
    <script>
        $(function () {
            // Send the CSRF token with every AJAX request in this page.
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            // --- Bind handlers using delegation so nothing else can stop them ---

            // AJAX submit of the whole form.
            $(document).on('submit', '#comment', function (e) {
                e.preventDefault();

                var dataString = $(this).serialize();
                var glGroup    = $('#gl_group').val();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('accounting.addsubgroupaction') }}',
                    data: dataString,
                    success: function (response) {
                        $('#gl_sub_group_name').val('').change();
                        $('#result').html(response);

                        // Re-fetch the next code from the server instead of
                        // incrementing the field's value client-side.
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('accounting.nextglsubgroup') }}',
                            data: { gl_group: glGroup },
                            cache: false,
                            success: function (nextCode) {
                                $('#gl_sub_group').val(nextCode);
                            }
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var msgs = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            alert(msgs);
                        } else {
                            alert('Something went wrong (status ' + xhr.status + '). Check console for details.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });

            // Ledger Type changed -> only populate Main Head dropdown.
            // (Result panel stays empty here; list is shown only after
            // Main Head is selected below — matches Add Account page behavior.)
            $(document).on('change', '#gl_type', function () {
                var glType = $(this).val();

                // Clear the result panel and reset downstream fields.
                $('#result').html('');
                $('#gl_sub_group').val('');

                if (!glType) {
                    $('#gl_group').empty()
                        .html('<option value="">Select Main Head</option>')
                        .val('').change();
                    return;
                }

                // Populate the #gl_group dropdown.
                $.ajax({
                    type: 'POST',
                    url: '{{ route('accounting.getGroupCodeByGlType') }}',
                    data: { gl_type: glType },
                    cache: false,
                    success: function (data) {
                        $('#gl_group').empty().html(data).val('').change();
                        if ($.fn.select2) { $('#gl_group').select2(); } // refresh select2
                    }
                });
            });

            // Main Head changed -> load Sub Head list + generate next Sub Head code.
            // Mirrors the old CI GetSubGroupList / GenGlSubgGoupCode pair.
            $(document).on('change', '#gl_group', function () {
                var glGroup = $(this).val();
                var glType  = $('#gl_type').val();

                if (!glGroup) {
                    $('#result').html('');
                    $('#gl_sub_group').val('');
                    return;
                }

                // Show existing Sub Heads under this Main Head in #result.
                $.ajax({
                    type: 'POST',
                    url: '{{ route('accounting.getsubgrouplist') }}',
                    data: { gl_type: glType, gl_group: glGroup },
                    success: function (data) {
                        $('#result').html(data);
                    }
                });

                // Generate/populate the next Sub Head code.
                $.ajax({
                    type: 'POST',
                    url: '{{ route('accounting.nextglsubgroup') }}',
                    data: { gl_group: glGroup, gl_type: glType },
                    cache: false,
                    success: function (data) {
                        $('#gl_sub_group').val(data);
                    }
                });
            });

            // --- Initialize Select2 (guarded) ---
            if ($.fn.select2) {
                $('.select2').select2();
            } else {
                console.warn('Select2 is not loaded on this page.');
            }
        });

        // While editing, do not let the type/group change handlers overwrite the
        // existing sub head code with a freshly generated one.
        @isset($subgroup)
        $(document).ready(function () {
            $(document).off('change', '#gl_type');
            $(document).off('change', '#gl_group');
        });
        @endisset
    </script>
@endpush