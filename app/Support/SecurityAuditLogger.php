<?php

namespace App\Support;

use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecurityAuditLogger
{
    public static function log(
        string $eventType,
        ?int $userId = null,
        ?int $companyId = null,
        array $context = [],
        ?Request $request = null
    ): void {
        try {
            if (! Schema::hasTable('security_audit_logs')) {
                return;
            }

            $request ??= request();

            SecurityAuditLog::create([
                'user_id' => $userId,
                'company_id' => $companyId,
                'event_type' => $eventType,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'context' => $context,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Security logging must not break auth flow.
        }
    }
}
