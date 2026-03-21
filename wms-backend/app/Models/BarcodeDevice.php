<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarcodeDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'browser_fingerprint',
        'last_active_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this device
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get only active devices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get online devices (active in last 5 minutes)
     */
    public function scopeOnline($query)
    {
        return $query->where('is_active', true)
            ->where('last_active_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope: Get devices by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Mark device as active (heartbeat)
     */
    public function updateActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    /**
     * Check if device is currently online
     */
    public function isOnline(): bool
    {
        return $this->is_active && $this->last_active_at?->isAfter(now()->subMinutes(5));
    }
}
