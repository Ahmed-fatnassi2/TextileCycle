<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsAdmin;
<<<<<<< HEAD
use App\Http\Middleware\EnsureUserIsRepairCustomer;
use App\Http\Middleware\EnsureUserIsWorkshopOwner;
=======
>>>>>>> origin/Donation/Association-"aziz"

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
<<<<<<< HEAD
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'repair-customer' => EnsureUserIsRepairCustomer::class,
            'workshop-owner' => EnsureUserIsWorkshopOwner::class,
        ]);
=======
        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
>>>>>>> origin/Donation/Association-"aziz"
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
