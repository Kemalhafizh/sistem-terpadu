<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

$user = User::where('email', 'admin@sultanagung1.sch.id')->first();
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "User found: " . $user->name . "\n";
echo "Password hash: " . $user->password . "\n";
echo "Check password123: " . (Hash::check('password123', $user->password) ? 'MATCH' : 'MISMATCH') . "\n";
