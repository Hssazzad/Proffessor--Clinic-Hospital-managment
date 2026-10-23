@extends('adminlte::page')

@section('title', 'Account Ledger')

@section('content_header')
    <h1>Account Ledger</h1>
@stop

@section('css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

    <style>
        .back_color_tr {
            background-color: #ddd;
        }
    </style>
@stop

@section('content')
<div class="container-fluid">
    <!-- Content Row -->
    <div class="row col-col-md-12">

        <!-- Area Chart -->
        <div class="col-xl-12 col-lg-12">

           

            <!-- Card Body -->
            <div class="main-card card border border-success rounded-2">
                <div class="card-body">

                    <form id="comment" method="POST" action="javascript:void(0);">
                        @csrf

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="AccountNo" class="control-label">Account</label>
                                </div>
                                <div class="col-sm-1">:</div>
                                <div class="col-sm-4">
                                    <select id="AccountNo" name="AccountNo" class="form-control select2" required>
                                        <option value="">-- Select Account --</option>
                                        @if(isset($AccountNo) && $AccountNo->count() > 0)
                                            @foreach($AccountNo as $account)
                                                <option value="{{ $account->AccountNo }}" @selected(($selectedAccount ?? '') == $account->AccountNo)>{{ $account->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if($errors->has('AccountNo'))
                                        <div class="error1">{{ $errors->first('AccountNo') }}</div>
                                    @endif
                                </div>
                                <div class="col-sm-2"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label for="FromDate" class="control-label">From Date</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" id="FromDate" name="FromDate" class="form-control" value="{{ old('FromDate') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label for="ToDate" class="control-label">To Date</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" id="ToDate" name="ToDate" class="form-control" value="{{ old('ToDate') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-success" id="html_button">HTML Report</button>
                            </div>
                            <div class="col-sm-1">:</div>
                            <div class="col-sm-4">
                                @if(Route::has('transaction.showledgerstatementpdf'))
                                    <button type="button" class="btn btn-danger" id="pdf_button">PDF Report</button>
                                @endif
                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <div id="page_details" name="page_details" style="width:100%;"></div>

        </div>
    </div>
    <!-- End of Main Content -->
</div>
@stop

@section('js')
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function () {
            $("#FromDate").datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true
            }).datepicker("setDate", new Date());

            $("#ToDate").datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true
            }).datepicker("setDate", new Date());

            // Initialize Select2
            $(".select2").select2();
        });
    </script>

    <script>
        // ============================================================
        // DataTables assets load kora hoye DYNAMICALLY (getScript chain)
        // jate adminlte layout-r kono jQuery/DataTables conflict na hoy.
        // Order guaranteed: core -> buttons -> fixedheader
        // ============================================================
        var dataTablesAssets = [
            'https://cdn.datatables.net/2.1.3/js/dataTables.min.js',
            'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js',
            'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js',
            'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js',
            'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js',
            'https://cdn.datatables.net/fixedheader/4.0.1/js/dataTables.fixedHeader.min.js'
        ];

        var dataTablesAssetsLoaded = false;

        function ensureDataTables(callback) {
            if (dataTablesAssetsLoaded) {
                if (callback) callback();
                return;
            }

            var i = 0;

            function next() {
                if (i >= dataTablesAssets.length) {
                    dataTablesAssetsLoaded = true;
                    if (callback) callback();
                    return;
                }
                $.getScript(dataTablesAssets[i]).always(function () {
                    i++;
                    next();
                });
            }

            next();
        }

        $(document).ready(function () {

            $("#html_button").on("click", function () {

                var AccountNo = $("#AccountNo").val();
                var FromDate = $("#FromDate").val();
                var ToDate = $("#ToDate").val();

                if (!AccountNo) {
                    alert('Please select an account');
                    return false;
                }

                ensureDataTables(function () {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('transaction.showledgerstatement') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            "AccountNo": AccountNo,
                            "FromDate": FromDate,
                            "ToDate": ToDate
                        },
                        cache: false,
                        success: function (data) {
                            $("#page_details").html("");
                            $("#page_details").html(data);

                            // DataTable initialize (fragment theke uthle)
                            if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#example')) {
                                $('#example').DataTable({
                                    "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
                                    'iDisplayLength': -1,
                                    dom: 'Bfrtip',
                                    buttons: ['copy', 'csv', 'excel', 'print', 'pdf'],
                                    fixedHeader: {
                                        header: true,
                                        footer: true
                                    }
                                });
                            }
                        }
                    });
                });
            });

            @if(Route::has('transaction.showledgerstatementpdf'))
            $("#pdf_button").on("click", function () {

                var AccountNo = $("#AccountNo").val();
                var FromDate = $("#FromDate").val();
                var ToDate = $("#ToDate").val();

                if (!AccountNo) {
                    alert('Please select an account');
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('transaction.showledgerstatementpdf') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        "AccountNo": AccountNo,
                        "FromDate": FromDate,
                        "ToDate": ToDate
                    },
                    success: function (returnData) {
                        if (returnData != "") {
                            var file = 'data:application/pdf;base64,' + returnData;
                            $("#page_details").html('<p align="center"><iframe width="80%" height="800px" src="' + file + '" frameborder="0" allowfullscreen=""></iframe>');
                        }
                    }
                });
            });
            @endif
        });
    </script>
@stop
