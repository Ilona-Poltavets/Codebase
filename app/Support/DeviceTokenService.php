<?php

namespace App\Support;

use App\Models\Device;
use App\Models\DeviceToken;
use Illuminate\Support\Str;

class DeviceTokenService
{
    /**
     * @return array{access_token:string,refresh_token:string,access_expires_at:\Illuminate\Support\Carbon,refresh_expires_at:\Illuminate\Support\Carbon}
     */
    public function issuePair(Device $device): array
    {
        $accessTtl = max(1, (int) config('tracker.access_token_ttl_minutes', 480));
        $refreshTtlDays = max(1, (int) config('tracker.refresh_token_ttl_days', 30));

        $accessToken = Str::random(80);
        $refreshToken = Str::random(96);
        $accessExpiresAt = now()->addMinutes($accessTtl);
        $refreshExpiresAt = now()->addDays($refreshTtlDays);

        DeviceToken::create([
            'device_id' => $device->id,
            'user_id' => $device->user_id,
            'company_id' => $device->company_id,
            'token_type' => 'access',
            'token_hash' => hash('sha256', $accessToken),
            'expires_at' => $accessExpiresAt,
        ]);

        DeviceToken::create([
            'device_id' => $device->id,
            'user_id' => $device->user_id,
            'company_id' => $device->company_id,
            'token_type' => 'refresh',
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => $refreshExpiresAt,
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'access_expires_at' => $accessExpiresAt,
            'refresh_expires_at' => $refreshExpiresAt,
        ];
    }

    public function findActive(string $plainToken, string $type): ?DeviceToken
    {
        return DeviceToken::with(['user', 'device'])
            ->where('token_type', $type)
            ->where('token_hash', hash('sha256', $plainToken))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revokeToken(DeviceToken $token): void
    {
        $token->update(['revoked_at' => now()]);
    }

    public function revokeDeviceTokens(int $deviceId): void
    {
        DeviceToken::where('device_id', $deviceId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
