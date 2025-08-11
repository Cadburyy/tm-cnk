@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-lg-12 d-flex justify-content-between align-items-center">
        <h2>Show Role</h2>
        <a class="btn btn-primary" href="{{ route('roles.index') }}">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="form-group">
            <strong>Name:</strong>
            {{ $role->name }}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <strong>Permissions:</strong>
            @if (!empty($rolePermissions))
                @foreach ($rolePermissions as $permission)
                    <span class="badge bg-success">{{ $permission->name }}</span>
                @endforeach
            @else
                <span>No permissions assigned.</span>
            @endif
        </div>
    </div>
</div>
@endsection