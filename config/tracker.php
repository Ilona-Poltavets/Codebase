<?php

return [
    'device_code_ttl_minutes' => (int) env('TRACKER_DEVICE_CODE_TTL_MINUTES', 10),
    'access_token_ttl_minutes' => (int) env('TRACKER_ACCESS_TOKEN_TTL_MINUTES', 480),
    'refresh_token_ttl_days' => (int) env('TRACKER_REFRESH_TOKEN_TTL_DAYS', 30),
    'screenshot_disk' => env('TRACKER_SCREENSHOT_DISK', 'local'),
];
