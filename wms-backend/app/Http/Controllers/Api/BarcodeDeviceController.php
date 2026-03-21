<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterBarcodeDeviceRequest;
use App\Http\Requests\UpdateBarcodeDeviceStatusRequest;
use App\Http\Requests\UpdateDeviceActivityRequest;
use App\Services\BarcodeDeviceService;
use Illuminate\Http\JsonResponse;

class BarcodeDeviceController extends Controller
{
    public function __construct(
        private BarcodeDeviceService $deviceService,
    ) {}

    /**
     * Get all barcode devices for the authenticated user
     * 
     * GET /api/barcode-devices
     */
    public function index(): JsonResponse
    {
        try {
            $devices = $this->deviceService->getUserDevices(auth()->id());

            return response()->json([
                'message' => 'Devices retrieved successfully',
                'data' => $devices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve devices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get online devices for the authenticated user
     * 
     * GET /api/barcode-devices/online
     */
    public function online(): JsonResponse
    {
        try {
            $devices = $this->deviceService->getOnlineDevices(auth()->id());

            return response()->json([
                'message' => 'Online devices retrieved successfully',
                'data' => $devices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve online devices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register a new barcode device
     * 
     * POST /api/barcode-devices/register
     */
    public function register(RegisterBarcodeDeviceRequest $request): JsonResponse
    {
        try {
            $device = $this->deviceService->registerDevice(
                userId: auth()->id(),
                deviceName: $request->device_name,
                deviceType: $request->device_type,
                browserFingerprint: $request->browser_fingerprint,
            );

            return response()->json([
                'message' => 'Device registered successfully',
                'data' => $device,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register device',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update device heartbeat (mark as active)
     * 
     * POST /api/barcode-devices/{id}/heartbeat
     */
    public function heartbeat(UpdateDeviceActivityRequest $request): JsonResponse
    {
        try {
            $deviceId = $request->device_id;

            // Verify device belongs to user
            if (!$this->deviceService->deviceBelongsToUser($deviceId, auth()->id())) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $device = $this->deviceService->updateDeviceActivity($deviceId);

            return response()->json([
                'message' => 'Device activity updated',
                'data' => $device,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update device activity',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get single device by ID
     * 
     * GET /api/barcode-devices/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getDevice($id);

            if (!$device) {
                return response()->json([
                    'message' => 'Device not found',
                ], 404);
            }

            // Verify device belongs to user
            if ($device->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'message' => 'Device retrieved successfully',
                'data' => $device,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle device status (Admin only)
     * 
     * POST /api/barcode-devices/toggle-status
     */
    public function toggleStatus(UpdateBarcodeDeviceStatusRequest $request): JsonResponse
    {
        try {
            $deviceId = $request->device_id;
            $device = $this->deviceService->toggleDeviceStatus($deviceId);

            $action = $device->is_active ? 'enabled' : 'disabled';

            return response()->json([
                'message' => "Device {$action} successfully",
                'data' => $device,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle device status',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete a device (Admin only or owner)
     * 
     * DELETE /api/barcode-devices/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Only admin or device owner can delete
            $device = $this->deviceService->getDevice($id);
            if (!$device || ($device->user_id !== auth()->id() && auth()->user()->role !== 'admin')) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $deleted = $this->deviceService->deleteDevice($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Device not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Device deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete device',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
