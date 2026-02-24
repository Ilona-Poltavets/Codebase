<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Screenshot;
use App\Models\TimeSegment;
use App\Models\TimeSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackerScreenshotController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $device = $request->attributes->get('tracker_device');
        if (! $device instanceof Device) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'session_id' => ['nullable', 'integer', 'exists:time_sessions,id'],
            'segment_id' => ['nullable', 'integer', 'exists:time_segments,id'],
            'taken_at' => ['required', 'date'],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'is_blurred' => ['nullable', 'boolean'],
            'sha256' => ['nullable', 'string', 'size:64'],
            'meta' => ['nullable', 'array'],
            'image' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $sessionId = $this->filterSessionId($data['session_id'] ?? null, $device->id, $device->user_id);
        $segmentId = $this->filterSegmentId($data['segment_id'] ?? null, $device->id, $device->user_id);

        $disk = (string) config('tracker.screenshot_disk', 'local');
        $path = null;
        $size = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('tracker-screenshots/'.date('Y/m/d'), $disk);
            $size = $file->getSize();
        }

        $screenshot = Screenshot::create([
            'session_id' => $sessionId,
            'segment_id' => $segmentId,
            'company_id' => $device->company_id,
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'disk' => $disk,
            'path' => $path,
            'size_bytes' => $size,
            'sha256' => $data['sha256'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'is_blurred' => (bool) ($data['is_blurred'] ?? false),
            'taken_at' => $data['taken_at'],
            'meta' => $data['meta'] ?? null,
        ]);

        return response()->json([
            'id' => $screenshot->id,
            'path' => $screenshot->path,
            'taken_at' => $screenshot->taken_at?->toIso8601String(),
        ], 201);
    }

    private function filterSessionId(?int $sessionId, int $deviceId, int $userId): ?int
    {
        if (! $sessionId) {
            return null;
        }

        $session = TimeSession::find($sessionId);
        if (! $session || $session->device_id !== $deviceId || $session->user_id !== $userId) {
            return null;
        }

        return $session->id;
    }

    private function filterSegmentId(?int $segmentId, int $deviceId, int $userId): ?int
    {
        if (! $segmentId) {
            return null;
        }

        $segment = TimeSegment::find($segmentId);
        if (! $segment || $segment->device_id !== $deviceId || $segment->user_id !== $userId) {
            return null;
        }

        return $segment->id;
    }
}
