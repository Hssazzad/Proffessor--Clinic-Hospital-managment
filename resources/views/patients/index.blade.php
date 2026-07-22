{{-- resources/views/patients/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Patient List')

@section('content_header')
  <h1>Patient List</h1>
@stop

@section('content')
@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Photo</th>
          <th>Patient Name</th>
          <th>Mobile</th>
          <th>Age</th>
          <th>Gender</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      @forelse($patients as $p)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>
            <img src="{{ !empty($p->photo) ? asset('uploads/photos/' . $p->photo) : asset('uploads/photos/no_image.png') }}"
                 style="width:45px;height:45px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
          </td>
          <td>{{ $p->patientname }}</td>
          <td>{{ $p->mobile_no ?? $p->mobileno }}</td>
          <td>{{ $p->age ?? '-' }}</td>
          <td>{{ $p->gender ?? '-' }}</td>
          <td>
            {{-- View / Edit buttons (if any) --}}
            <a href="{{ url('patients/'.$p->id.'/edit') }}" class="btn btn-sm btn-info">
              Edit
            </a>

            {{-- Admit button: new page --}}
            <a href="{{ route('patients.admit.create', $p->id) }}"
               class="btn btn-sm btn-success">
              Admit
            </a>

            {{-- OR: Open modal (AJAX) - see section 6 --}}
            {{-- <button type="button" class="btn btn-sm btn-success"
                    onclick="openAdmit({{ $p->id }})">
                Admit
            </button> --}}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted">No patients found.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-footer">
    {{ $patients->links() ?? '' }}
  </div>
</div>

{{-- Optional modal holder for AJAX admit form --}}
<div class="modal fade" id="admitModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body" id="admitModalBody"></div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  function openAdmit(id){
      // load admit form by AJAX into modal
      $('#admitModalBody').html('<p class="p-3">Loading...</p>');
      $('#admitModal').modal('show');

      fetch('/patients/' + id + '/admit')
          .then(res => res.text())
          .then(html => {
              $('#admitModalBody').html(html);
          })
          .catch(() => {
              $('#admitModalBody').html('<div class="p-3 text-danger">Failed to load.</div>');
          });
  }
</script>
@endsection
