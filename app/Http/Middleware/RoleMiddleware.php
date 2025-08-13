<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role;
        
        // Check if user is active
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'status' => 'Your account is not active. Please contact administrator.'
            ]);
        }
        
        // If user's role matches one of the allowed roles, proceed
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If user's role doesn't match, redirect to their appropriate dashboard
        // But first check if we're already redirecting to avoid loops
        $currentUrl = $request->url();
        $userDashboard = url('/' . $userRole . '/dashboard');
        
        if ($currentUrl === $userDashboard) {
            // Already on correct dashboard, allow access
            return $next($request);
        }
        
        // Redirect to appropriate dashboard
        return redirect('/' . $userRole . '/dashboard');
    }

}
