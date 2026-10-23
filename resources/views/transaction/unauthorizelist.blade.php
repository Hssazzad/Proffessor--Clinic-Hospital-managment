@extends('adminlte::page')

@section('title', 'Unauthorized Transactions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks mr-2"></i>Unauthorized Transaction List
            </h1>
            <small class="text-muted">Review, authorize, or delete pending cash, bank, multiple-receive and transfer transactions</small>
        </div>
        <div>
            <a href="{{ route('transaction.cashpayment') }}" class="btn btn-outline-danger btn-sm mr-1">
                <i class="fas fa-minus-circle mr-1"></i> Cash Payment
            </a>
            <a href="{{ route('transaction.cashreceive') }}" class="btn btn-outline-success btn-sm mr-1">
                <i class="fas fa-plus-circle mr-1"></i> Cash Receive
            </a>
            <a href="{{ route('transaction.multiplecashreceive') }}" class="btn btn-outline-success btn-sm mr-1">
                <i class="fas fa-receipt mr-1"></i> Multiple Receive
            </a>
            <a href="{{ route('transaction.bankpayment') }}" class="btn btn-outline-warning btn-sm mr-1">
                <i class="fas fa-university mr-1"></i> Bank Payment
            </a>
            <a href="{{ route('transaction.bankreceive') }}" class="btn btn-outline-primary btn-sm mr-1">
                <i class="fas fa-university mr-1"></i> Bank Receive
            </a>
            <a href="{{ route('transaction.transferposting') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-exchange-alt mr-1"></i> Transfer Posting
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('transaction.unauthorizelist') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="form-label font-weight-normal mb-1">From Date</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" name="from_date" id="from_date" class="form-control datepicker" 
                                   placeholder="dd-mm-yyyy" value="{{ $fromDate ?? '' }}" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="form-label font-weight-normal mb-1">To Date</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" name="to_date" id="to_date" class="form-control datepicker" 
                                   placeholder="dd-mm-yyyy" value="{{ $toDate ?? '' }}" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="form-label font-weight-normal mb-1">Type</label>
                        <select name="screen_type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            <option value="CP" {{ ($screenType ?? '') == 'CP' ? 'selected' : '' }}>Cash Payment (CP)</option>
                            <option value="CR" {{ ($screenType ?? '') == 'CR' ? 'selected' : '' }}>Cash Receive (CR)</option>
                            <option value="MCR" {{ ($screenType ?? '') == 'MCR' ? 'selected' : '' }}>Multiple Cash Receive (MCR)</option>
                            <option value="MCP" {{ ($screenType ?? '') == 'MCP' ? 'selected' : '' }}>Multiple Cash Payment (MCP)</option>
                            <option value="BP" {{ ($screenType ?? '') == 'BP' ? 'selected' : '' }}>Bank Payment (BP)</option>
                            <option value="BR" {{ ($screenType ?? '') == 'BR' ? 'selected' : '' }}>Bank Receive (BR)</option>
                            <option value="TP" {{ ($screenType ?? '') == 'TP' ? 'selected' : '' }}>Transfer (TP)</option>
                            <option value="OTP" {{ ($screenType ?? '') == 'OTP' ? 'selected' : '' }}>OT Payment (OTP)</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="form-label font-weight-normal mb-1">Keyword</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Ref / Particular / Account" value="{{ $search ?? '' }}">
                    </div>

                    <div class="col-md-2 col-sm-12 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('transaction.unauthorizelist') }}" class="btn btn-default btn-sm" title="Reset">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-list mr-1"></i> Pending Transactions ({{ $transactions->total() }})
                </h3>
            </div>
            <div class="card-tools d-flex align-items-center">
                <span class="badge badge-warning p-2 mr-3 font-weight-bold" style="font-size: 13px;">
                    Total Unauth Amount: Tk. {{ number_format((float) ($totalAmount ?? 0), 2) }}
                </span>
                <button type="button" class="btn btn-success btn-sm" id="btnBatchAuthorize" disabled>
                    <i class="fas fa-check-double mr-1"></i> Authorize Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped table-bordered text-nowrap mb-0 align-middle">
                <thead class="thead-dark text-center" style="font-size: 13px;">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="checkAll" title="Select All">
                        </th>
                        <th width="50">#</th>
                        <th width="100">Ref No</th>
                        <th width="100">Date</th>
                        <th width="70">Type</th>
                        <th class="text-left">Debit Account</th>
                        <th class="text-left">Credit Account</th>
                        <th class="text-left">Particulars</th>
                        <th width="110" class="text-right">Amount (Tk.)</th>
                        <th width="90">Inputer</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @php $batchCounter = 0; @endphp
                    @forelse($transactions as $index => $row)
                        @php
                            $typeBadge = match($row->ScreenType ?? '') {
                                'CP' => '<span class="badge badge-danger">Cash Payment</span>',
                                'CR' => '<span class="badge badge-success">Cash Receive</span>',
                                'MCR' => '<span class="badge badge-success">Multiple Receive</span>',
                                'MCP' => '<span class="badge badge-danger">Multiple Payment</span>',
                                'BP' => '<span class="badge badge-warning text-dark">Bank Payment</span>',
                                'BR' => '<span class="badge badge-primary">Bank Receive</span>',
                                'TP' => '<span class="badge badge-info">Transfer</span>',
                                'OTP' => '<span class="badge badge-danger">OT Payment</span>',
                                default => '<span class="badge badge-secondary">' . ($row->ScreenType ?? 'N/A') . '</span>',
                            };
                            $receiptNo = trim((string) ($row->Serial ?? '')) !== '' ? trim((string) $row->Serial) : trim((string) ($row->MoNumber ?? ''));
                            $isGrouped = !empty($row->_is_group) && in_array($row->ScreenType ?? '', ['MCR', 'MCP'], true);
                            $isMcr = in_array($row->ScreenType ?? '', ['MCR', 'MCP'], true) && $receiptNo !== '';
                            if ($isGrouped) { $batchCounter++; }
                        @endphp
                        <tr @if($isGrouped) style="background-color: #fff9e6 !important;" @endif>
                            <td class="text-center">
                                @if($isGrouped)
                                    <input type="checkbox" class="row-checkbox" value="{{ $row->_grouped_refs[0] ?? $row->RefNumber }}"
                                           data-batch-refs="{{ implode(',', $row->_grouped_refs ?? [$row->RefNumber]) }}">
                                @else
                                    <input type="checkbox" class="row-checkbox" value="{{ $row->RefNumber }}">
                                @endif
                            </td>
                            <td class="text-center font-weight-bold">
                                {{ $transactions->firstItem() + $index }}
                            </td>
                            <td class="text-center">
                                @if($isGrouped)
                                    <span class="badge badge-warning px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                        <i class="fas fa-layer-group mr-1"></i>Batch{{ str_pad($batchCounter, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <small class="d-block mt-1">
                                        <span class="badge badge-info px-2 py-1" title="Batch of {{ $row->_grouped_count ?? 1 }} items — Receipt #{{ $receiptNo }}">
                                            <i class="fas fa-receipt mr-1"></i>Receipt #{{ $receiptNo }}
                                        </span>
                                    </small>
                                    <small class="d-block text-muted">{{ $row->_grouped_count ?? 1 }} items</small>
                                @else
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                        #{{ $row->RefNumber }}
                                    </span>
                                    @if($isMcr)
                                        <small class="d-block mt-1">
                                            <span class="badge badge-info px-2 py-1" title="Part of receipt #{{ $receiptNo }} — clicking Authorize reviews the whole receipt as one payment">
                                                <i class="fas fa-receipt mr-1"></i>Receipt #{{ $receiptNo }}
                                            </span>
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $row->PostingDate ? date('d-m-Y', strtotime($row->PostingDate)) : '-' }}
                            </td>
                            <td class="text-center">
                                {!! $typeBadge !!}
                            </td>
                            @if($isGrouped)
                                <td>
                                    <span class="badge badge-danger px-2 py-1">
                                        <i class="fas fa-sign-out-alt mr-1"></i>Batch Debit Account
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fas fa-sign-in-alt mr-1"></i>Batch Credit Account
                                    </span>
                                </td>
                            @else
                                <td>
                                    <strong class="text-dark">[{{ $row->DrAccount }}]</strong> 
                                    <span class="text-muted">{{ $row->dr_title ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark">[{{ $row->CrAccount }}]</strong> 
                                    <span class="text-muted">{{ $row->cr_title ?? 'N/A' }}</span>
                                </td>
                            @endif
                            <td>
                                {{ $row->Particular ?? '-' }}
                                @if(!empty($row->Serial) || !empty($row->MoNumber))
                                    <small class="text-muted d-block">MO/Serial: {{ $row->Serial ?? $row->MoNumber }}</small>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold text-primary" style="font-size: 14px;">
                                @if($isGrouped)
                                    {{ number_format((float) ($row->_grouped_total ?? $row->amount ?? 0), 2) }}
                                    <small class="d-block text-muted" style="font-size: 11px;">Total Amount</small>
                                @else
                                    {{ number_format((float) ($row->amount ?? 0), 2) }}
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary px-2 py-1">
                                    {{ $row->InputerID ?: '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('transaction.authorizereview', ['ref_number' => $row->RefNumber]) }}"
                                   class="btn btn-success btn-xs" title="Review and authorize this transaction">
                                    <i class="fas fa-check"></i> Authorize
                                </a>

                                @if($isGrouped)
                                    <form method="POST" action="{{ route('transaction.unauthorize.delete') }}" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to DELETE/REJECT Batch{{ str_pad($batchCounter, 2, '0', STR_PAD_LEFT) }} ({{ $row->_grouped_count ?? 1 }} items)? This cannot be undone.');">
                                        @csrf
                                        <input type="hidden" name="ref_number" value="{{ implode(',', $row->_grouped_refs ?? [$row->RefNumber]) }}">
                                        <button type="submit" class="btn btn-outline-danger btn-xs" title="Delete entire batch">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('transaction.unauthorize.delete') }}" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to DELETE/REJECT Transaction #{{ $row->RefNumber }}? This cannot be undone.');">
                                        @csrf
                                        <input type="hidden" name="ref_number" value="{{ $row->RefNumber }}">
                                        <button type="submit" class="btn btn-outline-danger btn-xs" title="Delete transaction">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5>No Unauthorized Transactions Found</h5>
                                    <p class="mb-0">All transactions have been authorized or none match your filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <div class="text-muted small">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                </div>
                <div>
                    {{ $transactions->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Hidden Bulk Review Redirect --}}
<form id="bulkReviewForm" method="GET" action="{{ route('transaction.authorizereview') }}" style="display: none;">
    <input type="hidden" name="ref_numbers" id="bulkRefNumbers">
</form>

@stop

@section('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .table td, .table th { vertical-align: middle !important; }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function() {
        $(".datepicker").datepicker({
            dateFormat: "dd-mm-yy"
        });

        // Select all checkboxes
        $("#checkAll").on("change", function() {
            $(".row-checkbox").prop("checked", $(this).is(":checked"));
            updateSelectedCount();
        });

        // Individual checkbox change
        $(document).on("change", ".row-checkbox", function() {
            updateSelectedCount();
            if (!$(this).is(":checked")) {
                $("#checkAll").prop("checked", false);
            } else if ($(".row-checkbox:checked").length === $(".row-checkbox").length) {
                $("#checkAll").prop("checked", true);
            }
        });

        function updateSelectedCount() {
            var count = $(".row-checkbox:checked").length;
            $("#selectedCount").text(count);
            $("#btnBatchAuthorize").prop("disabled", count === 0);
        }

        // Batch authorize click -> go to review page first
        $("#btnBatchAuthorize").on("click", function() {
            var selected = [];
            $(".row-checkbox:checked").each(function() {
                // For batch rows, use all refs from data-batch-refs
                var batchRefs = $(this).data("batch-refs");
                if (batchRefs) {
                    var refs = String(batchRefs).split(",");
                    for (var i = 0; i < refs.length; i++) {
                        if (refs[i] && selected.indexOf(refs[i]) === -1) {
                            selected.push(refs[i]);
                        }
                    }
                } else {
                    selected.push($(this).val());
                }
            });

            if (selected.length === 0) {
                alert("Please select at least one transaction to authorize.");
                return;
            }

            $("#bulkRefNumbers").val(selected.join(","));
            $("#bulkReviewForm").submit();
        });
    });
</script>
@stop
