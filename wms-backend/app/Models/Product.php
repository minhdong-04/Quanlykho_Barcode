<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'unit',
        'purchase_price',
        'sale_price',
        'quantity',
        'low_stock_threshold',
    ];

    /**
     * Get the inventory record for this product
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'product_id');
    }

    public function stockLogs() { return $this->hasMany(StockLog::class); }
    public function lowStockAlerts() { return $this->hasMany('App\Models\LowStockAlert'); }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)->withTimestamps();
    }

    /**
     * Get current quantity from inventory table
     * This accessor ensures we always return the real inventory quantity
     */
    public function getCurrentQuantityAttribute(): int
    {
        return $this->inventory?->quantity ?? 0;
    }
}

