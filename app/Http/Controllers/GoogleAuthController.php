<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\EmailVerification;
use App\Helpers\RoleRedirectHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Google_Client;
use Google\Service\Oauth2;

class GoogleAuthController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(config('services.google.client_secret'));
        $this->googleClient->setRedirectUri(config('services.google.redirect'));
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }

    // Redirect to Google OAuth
    public function redirectToGoogle(Request $request)
    {
        $role = $request->get('role', 'student');
        
        // Store role in session for callback
        session(['oauth_role' => $role]);
        
        // Debug: Check redirect URI
        Log::info('Google OAuth Redirect URI:', [
            'configured_uri' => config('services.google.redirect'),
            'role' => $role
        ]);
        
        return redirect($this->googleClient->createAuthUrl());
    }

    // Handle Google OAuth callback
    public function handleGoogleCallback(Request $request)
    {
        // Add debugging
        Log::info('Google OAuth Callback accessed', [
            'url' => $request->fullUrl(),
            'code' => $request->has('code'),
            'session_id' => session()->getId()
        ]);

        if (!$request->has('code')) {
            Log::error('No OAuth code received');
            return redirect()->route('login')->with('error', 'Google authentication was cancelled.');
        }

        try {
            // Get access token
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($request->get('code'));
            
            if (isset($token['error'])) {
                return redirect()->route('login')->with('error', 'Failed to authenticate with Google.');
            }

            $this->googleClient->setAccessToken($token);

            // Get user info
            $oauth2Service = new Oauth2($this->googleClient);
            $googleUser = $oauth2Service->userinfo->get();

            // Validate email domain
            if (!str_ends_with($googleUser->getEmail(), '@diu.edu.bd')) {
                return redirect()->route('login')->with('error', 'Only @diu.edu.bd email addresses are allowed.');
            }

            // Get role from session
            $role = session('oauth_role', 'student');
            session()->forget('oauth_role');

            // Find existing user
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Enforce verification (both flags to be safe with the rest of the app)
                if (!$user->is_verified || !$user->email_verified_at) {
                    // Re-send verification link for convenience
                    try {
                        $this->sendVerificationEmail($user);
                    } catch (\Exception $e) {
                        Log::warning('Resend verification email failed: ' . $e->getMessage());
                    }
                    return redirect()->route('login')->with('error', 'Your account is not verified. A new verification link has been sent to your email.');
                }

                // Check if role matches
                if ($user->role !== $role) {
                    return redirect()->route('login')->with('error', 'Selected role does not match your account role.');
                }

                // Login existing user
                Auth::login($user, true);
                return RoleRedirectHelper::redirectToRoleDashboard($user->role);
            } else {
                // Defer account creation until user explicitly activates
                session([
                    'google_user_data' => [
                        'name' => $googleUser->getName(),
                        'first_name' => $googleUser->getGivenName(),
                        'last_name' => $googleUser->getFamilyName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getPicture(),
                        'role' => $role,
                    ]
                ]);

                return redirect()->route('auth.google.activate');
            }

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }

    // Logout from Google (for account switching)
    public function logoutFromGoogle()
    {
        // Clear Google session
        if (Auth::check()) {
            Auth::logout();
        }
        
        // Redirect to Google logout URL
        $logoutUrl = 'https://accounts.google.com/logout';
        return redirect($logoutUrl)->with('success', 'Logged out from Google. You can now login with a different account.');
    }

    // Handle Google Registration
    public function handleGoogleRegistration(Request $request)
    {
        // Get Google user data from session (must exist due to activation page)
        $googleUserData = session('google_user_data');
        
        if (!$googleUserData) {
            return redirect()->route('login')->with('error', 'Google authentication data not found. Please try again.');
        }

        try {
            // Check if user already exists (race condition safe)
            $existingUser = User::where('email', $googleUserData['email'])->first();
            if ($existingUser) {
                return redirect()->route('login')->with('error', 'User already exists. Please login instead.');
            }

            // Create new user (without verification for now)
            $user = User::create([
                'name' => $googleUserData['name'],
                'first_name' => $googleUserData['first_name'],
                'last_name' => $googleUserData['last_name'],
                'email' => $googleUserData['email'],
                'password' => Hash::make(Str::random(32)), // Random password
                'role' => $googleUserData['role'],
                'google_id' => $googleUserData['google_id'],
                'avatar' => $googleUserData['avatar'],
                'email_verified_at' => null, // Will be set after email verification
            ]);

            // Send verification email
            $this->sendVerificationEmail($user);

            // Clear session data
            session()->forget('google_user_data');

            return redirect()->route('login')->with([
                'success' => 'Registration successful! Please check your email for verification link. The link will expire in 10 minutes.',
                'email_sent' => $user->email
            ]);

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    // Send verification email
    private function sendVerificationEmail($user)
    {
        $verificationToken = Str::random(60);
        $expiresAt = now()->addMinutes(10);

        // Store verification token in database
        $user->update([
            'verification_token' => $verificationToken,
            'verification_expires_at' => $expiresAt
        ]);

        // Send email (basic mail send)
        $verificationUrl = route('email.verify', ['token' => $verificationToken]);
        
        try {
            Mail::to($user->email)->send(new EmailVerification($user, $verificationUrl));
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }
    }

    // Verify email
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)
                   ->where('verification_expires_at', '>', now())
                   ->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        // Mark email as verified (set both flags used across app)
        $user->update([
            'email_verified_at' => now(),
            'is_verified' => true,
            'verification_token' => null,
            'verification_expires_at' => null
        ]);

        return redirect()->route('login')->with('success', 'Email verified successfully! You can now login to your account.');
    }
}
