<?php

/*
|--------------------------------------------------------------------------
| Staff attendance and payroll
|--------------------------------------------------------------------------
*/

return [
    // ISO weekdays staff are expected at school (1 = Senin … 7 = Minggu).
    // A day only counts as a working day when it is one of these AND the
    // attendance data covers it (someone scanned in or was recorded on leave),
    // so national and school holidays, where nobody scans, are never counted
    // as absences. A scan on a Saturday event does not make Saturday a
    // working day for everyone else.
    'work_days' => array_values(array_filter(array_map('intval', explode(',', (string) env('HRD_WORK_DAYS', '1,2,3,4,5'))))),
];
