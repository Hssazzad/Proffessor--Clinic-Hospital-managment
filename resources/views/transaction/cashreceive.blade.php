@extends('adminlte::page')

@section('title', 'Cash Receive')

@section('plugins.Select2', true)

@section('content_header')
    <h6 class="Posting" style="font-size:2vw;">Cash Receive</h6>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-7 col-lg-7">

            <div class="row">
                <div class="col-sm-12">
                    <h2 class="m-0 font-weight-bold text-primary d-flex justify-content-center">
                        <font color="red">Last transaction: {{ session('refNumber') }}</font>
                    </h2>
                </div>
            </div>

            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form method="POST" action="{{ route('transaction.saveCashReceive') }}">
                        @csrf
                         <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="CreditAccount" class="control-label"> Receive From Account</label>
                                </div>
                                <div class="col-sm-6">
                                    <select id="CreditAccount" name="CreditAccount" class="select2 form-control" style="width:250px;">
                                        <option value="">Select  Account</option>
                                        @foreach($crAccount as $row)
                                            <option value="{{ $row->AccountNo }}" @selected(old('CreditAccount') == $row->AccountNo)>
                                                [{{ $row->gl_group }}-{{ $row->gl_sub_group }}] {{ $row->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="creditsign" name="creditsign" class="form-control" value="1">
                                    @error('CreditAccount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Amount" class="control-label">Amount</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Amount" name="Amount" class="form-control" value="{{ old('Amount') }}">
                                    <input type="hidden" id="debitsign" name="debitsign" class="form-control" value="-1">
                                    <span>
                                        <font color="blue">Available Balance {{ number_format((float) ($cashBalance ?? 0), 2) }}</font>
                                    </span>
                                    @error('Amount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                       

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Particular" class="control-label">Transaction Description</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Particular" name="Particular" class="form-control" value="{{ old('Particular') }}">
                                    @error('Particular')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PostingDate" class="control-label">Transaction Date</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="PostingDate" name="PostingDate" class="form-control" value="{{ old('PostingDate', $date ?? date('d-m-Y')) }}" readonly>
                                    @error('PostingDate')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Serial" class="control-label">MO/Recipt Number</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Serial" name="Serial" class="form-control" value="{{ old('Serial') }}" required>
                                    @error('Serial')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6"></div>
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endpush

@push('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $(function () {
        $("#PostingDate").datepicker({ dateFormat: "dd-mm-yy" }).datepicker("setDate", new Date());
        $(".select2").select2({ width: '100%', dropdownParent: $(document.body) });
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
@endpush
