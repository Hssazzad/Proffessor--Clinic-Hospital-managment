@extends('adminlte::page')

@section('title', 'Cash Payment')

@section('plugins.Select2', true)

@push('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .card, .card-body { overflow: visible; }
        #CreditAccount + .select2-container { width: 100% !important; }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-7 col-lg-7">

            <div class="row">
                <div class="col-sm-12">
                    <h6 class="Posting" style="font-size:2vw;">Cash Payment</h6>

                    @if ($refNumber)
                        <h2 class="m-0 font-weight-bold text-primary d-flex justify-content-center">
                            <font color="red">Last transaction: {{ $refNumber }}</font>
                        </h2>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                </div>
            </div>

            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form id="cashPaymentForm" method="POST" action="{{ route('transaction.cashpaymentaction') }}">
                        @csrf

                        {{-- Payment To Account (DebitAccount) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="DebitAccount" class="control-label">
                                        Payment To Account<br>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="DebitAccount" id="DebitAccount" class="select2" style="width:250px;" required>
                                        <option value="">Select Account</option>
                                        @foreach ($drAccount as $row)
                                            <option value="{{ $row->AccountNo }}" @selected(old('DebitAccount') == $row->AccountNo)>
                                                [{{ $row->gl_group }}-{{ $row->gl_sub_group }}] {{ $row->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="creditsign" name="creditsign" value="1">
                                    
                                    @error('DebitAccount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Amount" class="control-label">Amount</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Amount" name="Amount" class="form-control" value="{{ old('Amount') }}">
                                    <input type="hidden" id="debitsign" name="debitsign" value="-1">
                                    <span>
                                        <font color="blue">Available Balance {{ number_format((float) ($cashBalance ?? 0), 2) }}</font>
                                    </span>
                                    @error('Amount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        {{-- Particular --}}
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

                        {{-- Transaction Date --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PostingDate" class="control-label">Transaction Date</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="PostingDate" name="PostingDate" class="form-control" value="{{ old('PostingDate') }}" autocomplete="off" required>
                                    @error('PostingDate')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- MO Number (Serial) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Serial" class="control-label">MO/Receipt Number</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Serial" name="Serial" class="form-control" value="{{ old('Serial') }}" required>
                                    @error('Serial')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Taken By (EmployeeID) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="EmployeeID" class="control-label">Taken By</label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="EmployeeID" id="EmployeeID" class="select2 form-control" style="width:240px;">
                                        <option value="">Select Employee</option>
                                        @foreach ($employeeID as $row)
                                            <option value="{{ $row->EmployeeID }}" @selected(old('EmployeeID') == $row->EmployeeID)>
                                                {{ $row->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('EmployeeID')
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
         NB:
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(function () {
            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#cashPaymentForm')
            });

            $('#PostingDate').datepicker({ dateFormat: 'dd-mm-yy' }).datepicker('setDate', new Date());

            $('#CreditAccount').on('change', function () {
                var accountNo = $(this).val();
                if (!accountNo) {
                    $('#availableBalanceText').text('Available Balance 0.00');
                    return;
                }
                $.get("{{ route('transaction.fetchAccountBal') }}", { AccountNo: accountNo }, function (res) {
                    var bal = parseFloat(res.balance || 0);
                    $('#availableBalanceText').text('Available Balance ' + bal.toFixed(2));
                });
            });
        });
    </script>
@endpush