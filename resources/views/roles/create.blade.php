@extends('layouts.app')

@section('content')
<<<<<<< HEAD
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Role</h2>
        <a class="btn btn-secondary" href="{{ route('roles.index') }}">
            <i class="fa fa-arrow-left me-2"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm p-4">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label"><strong>Name:</strong></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Role Name" value="{{ old('name') }}" autocomplete="off">
                </div>
                <div class="col-12">
                    <label class="form-label"><strong>Permissions:</strong></label>
                    <div class="row">
                        @foreach($permission as $perm)
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="permission[]" value="{{ $perm->id }}" class="form-check-input" id="permission-{{ $perm->id }}" {{ old("permission.{$perm->id}") ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission-{{ $perm->id }}">
                                        {{ $perm->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
=======
<div class="row mb-3">
    <div class="col-lg-12 d-flex justify-content-between align-items-center">
        <h2>Create New Role</h2>
        <a class="btn btn-primary btn-sm" href="{{ route('roles.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('roles.store') }}">
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label><strong>Name:</strong></label>
            <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
        </div>
        <div class="col-12 mb-3">
            <label><strong>Permission:</strong></label><br>
            @foreach($permission as $perm)
                <label>
                    <input type="checkbox" name="permission[{{ $perm->id }}]" value="{{ $perm->id }}" class="me-1">
                    {{ $perm->name }}
                </label><br>
            @endforeach
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-floppy-disk"></i> Submit
            </button>
        </div>
    </div>
</form>

@endsection
>>>>>>> 5aa1b22209bd856f792520ff8474479260a2d9d6
