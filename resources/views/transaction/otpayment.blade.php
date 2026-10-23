@extends('adminlte::page')

@section('title', 'OT Payment')

@section('plugins.Select2', true)

@push('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .card, .card-body { overflow: visible; }
        #PayeeID + .select2-container, #PatientID + .select2-container,
        #CreditAccount + .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single { height: 38px; padding: 4px 6px; }
        .ot-total { font-size: 1.15rem; font-weight: 700; }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-7 col-lg-7">

            <div class="row">
                <div class="col-sm-12">
                    <h6 class="Posting" style="font-size:2vw;">OT Payment</h6>

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

            @unless ($expenseAccountOk)
                <div class="alert alert-danger">
                    The OT expense account <strong>{{ $expenseAccountNo }}</strong> is not available as an
                    expenditure ledger, so no OT payment can be posted.
                    Add it in
                    <a href="{{ route('accounting.addaccount') }}" class="alert-link font-weight-bold text-dark">
                        Accounting &rarr; Add Account/Ledger
                    </a>, or set <code>ACCT_OT_EXPENSE_ACCOUNT</code> to a valid expenditure account.
                </div>
            @endunless

            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form id="otPaymentForm" method="POST" action="{{ route('transaction.saveOtPayment') }}">
                        @csrf

                        {{-- Payee Type --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PayeeType" class="control-label">Payee Type</label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="PayeeType" id="PayeeType" class="form-control" required>
                                        <option value="">Select Payee Type</option>
                                        @foreach ($payeeTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('PayeeType') == $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('PayeeType')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Payee Name (loaded per payee type) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PayeeID" class="control-label">Payee Name</label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="PayeeID" id="PayeeID" class="select2 form-control" style="width:250px;" required disabled>
                                        <option value="">Select Payee Type First</option>
                                    </select>
                                    <span>
                                        <font color="blue" id="payeeCount">0 name(s) available</font>
                                    </span>
                                    @error('PayeeID')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Patient (searchable) --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PatientID" class="control-label">Patient / Admission</label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="PatientID" id="PatientID" class="select2 form-control" style="width:250px;">
                                        <option value="">Search patient name, code or mobile</option>
                                    </select>
                                    <input type="hidden" name="AdmissionID" id="AdmissionID" value="{{ old('AdmissionID') }}">
                                    <span>
                                        <font color="blue" id="patientDetail"></font>
                                    </span>
                                    @error('PatientID')
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
                                    <input type="text" id="Amount" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
                                    <span>
                                        <font color="blue">Available Balance {{ number_format($cashBalance, 2) }}</font>
                                    </span>
                                    @error('Amount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Payment Date --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PaymentDate" class="control-label">Payment Date</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="PaymentDate" name="PaymentDate" class="form-control" value="{{ old('PaymentDate') }}" autocomplete="off" required>
                                    @error('PaymentDate')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="PaymentMethod" class="control-label">Payment Method</label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="PaymentMethod" id="PaymentMethod" class="form-control" required>
                                        <option value="">Select Method</option>
                                        @foreach (['Cash', 'Bank'] as $method)
                                            <option value="{{ $method }}" @selected(old('PaymentMethod') == $method)>
                                                {{ $method }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span>
                                        <font color="blue">Debit: OT Expense {{ $expenseAccountNo }}</font>
                                    </span>
                                    @error('PaymentMethod')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Pay From Bank Account (Bank only) --}}
                        <div class="form-group" id="bankAccountRow" style="display:none;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="CreditAccount" class="control-label">
                                        Pay From Bank Account<br>
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <select name="CreditAccount" id="CreditAccount" class="select2 form-control" style="width:250px;" disabled>
                                        <option value="">Select Bank Account</option>
                                        @foreach ($bankAccounts as $account)
                                            <option value="{{ $account->AccountNo }}" @selected(old('CreditAccount') == $account->AccountNo)>
                                                [{{ $account->gl_group }}-{{ $account->gl_sub_group }}] {{ $account->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('CreditAccount')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="Note" class="control-label">Note</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" id="Note" name="Note" class="form-control" value="{{ old('Note') }}">
                                    @error('Note')
                                        <font color="red"><div class="error1">{{ $message }}</div></font>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6"></div>
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-primary" @disabled(!$expenseAccountOk)>Save Payment</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LIST ================= --}}
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card card-outline card-danger">
                <div class="card-body">
                    <form method="GET" action="{{ route('transaction.otpayment') }}" class="form-row align-items-end mb-3">
                        <div class="col-md-3 mb-2">
                            <label>Payee Type</label>
                            <select name="payee_type" id="filterPayeeType" class="form-control form-control-sm">
                                <option value="">All</option>
                                @foreach ($payeeTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['payee_type'] == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Payee Name</label>
                            <select name="payee_id" id="filterPayeeID" class="form-control form-control-sm">
                                <option value="">All</option>
                                @foreach ($filterPayees as $payee)
                                    <option value="{{ $payee['id'] }}" @selected((string) $filters['payee_id'] === (string) $payee['id'])>{{ $payee['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Patient</label>
                            <select name="patient_id" class="form-control form-control-sm">
                                <option value="">All</option>
                                @foreach ($filterPatients as $row)
                                    <option value="{{ $row->patient_id }}" @selected((string) $filters['patient_id'] === (string) $row->patient_id)>
                                        {{ $row->patient_code }} - {{ $row->patient_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $filters['from_date'] }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $filters['to_date'] }}">
                        </div>
                        <div class="col-md-12 mb-2 d-flex align-items-center" style="gap:8px;">
                            <button class="btn btn-danger btn-sm"><i class="fas fa-search mr-1"></i> Filter</button>
                            <a href="{{ route('transaction.otpayment') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            <span class="ml-3">
                                Records: <strong>{{ $totalCount }}</strong>
                                &nbsp;|&nbsp;
                                Total: <strong class="ot-total text-danger">{{ number_format($totalAmount, 2) }}</strong>
                            </span>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Payee Type</th>
                                    <th>Payee Name</th>
                                    <th>Patient</th>
                                    <th>Method</th>
                                    <th>Note</th>
                                    <th>Dr Account</th>
                                    <th>Cr Account</th>
                                    <th>Ref No.</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $payment->payeeTypeLabel() }}</td>
                                        <td>{{ $payment->payee_name ?: '-' }}</td>
                                        <td>
                                            @if ($payment->patient_code)
                                                {{ $payment->patient_code }} - {{ $payment->patient_name ?: '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $payment->payment_method }}</td>
                                        <td>{{ $payment->note ?: '-' }}</td>
                                        <td>{{ $payment->expense_account }}</td>
                                        <td>{{ $payment->credit_account }}</td>
                                        <td>{{ $payment->ref_number ?: '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $payment->isPosted() ? 'success' : 'warning' }}">
                                                {{ $payment->isPosted() ? 'Posted' : 'Unauthorized' }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold">{{ number_format((float) $payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="text-center text-muted py-4">No OT payment record found.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($payments->count())
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="10" class="text-right">Total ({{ $totalCount }} record(s))</th>
                                        <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                @if($payments->hasPages())
                    <div class="card-footer">{{ $payments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(function () {
            var payeesUrl   = "{{ route('transaction.otpayment.payees') }}";
            var patientsUrl = "{{ route('transaction.otpayment.patients') }}";

            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#otPaymentForm')
            });

            $('#PaymentDate').datepicker({ dateFormat: 'dd-mm-yy' }).datepicker('setDate', new Date());

            // ------------------------------------------------------------------
            // Payee Name: reloads from the payee type's own master every time the
            // type changes, so names can never carry over from another role.
            // ------------------------------------------------------------------
            function resetPayeeNames(message) {
                var $payee = $('#PayeeID');
                $payee.empty().append(
                    $('<option>', { value: '', text: message })
                ).prop('disabled', true).trigger('change.select2');
                $('#payeeCount').text('0 name(s) available');
            }

            function loadPayeeNames(type, selectedId) {
                if (!type) {
                    resetPayeeNames('Select Payee Type First');
                    return;
                }

                resetPayeeNames('Loading...');
                $('#payeeCount').text('Loading...');

                $.get(payeesUrl, { payee_type: type })
                    .done(function (res) {
                        var items = (res && res.data) ? res.data : [];
                        var $payee = $('#PayeeID');

                        $payee.empty().append($('<option>', { value: '', text: 'Select Payee' }));

                        items.forEach(function (item) {
                            $payee.append($('<option>', { value: item.id, text: item.name }));
                        });

                        if (selectedId) {
                            $payee.val(String(selectedId));
                        }

                        $payee.prop('disabled', items.length === 0).trigger('change.select2');
                        $('#payeeCount').text(items.length + ' name(s) available');

                        if (items.length === 0) {
                            $payee.append($('<option>', { value: '', text: 'No name for this payee type' }));
                        }
                    })
                    .fail(function () {
                        resetPayeeNames('Could not load names');
                        $('#payeeCount').text('');
                    });
            }

            $('#PayeeType').on('change', function () {
                loadPayeeNames($(this).val(), null);
            });

            // Restore the rejected selection so nothing silently disappears.
            @if (old('PayeeType'))
                loadPayeeNames('{{ old('PayeeType') }}', '{{ old('PayeeID') }}');
            @else
                resetPayeeNames('Select Payee Type First');
            @endif

            // ------------------------------------------------------------------
            // Patient: searchable Select2, carries the patient's latest admission.
            // ------------------------------------------------------------------
            $('#PatientID').select2({
                width: '100%',
                dropdownParent: $('#otPaymentForm'),
                placeholder: 'Search patient name, code or mobile',
                allowClear: true,
                ajax: {
                    url: patientsUrl,
                    type: 'GET',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term || '' };
                    },
                    processResults: function (res) {
                        return {
                            results: ((res && res.data) ? res.data : []).map(function (row) {
                                var label = row.patientcode + ' - ' + row.patientname;
                                if (row.admissionid) {
                                    label += ' (Adm: ' + row.admissionid + ')';
                                }
                                return { id: row.id, text: label, row: row };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function (item) {
                    if (!item.id) { return item.text; }

                    var row = item.row || {};
                    var head = $('<div>').text(item.text);
                    if (row.mobile_no) {
                        head.append($('<div>', { class: 'text-muted', style: 'font-size:85%;', text: 'Mobile: ' + row.mobile_no }));
                    }
                    return head;
                },
                templateSelection: function (item) {
                    return item.id ? item.text : item.text;
                }
            });

            function syncPatient(row) {
                $('#AdmissionID').val(row && row.admission_id ? row.admission_id : '');
                $('#patientDetail').text(row
                    ? 'Adm: ' + (row.admissionid || 'n/a')
                    : '');
            }

            $('#PatientID').on('select2:select', function (e) {
                syncPatient(e.params.data.row);
            });

            $('#PatientID').on('select2:clear', function () {
                syncPatient(null);
            });

            @if ($oldPatient)
                // Re-add the rejected patient so old() survives the redirect.
                $('#PatientID').append(
                    $('<option>', {
                        value: '{{ $oldPatient->id }}',
                        text: @json(trim($oldPatient->patientcode . ' - ' . $oldPatient->patientname, ' -')),
                        selected: true
                    })
                ).val('{{ $oldPatient->id }}').trigger('change.select2');
                $('#AdmissionID').val('{{ old('AdmissionID') }}');
            @endif

            // ------------------------------------------------------------------
            // Payment Method: reveal the bank ledger only for Bank.
            // ------------------------------------------------------------------
            function toggleBankAccount() {
                var isBank = $('#PaymentMethod').val() === 'Bank';
                $('#bankAccountRow').toggle(isBank);
                $('#CreditAccount').prop('disabled', !isBank).prop('required', isBank);
                if (!isBank) {
                    $('#CreditAccount').val('');
                }
            }

            $('#PaymentMethod').on('change', toggleBankAccount);
            toggleBankAccount();
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endpush