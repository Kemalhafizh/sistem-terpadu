<?php

// Bullying report labels; the codes are BullyingReport::TYPES, SEVERITIES and STATUSES.
return [
    'types' => [
        'verbal' => 'لفظي',
        'fisik' => 'جسدي',
        'cyber' => 'إلكتروني',
        'sosial_eksklusi' => 'إقصاء اجتماعي',
        'pemerasan' => 'ابتزاز',
        'pelecehan' => 'تحرش',
        'intimidasi' => 'ترهيب',
        'lainnya' => 'أخرى',
    ],
    'severities' => [
        'ringan' => 'خفيف',
        'sedang' => 'متوسط',
        'berat' => 'خطير',
        'sangat_berat' => 'خطير جدًا',
    ],
    'statuses' => [
        'reported' => 'تم الإبلاغ',
        'under_review' => 'قيد المراجعة',
        'investigating' => 'قيد التحقيق',
        'action_taken' => 'تم اتخاذ إجراء',
        'closed' => 'مغلق',
        'dismissed' => 'لم يُتابَع',
    ],
];
