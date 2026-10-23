{{-- Medicine list card: filters + table + pagination.
     Rendered by settings.medicines.index AND by the AJAX list-html endpoint. --}}
<div class="card pm-gov-card mb-0" id="medicineListCard">
    <div class="card-header py-2">
        <h3 class="card-title font-weight-bold pm-card-title">Medicine List</h3>
        <div class="card-tools d-flex align-items-center">
            <form id="csvImportForm" method="POST" action="{{ route('settings.medicines.csv.import') }}"
                  enctype="multipart/form-data" class="d-inline-flex align-items-center mr-2">
                @csrf
                <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv"
                       class="form-control form-control-sm mr-1" style="max-width:220px;" required
                       title="CSV file upload — header: name, strength, generic_name, medicine_type, sub_type, company, dosage_form, GroupName, commonly_used, active">
                <button type="submit" class="btn btn-sm btn-outline-success">
                    Import CSV
                </button>
            </form>
            <a href="{{ route('settings.medicines.csv.download') }}" class="btn btn-sm btn-outline-success mr-2">
                Download CSV
            </a>
            <span class="badge badge-info">{{ $medicines->total() }} total</span>
        </div>
    </div>

    <div class="card-body pb-0">
        {{-- Single filter bar: commonly used filter + search (same GET params as before) --}}
        <form id="filterForm" method="GET" action="{{ route('settings.medicines.index') }}" autocomplete="off" class="mb-3">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <label for="commonly_used_filter" class="small font-weight-bold text-muted mb-1">Commonly Used</label>
                    <select name="commonly_used_filter" id="commonly_used_filter"
                            class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedFilter == 'all' ? 'selected' : '' }}>All</option>
                        <option value="yes" {{ $selectedFilter == 'yes' ? 'selected' : '' }}>Commonly Used</option>
                        <option value="no" {{ $selectedFilter == 'no' ? 'selected' : '' }}>Normal Medicines</option>
                    </select>
                </div>
                <div class="col-md-5 col-sm-6">
                    <label for="search" class="small font-weight-bold text-muted mb-1">Search Medicine</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" id="search" value="{{ $search }}"
                               class="form-control" placeholder="Medicine name / company / generic...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Search</button>
                            @if($search !== '' || $selectedFilter != 'all')
                                <a href="{{ route('settings.medicines.index') }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-sm mb-0 pm-medicine-table">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Medicine Name</th>
                        <th>Strength</th>
                        <th>Type</th>
                        <th>Sub Type</th>
                        <th>Company</th>
                        <th>Dosage Form</th>
                        @if($hasGenericName)
                            <th>Generic Name</th>
                        @endif
                        <th>Note</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:90px;">Commonly Used</th>
                        <th style="width:120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $row)
                        <tr>
                            <td>{{ $medicines->firstItem() + $loop->index }}</td>
                            <td class="font-weight-bold">{{ $row->name }}</td>
                            <td>{{ $row->strength ?: '—' }}</td>
                            <td>{{ $row->medicineType?->type_name ?? '—' }}</td>
                            <td>{{ $row->medicineSubType?->subtype_name ?? '—' }}</td>
                            <td>{{ $row->companyRecord?->name ?? $row->company ?? '—' }}</td>
                            <td>{{ $row->dosageForm?->dosage_name ?? '—' }}</td>
                            @if($hasGenericName)
                                <td>{{ $row->generic_name ?? '—' }}</td>
                            @endif
                            <td title="{{ $row->note ?? '' }}">{{ !empty($row->note) ? Str::limit($row->note, 24) : '—' }}</td>
                            <td>
                                @if($row->active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($row->commonly_used)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!empty($row->is_template))
                                    <span class="badge badge-secondary">
                                        Template
                                    </span>
                                @else
                                <button type="button"
                                        class="btn btn-xs btn-outline-info btn-edit-medicine"
                                        title="Edit"
                                        data-id="{{ $row->id }}"
                                        data-name="{{ $row->name }}"
                                        data-strength="{{ $row->strength }}"
                                        data-type-id="{{ $row->medicine_type_id }}"
                                        data-subtype-id="{{ $row->medicine_sub_type_id }}"
                                        data-company-id="{{ $row->company_id }}"
                                        data-dosage-id="{{ $row->dosage_form_id }}"
                                        @if($hasGenericName) data-generic="{{ $row->generic_name }}" @endif
                                        data-note="{{ $row->note ?? '' }}"
                                        data-status="{{ $row->active ? 1 : 0 }}"
                                        data-commonly-used="{{ $row->commonly_used ? 1 : 0 }}">
                                    Edit
                                </button>
                                <form method="POST"
                                      action="{{ route('settings.medicines.destroy', $row->id) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete medicine \'{{ addslashes($row->name) }}\'? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasGenericName ? 12 : 11 }}" class="text-center text-muted py-4">
                                No medicines found. Use the form above to add the first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($medicines->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $medicines->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
