@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb d-flex justify-content-between align-items-center">
        <h2>Edit User</h2>
        <a class="btn btn-primary btn-sm" href="{{ route('users.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-2">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf
    @method('PUT')

    <div class="row mt-3">
        <div class="col-12 mb-3">
            <label><strong>Name:</strong></label>
            <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name', $user->name) }}">
        </div>
        <div class="col-12 mb-3">
            <label><strong>Email:</strong></label>
            <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email', $user->email) }}">
        </div>
        <div class="col-12 mb-3">
            <label><strong>Password:</strong></label>
            <input type="password" name="password" class="form-control" placeholder="Password">
        </div>
        <div class="col-12 mb-3">
            <label><strong>Confirm Password:</strong></label>
            <input type="password" name="confirm-password" class="form-control" placeholder="Confirm Password">
        </div>
        <div class="col-12 mb-3">
            <label><strong>Role:</strong></label>
            <select name="roles[]" class="form-control" multiple>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" {{ isset($userRole[$value]) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-floppy-disk"></i> Submit
            </button>
        </div>
    </div>
</form>

@endsection