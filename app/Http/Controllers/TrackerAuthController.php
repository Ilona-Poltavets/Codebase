<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use App\Support\DeviceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TrackerAuthController extends Controller
{
    public function __construct(private readonly DeviceTokenService $tokens)
    {
    }

    public function deviceCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_uuid' => ['required', 'string', 'max:64'],
            'device_name' => ['required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:50'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::where('email', mb_strtolower($data['email']))->first();
        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $device = Device::updateOrCreate(
            ['user_id' => $user->id, 'uuid' => $data['device_uuid']],
            [
                'company_id' => $user->company_id,
                'name' => $data['device_name'],
                'platform' => $data['platform'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'revoked_at' => null,
            ]
        );

        $code = Str::upper(Str::random(8));
        $device->update([
            'pairing_code_hash' => hash('sha256', $code),
            'pairing_code_expires_at' => now()->addMinutes((int) config('tracker.device_code_ttl_minutes', 10)),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'device_id' => $device->id,
            'device_code' => $code,
            'expires_at' => $device->pairing_code_expires_at?->toIso8601String(),
        ]);
    }

    public function issueToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_uuid' => ['required', 'string', 'max:64'],
            'device_code' => ['required', 'string', 'size:8'],
        ]);

        $device = Device::where('uuid', $data['device_uuid'])->first();
        if (! $device || $device->revoked_at) {
            return response()->json(['message' => 'Device not found or revoked'], 404);
        }

        $validCode = hash_equals((string) $device->pairing_code_hash, hash('sha256', mb_strtoupper($data['device_code'])));
        $notExpired = $device->pairing_code_expires_at && $device->pairing_code_expires_at->isFuture();

        if (! $validCode || ! $notExpired) {
            return response()->json(['message' => 'Invalid or expired device code'], 422);
        }

        $pair = $this->tokens->issuePair($device);
        $device->update([
            'pairing_code_hash' => null,
            'pairing_code_expires_at' => null,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'access_expires_at' => $pair['access_expires_at']->toIso8601String(),
            'refresh_expires_at' => $pair['refresh_expires_at']->toIso8601String(),
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $refreshToken = $this->tokens->findActive($data['refresh_token'], 'refresh');
        if (! $refreshToken || ! $refreshToken->device) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        if ($refreshToken->device->revoked_at) {
            return response()->json(['message' => 'Device revoked'], 401);
        }

        $this->tokens->revokeToken($refreshToken);
        $this->tokens->revokeDeviceTokens($refreshToken->device_id);

        $pair = $this->tokens->issuePair($refreshToken->device);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'access_expires_at' => $pair['access_expires_at']->toIso8601String(),
            'refresh_expires_at' => $pair['refresh_expires_at']->toIso8601String(),
        ]);
    }

    public function revoke(Request $request): JsonResponse
    {
        $device = $request->attributes->get('tracker_device');
        if (! $device instanceof Device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $this->tokens->revokeDeviceTokens($device->id);
        $device->update(['revoked_at' => now()]);

        return response()->json([], 204);
    }
}
