@extends('adminlte::page')

@section('title', 'Submenu List')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Submenu List</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Submenu List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Manage Submenus</h3>
                        <div class="card-tools d-flex">
                            <form id="searchForm" class="input-group input-group-sm mr-2" style="width: 200px;">
                                <input type="text" id="searchKey" class="form-control" placeholder="Search submenu...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#submenuModal" onclick="openAddModal()">
                                <i class="fas fa-plus"></i> Add New
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover m-0">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Parent Code</th>
                                    <th>Submenu Code</th>
                                    <th>Submenu Name</th>
                                    <th>Method</th>
                                    <th>Position</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="submenuTableBody">
                                @forelse($submenus as $key => $row)
                                <tr id="row-{{ $row->id }}">
                                    <td>{{ $submenus->firstItem() + $key }}</td>
                                    <td>{{ $row->menuparentcode }}</td>
                                    <td>{{ $row->submenucode }}</td>
                                    <td>{{ $row->submenuname }}</td>
                                    <td><code>{{ $row->method }}</code></td>
                                    <td>{{ $row->position }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" onclick="openEditModal({{ json_encode($row) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteSubmenu({{ $row->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-4">No data found in submenu table.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer clearfix d-flex justify-content-end" id="paginationLinks">
                        {{ $submenus->links('pagination::bootstrap-4') }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submenuModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Submenu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="submenuForm">
                @csrf
                <input type="hidden" id="submenu_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Parent Code</label>
                        <input type="number" id="menuparentcode" name="menuparentcode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Submenu Code</label>
                        <input type="number" id="submenucode" name="submenucode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Submenu Name</label>
                        <input type="text" id="submenuname" name="submenuname" class="form-control" maxlength="25" required>
                    </div>
                    <div class="form-group">
                        <label>Method</label>
                        <input type="text" id="method" name="method" class="form-control" maxlength="25" required>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="number" id="position" name="position" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function openAddModal() {
        $('#submenuForm')[0].reset();
        $('#submenu_id').val('');
        $('#modalTitle').text('Add New Submenu');
        $('#saveBtn').text('Submit');
    }

    function openEditModal(data) {
        $('#submenuForm')[0].reset();
        $('#modalTitle').text('Edit Submenu');
        $('#saveBtn').text('Save Changes');
        
        $('#submenu_id').val(data.id);
        $('#menuparentcode').val(data.menuparentcode);
        $('#submenucode').val(data.submenucode);
        $('#submenuname').val(data.submenuname);
        $('#method').val(data.method);
        $('#position').val(data.position);
        
        $('#submenuModal').modal('show');
    }

    $('#submenuForm').on('submit', function(e) {
        e.preventDefault();
        
        let id = $('#submenu_id').val();
        let url = id ? `{{ url('usermenu/submenulist/update') }}/${id}` : `{{ route('usermenu.submenulist.store') }}`;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + (id ? '&_method=PUT' : ''),
            success: function(response) {
                $('#submenuModal').modal('hide');
                fetchData();
            },
            error: function(xhr) {
                alert('Something went wrong. Please check your data inputs.');
            }
        });
    });

    function deleteSubmenu(id) {
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: `{{ url('usermenu/submenulist/delete') }}/${id}`,
                type: 'POST',
                data: {
                    _method: 'DELETE'
                },
                success: function(response) {
                    fetchData();
                },
                error: function(xhr) {
                    alert('Could not delete the record.');
                }
            });
        }
    }

    function fetchData(page = 1) {
        let search = $('#searchKey').val();
        $.ajax({
            url: `{{ route('usermenu.submenulist') }}?page=${page}&search=${search}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                if(data.data.length > 0) {
                    $.each(data.data, function(index, row) {
                        let sl = data.from + index;
                        html += `<tr id="row-${row.id}">
                            <td>${sl}</td>
                            <td>${row.menuparentcode}</td>
                            <td>${row.submenucode}</td>
                            <td>${row.submenuname}</td>
                            <td><code>${row.method}</code></td>
                            <td>${row.position}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick='openEditModal(${JSON.stringify(row)})'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSubmenu(${row.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center text-muted p-4">No data found in submenu table.</td></tr>';
                }
                $('#submenuTableBody').html(html);
                renderPagination(data);
            }
        });
    }

    function renderPagination(data) {
        let paginationHtml = '<ul class="pagination pagination-sm m-0 float-right">';
        
        if (data.prev_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">&laquo;</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        }

        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                if (i <= 3 || i >= data.last_page - 2 || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                } else if (i === 4 || i === data.last_page - 3) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
        }

        if (data.next_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">&raquo;</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }

        paginationHtml += '</ul>';
        $('#paginationLinks').html(paginationHtml);
    }

    // লারাভেলের ডিফল্ট পেজিনেশন লিংক অথবা AJAX পেজিনেশন লিংক—উভয় ক্লিকের জন্য এই ইভেন্ট হ্যান্ডলারটি কাজ করবে
    $(document).on('click', '#paginationLinks .page-link', function(e) {
        e.preventDefault();
        
        // যদি AJAX দিয়ে রেন্ডার হওয়া ডাটা-পেজ থাকে
        let page = $(this).data('page');
        
       
        if (!page) {
            let urlString = $(this).attr('href');
            if (urlString && urlString !== '#') {
                let url = new URL(urlString);
                page = url.searchParams.get("page");
            }
        }
        
        if (page) {
            fetchData(page);
        }
    });

    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        fetchData(1);
    });
</script>
@endsection