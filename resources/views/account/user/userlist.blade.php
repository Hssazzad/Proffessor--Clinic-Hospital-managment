@extends('adminlte::page')

@section('title', 'User List')

@section('content_header')
    <h4>User List</h4>
@stop

@section('content')

<div class="card">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">All Users</h5>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-striped table-sm">
            <thead class="bg-dark text-white">
                <tr>
                    <th>ID</th>
                    <th>Account Type</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Email Verified</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->accounttype }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->mobile }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Verified</span>
                            @else
                                <span class="badge badge-danger">Not Verified</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at }}</td>
                        <td>{{ $user->updated_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No user found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@stop