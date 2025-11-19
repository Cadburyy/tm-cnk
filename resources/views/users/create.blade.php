@extends('layouts.app')

@section('content')
<<<<<<< HEAD
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New User</h2>
        <a class="btn btn-secondary" href="{{ route('users.index') }}">
            <i class="fa fa-arrow-left me-2"></i> Back
        </a>
    </div>

    @if (count($errors) > 0)
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
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label"><strong>Name:</strong></label>
                    <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{{ old('name') }}" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label"><strong>Email:</strong></label>
                    <input type="email" name="email" id="email" placeholder="Email" class="form-control" value="{{ old('email') }}" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label"><strong>Password:</strong></label>
                    <input type="password" name="password" id="password" placeholder="Password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="confirm-password" class="form-label"><strong>Confirm Password:</strong></label>
                    <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm Password" class="form-control">
                </div>
                <div class="col-12">
                    <label for="roles" class="form-label"><strong>Role:</strong></label>
                    <select name="roles[]" id="roles" class="form-select" multiple="multiple">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" {{ in_array($value, old('roles', [])) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
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
=======
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Create New User</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary btn-sm mb-2" href="{{ route('users.index') }}">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

@if (count($errors) > 0)
    <div class="alert alert-danger">
      <strong>Whoops!</strong> There were some problems with your input.<br><br>
      <ul>
         @foreach ($errors->all() as $error)
           <li>{{ $error }}</li>
         @endforeach
      </ul>
    </div>
@endif

<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                <input type="text" name="name" placeholder="Name" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Email:</strong>
                <input type="email" name="email" placeholder="Email" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Password:</strong>
                <input type="password" name="password" placeholder="Password" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Confirm Password:</strong>
                <input type="password" name="confirm-password" placeholder="Confirm Password" class="form-control">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Role:</strong>
                <select name="roles[]" class="form-control" multiple="multiple">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                     @endforeach
                </select>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3">
                <i class="fa-solid fa-floppy-disk"></i> Submit
            </button>
        </div>
    </div>
</form>

>>>>>>> 5aa1b22209bd856f792520ff8474479260a2d9d6
@endsection