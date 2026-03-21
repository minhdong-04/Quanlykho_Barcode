<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = [
        'reference_code',
        'supplier_id',
        'created_by',
        'note',
    ];

    // Migration for `stock_ins` only defines `created_at`, not `updated_at`.
    // Disable automatic timestamps to avoid Eloquent inserting `updated_at`.
    public $timestamps = false;
}
