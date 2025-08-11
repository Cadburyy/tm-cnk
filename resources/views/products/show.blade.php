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

    {{-- CSV display and selection --}}
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
                        @php
                            $order = $row['order'] ?? '';
                            $supplier = $row['supplier'] ?? '';
                            $internalRef = $row['internal_reference'] ?? '';
                            $receiptDate = $row['receipt_date'] ?? '';
                            $itemNumber = $row['item_number'] ?? '';
                            $description = $row['description'] ?? '';
                            $description2 = $row['description2'] ?? '';
                            $quantityOrdered = $row['quantity_ordered'] ?? '';
                            $receiptQuantity = $row['receipt_quantity'] ?? '';

                            $exportString = implode('|', [
                                $order, $supplier, $internalRef, $receiptDate,
                                $itemNumber, $description, $description2,
                                $quantityOrdered, $receiptQuantity
                            ]);
                        @endphp

                        <tr>
                            <td><input type="checkbox" name="selected_rows[]" value="{{ $exportString }}"></td>
                            <td>{{ $order }}</td>
                            <td>{{ $supplier }}</td>
                            <td>{{ $internalRef }}</td>
                            <td>{{ $receiptDate }}</td>
                            <td>{{ $itemNumber }}</td>
                            <td>{{ $description }}</td>
                            <td>{{ $description2 }}</td>
                            <td>{{ $quantityOrdered }}</td>
                            <td>{{ $receiptQuantity }}</td>
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
        <p>No CSV file uploaded for this product.</p>
    @endif

    {{-- Back button --}}
    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
