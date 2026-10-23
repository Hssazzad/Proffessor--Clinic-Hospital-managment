@extends('adminlte::page')

@section('title', 'Transfer Posting')

@section('plugins.Select2', true)

@section('content_header')
    <h1 class="m-0 font-weight-bold text-primary">Transfer Posting</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        Transfer Posting
                        <font color='red' class="ml-2">Last transaction: {{ $refNumber }}</font>
                    </h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('transaction.transferpostingaction') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="DebitAccount" class="control-label">Debit Account</label>
                            <select id="DebitAccount" name="DebitAccount" class="form-control select2" required>
                                <option value="">Select Debit Account</option>
                                @foreach ($AccountNo as $account)
                                    <option value="{{ $account->AccountNo }}" {{ old('DebitAccount') == $account->AccountNo ? 'selected' : '' }}>
                                        {{ $account->title }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="debitsign" name="debitsign" class="form-control" value="-1">
                            @error('DebitAccount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="Amount" class="control-label">Amount</label>
                            <input type="text" id="Amount" name="Amount" class="form-control" value="{{ old('Amount') }}">
                            @error('Amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="CreditAccount" class="control-label">Credit Account</label>
                            <select id="CreditAccount" name="CreditAccount" class="form-control select2" required>
                                <option value="">Select Credit Account</option>
                                @foreach ($AccountNo as $account)
                                    <option value="{{ $account->AccountNo }}" {{ old('CreditAccount') == $account->AccountNo ? 'selected' : '' }}>
                                        {{ $account->title }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="creditsign" name="creditsign" class="form-control" value="1">
                            @error('CreditAccount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="Particular" class="control-label">Transaction Description</label>
                            <input type="text" id="Particular" name="Particular" class="form-control" value="{{ old('Particular') }}">
                            @error('Particular')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="PostingDate" class="control-label">Transaction Date</label>
                            <input type="text" id="PostingDate" name="PostingDate" class="form-control" value="{{ old('PostingDate', now()->format('d-m-Y')) }}" readonly>
                            @error('PostingDate')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="Serial" class="control-label">Serial No</label>
                            <input type="text" id="Serial" name="Serial" class="form-control" value="{{ old('Serial') }}">
                        </div>

                      

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">Save Transaction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(function () {
            $("#PostingDate").datepicker({ dateFormat: "dd-mm-yy"}).datepicker("setDate", new Date());
            $(".select2").select2({ width: '100%', dropdownParent: $(document.body) });
        });
    </script>
@endpush
