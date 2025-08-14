<?php
require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test local profile photo storage
use App\Models\User;

// Find a user
$user = User::find(7);

if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Current Profile Photo: " . ($user->profile_photo ?? 'null') . "\n";
    echo "Display URL: " . ($user->profile_photo_display_url ?? 'null') . "\n";
    
    // Test updating with a fake local path
    $testPath = 'profile_photos/profile_7_' . time() . '.jpg';
    $user->profile_photo = $testPath;
    $user->save();
    
    echo "Updated Profile Photo Path: " . $user->profile_photo . "\n";
    echo "Updated Display URL: " . $user->profile_photo_display_url . "\n";
    
} else {
    echo "User not found\n";
}
