<?php

namespace App\Support;

use App\Models\UsageEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UsageTracker
{
    /**
     * @param array<string,mixed> $meta
     */
    public static function log(
        string $eventType,
        ?int $companyId = null,
        ?int $userId = null,
        ?int $projectId = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        int $quantity = 1,
        ?int $billableUnits = null,
        array $meta = []
    ): void {
        try {
            if (! Schema::hasTable('usage_events')) {
                return;
            }

            $quantity = max(1, $quantity);
            $billableUnits = max(1, $billableUnits ?? $quantity);

            UsageEvent::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'project_id' => $projectId,
                'event_type' => $eventType,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'quantity' => $quantity,
                'billable_units' => $billableUnits,
                'meta' => $meta,
                'occurred_at' => now(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Usage tracking must not break core user flows.
        }
    }
}
