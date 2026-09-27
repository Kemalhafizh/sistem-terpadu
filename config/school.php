<?php

/*
 * Identity of the school this installation serves. These are the fallbacks;
 * the values an admin saves under Admin > Profil Sekolah (settings table)
 * take precedence. Read them through App\Support\School or the school_*()
 * helpers instead of writing them in a view — official documents (kop surat,
 * kuitansi, rapor) used to print eight different phone numbers and seven
 * different e-mail addresses.
 *
 * Institutional defaults below were checked on 2026-09-27 against the
 * school's own website (smaislamsultanagung1smg.sch.id, pages / and /profil/);
 * NPSN also matches the Dapodik-based listing on sekolahloka.com.
 *
 * Deliberately NO default for the principal's name and NIP or for the
 * accreditation: those change over time and the public sources disagree
 * (principal: "Maryono, S.Pd., M.Pd." on the school site vs "Abdul Muis" in
 * the Dapodik listing; accreditation "A+" vs "A (2021)"). A wrong name or NIP
 * on an official letter is worse than a blank the admin is asked to fill.
 */
return [
    'name' => env('SCHOOL_NAME', 'SMA Islam Sultan Agung 1 Semarang'),
    'short_name' => env('SCHOOL_SHORT_NAME', 'SMA Islam Sultan Agung 1'),
    'foundation_name' => env('SCHOOL_FOUNDATION_NAME', 'Yayasan Badan Wakaf Sultan Agung (YBWSA)'),

    'npsn' => env('SCHOOL_NPSN', '20328918'),
    'address' => env('SCHOOL_ADDRESS', 'Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242'),
    'phone' => env('SCHOOL_PHONE', '(024) 8312631'),
    'email' => env('SCHOOL_EMAIL', 'opsmaissula1smg@gmail.com'),
    'whatsapp' => env('SCHOOL_WHATSAPP', '+6289682585200'),
    'website' => env('SCHOOL_WEBSITE', 'https://smaislamsultanagung1smg.sch.id'),

    'accreditation' => env('SCHOOL_ACCREDITATION'),
    'principal_name' => env('SCHOOL_PRINCIPAL_NAME'),
    'principal_nip' => env('SCHOOL_PRINCIPAL_NIP'),

    /*
     * Centre and radius of the attendance geofence. Every GPS check-in path
     * reads these (mobile student check-in, HRD staff check-in,
     * AttendanceService) — they used four different centres up to ~4.8 km
     * apart. The default is the centre the mobile check-in and the Android
     * app already used; the Dapodik listing gives -6.996687, 110.430995
     * (~280 m east). An admin should confirm the point on the map in
     * Admin > Profil Sekolah.
     */
    'campus' => [
        'latitude' => (float) env('SCHOOL_CAMPUS_LATITUDE', -6.996160),
        'longitude' => (float) env('SCHOOL_CAMPUS_LONGITUDE', 110.428510),
        'radius_meters' => (float) env('SCHOOL_CAMPUS_RADIUS_METERS', 250),
    ],
];
