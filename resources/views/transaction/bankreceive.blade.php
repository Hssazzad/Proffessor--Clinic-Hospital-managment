@extends('adminlte::page')

@section('title', 'Bank Receive')

@section('plugins.Select2', true)

@section('content_header')
    <h6 class="Posting" style="font-size:2vw;">Bank Receive</h6>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-7 col-lg-7">

            <div class="row">
                <div class="col-sm-12">
                    <h2 class="m-0 font-weight-bold text-primary d-flex justify-content-center">
                        <font color="red">Last transaction: {{ $refNumber ?? session('refNumber') }}</font>
                    </h2>

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

                    <form id="bankReceiveForm" method="POST" action="{{ route('transaction.saveBankReceive') }}">
                        @csrf

                        {{-- Deposit Into Bank Account (DebitAccount) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="DebitAccount" class="control-label">Deposit Into Bank Account</label>
                                </div>
                                <div class="col-sm-6">
                                    <select id="DebitAccount" name="DebitAccount" class="select2 form-control" style="width:250px;" required>
                                        <option value="">Select Account</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->AccountNo }}" @selected(old('DebitAccount') == $account->AccountNo)>
                                                [{{ $account->gl_group }}-{{ $account->gl_sub_group }}] {{ $account->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="debitsign" name="debitsign" class="form-control" value="-1">
                                    <br>
                                    <span>
                                        <font color="blue">Available Balance <span id="bankBalDisplay">0.00</span></font>
                                    </span>
                                    @error('DebitAccount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Receive From Account (CreditAccount) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="CreditAccount" class="control-label">Receive From Account</label>
                                </div>
                                <div class="col-sm-6">
                                    <select id="CreditAccount" name="CreditAccount" class="select2 form-control" style="width:250px;" required>
                                        <option value="">Select Account</option>
                                        @foreach($crAccounts as $row)
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

                        {{-- Cheque / Slip / Ref No (Serial) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Serial" class="control-label">Cheque / Slip / Ref Number</label>
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

                        {{-- Customer Bank & Branch --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="BankName" class="control-label">Customer Bank & Branch</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="BankName" name="BankName" class="form-control" value="{{ old('BankName') }}">
                                    @error('BankName')
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
                                    <input type="text" id="PostingDate" name="PostingDate" class="form-control" value="{{ old('PostingDate', date('d-m-Y')) }}" readonly required>
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
@stop

@push('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endpush

@push('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $(function () {
        $("#PostingDate").datepicker({ dateFormat: "dd-mm-yy" }).datepicker("setDate", new Date());
        $("#ChequeDate").datepicker({ dateFormat: "dd-mm-yy" });
        $(".select2").select2({ width: '100%', dropdownParent: $('#bankReceiveForm') });

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

        $('#DebitAccount').on('change', function () {
            fetchBal($(this).val());
        });

        if ($('#DebitAccount').val()) {
            fetchBal($('#DebitAccount').val());
        }
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
@endpush
