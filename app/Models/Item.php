<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_number',
        'item_description',
        'effective_date',
        'bulan',
        'loc_qty_change',
        'unit_of_measure',
        'remarks',
        'item_group',
        'dept'
    ];
}