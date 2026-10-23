{{-- resources/views/Accounting/gl_group_list_for_gl_create.blade.php --}}
<style>
    .gl-wrap {
        --gl-primary:#4f46e5; --gl-primary-dark:#4338ca; --gl-border:#e5e7eb;
        --gl-text:#1f2937; --gl-muted:#6b7280;
        font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
        color:var(--gl-text); width:100%; margin-top:8px;
    }
    .gl-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;}
    .gl-title{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:700;margin:0;}
    .gl-title-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;
        background:linear-gradient(135deg,var(--gl-primary),#7c3aed);color:#fff;font-size:16px;}
    .gl-count{background:#eef2ff;color:var(--gl-primary-dark);font-size:13px;font-weight:600;padding:5px 12px;border-radius:999px;}
    .gl-search{border:1px solid var(--gl-border);border-radius:8px;padding:8px 14px;font-size:14px;outline:none;min-width:220px;
        transition:border-color .15s,box-shadow .15s;}
    .gl-search:focus{border-color:var(--gl-primary);box-shadow:0 0 0 3px rgba(79,70,229,.12);}
    .gl-card{border:1px solid var(--gl-border);border-radius:14px;overflow:hidden;
        box-shadow:0 1px 3px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.04);background:#fff;width:100%;}
    .gl-wrap table{width:100%;border-collapse:collapse;margin:0;}
    .gl-wrap thead th{background:linear-gradient(135deg,var(--gl-primary),var(--gl-primary-dark));
        color:#fff;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;
        padding:14px 18px;text-align:left;}
    .gl-wrap tbody td{padding:13px 18px;font-size:14px;border-bottom:1px solid #f1f2f4;}
    .gl-wrap tbody tr{transition:background .15s ease;}
    .gl-wrap tbody tr:nth-child(even){background:#fafbfc;}
    .gl-wrap tbody tr:hover{background:#eef2ff;}
    .gl-wrap tbody tr:last-child td{border-bottom:none;}
    .gl-type-pill{display:inline-block;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;
        padding:3px 11px;border-radius:8px;font-size:13px;font-weight:600;}
    .gl-code{font-family:'SF Mono','Fira Code',Menlo,Consolas,monospace;font-weight:600;
        color:var(--gl-primary-dark);background:#eef2ff;padding:2px 9px;border-radius:6px;font-size:13px;}
    .gl-group-num{font-weight:600;}
    .gl-empty{padding:48px 20px;text-align:center;color:var(--gl-muted);}
    .gl-empty-icon{font-size:40px;opacity:.35;margin-bottom:10px;}
</style>

<div class="gl-wrap">
    <div class="gl-header">
        <h2 class="gl-title"><span class="gl-title-icon">📒</span> GL Group List</h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <input type="text" class="gl-search" id="glSearch" placeholder="Search groups…">
            <span class="gl-count">{{ count($rs_result) }} {{ count($rs_result) === 1 ? 'group' : 'groups' }}</span>
        </div>
    </div>

    <div class="gl-card">
        @if (count($rs_result))
            <table id="glTable">
                <thead>
                    <tr><th>GL Type</th><th>GL Group</th><th>Code</th><th style="width:160px;text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($rs_result as $row)
                        <tr>
                            <td><span class="gl-type-pill">{{ $row->gl_type }}</span></td>
                            <td><span class="gl-group-num">{{ $row->gl_group }}</span></td>
                            <td><span class="gl-code">{{ $row->gl_group_name }}</span></td>
                            <td style="text-align:right;white-space:nowrap;">
                                <a href="{{ route('accounting.editgroup', $row->ID) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">✏️ Edit</a>
                                <form method="POST" action="{{ route('accounting.deletegroup') }}"
                                      style="display:inline-block;" onsubmit="return confirm('Delete this group?');">
                                    @csrf
                                    <input type="hidden" name="gl_group" value="{{ $row->gl_group }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gl-empty"><div class="gl-empty-icon">📭</div><p>No GL groups found for this type yet.</p></div>
        @endif
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('glSearch');
        if (!input) return;
        input.addEventListener('keyup', function () {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#glTable tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    })();
</script>