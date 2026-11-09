<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FaceRecognitionApi;
use Tests\TestCase;

class AttendanceFaceCheckinTest extends TestCase
{
    public function test_blurry_face_is_reported_as_a_quality_issue_not_unregistered(): void
    {
        $imageBytes = str_repeat('x', 64);

        $this->mock(FaceRecognitionApi::class)
            ->shouldReceive('analyzeLiveCameraFrame')
            ->once()
            ->with($imageBytes)
            ->andReturn([
                'faces' => [[
                    'registration' => 'UNREGISTERED',
                    'reason_code' => 'IMAGE_TOO_BLURRY',
                ]],
            ]);

        $user = new User(['two_factor_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->postJson('/attendance/face-checkin', [
                'image' => base64_encode($imageBytes),
            ]);

        $response->assertOk()->assertExactJson([
            'decision' => 'DENY',
            'code' => 'IMAGE_TOO_BLURRY',
            'title' => 'Wajah Kurang Jelas',
            'message' => 'Foto wajah kurang jelas atau terlalu jauh. Silakan mendekat ke kamera.',
        ]);
    }
}
