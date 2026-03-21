<?php

namespace App\Repositories\Contracts;

use App\Models\BarcodeDevice;
use Illuminate\Database\Eloquent\Collection;

interface BarcodeDeviceRepositoryInterface
{
    /**
     * Find device by ID
     */
    public function findById(int $id): ?BarcodeDevice;

    /**
     * Find device by fingerprint and user
     */
    public function findByFingerprint(int $userId, string $fingerprint): ?BarcodeDevice;

    /**
     * Get all devices for a user
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get active devices for a user
     */
    public function getActiveByUser(int $userId): Collection;

    /**
     * Get online devices for a user
     */
    public function getOnlineByUser(int $userId): Collection;

    /**
     * Get all online devices across all users
     */
    public function getAllOnline(): Collection;

    /**
     * Create a new device
     */
    public function create(array $data): BarcodeDevice;

    /**
     * Update device
     */
    public function update(int $id, array $data): BarcodeDevice;

    /**
     * Delete device
     */
    public function delete(int $id): bool;

    /**
     * Toggle device active status
     */
    public function toggleStatus(int $id): BarcodeDevice;

    /**
     * Update last activity timestamp
     */
    public function updateActivity(int $id): BarcodeDevice;
}
