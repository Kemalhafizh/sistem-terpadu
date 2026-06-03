<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$admins = App\Models\User::where('role', 'admin')->get()->toArray();
print_r($admins);
