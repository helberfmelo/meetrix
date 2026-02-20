<?php
/**
 * Sovereign Migration & Cache Clear Script
 * Bypasses the Laravel Router to force-sync the production environment.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));
ob_start();

$sendPlainTextHeader = static function (): void {
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }
};

// 1. Boot Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

try {
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();
} catch (\Throwable $e) {
    $sendPlainTextHeader();
    $bootstrapOutput = trim((string) ob_get_clean());
    if ($bootstrapOutput !== '') {
        echo "BOOTSTRAP OUTPUT DETECTED:\n";
        echo $bootstrapOutput . "\n\n";
    }
    echo "BOOTSTRAP ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit;
}

$bootstrapOutput = trim((string) ob_get_clean());
$sendPlainTextHeader();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($bootstrapOutput !== '') {
    echo "NOTICE: Unexpected bootstrap output detected (possible BOM/whitespace).\n";
    echo $bootstrapOutput . "\n\n";
}

echo "--- SOVEREIGN SYNC START ---\n\n";

try {
    echo "1. Running Fresh Migrations & Seeding (Nuclear)...\n";
    Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
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
