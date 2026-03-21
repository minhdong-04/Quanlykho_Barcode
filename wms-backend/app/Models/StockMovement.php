<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $guarded = [];
    
    protected static function boot()
    {
        parent::boot();
        
        // Prevent updates/deletes
        static::updating(function () {
            throw new \RuntimeException('Stock movements cannot be updated');
        });
        
        static::deleting(function () {
            throw new \RuntimeException('Stock movements cannot be deleted');
        });
    }

    /**
     * Get the product associated with this movement
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed this movement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Supplier associated with this movement (optional)
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}

