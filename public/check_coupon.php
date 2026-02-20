<?php
/**
 * Diagnostic Script: Coupon Usage Check
 */

use App\Models\Coupon;
use App\Models\User;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

echo "--- COUPON DIAGNOSTIC START ---\n\n";

$coupon = Coupon::where('code', 'cupom100')->first();

if ($coupon) {
    echo "Coupon: " . $coupon->code . "\n";
    echo "Times Used: " . $coupon->times_used . "\n";
    echo "Discount: " . $coupon->discount_value . " " . $coupon->discount_type . "\n";
    echo "Active: " . ($coupon->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "Error: Coupon 'cupom100' not found!\n";
}

echo "\n--- LATEST USERS ---\n";
$users = User::latest()->take(5)->get();
foreach ($users as $u) {
    echo "ID: " . $u->id . " | Email: " . $u->email . " | Tier: " . $u->subscription_tier . " | Created: " . $u->created_at . "\n";
}

echo "\n--- DIAGNOSTIC COMPLETE ---\n";
