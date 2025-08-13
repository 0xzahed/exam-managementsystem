<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\RoleRedirectHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Show login form
    public function showLogin()
    {
        // If user is already authenticated, redirect to dashboard
        $redirectResponse = RoleRedirectHelper::redirectIfAuthenticated();
        if ($redirectResponse) {
            return $redirectResponse;
        }
        
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@diu.edu.bd',
            'password' => 'required|min:6',
            'role' => 'required|in:student,instructor,admin'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $role = $request->role;

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is active
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['status' => 'Your account is not active. Please contact administrator.'])->withInput();
            }
            
            // Check if user is verified
            if (!$user->is_verified) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is not verified. Please check your email for verification code.'])->withInput();
            }
            
            // Check if user role matches selected role
            if ($user->role !== $role) {
                Auth::logout();
                return back()->withErrors(['role' => 'Selected role does not match your account role.'])->withInput();
            }

            // Update last login time
            User::where('id', $user->id)->update(['last_login_at' => now()]);

            $request->session()->regenerate();
            
            // Redirect based on role
            return RoleRedirectHelper::redirectToRoleDashboard($user->role);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    // Show registration form
    public function showRegister()
    {
        // If user is already authenticated, redirect to dashboard
        $redirectResponse = RoleRedirectHelper::redirectIfAuthenticated();
        if ($redirectResponse) {
            return $redirectResponse;
        }
        
        return view('auth.registration');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|ends_with:@diu.edu.bd',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:student,instructor',
            'user_id' => 'required|string|max:50|unique:users,employee_student_id',
            'terms' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'employee_student_id' => $request->user_id,
            'student_id' => $request->role === 'student' ? $request->user_id : null,
            'employee_id' => $request->role === 'instructor' ? $request->user_id : null,
            'is_verified' => false,
        ]);

        // Send verification code
        $emailController = new \App\Http\Controllers\EmailController();
        $codeSent = $emailController->sendVerificationCode($user);

        if ($codeSent) {
            return redirect()->route('verify.code.form')->with([
                'success' => 'Registration successful! A 6-digit verification code has been sent to your email.',
                'email' => $user->email
            ]);
        } else {
            return back()->withErrors(['email' => 'Registration successful but failed to send verification code. Please contact support.']);
        }
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
