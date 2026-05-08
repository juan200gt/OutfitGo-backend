<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$response = Illuminate\Support\Facades\Http::withToken(env('REPLICATE_API_TOKEN'))
    ->get('https://api.replicate.com/v1/models/black-forest-labs/flux-1.1-pro');
echo "FLUX 1.1 PRO: " . $response->status() . "\n";

$response2 = Illuminate\Support\Facades\Http::withToken(env('REPLICATE_API_TOKEN'))
    ->get('https://api.replicate.com/v1/models/black-forest-labs/flux-2-pro');
echo "FLUX 2 PRO: " . $response2->status() . "\n";
