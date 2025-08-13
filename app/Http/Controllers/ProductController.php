<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ProductCsvRow;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /**
     * Set up middleware for permissions.
     */
    function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','show']]);
        $this->middleware('permission:product-create', ['only' => ['create','store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get the latest products without any filtering.
        $products = Product::latest()->paginate(5);
        return view('products.index', compact('products'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return View
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
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

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Product $product
     * @return View
     */
    public function show(Request $request, Product $product): View
    {
        $csvData = [];
        $query = $product->csvRows();
        
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user's name exists as a supplier code in the database
        $isSupplier = false;
        if ($user) {
            $supplierCount = ProductCsvRow::where('supplier', $user->name)->count();
            if ($supplierCount > 0) {
                $isSupplier = true;
                // If the user is a supplier, filter the query by their supplier code
                $query->where('supplier', $user->name);
            }
        }
        
        // Apply the order_code filter if it's provided in the request
        if ($request->filled('order_code')) {
            $query->where('order_code', 'like', '%' . $request->input('order_code') . '%');
        }

        // Only retrieve data if a search was performed or if the user is a supplier
        if ($request->filled('order_code') || $isSupplier) {
            $csvData = $query->get()->toArray();
        }

        // Pass the data to the view
        return view('products.show', compact('product', 'csvData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return View
     */
    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
            'csv_files' => 'nullable|array', // Validate that csv_files is an array
            'csv_files.*' => 'file|mimes:csv,txt', // Validate each file in the array
        ]);

        $data = $request->only(['name', 'detail']);

        // Update product name and detail
        $product->update($data);

        // Check if new CSV files were uploaded
        if ($request->hasFile('csv_files')) {
            // Loop through each uploaded file
            foreach ($request->file('csv_files') as $file) {
                $path = $file->store('csv_files', 'public');

                // Helper function to clean and convert numeric strings
                $convertNumber = function ($numStr) {
                    if (!is_string($numStr)) return $numStr;
                    $numStr = str_replace('.', '', $numStr);
                    $numStr = str_replace(',', '.', $numStr);
                    return is_numeric($numStr) ? (float)$numStr : null;
                };

                // Helper function to convert percentage strings
                $convertPercentage = function ($percentStr) use ($convertNumber) {
                    if (!is_string($percentStr)) return $percentStr;
                    $percentStr = str_replace('%', '', $percentStr);
                    return $convertNumber($percentStr);
                };

                if (($handle = fopen(storage_path("app/public/{$path}"), 'r')) !== false) {
                    $csvHeaders = fgetcsv($handle, 1000, ';');

                    // Map CSV headers to database column names
                    $mapping = [
                        'Supplier' => 'supplier',
                        'Order' => 'order_code',
                        'Internal Reference' => 'internal_reference',
                        'Item Number' => 'item_number',
                        'Description' => 'description',
                        'Description2' => 'description2',
                        'Quantity Ordered' => 'quantity_ordered',
                        'Unit of Measure' => 'unit_of_measure',
                        'PO Cost' => 'po_cost',
                        'Currency' => 'currency',
                        'Taxable' => 'taxable',
                        'Tax Class' => 'tax_class',
                        'Tax Rate' => 'tax_rate',
                        'Receipt Date' => 'receipt_date',
                        'External Reference' => 'external_reference',
                        'Transaction Date' => 'transaction_date',
                        'Receipt Quantity' => 'receipt_quantity',
                        'Receipt_Price' => 'receipt_price', // Corrected header
                    ];

                    while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                        $rowData = [];
                        foreach ($csvHeaders as $i => $header) {
                            $header = trim($header);
                            if (!isset($mapping[$header])) continue;
                            $key = $mapping[$header];
                            $value = isset($row[$i]) ? trim($row[$i]) : null;
                            
                            // Convert all strings to UTF-8
                            if (is_string($value)) {
                                $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                            }
                            
                            $rowData[$key] = $value;
                        }

                        // Apply type conversions and cleaning
                        $rowData['quantity_ordered'] = (int) $convertNumber($rowData['quantity_ordered'] ?? null);
                        $rowData['po_cost'] = $convertNumber($rowData['po_cost'] ?? null);
                        $rowData['tax_rate'] = $convertPercentage($rowData['tax_rate'] ?? null);
                        $rowData['receipt_quantity'] = (int) $convertNumber($rowData['receipt_quantity'] ?? null);
                        $rowData['receipt_price'] = $convertNumber($rowData['receipt_price'] ?? null);
                        $rowData['taxable'] = (strtolower($rowData['taxable'] ?? '') === 'yes');

                        try {
                            $rowData['receipt_date'] = !empty($rowData['receipt_date']) ? Carbon::createFromFormat('d/m/Y', $rowData['receipt_date'])->format('Y-m-d') : null;
                        } catch (\Exception $e) {
                            $rowData['receipt_date'] = null;
                        }
                        try {
                            $rowData['transaction_date'] = !empty($rowData['transaction_date']) ? Carbon::createFromFormat('d/m/Y', $rowData['transaction_date'])->format('Y-m-d') : null;
                        } catch (\Exception $e) {
                            $rowData['transaction_date'] = null;
                        }

                        $product->csvRows()->create($rowData);
                    }
                    fclose($handle);
                }
            }
        }

        return redirect()->route('products.show', $product->id)
            ->with('success', 'Product updated successfully with CSV data.');
    }

    /**
     * Export selected CSV rows to a file.
     *
     * @param Request $request
     * @param Product $product
     * @return StreamedResponse|RedirectResponse
     */
    public function exportSelected(Request $request, Product $product): StreamedResponse
    {
        $selectedIds = $request->input('selected_rows', []);
        if (empty($selectedIds)) {
            return back()->with('error', 'No rows selected for export.');
        }

        $rowsToExport = $product->csvRows()->whereIn('id', $selectedIds)->get()->toArray();

        return response()->streamDownload(function () use ($rowsToExport) {
            $handle = fopen('php://output', 'w');
            if (!empty($rowsToExport)) {
                // Use semicolon as delimiter for consistency with the original file
                fputcsv($handle, array_keys($rowsToExport[0]), ';');
                foreach ($rowsToExport as $row) {
                    fputcsv($handle, $row, ';');
                }
            }
            fclose($handle);
        }, 'selected_rows.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return RedirectResponse
     */
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
