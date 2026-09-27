<?php

/*
 * Thresholds for `php artisan app:health-check`.
 */
return [

    // Disk usage (percent of the volume holding the app) that turns the
    // Disk Space check into WARNING / CRITICAL and the command's exit code to 1.
    'disk_warning_percent' => (float) env('HEALTH_DISK_WARNING_PERCENT', 80),
    'disk_critical_percent' => (float) env('HEALTH_DISK_CRITICAL_PERCENT', 90),

    // How much of the end of storage/logs/laravel.log is scanned for recent
    // ERROR/CRITICAL/EMERGENCY entries. The log is never loaded whole — a
    // large log used to exhaust PHP's memory and crash the health check.
    'log_tail_bytes' => (int) env('HEALTH_LOG_TAIL_BYTES', 1024 * 1024),
    'log_tail_lines' => (int) env('HEALTH_LOG_TAIL_LINES', 1500),

];
