<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActivityLogger
{
    public static function log(int $projectId, ?int $userId, string $eventType, array $details = []): void
    {
        try {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            ActivityLog::create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'event_type' => $eventType,
                'details' => $details,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Activity feed must not break business actions.
        }
    }
}
