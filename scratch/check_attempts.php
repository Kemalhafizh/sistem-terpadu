<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$attempts = App\Models\LoginAttempt::latest()->take(20)->get()->toArray();
print_r($attempts);
