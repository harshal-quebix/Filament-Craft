<?php

use App\Http\Middleware\CheckAppInstalled;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// If the app hasn't been installed yet, force safe defaults in memory
// so the web middleware (EncryptCookies, StartSession, etc.) doesn't
// crash before the installer can run.
$installedFile = __DIR__.'/../storage/installed';

if (! file_exists($installedFile)) {
    // Session / cache drivers must not hit the database before it exists
    $_ENV['SESSION_DRIVER']    = 'file';
    $_SERVER['SESSION_DRIVER'] = 'file';
    putenv('SESSION_DRIVER=file');

    $_ENV['CACHE_STORE']    = 'file';
    $_SERVER['CACHE_STORE'] = 'file';
    putenv('CACHE_STORE=file');

    $_ENV['QUEUE_CONNECTION']    = 'sync';
    $_SERVER['QUEUE_CONNECTION'] = 'sync';
    putenv('QUEUE_CONNECTION=sync');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            append: [
                SetLocale::class,
            ],
            prepend: [
                CheckAppInstalled::class,
            ]
        );
        $middleware->alias([
            'app.installed' => CheckAppInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
