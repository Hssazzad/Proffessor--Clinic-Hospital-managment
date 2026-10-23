@extends('adminlte::page')

@section('title', 'Bank Payment')

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
                    <h6 class="Posting" style="font-size:2vw;">Bank Payment</h6>

                    @if ($refNumber ?? session('refNumber'))
                        <h2 class="m-0 font-weight-bold text-primary d-flex justify-content-center">
                            <font color="red">Last transaction: {{ $refNumber ?? session('refNumber') }}</font>
                        </h2>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                </div>
            </div>

            @if ($bankAccounts->isEmpty())
                <div class="alert alert-warning">
                    No bank accounts have been configured under <strong>GL Group 202 (Bank)</strong> yet.
                    Please add your bank account in
                    <a href="{{ route('accounting.addaccount') }}" class="alert-link font-weight-bold text-dark">
                        Accounting &rarr; Add Account/Ledger
                    </a>.
                </div>
            @endif

            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form id="bankPaymentForm" method="POST" action="{{ route('transaction.saveBankPayment') }}">
                        @csrf

                        {{-- Pay From Bank Account (CreditAccount) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="CreditAccount" class="control-label">
                                        Pay From Bank Account<br>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="CreditAccount" id="CreditAccount" class="select2" style="width:250px;" required>
                                        <option value="">Select Account</option>
                                        @foreach ($bankAccounts as $account)
                                            <option value="{{ $account->AccountNo }}" @selected(old('CreditAccount') == $account->AccountNo)>
                                                [{{ $account->gl_group }}-{{ $account->gl_sub_group }}] {{ $account->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="creditsign" name="creditsign" value="1">
                                    <br>
                                    <span>
                                        <font color="blue">Available Balance <span id="bankBalDisplay">0.00</span></font>
                                    </span>
                                    @error('CreditAccount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

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
                                        @foreach ($drAccounts as $row)
                                            <option value="{{ $row->AccountNo }}" @selected(old('DebitAccount') == $row->AccountNo)>
                                                [{{ $row->gl_group }}-{{ $row->gl_sub_group }}] {{ $row->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="debitsign" name="debitsign" value="-1">
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
                                    @error('Amount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Cheque / Ref Number (Serial) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Serial" class="control-label">Cheque / Ref Number</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Serial" name="Serial" class="form-control" value="{{ old('Serial') }}">
                                    @error('Serial')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Cheque Date --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="ChequeDate" class="control-label">Cheque Date</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="ChequeDate" name="ChequeDate" class="form-control" value="{{ old('ChequeDate') }}" autocomplete="off">
                                    @error('ChequeDate')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Beneficiary Bank --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="BankName" class="control-label">Beneficiary Bank / Details</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="BankName" name="BankName" class="form-control" value="{{ old('BankName') }}">
                                    @error('BankName')
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

                        {{-- Taken By (EmployeeID) --}}
                        @if(isset($employeeID) && $employeeID->isNotEmpty())
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
                        @endif

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
@endsection

@push('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(function () {
            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#bankPaymentForm')
            });

            $('#PostingDate').datepicker({ dateFormat: 'dd-mm-yy' }).datepicker('setDate', new Date());
            $('#ChequeDate').datepicker({ dateFormat: 'dd-mm-yy' });

            function fetchBal(accountNo) {
                if (!accountNo) {
                    $('#bankBalDisplay').text('0.00');
                    return;
                }
                $.get("{{ route('transaction.fetchAccountBal') }}", { AccountNo: accountNo }, function (res) {
                    var bal = parseFloat(res.balance || 0);
                    $('#bankBalDisplay').text(bal.toFixed(2));
                }).fail(function () {
                    $('#bankBalDisplay').text('0.00');
                });
            }

            $('#CreditAccount').on('change', function () {
                fetchBal($(this).val());
            });

            if ($('#CreditAccount').val()) {
                fetchBal($('#CreditAccount').val());
            }
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endpush
