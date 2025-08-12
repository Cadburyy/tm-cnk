<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCsvRow extends Model
{
    protected $fillable = [
        'product_id',
        'order_code',
        'supplier',
        'internal_reference',
        'item_number',
        'description',
        'description2',
        'quantity_ordered',
        'unit_of_measure',
        'po_cost',
        'currency',
        'taxable',
        'tax_class',
        'tax_rate',
        'receipt_date',
        'external_reference',
        'transaction_date',
        'receipt_quantity',
        'receipt_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
