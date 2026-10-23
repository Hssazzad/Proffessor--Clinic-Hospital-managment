@extends('adminlte::page')

@section('title', 'Authorize Transaction Review')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-check mr-2"></i>Transaction Authorization Review
            </h1>
            <small class="text-muted">Review debit and credit details before final authorization</small>
        </div>
        <div>
            <a href="{{ route('transaction.unauthorizelist') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">

    @php
        $typeLabelFor = function ($screenType) {
            return match($screenType ?? '') {
                'CP' => 'Cash Payment',
                'CR' => 'Cash Receive',
                'MCR' => 'Multiple Cash Receive',
                'MCP' => 'Multiple Cash Payment',
                'TP' => 'Transfer Posting',
                'BP' => 'Bank Payment',
                'BR' => 'Bank Receive',
                'OTP' => 'OT Payment',
                default => $screenType ?? 'N/A',
            };
        };
        // Fallback for older controller data without grouping.
        $groups = $receiptGroups ?? null;
        if (empty($groups)) {
            $groups = [];
            foreach (($transactions ?? []) as $single) {
                $s = $single['summary'];
                $groups[] = [
                    'isMultiple' => false,
                    'screenType' => (string) ($s->ScreenType ?? ''),
                    'serial' => trim((string) ($s->Serial ?? $s->MoNumber ?? '')),
                    'date' => $s->PostingDate ?? null,
                    'inputer' => $s->InputerID ?? null,
                    'items' => [$single],
                    'total' => (float) ($s->amount ?? 0),
                ];
            }
        }
    @endphp

    @foreach($groups as $group)
        @if(in_array($group['screenType'] ?? '', ['MCR', 'MCP'], true))
            @php
                $items = $group['items'];
                $firstSummary = $items[0]['summary'];
                $firstDebit = $items[0]['debit'];
                $serial = $group['serial'] !== '' ? $group['serial'] : ($firstSummary->Serial ?? $firstSummary->MoNumber ?? '-');
                $refs = collect($items)->map(fn($it) => $it['summary']->RefNumber)->values()->all();
                $isMcp = ($group['screenType'] ?? '') === 'MCP';
                $firstCredit = $items[0]['credit'];
                $cashAccNo = $isMcp
                    ? ($firstSummary->CrAccount ?? ($firstCredit->AccountNo ?? '-'))
                    : ($firstSummary->DrAccount ?? ($firstDebit->AccountNo ?? '-'));
                $cashAccName = $isMcp
                    ? ($firstSummary->cr_title ?? ($firstCredit->account_title ?? 'N/A'))
                    : ($firstSummary->dr_title ?? ($firstDebit->account_title ?? 'N/A'));
            @endphp
            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        @if($group['serial'] !== '') Receipt #{{ $serial }} @else Transaction #{{ implode(', #', $refs) }} @endif
                        <span class="badge badge-info ml-2">{{ $typeLabelFor($group['screenType']) }}</span>
                        @if(count($items) > 1)<span class="badge badge-warning ml-2">{{ count($items) }} items</span>@endif
                    </h3>
                    <div class="card-tools"><small class="text-muted">Txn #{{ implode(', #', $refs) }}</small></div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Date:</strong> {{ !empty($group['date']) ? date('d-m-Y', strtotime($group['date'])) : '-' }}</div>
                        <div class="col-md-3"><strong>Total Amount:</strong> <span class="text-primary font-weight-bold">Tk. {{ number_format((float) $group['total'], 2) }}</span></div>
                        <div class="col-md-3"><strong>MO/Serial:</strong> {{ $serial }}</div>
                        <div class="col-md-3"><strong>Inputer:</strong> {{ $group['inputer'] ?: '-' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card card-outline card-danger h-100">
                                <div class="card-header py-2"><h5 class="card-title mb-0 font-weight-bold">Debit Transaction — {{ $isMcp ? 'Total' : 'Cash (Total)' }}</h5></div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            @if($isMcp)
                                                <tr><th width="40%">Total Lines</th><td>{{ count($items) }} items</td></tr>
                                                <tr><th>Account Name</th><td>Multiple Debit Accounts</td></tr>
                                            @else
                                                <tr><th width="40%">Account No</th><td>{{ $cashAccNo }}</td></tr>
                                                <tr><th>Account Name</th><td>{{ $cashAccName }}</td></tr>
                                            @endif
                                            <tr><th>Debit Amount</th><td class="font-weight-bold text-danger">Tk. {{ number_format((float) $group['total'], 2) }}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-success h-100">
                                <div class="card-header py-2"><h5 class="card-title mb-0 font-weight-bold">Credit Transaction — {{ $isMcp ? 'Cash (Total)' : 'Total' }}</h5></div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            @if($isMcp)
                                                <tr><th width="40%">Account No</th><td>{{ $cashAccNo }}</td></tr>
                                                <tr><th>Account Name</th><td>{{ $cashAccName }}</td></tr>
                                            @else
                                                <tr><th width="40%">Total Lines</th><td>{{ count($items) }} items</td></tr>
                                                <tr><th>Account Name</th><td>Multiple Credit Accounts</td></tr>
                                            @endif
                                            <tr><th>Credit Amount</th><td class="font-weight-bold text-success">Tk. {{ number_format((float) $group['total'], 2) }}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-success">
                        <div class="card-header py-2"><h5 class="card-title mb-0 font-weight-bold">Itemwise Breakup — {{ $isMcp ? 'Debit' : 'Credit' }} Lines</h5></div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-sm table-bordered table-striped mb-0">
                                <thead class="text-center" style="font-size: 13px;">
                                    <tr>
                                        <th width="40">#</th>
                                        <th width="90">Txn Ref</th>
                                        <th width="130">Account No</th>
                                        <th class="text-left">Account Name</th>
                                        <th class="text-left">Description</th>
                                        <th width="140" class="text-right">Amount (Tk.)</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 13px;">
                                    @foreach($items as $i => $it)
                                        @php
                                            $cs = $it['summary'];
                                            $line = $isMcp ? $it['debit'] : $it['credit'];
                                            $lineAccNo = $isMcp ? $cs->DrAccount : $cs->CrAccount;
                                            $lineAccName = $isMcp ? ($cs->dr_title ?? null) : ($cs->cr_title ?? null);
                                            $lineAmount = $isMcp ? ($line->DrAmount ?? null) : ($line->CrAmount ?? null);
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold">{{ $i + 1 }}</td>
                                            <td class="text-center">#{{ $cs->RefNumber }}</td>
                                            <td class="text-center">{{ $lineAccNo ?? ($line->AccountNo ?? '-') }}</td>
                                            <td>{{ $lineAccName ?? ($line->account_title ?? 'N/A') }}</td>
                                            <td>{{ $cs->Particular ?: '-' }}</td>
                                            <td class="text-right font-weight-bold {{ $isMcp ? 'text-danger' : 'text-success' }}">{{ number_format((float) ($lineAmount ?? $cs->amount ?? 0), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">{{ $isMcp ? 'Total Payment:' : 'Total Receive:' }}</th>
                                        <th class="text-right text-primary">Tk. {{ number_format((float) $group['total'], 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @php
                $item = $group['items'][0];
                $summary = $item['summary'];
                $debit = $item['debit'];
                $credit = $item['credit'];
                $typeLabel = $typeLabelFor($summary->ScreenType ?? '');
            @endphp

            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        Transaction #{{ $summary->RefNumber }}
                        <span class="badge badge-info ml-2">{{ $typeLabel }}</span>
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Date:</strong> {{ $summary->PostingDate ? date('d-m-Y', strtotime($summary->PostingDate)) : '-' }}</div>
                        <div class="col-md-3"><strong>Amount:</strong> <span class="text-primary font-weight-bold">Tk. {{ number_format((float) ($summary->amount ?? 0), 2) }}</span></div>
                        <div class="col-md-3"><strong>MO/Serial:</strong> {{ $summary->Serial ?? $summary->MoNumber ?? '-' }}</div>
                        <div class="col-md-3"><strong>Inputer:</strong> {{ $summary->InputerID ?: '-' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Particular:</strong> {{ $summary->Particular ?? '-' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-danger h-100">
                                <div class="card-header py-2">
                                    <h5 class="card-title mb-0 font-weight-bold">Debit Transaction</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Account No</th>
                                                <td>{{ $summary->DrAccount ?? ($debit->AccountNo ?? '-') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Account Name</th>
                                                <td>{{ $summary->dr_title ?? ($debit->account_title ?? 'N/A') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Debit Amount</th>
                                                <td class="font-weight-bold text-danger">Tk. {{ number_format((float) ($debit->DrAmount ?? $summary->amount ?? 0), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Balance After</th>
                                                <td>Tk. {{ number_format((float) ($debit->Balance ?? 0), 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-success h-100">
                                <div class="card-header py-2">
                                    <h5 class="card-title mb-0 font-weight-bold">Credit Transaction</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Account No</th>
                                                <td>{{ $summary->CrAccount ?? ($credit->AccountNo ?? '-') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Account Name</th>
                                                <td>{{ $summary->cr_title ?? ($credit->account_title ?? 'N/A') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Credit Amount</th>
                                                <td class="font-weight-bold text-success">Tk. {{ number_format((float) ($credit->CrAmount ?? $summary->amount ?? 0), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Balance After</th>
                                                <td>Tk. {{ number_format((float) ($credit->Balance ?? 0), 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="card card-outline card-primary">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div class="text-muted mb-2 mb-md-0">
                <i class="fas fa-info-circle mr-1"></i>
                Please verify all details above before authorization.
            </div>
            <div>
                <a href="{{ route('transaction.unauthorizelist') }}" class="btn btn-default mr-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
                <form method="POST" action="{{ route('transaction.authorize') }}" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to authorize {{ count($refNumbers) }} transaction(s)?');">
                    @csrf
                    <input type="hidden" name="ref_numbers" value="{{ implode(',', $refNumbers) }}">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-double mr-1"></i>
                        Authorize {{ count($refNumbers) > 1 ? count($refNumbers) . ' Transactions' : 'Transaction' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@stop