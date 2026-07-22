@extends('adminlte::page')

@section('title','Create User')

@section('content')
<div class="card">
  <div class="card-header bg-primary text-white">Create User</div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('user.store') }}">
      @csrf
      <div class="mb-3">
        <label>Name</label>
        <input name="name" value="{{ old('name') }}" class="form-control" required>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
      </div>

      <button class="btn btn-success"><i class="fas fa-user-plus"></i> Create</button>
    </form>
  </div>
</div>

<form action="{{ route('system.clear') }}" method="POST">
    @csrf
    <button class="btn btn-danger">Clear All Caches</button>
</form>
<div class="topbar">
	  <?php echo " <a href='https://auth-db1498.hstgr.io/' target='_blank'>Database</a>"; ?>
     	      
	   </div>
      DB_DATABASE=u972011074_vzeTw <br>
DB_USERNAME=u972011074_YkczZ <br>
DB_PASSWORD=I]WCh[dZZ2&6u84G
@endsection
