<?php
// Simple test script to check enrollment functionality
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::create('/courses/enroll', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Headers: " . json_encode($response->headers->all()) . "\n";

if ($response->getStatusCode() === 302) {
    echo "Redirect to: " . $response->headers->get('Location') . "\n";
}

$kernel->terminate($request, $response);
