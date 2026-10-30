<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsRepairCustomer;
use App\Http\Middleware\EnsureUserIsWorkshopOwner;
// Add the Prometheus Middleware
use Arquivei\LaravelPrometheusExporter\RouteMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register the Prometheus middleware to track HTTP requests
        $middleware->append(RouteMiddleware::class);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'repair-customer' => EnsureUserIsRepairCustomer::class,
            'workshop-owner' => EnsureUserIsWorkshopOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();