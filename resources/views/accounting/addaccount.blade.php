{{-- resources/views/accounting/addaccount.blade.php --}}
@extends('adminlte::page')

@section('title', 'Add Account')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container { width: 250px !important; }
    </style>
@endpush

@section('content')
<form id="accountForm" action="{{ route('accounting.addaccountaction') }}" method="POST">
    @csrf
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <h6 class="parameter" style="font-size:2vw;">Add Account</h6>

                <div class="main-card card border border-success rounded-2">
                    <div class="card-body">

                        {{-- Accounts Type (gl_type) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="control-label">Accounts Type</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <select name="gl_type" id="gl_type" class="select2" style="width:250px;" required>
                                        <option value="">Select Parent</option>
                                        @foreach ($gltype as $row)
                                            <option value="{{ $row->code }}">{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('gl_type')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Group Name (gl_group) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="control-label">Group Name</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <select name="gl_group" id="gl_group" class="select2 form-control" style="width:240px;" required>
                                        <option value="">Select gl group</option>
                                    </select>
                                    @error('gl_group')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Sub Group Code (gl_sub_group) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="control-label">Sub Group Code</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <select name="gl_sub_group" id="gl_sub_group" class="select2 form-control" style="width:240px;" required>
                                        <option value="">Select gl Sub group</option>
                                    </select>
                                    @error('gl_sub_group')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Account No --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="control-label">Account No</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <input type="text" id="AccountNo" name="AccountNo" class="form-control" required>
                                    @error('AccountNo')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Account Name (title) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label class="control-label">Account Name</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <input type="text" id="title" name="title" class="form-control" required>
                                    @error('title')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="main-card card border border-success rounded-2">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary">Add Now</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Result / List Area --}}
            <div class="row mt-3">
                <div class="col-sm-12">
                    <span id="result"></span>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // CSRF setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function () {

        // Select2 init
        $(".select2").select2();

        // ============================================
        // 1. gl_type change -> load Group dropdown + list
        // ============================================
        $("#gl_type").on("change", function () {
            var sel1 = $(this).val();

            // Result panel clear (Account List will show only after Sub Group is selected)
            $("#result").html('');

            // Fill Group dropdown
            $.ajax({
                type: "post",
                url: "{{ route('accounting.getGroupCodeByGlType') }}",
                data: { "gl_type": sel1 },
                success: function (data) {
                    $("#gl_group").html(data).val("").trigger('change');
                    $("#gl_sub_group").html('<option value="">Select gl Sub group</option>').val("").trigger('change');
                    $("#AccountNo").val('');
                }
            });
        });

        // ============================================
        // 2. gl_group change -> load Sub Group dropdown
        // ============================================
        $("#gl_group").on("change", function () {
            var sel1 = $(this).val();
            var gl_type = $("#gl_type").val();

            $("#result").html('');

            if (!sel1) {
                $("#gl_sub_group").html('<option value="">Select gl Sub group</option>').val("").trigger('change');
                $("#AccountNo").val('');
                return;
            }

            $.ajax({
                type: "post",
                url: "{{ route('accounting.getsubgroupbyglgroup') }}",
                data: {
                    "gl_type": gl_type,
                    "gl_group": sel1
                },
                success: function (data) {
                    $("#gl_sub_group").html(data).val("").trigger('change');
                    $("#AccountNo").val('');
                }
            });
        });

        // ============================================
        // 3. gl_sub_group change -> generate AccountNo + load Account list
        // ============================================
        $("#gl_sub_group").on("change", function () {
            var sel1 = $(this).val();
            var gl_type = $("#gl_type").val();
            var gl_group = $("#gl_group").val();

            if (!sel1) {
                $("#AccountNo").val('');
                return;
            }

            // Next Account No
            $.ajax({
                type: "post",
                url: "{{ route('accounting.nextaccountno') }}",
                data: {
                    "gl_sub_group": sel1,
                    "gl_type": gl_type,
                    "gl_group": gl_group
                },
                success: function (data) {
                    $("#AccountNo").val(data);
                }
            });

            // Account List
            $.ajax({
                type: "post",
                url: "{{ route('accounting.getaccountlist') }}",
                data: {
                    "gl_sub_group": sel1,
                    "gl_type": gl_type,
                    "gl_group": gl_group
                },
                success: function (data) {
                    $("#result").html(data);
                }
            });
        });

        // ============================================
        // 4. Form Submit (AJAX)
        // ============================================
        $("#accountForm").on("submit", function (e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "{{ route('accounting.addaccountaction') }}",
                data: formData,
                success: function (response) {
                    // list update
                    $("#result").html(response);

                    // form clear (Account Name)
                    $("#title").val('');

                    // next AccountNo regenerate
                    var gl_sub_group = $("#gl_sub_group").val();
                    var gl_type = $("#gl_type").val();
                    var gl_group = $("#gl_group").val();

                    if (gl_sub_group) {
                        $.ajax({
                            type: "post",
                            url: "{{ route('accounting.nextaccountno') }}",
                            data: {
                                "gl_sub_group": gl_sub_group,
                                "gl_type": gl_type,
                                "gl_group": gl_group
                            },
                            success: function (data) {
                                $("#AccountNo").val(data);
                            }
                        });
                    }

                    alert('Account Created Successfully');
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        alert('Validation Error. Please check the fields.');
                    } else {
                        alert('Something went wrong / Duplicate Entry');
                    }
                }
            });
        });
    });
</script>
@endpush