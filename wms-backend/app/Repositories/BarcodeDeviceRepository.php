<?php

namespace App\Repositories;

use App\Models\BarcodeDevice;
use App\Repositories\Contracts\BarcodeDeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BarcodeDeviceRepository implements BarcodeDeviceRepositoryInterface
{
    /**
     * Find device by ID
     */
    public function findById(int $id): ?BarcodeDevice
    {
        return BarcodeDevice::find($id);
    }

    /**
     * Find device by fingerprint and user
     */
    public function findByFingerprint(int $userId, string $fingerprint): ?BarcodeDevice
    {
        return BarcodeDevice::where('user_id', $userId)
            ->where('browser_fingerprint', $fingerprint)
            ->first();
    }

    /**
     * Get all devices for a user
     */
    public function getByUser(int $userId): Collection
    {
        return BarcodeDevice::byUser($userId)
            ->orderByDesc('last_active_at')
            ->get();
    }

    /**
     * Get active devices for a user
     */
    public function getActiveByUser(int $userId): Collection
    {
        return BarcodeDevice::byUser($userId)
            ->active()
            ->orderByDesc('last_active_at')
            ->get();
    }

    /**
     * Get online devices for a user
     */
    public function getOnlineByUser(int $userId): Collection
    {
        return BarcodeDevice::byUser($userId)
            ->online()
            ->orderByDesc('last_active_at')
            ->get();
    }

    /**
     * Get all online devices across all users
     */
    public function getAllOnline(): Collection
    {
        return BarcodeDevice::online()
            ->with('user')
            ->orderByDesc('last_active_at')
            ->get();
    }

    /**
     * Create a new device
     */
    public function create(array $data): BarcodeDevice
    {
        return BarcodeDevice::create($data);
    }

    /**
     * Update device
     */
    public function update(int $id, array $data): BarcodeDevice
    {
        $device = $this->findById($id);
        if (!$device) {
            throw new \Exception("Device with ID {$id} not found");
        }

        $device->update($data);
        return $device->fresh();
    }

    /**
     * Delete device
     */
    public function delete(int $id): bool
    {
        $device = $this->findById($id);
        if (!$device) {
            return false;
        }

        return (bool) $device->delete();
    }

    /**
     * Toggle device active status
     */
    public function toggleStatus(int $id): BarcodeDevice
    {
        $device = $this->findById($id);
        if (!$device) {
            throw new \Exception("Device with ID {$id} not found");
        }

        $device->is_active = !$device->is_active;
        $device->save();

        return $device->fresh();
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(int $id): BarcodeDevice
    {
        $device = $this->findById($id);
        if (!$device) {
            throw new \Exception("Device with ID {$id} not found");
        }

        $device->updateActivity();
        return $device->fresh();
    }
}
