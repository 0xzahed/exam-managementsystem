<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Debug</title>
</head>
<body>
    <h1>Google OAuth Debug</h1>
    
    <?php
    require_once 'vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId('443223614333-l9sv4t9t7e7g8a9e30m00p8avr4b25hv.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://127.0.0.1:8000/auth/google/callback');
    $client->addScope('email');
    $client->addScope('profile');
    
    $authUrl = $client->createAuthUrl();
    ?>
    
    <h2>Configuration:</h2>
    <p><strong>Client ID:</strong> 443223614333-l9sv4t9t7e7g8a9e30m00p8avr4b25hv.apps.googleusercontent.com</p>
    <p><strong>Redirect URI:</strong> http://127.0.0.1:8000/auth/google/callback</p>
    
    <h2>Generated OAuth URL:</h2>
    <p style="word-break: break-all;"><?php echo htmlspecialchars($authUrl); ?></p>
    
    <h2>Test OAuth:</h2>
    <a href="<?php echo htmlspecialchars($authUrl); ?>" target="_blank" style="background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">Login with Google</a>
    
</body>
</html>
