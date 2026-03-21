<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'reference_code',
        'created_by',
        'note',
    ];

    // Migration for `stock_outs` only defines `created_at`, not `updated_at`.
    // Disable automatic timestamps to avoid Eloquent inserting `updated_at`.
    public $timestamps = false;
}
