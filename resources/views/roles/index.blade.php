@extends('layouts.app')

@section('content')
<div class="row mb-3">
    <div class="col-lg-12 d-flex justify-content-between align-items-center">
        <h2>Role Management</h2>
        @can('role-create')
            <a class="btn btn-success btn-sm" href="{{ route('roles.create') }}">
                <i class="fa fa-plus"></i> Create New Role
            </a>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th width="100px">No</th>
            <th>Name</th>
            <th width="280px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($roles as $key => $role)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $role->name }}</td>
            <td>
                <a class="btn btn-info btn-sm" href="{{ route('roles.show', $role->id) }}">
                    <i class="fa-solid fa-list"></i> Show
                </a>
                @can('role-edit')
                    <a class="btn btn-primary btn-sm" href="{{ route('roles.edit', $role->id) }}">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                @endcan
                @can('role-delete')
                    <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{!! $roles->links('pagination::bootstrap-5') !!}

@endsection