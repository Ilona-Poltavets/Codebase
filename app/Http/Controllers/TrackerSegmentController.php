<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\TimeSegment;
use App\Models\TimeSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackerSegmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $device = $request->attributes->get('tracker_device');
        if (! $device instanceof Device) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'segments' => ['required', 'array', 'min:1'],
            'segments.*.session_id' => ['required', 'integer', 'exists:time_sessions,id'],
            'segments.*.started_at' => ['required', 'date'],
            'segments.*.ended_at' => ['nullable', 'date'],
            'segments.*.seconds' => ['nullable', 'integer', 'min:0'],
            'segments.*.activity_level' => ['nullable', 'integer', 'between:0,100'],
            'segments.*.is_idle' => ['nullable', 'boolean'],
            'segments.*.app_name' => ['nullable', 'string', 'max:190'],
            'segments.*.window_title' => ['nullable', 'string', 'max:190'],
            'segments.*.url' => ['nullable', 'url', 'max:2048'],
            'segments.*.meta' => ['nullable', 'array'],
        ]);

        $created = [];
        foreach ($data['segments'] as $segmentData) {
            $session = TimeSession::findOrFail((int) $segmentData['session_id']);
            if ($session->device_id !== $device->id || $session->user_id !== $device->user_id) {
                continue;
            }

            $segment = TimeSegment::create([
                'session_id' => $session->id,
                'company_id' => $device->company_id,
                'user_id' => $device->user_id,
                'device_id' => $device->id,
                'started_at' => $segmentData['started_at'],
                'ended_at' => $segmentData['ended_at'] ?? null,
                'seconds' => $segmentData['seconds'] ?? 0,
                'activity_level' => $segmentData['activity_level'] ?? null,
                'is_idle' => (bool) ($segmentData['is_idle'] ?? false),
                'app_name' => $segmentData['app_name'] ?? null,
                'window_title' => $segmentData['window_title'] ?? null,
                'url' => $segmentData['url'] ?? null,
                'meta' => $segmentData['meta'] ?? null,
            ]);

            $created[] = $segment->id;
        }

        return response()->json([
            'created_count' => count($created),
            'segment_ids' => $created,
        ], 201);
    }
}
