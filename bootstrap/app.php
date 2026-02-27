<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrackPageVisit::class,
            \App\Http\Middleware\DetectCustomDomain::class,
        ]);

        $middleware->alias([
            'check.license' => \App\Http\Middleware\CheckLicenseMiddleware::class,
            'license.check' => \App\Http\Middleware\CheckLicenseMiddleware::class,
            'license.realtime' => \App\Http\Middleware\VerifyLicenseOnRequest::class,
        ]);
        
        // CRITICAL: Exclude session recording routes from string manipulation
        // These middleware can corrupt or truncate large JSON payloads
        $middleware->trimStrings(except: [
            fn (Request $request) => $request->is('api/record-session', 'api/record-session/*', 'api/rec', 'api/rec/*'),
        ]);
        
        $middleware->convertEmptyStringsToNull(except: [
            fn (Request $request) => $request->is('api/record-session', 'api/record-session/*', 'api/rec', 'api/rec/*'),
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/rec/*',
            'api/record-session/*',
            'api/track/*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
