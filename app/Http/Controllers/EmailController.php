<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailController extends Controller
{
    // Send verification code to email
    public function sendVerificationCode(User $user)
    {
        // Generate 6-digit random code
        $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update user with verification code and expiry time (10 minutes)
        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => now()->addMinutes(10),
            'is_verified' => false
        ]);

        try {
            // Send email with verification code
            Mail::send('emails.verification-code', [
                'user' => $user,
                'code' => $verificationCode
            ], function ($message) use ($user) {
                $message->to($user->email)
                       ->subject('InsightEdu - Email Verification Code');
            });

            Log::info('Verification code sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $verificationCode
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification code email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    // Verify code and activate account
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Check if code matches and is not expired
        if ($user->verification_code !== $request->code) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        if ($user->verification_code_expires_at < now()) {
            return back()->withErrors(['code' => 'Verification code has expired. Please request a new one.']);
        }

        // Activate account
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null
        ]);

        return redirect()->route('login')->with('success', 'Account activated successfully! You can now login.');
    }

    // Resend verification code
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if ($user->is_verified) {
            return back()->withErrors(['email' => 'Account is already verified.']);
        }

        $sent = $this->sendVerificationCode($user);

        if ($sent) {
            return back()->with('success', 'New verification code sent to your email.');
        } else {
            return back()->withErrors(['email' => 'Failed to send verification code. Please try again.']);
        }
    }
}
