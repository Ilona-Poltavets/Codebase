<?php

use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceToken;
use App\Models\Screenshot;
use App\Models\TimeSegment;
use App\Models\TimeSession;
use App\Models\User;

test('desktop tracker api auth flow and data ingest works', function () {
    $company = Company::create([
        'name' => 'Tracker Company',
        'description' => 'Tracker test company',
        'domain' => 'tracker.local',
        'owner_id' => 1,
        'plan' => 'free',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Tracker User',
        'company_id' => $company->id,
        'password' => 'password',
    ]);

    $deviceCodeResponse = $this->postJson('/api/tracker/auth/device-code', [
        'email' => $user->email,
        'password' => 'password',
        'device_uuid' => 'desktop-uuid-001',
        'device_name' => 'Desktop Windows',
        'platform' => 'windows',
        'app_version' => '1.0.0',
    ])->assertOk();

    $deviceCode = (string) $deviceCodeResponse->json('device_code');
    expect(strlen($deviceCode))->toBe(8);

    $tokenResponse = $this->postJson('/api/tracker/auth/token', [
        'device_uuid' => 'desktop-uuid-001',
        'device_code' => $deviceCode,
    ])->assertOk();

    $accessToken = (string) $tokenResponse->json('access_token');
    $refreshToken = (string) $tokenResponse->json('refresh_token');
    expect($accessToken)->not->toBe('');
    expect($refreshToken)->not->toBe('');

    $sessionResponse = $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/tracker/sessions', [
            'started_at' => now()->subMinutes(20)->toIso8601String(),
            'timezone' => 'Europe/Berlin',
            'meta' => ['source' => 'desktop'],
        ])->assertCreated();

    $sessionId = (int) $sessionResponse->json('id');
    expect($sessionId)->toBeGreaterThan(0);

    $segmentResponse = $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/tracker/segments', [
            'segments' => [[
                'session_id' => $sessionId,
                'started_at' => now()->subMinutes(15)->toIso8601String(),
                'ended_at' => now()->subMinutes(10)->toIso8601String(),
                'seconds' => 300,
                'activity_level' => 87,
                'is_idle' => false,
                'app_name' => 'Codebase',
                'window_title' => 'Tracker',
            ]],
        ])->assertCreated();

    $segmentId = (int) $segmentResponse->json('segment_ids.0');
    expect($segmentId)->toBeGreaterThan(0);

    $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->postJson('/api/tracker/screenshots', [
            'session_id' => $sessionId,
            'segment_id' => $segmentId,
            'taken_at' => now()->toIso8601String(),
            'width' => 1920,
            'height' => 1080,
            'is_blurred' => false,
            'sha256' => str_repeat('a', 64),
        ])->assertCreated();

    $refreshResponse = $this->postJson('/api/tracker/auth/refresh', [
        'refresh_token' => $refreshToken,
    ])->assertOk();

    $newAccessToken = (string) $refreshResponse->json('access_token');
    expect($newAccessToken)->not->toBe('');

    $this->withHeader('Authorization', 'Bearer '.$newAccessToken)
        ->postJson('/api/tracker/auth/revoke')
        ->assertNoContent();

    $this->withHeader('Authorization', 'Bearer '.$newAccessToken)
        ->postJson('/api/tracker/sessions', [
            'started_at' => now()->toIso8601String(),
        ])->assertStatus(401);

    expect(Device::count())->toBe(1);
    expect(TimeSession::count())->toBe(1);
    expect(TimeSegment::count())->toBe(1);
    expect(Screenshot::count())->toBe(1);
    expect(DeviceToken::whereNull('revoked_at')->count())->toBe(0);
});

test('tracker protected endpoints reject missing bearer token', function () {
    $this->postJson('/api/tracker/sessions', [
        'started_at' => now()->toIso8601String(),
    ])->assertStatus(401);
});
