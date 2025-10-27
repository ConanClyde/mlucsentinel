<?php

/**
 * Test Broadcasting Script
 * Run with: php test-broadcast.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Events\AdministratorUpdated;
use App\Models\Administrator;

echo "=== Broadcasting Test ===\n\n";

// Get a random administrator
$administrator = Administrator::with(['user', 'adminRole'])->first();

if (! $administrator) {
    echo "❌ No administrators found in database.\n";
    echo "Please create an administrator first.\n";
    exit(1);
}

echo "Testing broadcast with administrator:\n";
echo "  ID: {$administrator->id}\n";
echo "  Name: {$administrator->user->first_name} {$administrator->user->last_name}\n";
echo "  Email: {$administrator->user->email}\n\n";

echo "Broadcasting 'updated' event...\n";

try {
    broadcast(new AdministratorUpdated($administrator, 'updated'));
    echo "✅ Event broadcasted successfully!\n\n";
    echo "Check your browser console for the received event.\n";
    echo "If you have the administrators page open, you should see the row highlight.\n";
} catch (\Exception $e) {
    echo "❌ Failed to broadcast event:\n";
    echo "  Error: {$e->getMessage()}\n";
    echo "  Make sure Reverb server is running: php artisan reverb:start\n";
    exit(1);
}
