<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display profile settings form for both student and instructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $user = Auth::user();
        // Load role-specific settings view
        if ($user->role === 'instructor') {
            return view('profile.instructor_settings', compact('user'));
        }
        return view('profile.student_settings', compact('user'));
    }

    /**
     * Update profile settings.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
            'password' => 'nullable|string|min:8|confirmed',
            // Student fields
            'phone' => 'nullable|string|max:20',
            'year_of_study' => 'nullable|integer|min:1|max:4',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:500',
            'email_notifications' => 'nullable|boolean',
            'assignment_reminders' => 'nullable|boolean',
            // Instructor fields
            'title' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:100',
            'office_location' => 'nullable|string|max:100',
            'office_hours' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'education' => 'nullable|string|max:1000',
            'research_interests' => 'nullable|string|max:1000',
        ]);
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            Log::info('Profile photo upload started for user: ' . $user->id);
            
            try {
                $file = $request->file('profile_photo');
                
                // Validate file
                if (!$file->isValid()) {
                    throw new \Exception('Invalid file upload');
                }
                
                // Generate unique filename
                $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store file in public/storage/profile_photos directory
                $path = $file->storeAs('profile_photos', $filename, 'public');
                
                Log::info('Profile photo stored at: ' . $path);
                
                // Delete old profile photo if exists
                if ($user->profile_photo && strpos($user->profile_photo, 'profile_photos/') !== false) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                
                // Save new path to user
                $user->profile_photo = $path;
                Log::info('Profile photo path saved: ' . $path);
                
            } catch (\Exception $e) {
                Log::error('Profile photo upload failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to upload profile photo. Please try again.');
            }
        }
        // Assign common fields
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        // Assign role-specific fields
        if ($user->role === 'student') {
            $user->phone = $request->phone;
            $user->year_of_study = $request->year_of_study;
            $user->date_of_birth = $request->date_of_birth;
            $user->gender = $request->gender;
            $user->bio = $request->bio;
            $user->email_notifications = $request->has('email_notifications');
            $user->assignment_reminders = $request->has('assignment_reminders');
        } else {
            $user->title = $request->title;
            $user->phone = $request->phone;
            $user->department = $request->department;
            $user->specialization = $request->specialization;
            $user->office_location = $request->office_location;
            $user->office_hours = $request->office_hours;
            $user->website = $request->website;
            $user->linkedin = $request->linkedin;
            $user->date_of_birth = $request->date_of_birth;
            $user->gender = $request->gender;
            $user->education = $request->education;
            $user->research_interests = $request->research_interests;
            $user->bio = $request->bio;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->route('profile.settings', ['success' => 1])->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user password.
     */
    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.settings', ['success' => 1])->with('success', 'Password updated successfully.');
    }
}
