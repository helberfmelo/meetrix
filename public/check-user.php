<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

header('Content-Type: text/plain');

$email = 'admin@meetrix.pro';
$user = User::where('email', $email)->first();

if ($user) {
    echo "User found: " . $user->email . "\n";
    echo "Is admin: " . ($user->is_master_admin ? 'YES' : 'NO') . "\n";
    echo "Created at: " . $user->created_at . "\n";
} else {
    echo "User NOT found.\n";
    // List some users to see if any exist
    echo "Listing users:\n";
    foreach (User::limit(5)->get() as $u) {
        echo "- " . $u->email . "\n";
    }
}
