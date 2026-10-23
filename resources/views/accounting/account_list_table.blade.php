{{-- resources/views/accounting/account_list_table.blade.php --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.0.6/css/scroller.bootstrap4.min.css">

<h6 class="Change" style="font-size:2vw;">Accounting Head</h6>

<div class="table-responsive">
    <table id="example" class="table table-bordered table-striped table-hover display" style="width:98%">
        <thead>
        <tr>
            <th> GL Type</th>
            <th> GL group</th>
            <th> GL sub Group Name</th>
            <th> GL Account No</th>
            <th> Account name</th>
            <th> Balance</th>
            <th> EDIT</th>
            <th> DELETE</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($rs_result as $row)
            <tr>
                <td>{{ $row->gl_type_name }}</td>
                <td>{{ $row->gl_group }}</td>
                <td>{{ $row->gl_sub_group }}</td>
                <td><code>{{ $row->AccountNo }}</code></td>
                <td>{{ $row->title }}</td>
                <td style="text-align:right; font-weight:600; color:{{ (float)$row->balance > 0 ? '#2e7d32' : ((float)$row->balance < 0 ? '#c62828' : '#555') }};">
                    {{ number_format((float)$row->balance, 2) }}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                            data-id="{{ $row->ID }}" 
                            data-gl_type="{{ $row->gl_type_name }}"
                            data-gl_group="{{ $row->gl_group }}"
                            data-gl_sub_group="{{ $row->gl_sub_group }}"
                            data-accountno="{{ $row->AccountNo }}"
                            data-title="{{ $row->title }}"
                            title="Edit"><i class="fas fa-edit"></i> Edit</button>
                </td>
                <td class="text-center">
                    <form method="POST" action="{{ route('accounting.deleteaccount') }}" class="d-inline"
                          onsubmit="return confirm('Click OK to continue?');">
                        @csrf
                        <input type="hidden" name="AccountNo" value="{{ $row->AccountNo }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script>
    (function () {
        var datatablesCss = [
            'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css'
        ];
        datatablesCss.forEach(function (href) {
            if (!document.querySelector('link[href="' + href + '"]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }
        });

        function initAccountListTable() {
            if (!window.jQuery || !$.fn.DataTable) {
                $.getScript('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', function () {
                    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#example')) {
                        $('#example').DataTable({
                            deferRender: true,
                            scrollY: 1000,
                            scrollCollapse: true,
                            scroller: true,
                            fixedHeader: { header: true, footer: true },
                            pageLength: 500
                        });
                    }
                });
                return;
            }
            if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable({
                    deferRender: true,
                    scrollY: 1000,
                    scrollCollapse: true,
                    scroller: true,
                    fixedHeader: { header: true, footer: true },
                    pageLength: 500
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAccountListTable);
        } else {
            initAccountListTable();
        }
    })();
</script>