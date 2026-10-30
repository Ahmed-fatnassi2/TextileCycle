<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsRepairCustomer;
use App\Http\Middleware\EnsureUserIsWorkshopOwner;
use Arquivei\LaravelPrometheusExporter\PrometheusLaravelRouteMiddleware; // <-- Correct class name

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->append(PrometheusLaravelRouteMiddleware::class); // <-- Correct class name

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'repair-customer' => EnsureUserIsRepairCustomer::class,
            'workshop-owner' => EnsureUserIsWorkshopOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();