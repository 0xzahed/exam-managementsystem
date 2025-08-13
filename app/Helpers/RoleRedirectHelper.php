<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleRedirectHelper
{
    /**
     * Redirect user to appropriate dashboard based on their role
     * 
     * @param string|null $role - Optional role parameter. If not provided, uses authenticated user's role
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function redirectToRoleDashboard($role = null)
    {
        // If no role provided, get from authenticated user
        if (!$role && Auth::check()) {
            $role = Auth::user()->role;
        }

        // If still no role, redirect to general dashboard
        if (!$role) {
            return redirect()->route('dashboard');
        }

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'instructor':
                return redirect()->route('instructor.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }

    /**
     * Get dashboard route name based on role
     * 
     * @param string $role
     * @return string
     */
    public static function getDashboardRoute($role)
    {
        switch ($role) {
            case 'admin':
                return 'admin.dashboard';
            case 'instructor':
                return 'instructor.dashboard';
            case 'student':
                return 'student.dashboard';
            default:
                return 'dashboard';
        }
    }

    /**
     * Check if user is authenticated and redirect if necessary
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public static function redirectIfAuthenticated()
    {
        if (Auth::check()) {
            return self::redirectToRoleDashboard();
        }
        
        return null;
    }
}
