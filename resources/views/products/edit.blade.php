@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Edit Product</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary btn-sm mb-2" href="{{ route('products.index') }}">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
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

<form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-group mb-2">
        <strong>Name:</strong>
        <input type="text" name="name" value="{{ $product->name }}" class="form-control" placeholder="Name">
    </div>

    <div class="form-group mb-2">
        <strong>Detail:</strong>
        <textarea class="form-control" style="height:150px" name="detail" placeholder="Detail">{{ $product->detail }}</textarea>
    </div>

    <div class="form-group mb-2">
        <strong>Upload CSV File:</strong>
        <input type="file" name="csv_file" class="form-control">
        @if($product->csv_file)
            <small>Current file: {{ $product->csv_file }}</small>
        @endif
    </div>

    <div class="text-center mt-3">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk"></i> Submit
        </button>
    </div>
</form>
@endsection
