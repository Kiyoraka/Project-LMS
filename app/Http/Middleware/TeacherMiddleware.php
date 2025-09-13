<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
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
        // Check if user is authenticated and has teacher role
        if (!$request->user() || !$request->user()->isTeacher()) {
            abort(403, 'Unauthorized action. Only Teachers can access this area.');
        }

        return $next($request);
    }
}