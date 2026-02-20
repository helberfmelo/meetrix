<?php
/**
 * Sovereign Migration & Cache Clear Script
 * Bypasses the Laravel Router to force-sync the production environment.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

// 1. Boot Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

echo "--- SOVEREIGN SYNC START ---\n\n";

try {
    echo "1. Running Fresh Migrations (Nuclear)...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    echo Artisan::output() . "\n";

    echo "2. Clearing Route Cache...\n";
    Artisan::call('route:clear');
    echo Artisan::output() . "\n";

    echo "3. Clearing View Cache...\n";
    Artisan::call('view:clear');
    echo Artisan::output() . "\n";

    echo "4. Clearing Config Cache...\n";
    Artisan::call('config:clear');
    echo Artisan::output() . "\n";

    echo "\n--- SYNC COMPLETE ---\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
