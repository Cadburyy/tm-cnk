<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','show']]);
        $this->middleware('permission:product-create', ['only' => ['create','store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $products = Product::latest()->paginate(5);
        return view('products.index', compact('products'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
        ]);

        Product::create($request->only(['name', 'detail']));

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $csvData = $product->csvRows()->get()->toArray();
        return view('products.show', compact('product', 'csvData'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
            'csv_file' => 'nullable|file|mimes:csv,txt',
        ]);

        $data = $request->only(['name', 'detail']);

        if ($request->hasFile('csv_file')) {
            if ($product->csv_file && Storage::disk('public')->exists($product->csv_file)) {
                Storage::disk('public')->delete($product->csv_file);
            }
            $path = $request->file('csv_file')->store('csv_files', 'public');
            $data['csv_file'] = $path;
            $product->update($data);

            $product->csvRows()->delete();

            if (($handle = fopen(storage_path("app/public/{$path}"), 'r')) !== false) {
                $header = fgetcsv($handle, 1000, ',');
                if ($header !== false) {
                    // Normalize headers: lowercase + underscores instead of spaces
                    $header = array_map(function ($h) {
                        return strtolower(str_replace(' ', '_', trim($h)));
                    }, $header);

                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($header) !== count($row)) {
                            continue; // skip rows with mismatch columns
                        }

                        // Trim all values
                        $row = array_map('trim', $row);

                        $rowData = array_combine($header, $row);

                        $product->csvRows()->create([
                            'order' => $rowData['order'] ?? null,
                            'supplier' => $rowData['supplier'] ?? null,
                            'internal_reference' => $rowData['internal_reference'] ?? null,
                            'item_number' => $rowData['item_number'] ?? null,
                            'description' => $rowData['description'] ?? null,
                            'description2' => $rowData['description2'] ?? null,
                            'quantity_ordered' => $rowData['quantity_ordered'] ?? null,
                            'unit_of_measure' => $rowData['unit_of_measure'] ?? null,
                            'po_cost' => $rowData['po_cost'] ?? null,
                            'currency' => $rowData['currency'] ?? null,
                            'taxable' => isset($rowData['taxable']) ? (bool) $rowData['taxable'] : null,
                            'tax_class' => $rowData['tax_class'] ?? null,
                            'tax_rate' => $rowData['tax_rate'] ?? null,
                            'receipt_date' => $rowData['receipt_date'] ?? null,
                            'external_reference' => $rowData['external_reference'] ?? null,
                            'transaction_date' => $rowData['transaction_date'] ?? null,
                            'receipt_quantity' => $rowData['receipt_quantity'] ?? null,
                            'receipt_price' => $rowData['receipt_price'] ?? null,
                        ]);
                    }
                }
                fclose($handle);
            }
        } else {
            $product->update($data);
        }

        return redirect()->route('products.show', $product->id)
            ->with('success', 'Product updated successfully with CSV data.');
    }

    public function exportSelected(Request $request, Product $product): StreamedResponse
    {
        $selectedIndexes = $request->input('selected_rows', []);
        if (empty($selectedIndexes)) {
            return back()->with('error', 'No rows selected for export.');
        }

        // Because in show.blade you are using a custom string as value,
        // you might need to parse these strings here to match database rows.
        // But if you want to export based on IDs, the value should be the row ID instead.
        // Assuming you want to export by IDs, change your blade checkbox value to row id.

        $rowsToExport = $product->csvRows()->whereIn('id', $selectedIndexes)->get()->toArray();

        return response()->streamDownload(function () use ($rowsToExport) {
            $handle = fopen('php://output', 'w');
            if (!empty($rowsToExport)) {
                fputcsv($handle, array_keys($rowsToExport[0]));
                foreach ($rowsToExport as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        }, 'selected_rows.csv', ['Content-Type' => 'text/csv']);
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->csv_file && Storage::disk('public')->exists($product->csv_file)) {
            Storage::disk('public')->delete($product->csv_file);
        }
        $product->csvRows()->delete();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }
}
