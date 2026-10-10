<?php

// Bullying report labels; the codes are BullyingReport::TYPES, SEVERITIES and STATUSES.
return [
    'types' => [
        'verbal' => 'Verbal',
        'fisik' => 'Physical',
        'cyber' => 'Cyber',
        'sosial_eksklusi' => 'Social exclusion',
        'pemerasan' => 'Extortion',
        'pelecehan' => 'Harassment',
        'intimidasi' => 'Intimidation',
        'lainnya' => 'Other',
    ],
    'severities' => [
        'ringan' => 'Minor',
        'sedang' => 'Moderate',
        'berat' => 'Serious',
        'sangat_berat' => 'Very serious',
    ],
    'statuses' => [
        'reported' => 'Reported',
        'under_review' => 'Under review',
        'investigating' => 'Under investigation',
        'action_taken' => 'Action taken',
        'closed' => 'Closed',
        'dismissed' => 'Not pursued',
    ],
];
