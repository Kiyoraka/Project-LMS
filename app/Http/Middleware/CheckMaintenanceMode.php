<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        $maintenanceMode = \App\Models\Setting::get('general', 'maintenanceMode', false);
        
        if ($maintenanceMode) {
            // Allow super admin to access everything during maintenance
            if (Auth::check() && Auth::user()->role === 'super_admin') {
                return $next($request);
            }
            
            // Block all other users (teachers and students)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'maintenance',
                    'message' => 'System is under maintenance, please wait'
                ], 503);
            }
            
            // Redirect to login with maintenance message
            return redirect('/login')->with('maintenance_mode', 'System is under maintenance, please wait');
        }
        
        return $next($request);
    }
} 