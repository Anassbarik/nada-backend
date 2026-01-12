<?php

return [
    // Global kill-switch (useful during incidents)
    'enabled' => env('ADMIN_LOGS_ENABLED', true),

    // Only log these HTTP methods (state-changing by default)
    'methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    // Max payload size stored in DB (bytes). If exceeded, we store a compact summary instead.
    'max_payload_bytes' => (int) env('ADMIN_LOGS_MAX_PAYLOAD_BYTES', 16_384), // 16KB

    // Default logs listing range (days) when no filters are provided.
    'default_list_days' => (int) env('ADMIN_LOGS_DEFAULT_LIST_DAYS', 30),

    // Retention for prune command/schedule (days).
    'retention_days' => (int) env('ADMIN_LOGS_RETENTION_DAYS', 90),
];


