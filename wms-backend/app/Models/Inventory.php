<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            if ($model->quantity < 0) {
                throw new \InvalidArgumentException('Inventory quantity cannot be negative');
            }
        });
    }
    
    // Also add accessor
    public function setQuantityAttribute($value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Inventory quantity cannot be negative');
        }
        $this->attributes['quantity'] = $value;
    }

    protected $fillable = [
        'product_id',
        'quantity',
        'reorder_level',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}