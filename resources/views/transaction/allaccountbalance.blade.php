@extends('adminlte::page')

@section('title', config('app.name') . ' - All Account Ledger')
@section('content_header')
    <h1 class="all-ledger-title m-0 text-dark">All Account Ledger</h1>
@stop

@section('content')
<div class="container-fluid">
<div class="card card-outline card-success shadow-sm all-ledger-card">
    <div class="card-body all-ledger-body">
        <form method="get" action="{{ url()->current() }}">
            <div class="report-field">
                <label for="report_date">Date</label>
                <input type="text" id="report_date" name="report_date" class="datepicker" autocomplete="off" value="{{ $reportDate }}">
            </div>
            <div class="report-field">
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="all" @selected($type === 'all')>All</option>
                    <option value="1" @selected($type === '1')>Asset</option>
                    <option value="2" @selected($type === '2')>Liability</option>
                    <option value="4" @selected($type === '4')>Income</option>
                    <option value="5" @selected($type === '5')>Expenditure</option>
                </select>
            </div>
            <div class="report-actions">
                <button class="btn btn-success report-btn" name="view_data" value="1">View Data</button>
                <button type="button" id="pdf_report" class="btn btn-danger report-btn">PDF Report</button>
                <button type="button" id="only_data_report" class="btn btn-primary report-btn">Only Data Report</button>
            </div>
            <input type="hidden" id="search" name="search" value="{{ $search }}">
        </form>
    </div>
</div>
@if($showData)
@include('transaction.partials.allaccountbalance_data', ['accounts' => $accounts, 'reportDate' => $reportDate])
@endif
<div id="pdf_details" class="mt-3"></div>
</div>
@stop
@push('css')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
.all-ledger-title { font-size: 22px; font-weight: 400; color: #333; margin-bottom: 15px !important; }
.all-ledger-card { margin: 0; border: 1px solid #20c997; border-radius: 6px; background: #fff; box-shadow: none; padding: 10px; }
.all-ledger-body { padding: 25px; }
.report-field { display: flex; align-items: center; margin-bottom: 20px; }
.report-field label { width: 200px; margin: 0; font-size: 15px; font-weight: 400; color: #333; }
.report-field input, .report-field select { width: 250px; height: 32px; border: 1px solid #aaa; border-radius: 3px; padding: 4px 10px; font-size: 14px; background: #fff; }
.report-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 35px; }
.report-btn { font-size: 14px; padding: 7px 20px; height: auto; border: none; border-radius: 4px; color: #fff; cursor: pointer; }
.btn-success { background-color: #20c997; }
.btn-danger { background-color: #d9534f; }
.btn-primary { background-color: #428bca; }
.data-report { margin-top: 20px; background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 4px; }
.data-report-heading { font-size: 16px; padding-bottom: 10px; margin-bottom: 15px; border-bottom: 1px solid #eee; }
.data-report table { width: 100%; border-collapse: collapse; }
.data-report th, .data-report td { border: 1px solid #ccc; padding: 6px 8px; font-size: 13px; }
.data-report th { background: #3498db; color: #fff; font-weight: 600; text-align: left; }
.data-report tbody tr:nth-child(even) { background: #f9f9f9; }
.data-report .number { text-align: right; white-space: nowrap; }
.data-report .empty { text-align: center; color: #777; padding: 15px; }
.pdf-frame { width: 100%; height: 600px; border: 1px solid #ddd; background: #f4f6f9; }
@media (max-width: 768px) {
    .report-field { display: block; margin-bottom: 15px; }
    .report-field label, .report-field input, .report-field select { display: block; width: 100%; }
    .report-field label { margin-bottom: 5px; }
    .report-actions { flex-direction: column; gap: 10px; }
    .report-btn { width: 100%; }
}
@media print {
    @page { size: A4 portrait; margin: 15mm; }
    html, body { width: 100%; margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important; }
    .main-sidebar, .main-header, .content-header, .all-ledger-card, #pdf_details, .btn { display: none !important; }
    .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
    .data-report { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; }
    .data-report-heading { display: block !important; text-align: center; font-size: 20px; font-weight: bold; border-bottom: none; margin-bottom: 15px; }
    .data-report table { width: 100% !important; border-collapse: collapse; }
    .data-report th, .data-report td { font-size: 11px !important; padding: 6px 8px !important; border: 1px solid #000 !important; }
    .data-report th { background: #3498db !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .data-report .number { text-align: right; }
}
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
$(document).ready(function() {
    // Initialize Datepicker
    $("#report_date").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true
    });
});

$('#only_data_report').on('click', function () {
    var button = $(this);
    button.prop('disabled', true).text('Loading...');
    $.get('{{ route('accounting.allaccountbalancepdf') }}', {
        report_date: $('#report_date').val(), type: $('#type').val(), search: $('#search').val(), only_data: 1
    }).done(function (pdf) {
        $('#account-ledger-data').remove();
        $('#pdf_details').html('<div class="data-report"><div class="data-report-heading"><strong>Only Data Report</strong></div><iframe class="pdf-frame" src="data:application/pdf;base64,' + pdf + '"></iframe></div>');
        document.getElementById('pdf_details').scrollIntoView({behavior: 'smooth'});
    }).always(function () { button.prop('disabled', false).text('Only Data Report'); });
});

$('#pdf_report').on('click', function () {
    var button = $(this);
    button.prop('disabled', true).text('Loading...');
    $.get('{{ route('accounting.allaccountbalancepdf') }}', {
        report_date: $('#report_date').val(), type: $('#type').val(), search: $('#search').val()
    }).done(function (pdf) {
        $('#pdf_details').html('<div class="data-report"><div class="data-report-heading"><strong>PDF Report</strong></div><iframe class="pdf-frame" src="data:application/pdf;base64,' + pdf + '"></iframe></div>');
        document.getElementById('pdf_details').scrollIntoView({behavior: 'smooth'});
    }).always(function () { button.prop('disabled', false).text('PDF Report'); });
});
</script>
@endpush
