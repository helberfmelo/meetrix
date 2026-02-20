<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

echo "DEBUG: HIT BOOTSTRAP APP\n";

$app = Application::configure(basePath: dirname(__DIR__));
echo "DEBUG: APP CONFIGURED\n";

$app = $app->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    );
echo "DEBUG: ROUTING CONFIGURED\n";

$app = $app->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\SuperAdmin::class,
        ]);
    });
echo "DEBUG: MIDDLEWARE CONFIGURED\n";

$app = $app->withExceptions(function (Exceptions $exceptions) {
        //
    });
echo "DEBUG: EXCEPTIONS CONFIGURED\n";

return $app->create();
