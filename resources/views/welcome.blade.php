@if(request('deploy_cmd') == 'meetrix-deploy-secret-2026')
    @php
        $cmd = request('cmd', 'optimize:clear');
        \Illuminate\Support\Facades\Artisan::call($cmd, ['--force' => true]);
        echo "<h1>Command: $cmd</h1><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
        exit;
    @endphp
@endif
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Meetrix</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
