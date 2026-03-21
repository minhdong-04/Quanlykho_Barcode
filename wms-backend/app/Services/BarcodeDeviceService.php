<?php

namespace App\Services;

use App\Exceptions\Domain\InvalidBarcodeException;
use App\Models\BarcodeDevice;
use App\Repositories\Contracts\BarcodeDeviceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BarcodeDeviceService
{
    public function __construct(
        private BarcodeDeviceRepositoryInterface $deviceRepository,
    ) {}

    /**
     * Register a new barcode device
     * 
     * @param int $userId
     * @param string $deviceName
     * @param string $deviceType (bluetooth|usb|camera)
     * @param string $browserFingerprint
     * @return BarcodeDevice
     */
    public function registerDevice(
        int $userId,
        string $deviceName,
        string $deviceType,
        string $browserFingerprint
    ): BarcodeDevice {
        // Check if device already exists with same fingerprint
        $existingDevice = $this->deviceRepository->findByFingerprint(
            $userId,
            $browserFingerprint
        );

        if ($existingDevice) {
            // Update last activity and return existing device
            return $this->deviceRepository->updateActivity($existingDevice->id);
        }

        // Create new device
        return $this->deviceRepository->create([
            'user_id' => $userId,
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'browser_fingerprint' => $browserFingerprint,
            'last_active_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Get all devices for a user
     */
    public function getUserDevices(int $userId): Collection
    {
        return $this->deviceRepository->getByUser($userId);
    }

    /**
     * Get active devices for a user
     */
    public function getActiveDevices(int $userId): Collection
    {
        return $this->deviceRepository->getActiveByUser($userId);
    }

    /**
     * Get online devices for a user (last activity within 5 minutes)
     */
    public function getOnlineDevices(int $userId): Collection
    {
        return $this->deviceRepository->getOnlineByUser($userId);
    }

    /**
     * Get all online devices across all users (for admin monitoring)
     */
    public function getAllOnlineDevices(): Collection
    {
        return $this->deviceRepository->getAllOnline();
    }

    /**
     * Get device by ID
     */
    public function getDevice(int $deviceId): ?BarcodeDevice
    {
        return $this->deviceRepository->findById($deviceId);
    }

    /**
     * Update device activity (heartbeat from client)
     * This ensures the device stays marked as "online"
     */
    public function updateDeviceActivity(int $deviceId): BarcodeDevice
    {
        return DB::transaction(function () use ($deviceId) {
            $device = $this->getDevice($deviceId);

            if (!$device) {
                throw new InvalidBarcodeException("Device not found");
            }

            return $this->deviceRepository->updateActivity($deviceId);
        });
    }

    /**
     * Toggle device status (block/unblock by admin)
     * 
     * @param int $deviceId
     * @return BarcodeDevice
     */
    public function toggleDeviceStatus(int $deviceId): BarcodeDevice
    {
        return DB::transaction(function () use ($deviceId) {
            $device = $this->getDevice($deviceId);

            if (!$device) {
                throw new InvalidBarcodeException("Device not found");
            }

            $updated = $this->deviceRepository->toggleStatus($deviceId);

            // Broadcast event to client to disconnect if blocked
            if (!$updated->is_active) {
                \App\Events\DeviceBlockedEvent::dispatch($updated);
            }

            return $updated;
        });
    }

    /**
     * Delete a device
     */
    public function deleteDevice(int $deviceId): bool
    {
        return DB::transaction(function () use ($deviceId) {
            $device = $this->getDevice($deviceId);

            if (!$device) {
                throw new InvalidBarcodeException("Device not found");
            }

            return $this->deviceRepository->delete($deviceId);
        });
    }

    /**
     * Validate device is active before allowing scan operations
     * 
     * @param int $deviceId
     * @return bool
     */
    public function isDeviceActive(int $deviceId): bool
    {
        $device = $this->getDevice($deviceId);
        return $device && $device->is_active;
    }

    /**
     * Check if device belongs to user
     */
    public function deviceBelongsToUser(int $deviceId, int $userId): bool
    {
        $device = $this->getDevice($deviceId);
        return $device && $device->user_id === $userId;
    }
}
