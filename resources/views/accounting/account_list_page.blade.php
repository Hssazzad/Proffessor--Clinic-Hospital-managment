{{-- resources/views/accounting/account_list_page.blade.php --}}
@extends('adminlte::page')

@section('title', 'Account List')

@push('css')
    <style>
        .account-list-card .card-header { background: #6659e6 !important; color: #fff !important; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @include('accounting.account_list', ['rs_result' => $rs_result])
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $(document).on('click', '.edit-account-btn', function () {
                window.location.href = '{{ url('Accounting/account') }}/' + $(this).data('id') + '/edit';
            });

            $(document).on('click', '.delete-account-btn', function () {
                var accountNo = $(this).data('accountno');
                if (!confirm('Are you sure you want to delete account ' + accountNo + '?')) {
                    return;
                }
                $.post('{{ route('accounting.deleteaccount') }}', { AccountNo: accountNo }, function (res) {
                    alert(res.message || 'Deleted successfully.');
                    window.location.reload();
                }).fail(function (xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Delete failed.');
                });
            });
        });
    </script>
@endpush