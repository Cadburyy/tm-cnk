{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Product Details</h1>

    {{-- Product basic info --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ $product->name }}</h4>
            <p>{{ $product->detail }}</p>
        </div>
    </div>

    {{-- CSV filter form --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter CSV Data</h5>
            <form action="{{ route('products.show', $product->id) }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="order_code" class="form-label">Order Code</label>
                        <input type="text" class="form-control" id="order_code" name="order_code" placeholder="Filter by order code" value="{{ request('order_code') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Search</button>
                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CSV display and selection --}}
    @if (request()->filled('order_code'))
        @if (!empty($csvData))
            <form action="{{ route('products.exportSelected', $product->id) }}" method="POST">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Order</th>
                                <th>Supplier</th>
                                <th>Internal Reference</th>
                                <th>Receipt Date</th>
                                <th>Item Number</th>
                                <th>Description</th>
                                <th>Description 2</th>
                                <th>Quantity Ordered</th>
                                <th>Receipt Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($csvData as $row)
                            <tr>
                                <td><input type="checkbox" name="selected_rows[]" value="{{ $row['id'] }}"></td>
                                <td>{{ $row['order_code'] ?? '' }}</td>
                                <td>{{ $row['supplier'] ?? '' }}</td>
                                <td>{{ $row['internal_reference'] ?? '' }}</td>
                                <td>{{ $row['receipt_date'] ?? '' }}</td>
                                <td>{{ $row['item_number'] ?? '' }}</td>
                                <td>{{ $row['description'] ?? '' }}</td>
                                <td>{{ $row['description2'] ?? '' }}</td>
                                <td>{{ $row['quantity_ordered'] ?? '' }}</td>
                                <td>{{ $row['receipt_quantity'] ?? '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    Export Selected to CSV
                </button>
            </form>
        @else
            <p>No CSV data found for the order code '{{ request('order_code') }}'.</p>
        @endif
    @endif

    {{-- Back button --}}
    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
