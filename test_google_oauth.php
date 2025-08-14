<?php
// Test Google OAuth Configuration
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('443223614333-l9sv4t9t7e7g8a9e30m00p8avr4b25hv.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://127.0.0.1:8000/auth/google/callback');
$client->addScope('email');
$client->addScope('profile');

echo "Google OAuth URL:\n";
echo $client->createAuthUrl() . "\n\n";

echo "Configured Redirect URI:\n";
echo $client->getRedirectUri() . "\n";
?>
