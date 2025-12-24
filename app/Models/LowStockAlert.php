<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LowStockAlert extends Model
{
    protected $table = 'low_stock_alerts';
    protected $fillable = ['product_id', 'current_quantity', 'reorder_level', 'notified'];
}
