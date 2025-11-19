<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'detail',
        'csv_file', // add this so Laravel allows mass assignment
    ];

    public function csvRows()
    {
        return $this->hasMany(ProductCsvRow::class);
    }
}
