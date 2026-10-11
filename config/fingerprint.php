<?php

/*
|--------------------------------------------------------------------------
| Fingerprint machine log import (FASE 119.6.1)
|--------------------------------------------------------------------------
|
| Staff sign in on the school's fingerprint machine; TU uploads the log the
| machine exports (CSV or Excel). Machines differ in their column names, so
| each field lists the header names it may appear under (matched without
| regard to case or spaces), or a 0-based column index for files without a
| header row. The defaults cover the common exports (ZKTeco/Solution
| "AC-No./Time", Fingerspot "PIN/Tanggal Scan").
|
*/

return [
    // ',', ';', "\t", or 'auto' to pick whichever the first line uses most.
    'delimiter' => env('FINGERPRINT_DELIMITER', 'auto'),

    // false for raw logs with no header row; then the columns below must be indexes.
    'has_header' => env('FINGERPRINT_HAS_HEADER', true),

    'columns' => [
        'machine_id' => ['ac-no.', 'ac-no', 'no.', 'pin', 'user id', 'userid', 'id', 'enroll number', 'no. id'],
        // One column holding date and time…
        'datetime' => ['time', 'datetime', 'date/time', 'waktu', 'tanggal scan', 'checktime', 'check time'],
        // …or, when the machine splits them, these two.
        'date' => ['date', 'tanggal'],
        'time' => ['jam', 'clock'],
    ],

    // PHP date format of the date/time text, or null to accept the usual
    // forms (2026-10-05 06:58:12, 05/10/2026 06:58, 05-10-2026 06:58).
    'datetime_format' => env('FINGERPRINT_DATETIME_FORMAT'),

    // A first scan after this time marks the day "terlambat".
    'late_after' => env('FINGERPRINT_LATE_AFTER', '07:00:00'),
];
