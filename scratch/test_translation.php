<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Locale: " . app()->getLocale() . "\n";

app()->setLocale('ar');
echo "New Locale: " . app()->getLocale() . "\n";

$keys = [
    'navigation.language',
    'Dashboard',
    'Profile',
    'Log Out',
    'navigation.dashboard',
];

foreach ($keys as $key) {
    $val = __($key);
    echo "$key: " . (is_array($val) ? "ARRAY: " . json_encode($val) : "STRING: " . $val) . "\n";
}
