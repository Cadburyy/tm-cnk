@extends('layouts.app')

@section('content')
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