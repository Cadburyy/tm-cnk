<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outing extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'akun',
        'voucher',
        'nama',
        'nama_pt',
        'part',
        'keterangan',
        'nominal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];
}