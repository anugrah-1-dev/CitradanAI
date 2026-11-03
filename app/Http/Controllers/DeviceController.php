<?php

namespace App\Http\Controllers;

use App\Exceptions\FaceRecognitionApiException;
use App\Services\FaceRecognitionApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(FaceRecognitionApi $faceApi)
    {
        $apiReady = $faceApi->isReady();
        $devices = [];

        if ($apiReady) {
            try {
                $raw = $faceApi->getDevices();
                $devices = array_map(fn (array $d) => $this->mapDevice($d), $raw);
            } catch (FaceRecognitionApiException) {
                // kosong jika API error
            }
        }

        return view('devices.index', compact('apiReady', 'devices'));
    }

    public function store(Request $request, FaceRecognitionApi $faceApi): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:200',
            'device_code' => 'required|string|min:3|max:80',
            'location' => 'required|string|max:200',
            'rgb' => 'boolean',
            'active_liveness' => 'boolean',
            'depth' => 'boolean',
        ]);

        try {
            $result = $faceApi->createDevice([
                'name' => $request->input('name'),
                'device_code' => $request->input('device_code'),
                'capabilities' => [
                    'location' => $request->input('location'),
                    'rgb' => (bool) $request->input('rgb', true),
                    'active_liveness' => (bool) $request->input('active_liveness', true),
                    'depth' => (bool) $request->input('depth', false),
                ],
            ]);

            return response()->json([
                'device' => $this->mapDevice($result),
                'credential' => $result['credential'] ?? null,
            ], 201);
        } catch (FaceRecognitionApiException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->apiCode], min(max($e->status, 400), 599));
        }
    }

    public function toggle(Request $request, string $deviceId, FaceRecognitionApi $faceApi): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);

        try {
            $result = $faceApi->updateDevice($deviceId, [
                'is_active' => (bool) $request->input('is_active'),
            ]);

            return response()->json($this->mapDevice($result));
        } catch (FaceRecognitionApiException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->apiCode], min(max($e->status, 400), 599));
        }
    }

    public function rotateCredential(string $deviceId, FaceRecognitionApi $faceApi): JsonResponse
    {
        try {
            $result = $faceApi->rotateDeviceCredential($deviceId);

            return response()->json($result);
        } catch (FaceRecognitionApiException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->apiCode], min(max($e->status, 400), 599));
        }
    }

    public function refresh(FaceRecognitionApi $faceApi): JsonResponse
    {
        try {
            $raw = $faceApi->getDevices();
            $devices = array_map(fn (array $d) => $this->mapDevice($d), $raw);

            return response()->json(['devices' => $devices]);
        } catch (FaceRecognitionApiException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => $e->apiCode], min(max($e->status, 400), 599));
        }
    }

    private function mapDevice(array $device): array
    {
        $capabilities = $device['capabilities'] ?? [];
        $names = [];
        foreach (['rgb', 'depth', 'active_liveness'] as $key) {
            if (! empty($capabilities[$key])) {
                $names[] = $key === 'active_liveness' ? 'Liveness' : strtoupper($key);
            }
        }

        $lastSeen = $device['last_seen_at'] ?? null;
        $lastSeenTs = $lastSeen ? strtotime($lastSeen) : 0;
        $online = ($device['is_active'] ?? false) && $lastSeenTs > 0 && (time() - $lastSeenTs <= 120);

        $cameraStatus = $capabilities['camera_status'] ?? null;

        return [
            'id' => $device['id'] ?? '',
            'code' => $device['device_code'] ?? '',
            'name' => $device['name'] ?? '',
            'location' => is_string($capabilities['location'] ?? null) ? $capabilities['location'] : '-',
            'online' => $online,
            'active' => $device['is_active'] ?? false,
            'last_seen_at' => $lastSeen ?? '',
            'version' => is_string($capabilities['software_version'] ?? null) ? $capabilities['software_version'] : '-',
            'capabilities' => $names,
            'camera_status' => ($cameraStatus === 'ready' || $cameraStatus === 'Siap') ? 'Siap' : 'Terputus',
        ];
    }
}

