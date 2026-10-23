@extends('adminlte::page')

@section('title', 'Investigation Master')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1 class="m-0" style="font-size:1.25rem;font-weight:700;color:#1e293b">
    <i class="fas fa-flask text-primary mr-2"></i> Investigation Master
  </h1>
</div>
@stop

@section('content')

<div id="imAlert" class="alert d-none" role="alert" style="border-radius:6px;font-weight:500"></div>

<div class="row">

  {{-- ══ LEFT PANEL (70%) ══ --}}
  <div class="col-md-8 col-lg-8">
    <div class="card card-outline card-primary card-im">
      <div class="card-header p-2 px-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted" style="font-size:0.78rem;font-weight:600">
              <i class="fas fa-list mr-1"></i> All Investigations
              <span class="badge badge-primary ml-1" id="imCount">{{ $investigations->total() }}</span>
            </span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <select id="imFilterCategory" class="form-control form-control-sm" style="width:auto;min-width:130px;font-size:0.78rem">
              <option value="">All Categories</option>
              @foreach($investigations as $inv)
                <option value="{{ $inv->category }}">{{ $inv->category }}</option>
              @endforeach
            </select>
            <div class="input-group input-group-sm" style="width:200px">
              <div class="input-group-prepend">
                <span class="input-group-text" style="background:#fff;border-right:none">
                  <i class="fas fa-search text-muted" style="font-size:0.75rem"></i>
                </span>
              </div>
              <input type="text" id="imSearch" class="form-control" placeholder="Search name..." style="border-left:none;font-size:0.78rem">
            </div>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div style="overflow-x:auto">
          <table class="table im-table" id="imTable">
            <thead>
              <tr>
                <th style="width:36px">#</th>
                <th>Investigation Name</th>
                <th style="width:140px">Category</th>
                <th style="width:80px">Status</th>
                <th style="width:110px">Created</th>
                <th style="width:90px">Actions</th>
              </tr>
            </thead>
            <tbody id="imTbody">
              @forelse($investigations as $i => $inv)
              <tr class="im-row"
                  data-id="{{ $inv->id }}"
                  data-name="{{ $inv->name }}"
                  data-category="{{ $inv->category }}"
                  data-description="{{ $inv->description }}"
                  data-active="{{ $inv->active }}">
                <td class="text-muted">{{ $investigations->firstItem() + $i }}</td>
                <td><strong style="font-size:0.85rem">{{ $inv->name }}</strong></td>
                <td><span class="im-cat-badge">{{ $inv->category }}</span></td>
                <td>
                  @if($inv->active)
                    <span class="im-status im-status-active">Active</span>
                  @else
                    <span class="im-status im-status-inactive">Inactive</span>
                  @endif
                </td>
                <td class="text-muted" style="font-size:0.78rem">
                  {{ $inv->created_at ? \Carbon\Carbon::parse($inv->created_at)->format('d M Y') : '—' }}
                </td>
                <td>
                  <button class="im-btn im-btn-edit" onclick="openEditModal(this)" title="Edit">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="im-btn im-btn-del" onclick="deleteInvestigation({{ $inv->id }}, this)" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr id="imEmptyRow">
                <td colspan="6" class="text-center py-4 text-muted" style="font-size:0.85rem">
                  No investigations found.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if($investigations->hasPages())
      <div class="card-footer py-2 px-3" style="background:#fafbfc;border-top:1px solid #e2e8f0">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted" style="font-size:0.75rem">
            Showing {{ $investigations->firstItem() }}–{{ $investigations->lastItem() }} of {{ $investigations->total() }}
          </span>
          <div style="font-size:0.78rem">
            {{ $investigations->links() }}
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- ══ RIGHT PANEL (30%) ══ --}}
  <div class="col-md-4 col-lg-4">
    <div class="card card-outline card-primary card-im">
      <div class="card-header p-2 px-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
        <span style="font-size:0.78rem;font-weight:600;color:#475569">
          <i class="fas fa-bolt mr-1"></i> Quick Actions
        </span>
      </div>
      <div class="card-body p-3">
        <button class="im-action-btn im-action-btn-primary" onclick="openAddModal()">
          <i class="fas fa-plus-circle"></i> Add Investigation
        </button>
        <button class="im-action-btn im-action-btn-outline" onclick="showAlert('Import feature coming soon.','info')">
          <i class="fas fa-file-import"></i> Import
        </button>
        <button class="im-action-btn im-action-btn-outline" onclick="showAlert('Export feature coming soon.','info')">
          <i class="fas fa-file-export"></i> Export
        </button>
      </div>
    </div>
  </div>

</div>

{{-- ══ ADD INVESTIGATION MODAL ══ --}}
<div class="modal fade" id="imAddModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border:none;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.12)">
      <div class="modal-header px-4 py-3" style="border-bottom:1px solid #e2e8f0;background:#f8fafc;border-radius:10px 10px 0 0">
        <h5 class="modal-title m-0" style="font-size:0.95rem;font-weight:700;color:#1e293b">
          <i class="fas fa-plus-circle text-primary mr-2"></i> Add Investigation
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="imAddForm" autocomplete="off">
        @csrf
        <div class="modal-body px-4 py-3">
          <div id="imAddError" class="alert alert-danger d-none py-2 px-3" style="font-size:0.8rem;border-radius:5px"></div>
          <div class="form-group mb-3">
            <label class="im-label">Investigation Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="imAddName" class="form-control form-control-sm" placeholder="e.g. Complete Blood Count" required>
          </div>
          <div class="form-group mb-3">
            <label class="im-label">Category</label>
            <input type="text" name="category" id="imAddCategory" class="form-control form-control-sm" placeholder="e.g. Hematology">
          </div>
          <div class="form-group mb-3">
            <label class="im-label">Description</label>
            <textarea name="description" id="imAddDesc" class="form-control form-control-sm" rows="2" placeholder="Brief description..."></textarea>
          </div>
          <div class="form-group mb-0">
            <label class="im-label">Status</label>
            <select name="active" id="imAddStatus" class="form-control form-control-sm">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer px-4 py-3" style="border-top:1px solid #e2e8f0">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary" id="imAddBtn">
            <i class="fas fa-save mr-1"></i> Save Investigation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ══ EDIT INVESTIGATION MODAL ══ --}}
<div class="modal fade" id="imEditModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border:none;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.12)">
      <div class="modal-header px-4 py-3" style="border-bottom:1px solid #e2e8f0;background:#f8fafc;border-radius:10px 10px 0 0">
        <h5 class="modal-title m-0" style="font-size:0.95rem;font-weight:700;color:#1e293b">
          <i class="fas fa-pen text-primary mr-2"></i> Edit Investigation
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="imEditForm" autocomplete="off">
        @csrf
        @method('PUT')
        <input type="hidden" id="imEditId" name="id">
        <div class="modal-body px-4 py-3">
          <div id="imEditError" class="alert alert-danger d-none py-2 px-3" style="font-size:0.8rem;border-radius:5px"></div>
          <div class="form-group mb-3">
            <label class="im-label">Investigation Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="imEditName" class="form-control form-control-sm" required>
          </div>
          <div class="form-group mb-3">
            <label class="im-label">Category</label>
            <input type="text" name="category" id="imEditCategory" class="form-control form-control-sm">
          </div>
          <div class="form-group mb-3">
            <label class="im-label">Description</label>
            <textarea name="description" id="imEditDesc" class="form-control form-control-sm" rows="2"></textarea>
          </div>
          <div class="form-group mb-0">
            <label class="im-label">Status</label>
            <select name="active" id="imEditStatus" class="form-control form-control-sm">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer px-4 py-3" style="border-top:1px solid #e2e8f0">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary" id="imEditBtn">
            <i class="fas fa-save mr-1"></i> Update Investigation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ══ DELETE CONFIRMATION MODAL ══ --}}
<div class="modal fade" id="imDeleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content" style="border:none;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.12)">
      <div class="modal-body text-center py-4 px-4">
        <div style="width:48px;height:48px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem">
          <i class="fas fa-trash-alt" style="color:#dc2626;font-size:1.1rem"></i>
        </div>
        <h6 style="font-weight:700;color:#1e293b;margin-bottom:0.25rem">Delete Investigation?</h6>
        <p style="font-size:0.8rem;color:#64748b;margin-bottom:1.25rem">
          This action cannot be undone.
        </p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-sm btn-danger" id="imDeleteConfirm">
            <i class="fas fa-trash mr-1"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@stop

@section('css')
<style>
.card-im { border-radius:8px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
.card-im .card-header { border-radius:7px 7px 0 0; }

.im-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.im-table thead { position:sticky; top:0; z-index:2; }
.im-table thead th {
  background:#f1f5f9;
  padding:0.5rem 0.6rem;
  font-size:0.68rem;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:0.03em;
  color:#64748b;
  border-bottom:1.5px solid #e2e8f0;
  white-space:nowrap;
  text-align:left;
}
.im-table tbody td {
  padding:0.5rem 0.6rem;
  border-bottom:1px solid #f1f5f9;
  vertical-align:middle;
  color:#1e293b;
}
.im-table tbody tr:hover { background:#f8fafc; }
.im-table tbody tr:last-child td { border-bottom:none; }
.im-table tbody tr.im-highlight {
  animation: imPulse 2s ease-out;
}
@keyframes imPulse {
  0% { background:#eff6ff; }
  70% { background:#eff6ff; }
  100% { background:transparent; }
}

.im-cat-badge {
  display:inline-block;
  padding:0.15rem 0.5rem;
  border-radius:4px;
  font-size:0.72rem;
  font-weight:600;
  background:#eff6ff;
  color:#1d4ed8;
}

.im-status {
  display:inline-block;
  padding:0.12rem 0.45rem;
  border-radius:3px;
  font-size:0.68rem;
  font-weight:600;
}
.im-status-active { background:#dcfce7; color:#15803d; }
.im-status-inactive { background:#f1f5f9; color:#64748b; }

.im-btn {
  width:28px;height:28px;
  border:none;border-radius:5px;
  cursor:pointer;font-size:0.72rem;
  transition:background 0.15s;
  display:inline-flex;align-items:center;justify-content:center;
}
.im-btn-edit { background:#eff6ff; color:#2563eb; }
.im-btn-edit:hover { background:#dbeafe; }
.im-btn-del { background:#fef2f2; color:#dc2626; margin-left:4px; }
.im-btn-del:hover { background:#fee2e2; }

.im-action-btn {
  display:flex;
  align-items:center;
  gap:0.5rem;
  width:100%;
  padding:0.55rem 0.85rem;
  border-radius:6px;
  font-size:0.8rem;
  font-weight:600;
  cursor:pointer;
  transition:background 0.15s;
  border:none;
  margin-bottom:0.4rem;
  font-family:inherit;
}
.im-action-btn:last-child { margin-bottom:0; }
.im-action-btn-primary { background:#2563eb; color:#fff; }
.im-action-btn-primary:hover { background:#1d4ed8; }
.im-action-btn-outline { background:#fff; color:#475569; border:1.5px solid #e2e8f0; }
.im-action-btn-outline:hover { background:#f8fafc; border-color:#cbd5e1; }

.im-label {
  display:block;
  font-size:0.72rem;
  font-weight:600;
  color:#64748b;
  margin-bottom:0.2rem;
}

#imAddModal .form-control-sm, #imEditModal .form-control-sm {
  font-size:0.85rem;
  padding:0.4rem 0.6rem;
  border-radius:5px;
  border:1.5px solid #e2e8f0;
}
#imAddModal .form-control-sm:focus, #imEditModal .form-control-sm:focus {
  border-color:#3b82f6;
  box-shadow:0 0 0 3px rgba(59,130,246,0.1);
}

@media (max-width:767px) {
  .col-md-8, .col-md-4 { width:100%; }
}
</style>
@stop

@section('js')
<script>
(function(){
  var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  var storeUrl   = '{{ route("settings.investigation.store") }}';
  var updateUrl  = '{{ route("settings.investigation.update", ":id") }}';
  var destroyUrl = '{{ route("settings.investigation.destroy", ":id") }}';
  var indexUrl   = '{{ route("settings.investigation.index") }}';

  var pendingDeleteId = null;
  var pendingDeleteBtn = null;

  function showAlert(msg, type) {
    var box = document.getElementById('imAlert');
    box.className = 'alert alert-' + type + ' mb-3';
    box.textContent = msg;
    box.classList.remove('d-none');
    clearTimeout(box._timer);
    box._timer = setTimeout(function(){ box.classList.add('d-none'); }, 4000);
  }

  function esc(s){ var d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }

  function refreshTable() {
    fetch(indexUrl, { headers: { 'Accept': 'text/html' } })
      .then(function(r){ return r.text(); })
      .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newTbody = doc.getElementById('imTbody');
        var newCount = doc.getElementById('imCount');
        if (newTbody) {
          document.getElementById('imTbody').innerHTML = newTbody.innerHTML;
        }
        if (newCount) {
          document.getElementById('imCount').textContent = newCount.textContent;
        }
        // Pagination
        var oldFooter = document.querySelector('#imTable').closest('.card').querySelector('.card-footer');
        if (oldFooter) {
          var newFooter = doc.querySelector('#imTable').closest('.card')?.querySelector('.card-footer');
          if (newFooter) {
            oldFooter.innerHTML = newFooter.innerHTML;
          }
        }
        renumberRows();
      })
      .catch(function(){});
  }

  function renumberRows() {
    var rows = document.querySelectorAll('#imTbody tr.im-row');
    rows.forEach(function(r, idx){
      var firstTd = r.querySelector('td');
      if (firstTd) { firstTd.textContent = idx + 1; }
    });
  }

  // ── ADD ──
  document.getElementById('imAddForm').addEventListener('submit', function(e){
    e.preventDefault();
    var btn = document.getElementById('imAddBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

    var name  = document.getElementById('imAddName').value.trim();
    var cat   = document.getElementById('imAddCategory').value.trim();
    var desc  = document.getElementById('imAddDesc').value.trim();
    var active = document.getElementById('imAddStatus').value;

    var errorBox = document.getElementById('imAddError');
    errorBox.classList.add('d-none');

    if (!name) {
      errorBox.textContent = 'Name is required.';
      errorBox.classList.remove('d-none');
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Investigation';
      return;
    }

    fetch(storeUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ name: name, category: cat, description: desc, active: active })
    })
    .then(function(r){
      if (r.status === 422) { return r.json().then(function(j){ throw new Error(Object.values(j.errors || {}).flat().join(', ') || 'Validation error'); }); }
      if (r.status === 302) { showAlert('Investigation added successfully.', 'success'); }
      if (!r.ok) { throw new Error('Server error'); }
      return r;
    })
    .then(function(){
      $('#imAddModal').modal('hide');
      refreshTable();
    })
    .catch(function(err){
      errorBox.textContent = err.message;
      errorBox.classList.remove('d-none');
    })
    .finally(function(){
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Investigation';
    });
  });

  // ── OPEN ADD ──
  window.openAddModal = function() {
    document.getElementById('imAddForm').reset();
    document.getElementById('imAddError').classList.add('d-none');
    document.getElementById('imAddStatus').value = '1';
    $('#imAddModal').modal('show');
    setTimeout(function(){ document.getElementById('imAddName').focus(); }, 300);
  };

  // ── OPEN EDIT ──
  window.openEditModal = function(btn) {
    var row = btn.closest('tr.im-row');
    document.getElementById('imEditId').value    = row.dataset.id;
    document.getElementById('imEditName').value  = row.dataset.name;
    document.getElementById('imEditCategory').value = row.dataset.category;
    document.getElementById('imEditDesc').value  = row.dataset.description;
    document.getElementById('imEditStatus').value = row.dataset.active;
    document.getElementById('imEditError').classList.add('d-none');
    $('#imEditModal').modal('show');
    setTimeout(function(){ document.getElementById('imEditName').focus(); }, 300);
  };

  // ── EDIT SUBMIT ──
  document.getElementById('imEditForm').addEventListener('submit', function(e){
    e.preventDefault();
    var btn = document.getElementById('imEditBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Updating...';

    var id   = document.getElementById('imEditId').value;
    var name  = document.getElementById('imEditName').value.trim();
    var cat   = document.getElementById('imEditCategory').value.trim();
    var desc  = document.getElementById('imEditDesc').value.trim();
    var active = document.getElementById('imEditStatus').value;

    var errorBox = document.getElementById('imEditError');
    errorBox.classList.add('d-none');

    if (!name) {
      errorBox.textContent = 'Name is required.';
      errorBox.classList.remove('d-none');
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Investigation';
      return;
    }

    var url = updateUrl.replace(':id', id);

    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ _method: 'PUT', name: name, category: cat, description: desc, active: active })
    })
    .then(function(r){
      if (r.status === 422) { return r.json().then(function(j){ throw new Error(Object.values(j.errors || {}).flat().join(', ') || 'Validation error'); }); }
      if (r.status === 302) { showAlert('Investigation updated successfully.', 'success'); }
      if (!r.ok) { throw new Error('Server error'); }
      return r;
    })
    .then(function(){
      $('#imEditModal').modal('hide');
      refreshTable();
    })
    .catch(function(err){
      errorBox.textContent = err.message;
      errorBox.classList.remove('d-none');
    })
    .finally(function(){
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Investigation';
    });
  });

  // ── DELETE ──
  window.deleteInvestigation = function(id, btn) {
    pendingDeleteId = id;
    pendingDeleteBtn = btn;
    $('#imDeleteModal').modal('show');
  };

  document.getElementById('imDeleteConfirm').addEventListener('click', function(){
    if (!pendingDeleteId) return;
    var btn = this;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';

    var url = destroyUrl.replace(':id', pendingDeleteId);

    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(function(r){
      if (!r.ok) { return r.json().then(function(j){ throw new Error(j.message || 'Delete failed'); }); }
      return r;
    })
    .then(function(){
      $('#imDeleteModal').modal('hide');
      showAlert('Investigation deleted successfully.', 'success');
      refreshTable();
    })
    .catch(function(err){
      showAlert(err.message, 'danger');
    })
    .finally(function(){
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash mr-1"></i> Delete';
      pendingDeleteId = null;
      pendingDeleteBtn = null;
    });
  });

  // ── SEARCH + FILTER ──
  function filterTable() {
    var q = (document.getElementById('imSearch').value || '').toLowerCase().trim();
    var cat = document.getElementById('imFilterCategory').value;

    document.querySelectorAll('#imTbody tr.im-row').forEach(function(r){
      var name = (r.dataset.name || '').toLowerCase();
      var rowCat = (r.dataset.category || '');
      var matchName = !q || name.indexOf(q) !== -1;
      var matchCat = !cat || rowCat === cat;
      r.style.display = (matchName && matchCat) ? '' : 'none';
    });

    var emptyRow = document.getElementById('imEmptyRow');
    var visible = document.querySelectorAll('#imTbody tr.im-row[style*="display:"]:not([style*="display: none"])').length +
                  document.querySelectorAll('#imTbody tr.im-row:not([style])').length;
    if (emptyRow) {
      emptyRow.style.display = visible === 0 ? '' : 'none';
    }
  }

  document.getElementById('imSearch').addEventListener('input', filterTable);
  document.getElementById('imFilterCategory').addEventListener('change', filterTable);

  // ── Close modals on Escape ──
  $(document).on('keydown', function(e){
    if (e.key === 'Escape') {
      $('.modal.show').modal('hide');
    }
  });

})();
</script>
@stop
