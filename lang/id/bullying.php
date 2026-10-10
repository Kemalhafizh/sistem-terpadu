<?php

// Bullying report labels; the codes are BullyingReport::TYPES, SEVERITIES and STATUSES.
return [
    'types' => [
        'verbal' => 'Verbal',
        'fisik' => 'Fisik',
        'cyber' => 'Siber',
        'sosial_eksklusi' => 'Pengucilan sosial',
        'pemerasan' => 'Pemerasan',
        'pelecehan' => 'Pelecehan',
        'intimidasi' => 'Intimidasi',
        'lainnya' => 'Lainnya',
    ],
    'severities' => [
        'ringan' => 'Ringan',
        'sedang' => 'Sedang',
        'berat' => 'Berat',
        'sangat_berat' => 'Sangat berat',
    ],
    'statuses' => [
        'reported' => 'Dilaporkan',
        'under_review' => 'Ditinjau',
        'investigating' => 'Diselidiki',
        'action_taken' => 'Sudah ditindaklanjuti',
        'closed' => 'Ditutup',
        'dismissed' => 'Tidak dilanjutkan',
    ],
];
