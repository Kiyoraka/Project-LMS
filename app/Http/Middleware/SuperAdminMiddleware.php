<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has super_admin role
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admins can access this area.');
        }

        return $next($request);
    }
}