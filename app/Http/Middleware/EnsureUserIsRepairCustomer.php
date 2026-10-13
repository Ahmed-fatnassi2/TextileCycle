<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRepairCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->role === User::ROLE_WORKSHOP_OWNER, 403);

        return $next($request);
    }
}