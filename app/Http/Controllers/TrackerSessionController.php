<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\TimeSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackerSessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $device = $request->attributes->get('tracker_device');
        if (! $device instanceof Device) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'meta' => ['nullable', 'array'],
        ]);

        $session = TimeSession::create([
            'company_id' => $device->company_id,
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'started_at' => $data['started_at'],
            'timezone' => $data['timezone'] ?? null,
            'status' => 'active',
            'meta' => $data['meta'] ?? null,
        ]);

        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'started_at' => $session->started_at?->toIso8601String(),
        ], 201);
    }

    public function stop(Request $request, TimeSession $session): JsonResponse
    {
        $device = $request->attributes->get('tracker_device');
        if (! $device instanceof Device) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($session->device_id !== $device->id || $session->user_id !== $device->user_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'ended_at' => ['required', 'date'],
            'total_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $endedAt = Carbon::parse($data['ended_at']);
        $session->update([
            'ended_at' => $endedAt,
            'total_seconds' => $data['total_seconds'] ?? max(0, $session->started_at->diffInSeconds($endedAt)),
            'status' => 'stopped',
        ]);

        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'ended_at' => $session->ended_at?->toIso8601String(),
            'total_seconds' => $session->total_seconds,
        ]);
    }
}
