{{-- resources/views/Accounting/addgroup.blade.php --}}
@extends('adminlte::page')

@section('title', 'Add GL Code')

@push('styles')
    
@endpush

@section('content')
<form id="comment" action="{{ isset($group) ? route('accounting.updategroup', $group->ID) : route('accounting.addgroupaction') }}" method="POST">
    @csrf
    @isset($group)
        @method('PUT')
    @endisset

    <div class="container-fluid">
        <div class="row col-col-md-12">
            <div class="col-xl-12 col-lg-12">

                <h6 class="m-0 font-weight-bold text-primary"></h6>
                <h6 class="parameter" style="font-size:2vw;">{{ isset($group) ? 'Edit GL Group' : 'GL main Head Input' }}</h6>

                <div class="main-card card border border-success rounded-2">
                    <div class="card-body">

                        {{-- Ledger Type --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="gl_type" class="control-label">Ledger Type</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <select name="gl_type" id="gl_type" class="select2" style="width:250px;">
                                        <option value="">Select Ledger Type</option>
                                        @foreach ($gltype as $row)
                                            <option value="{{ $row->code }}"
                                                {{ (isset($group) ? $group->gl_type : old('gl_type')) == $row->code ? 'selected' : '' }}>
                                                {{ $row->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gl_type')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>

                            {{-- Main Head Code (auto-filled via AJAX, readonly) --}}
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="gl_group" class="control-label">Main Head Code</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <input type="text" id="gl_group" name="gl_group" class="form-control"
                                           value="{{ isset($group) ? $group->gl_group : old('gl_group') }}" readonly>
                                    @error('gl_group')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>

                            {{-- Main Head Name --}}
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="gl_group_name" class="control-label">Main Head Name</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <input type="text" id="gl_group_name" name="gl_group_name" class="form-control"
                                           value="{{ isset($group) ? $group->gl_group_name : old('gl_group_name') }}" required>
                                    @error('gl_group_name')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>

                            {{-- Trading Account Head (kept commented, as in original) --}}
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="tradingac" class="control-label">Trading Account Head</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    {{--
                                    <select name="tradingac" id="tradingac" class="select2" style="width:250px;">
                                        <option value="">Select tradingac</option>
                                        @foreach ($tradingaccount as $row)
                                            <option value="{{ $row->code }}">{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                    --}}
                                    @error('tradingac')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>

                            <div class="main-card card border border-success rounded-2">
                                <div class="card-body text-center">
                                    <button type="submit" class="btn btn-primary">{{ isset($group) ? 'Update' : 'Add Now' }}</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12">
                    <span id="result" name="result"></span>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script>
    // Send CSRF token with every AJAX request
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(function () {
        $("#comment").submit(function () {
            var dataString = $("#comment").serialize();
            var nextval = parseInt($("#gl_group").val(), 10) + 1;

            $.ajax({
                type: "POST",
                url: "{{ route('accounting.addgroupaction') }}",
                data: dataString,
                error: function () {
                    alert('Duplicate Entry');
                },
                success: function (response) {
                    $('#gl_group').val(nextval);
                    $('#gl_group_name').val("").change();
                    $('#result').html(response);
                }
            });

            return false; // stop the actual form post
        });
    });

    $(document).ready(function () {
        $("#gl_type").on("change", function () {
            var sel1 = $(this).val();

            $.ajax({
                type: "post",
                url: "{{ route('accounting.getgrouplist') }}",
                data: { "gl_type": sel1 },
                success: function (data) {
                    $("#result").html(data);
                }
            });

            $.ajax({
                type: "post",
                url: "{{ route('accounting.nextglgroup') }}",
                data: { "gl_type": sel1 },
                success: function (data) {
                    $("#gl_group").val(data);
                }
            });
        });
    });

    // Do not auto-generate a new code while editing an existing group.
    @isset($group)
    $(document).ready(function () {
        $("#gl_type").off("change");
    });
    @endisset

    $(function () {
        $(".select2").select2(); // Initialize Select2
    });
</script>
@endpush