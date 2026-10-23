{{-- resources/views/Accounting/gl_sub_group_list.blade.php --}}
{{-- UI/UX redesign only. No logic, route, controller, or DB changes.
     Variables, routes, forms, CSRF and the search JS below are preserved. --}}
<style>
    .gl-sub-wrap {
        --gl-primary: #198754;
        --gl-primary-dark: #146c43;
        --gl-primary-soft: #e8f5ee;
        --gl-border: #e5e7eb;
        --gl-text: #1f2937;
        --gl-muted: #6b7280;
        --gl-row-alt: #fafbfc;
        --gl-row-hover: #f3f8f5;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--gl-text);
        width: 100%;
    }

    /* Page header */
    .gl-sub-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }
    .gl-sub-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .gl-sub-header-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: var(--gl-primary-soft);
        color: var(--gl-primary-dark);
        font-size: 18px;
    }
    .gl-sub-title {
        font-size: 20px;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
        color: var(--gl-text);
    }
    .gl-sub-subtitle {
        font-size: 13px;
        color: var(--gl-muted);
        margin: 2px 0 0;
    }
    .gl-sub-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--gl-primary);
        color: #fff;
        border: 1px solid var(--gl-primary);
        border-radius: 9px;
        padding: 9px 16px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, box-shadow .15s ease;
    }
    .gl-sub-add-btn:hover,
    .gl-sub-add-btn:focus {
        background: var(--gl-primary-dark);
        color: #fff;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(25, 135, 84, .28);
    }

    /* Toolbar card */
    .gl-sub-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #fff;
        border: 1px solid var(--gl-border);
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .gl-sub-search-box {
        position: relative;
        flex: 1 1 260px;
        min-width: 220px;
    }
    .gl-sub-search-box .fa-search {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gl-muted);
        font-size: 14px;
        pointer-events: none;
    }
    .gl-sub-search {
        width: 100%;
        border: 1px solid var(--gl-border);
        border-radius: 9px;
        padding: 10px 14px 10px 38px;
        font-size: 14px;
        color: var(--gl-text);
        background: #fff;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .gl-sub-search::placeholder { color: #9ca3af; }
    .gl-sub-search:focus {
        border-color: var(--gl-primary);
        box-shadow: 0 0 0 3px rgba(25, 135, 84, .12);
    }
    .gl-sub-count {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--gl-primary-soft);
        color: var(--gl-primary-dark);
        font-size: 13px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* Table card */
    .gl-sub-card {
        background: #fff;
        border: 1px solid var(--gl-border);
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 16px rgba(16, 24, 40, .03);
        overflow: hidden;
    }
    .gl-sub-card .table-responsive { margin: 0; }
    .gl-sub-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 14px;
    }
    .gl-sub-table thead th {
        background: #f8faf9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        text-align: left;
        padding: 13px 18px;
        border-bottom: 1px solid var(--gl-border);
        vertical-align: middle;
        white-space: nowrap;
    }
    .gl-sub-table tbody td {
        padding: 13px 18px;
        font-size: 14px;
        color: var(--gl-text);
        border-bottom: 1px solid #f1f2f4;
        vertical-align: middle;
    }
    .gl-sub-table tbody tr { transition: background .15s ease; }
    .gl-sub-table tbody tr:nth-child(even) { background: var(--gl-row-alt); }
    .gl-sub-table tbody tr:hover { background: var(--gl-row-hover); }
    .gl-sub-table tbody tr:last-child td { border-bottom: none; }

    /* Badges / cells */
    .gl-sub-type-badge {
        display: inline-block;
        background: var(--gl-primary-soft);
        color: var(--gl-primary-dark);
        border: 1px solid #cdeadd;
        padding: 3px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }
    .gl-sub-group {
        font-weight: 600;
        color: var(--gl-text);
    }
    .gl-sub-code {
        display: inline-block;
        font-family: 'SF Mono', 'Fira Code', Menlo, Consolas, monospace;
        font-weight: 600;
        color: #334155;
        background: #f1f5f9;
        padding: 3px 10px;
        border-radius: 7px;
        font-size: 13px;
    }
    .gl-sub-name { font-weight: 500; color: var(--gl-text); }

    /* Action column */
    .gl-sub-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
        white-space: nowrap;
    }
    .gl-sub-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        line-height: 1.2;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .gl-sub-btn .fas { font-size: 13px; }
    .gl-sub-btn-edit {
        color: var(--gl-primary-dark);
        background: #fff;
        border-color: #cdeadd;
    }
    .gl-sub-btn-edit:hover,
    .gl-sub-btn-edit:focus {
        background: var(--gl-primary-soft);
        color: var(--gl-primary-dark);
        text-decoration: none;
    }
    .gl-sub-btn-delete {
        color: #b91c1c;
        background: #fff;
        border-color: #f3c6c6;
    }
    .gl-sub-btn-delete:hover,
    .gl-sub-btn-delete:focus {
        background: #fef2f2;
        color: #b91c1c;
    }

    /* Empty state */
    .gl-sub-empty {
        text-align: center;
        padding: 56px 24px;
        color: var(--gl-muted);
    }
    .gl-sub-empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 16px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        font-size: 22px;
    }
    .gl-sub-empty-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin: 0 0 6px;
    }
    .gl-sub-empty-text {
        font-size: 14px;
        margin: 0 0 18px;
    }

    @media (max-width: 575px) {
        .gl-sub-header { flex-direction: column; align-items: stretch; }
        .gl-sub-add-btn { justify-content: center; }
        .gl-sub-toolbar { flex-direction: column; align-items: stretch; }
        .gl-sub-count { justify-content: center; }
    }
</style>

<div class="gl-sub-wrap">
    {{-- Page header --}}
    <div class="gl-sub-header">
        <div class="gl-sub-header-left">
            <span class="gl-sub-header-icon"><i class="fas fa-sitemap"></i></span>
            <div>
                <h2 class="gl-sub-title">GL Sub Head List</h2>
                <p class="gl-sub-subtitle">Manage and view all accounting sub heads</p>
            </div>
        </div>
        <a href="{{ route('accounting.addsubgroup') }}" class="gl-sub-add-btn">
            <i class="fas fa-plus"></i> Add Sub Head
        </a>
    </div>

    {{-- Toolbar --}}
    <div class="gl-sub-toolbar">
        <div class="gl-sub-search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="gl-sub-search" id="glSubSearch" placeholder="Search sub heads...">
        </div>
        <span class="gl-sub-count">
            <i class="fas fa-layer-group"></i>
            {{ count($rs_result) }} {{ count($rs_result) === 1 ? 'Sub Head' : 'Sub Heads' }}
        </span>
    </div>

    {{-- Table card --}}
    <div class="gl-sub-card">
        @if (count($rs_result))
            <div class="table-responsive">
                <table class="gl-sub-table" id="glSubTable">
                    <thead>
                        <tr>
                            <th>GL Type</th>
                            <th>Main Head</th>
                            <th>Sub Head Code</th>
                            <th>Sub Head Name</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rs_result as $row)
                            <tr>
                                <td><span class="gl-sub-type-badge">{{ $row->gl_type }}</span></td>
                                <td><span class="gl-sub-group">{{ $row->gl_group }}</span></td>
                                <td><span class="gl-sub-code">{{ $row->gl_sub_group }}</span></td>
                                <td><span class="gl-sub-name">{{ $row->gl_sub_group_name }}</span></td>
                                <td>
                                    <div class="gl-sub-actions">
                                        <a href="{{ route('accounting.editsubgroup', $row->ID) }}"
                                           class="gl-sub-btn gl-sub-btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('accounting.deletesubgroup') }}"
                                              style="display:inline-block; margin:0;" onsubmit="return confirm('Delete this sub group?');">
                                            @csrf
                                            <input type="hidden" name="gl_sub_group" value="{{ $row->gl_sub_group }}">
                                            <button type="submit" class="gl-sub-btn gl-sub-btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="gl-sub-empty">
                <div class="gl-sub-empty-icon"><i class="fas fa-folder-open"></i></div>
                <p class="gl-sub-empty-title">No GL Sub Heads Found</p>
                <p class="gl-sub-empty-text">Add your first sub head to get started.</p>
                <a href="{{ route('accounting.addsubgroup') }}" class="gl-sub-add-btn">
                    <i class="fas fa-plus"></i> Add Sub Head
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('glSubSearch');
        if (!input) return;
        input.addEventListener('keyup', function () {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#glSubTable tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    })();
</script>
