<?php

namespace App\Http\Controllers;

use App\Exceptions\FaceRecognitionApiException;
use App\Services\FaceRecognitionApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreamingCameraController extends Controller
{
    public function index(FaceRecognitionApi $faceApi)
    {
        $apiReady = $faceApi->isReady();
        $branches = [];

        if ($apiReady) {
            try {
                $branches = $faceApi->getBranches();
            } catch (FaceRecognitionApiException) {
                // branches kosong jika API error
            }
        }

        $branchId = '';
        try {
            $branchId = $faceApi->isConfigured() ? $faceApi->branchId() : '';
        } catch (FaceRecognitionApiException) {
            // fallback kosong
        }

        return view('streaming.index', compact('apiReady', 'branches', 'branchId'));
    }

    public function analyze(Request $request, FaceRecognitionApi $faceApi): JsonResponse
    {
        try {
            if (! $request->hasFile('frame') && $request->getContent() === '') {
                return response()->json(['message' => 'Frame kamera tidak ditemukan.'], 422);
            }

            $branchId = $request->input('branch_id', '');

            if ($request->hasFile('frame')) {
                $imageBytes = file_get_contents($request->file('frame')->getRealPath());
            } else {
                $imageBytes = $request->getContent();
            }

            $result = $faceApi->analyzeLiveCameraFrame($imageBytes, $branchId ?: null);

            return response()->json($result);
        } catch (FaceRecognitionApiException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->apiCode,
            ], min(max($e->status, 400), 599));
        }
    }
}
