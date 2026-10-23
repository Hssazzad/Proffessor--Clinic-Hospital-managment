@extends('adminlte::page')

@section('plugins.Datatables', true)

@section('title', 'O/E Master List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary font-weight-bold mb-0">O/E Master List</h1>
        <a href="{{ route('settings.oe.create') }}" class="btn btn-primary font-weight-bold">
            <i class="fas fa-plus-circle mr-1"></i> Add New
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="card-title mb-0 font-weight-bold">
                    <i class="fas fa-list mr-1"></i> All O/E Masters
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="oeMasterTable" class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px">SL</th>
                                <th>Name</th>
                                <th>Field Code</th>
                                <th>Short Name</th>
                                <th>Unit</th>
                                <th>Input Type</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th style="width:140px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oeMasters as $oe)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold">{{ $oe->name }}</td>
                                    <td>
                                        @if($oe->field_code && isset($fieldCodes[$oe->field_code]))
                                            <span class="badge badge-primary">
                                                {{ $fieldCodes[$oe->field_code] }} ({{ $oe->field_code }})
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $oe->short_name ?? '—' }}</td>
                                    <td>{{ $oe->unit ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($oe->input_type) }}</span>
                                    </td>
                                    <td>{{ $oe->sort_order }}</td>
                                    <td>
                                        @if($oe->active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('settings.oe.edit', $oe->id) }}"
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('settings.oe.destroy', $oe->id) }}"
                                              style="display:inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete this O/E Master?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">
                                        No O/E Master found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    $('#oeMasterTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        language: {
            emptyTable: 'No O/E Master found.'
        }
    });
});
</script>
@stop
