<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\AnnouncementNotification;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'instructor') {
            // For instructors: show all announcements they created
            $announcements = Announcement::where('instructor_id', $user->id)
                ->with(['course', 'instructor'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // For students: show announcements from enrolled courses
            $enrolledCourseIds = $user->enrollments->pluck('course_id')->toArray();
            
            $announcements = Announcement::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->with(['course', 'instructor'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        
        return view('announcements.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'priority' => 'nullable|in:low,medium,high',
            'send_email' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $announcement = Announcement::create([
                'title' => $request->title,
                'content' => $request->content,
                'course_id' => $request->course_id,
                'instructor_id' => Auth::id(),
                'priority' => $request->priority ?? 'medium',
                'send_email' => $request->has('send_email'),
                'is_published' => true
            ]);

            // Send email notifications if requested
            if ($request->has('send_email')) {
                $this->sendEmailNotifications($announcement);
                $announcement->update(['sent_at' => now()]);
            }

            DB::commit();

            return redirect()->route('instructor.announcements.index')
                ->with('success', '🎉 Announcement "' . $announcement->title . '" created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create announcement: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        // Check if user has access to this announcement
        $user = Auth::user();
        
        if ($user->role === 'instructor') {
            if ($announcement->instructor_id !== $user->id) {
                abort(403, 'Access denied.');
            }
        } else {
            // Check if student is enrolled in the course
            $isEnrolled = $user->enrollments()->where('course_id', $announcement->course_id)->exists();
            if (!$isEnrolled) {
                abort(403, 'Access denied.');
            }
        }

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        // Check if user owns this announcement
        if ($announcement->instructor_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $courses = Course::where('instructor_id', Auth::id())->get();
        
        return view('announcements.edit', compact('announcement', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        // Check if user owns this announcement
        if ($announcement->instructor_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'priority' => 'nullable|in:low,medium,high',
            'send_email' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $announcement->update([
                'title' => $request->title,
                'content' => $request->content,
                'course_id' => $request->course_id,
                'priority' => $request->priority ?? 'medium',
                'send_email' => $request->has('send_email')
            ]);

            // Send email notifications if requested and not sent before
            if ($request->has('send_email') && !$announcement->sent_at) {
                $this->sendEmailNotifications($announcement);
                $announcement->update(['sent_at' => now()]);
            }

            DB::commit();

            return redirect()->route('instructor.announcements.index')
                ->with('success', '🎉 Announcement "' . $announcement->title . '" updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update announcement: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        // Check if user owns this announcement
        if ($announcement->instructor_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $title = $announcement->title;
        $announcement->delete();

        return redirect()->route('instructor.announcements.index')
            ->with('success', '🗑️ Announcement "' . $title . '" deleted successfully!');
    }

    /**
     * Send email notifications to enrolled students
     */
    private function sendEmailNotifications(Announcement $announcement)
    {
        $course = $announcement->course;
        $enrolledStudents = $course->students;

        foreach ($enrolledStudents as $student) {
            try {
                Mail::to($student->email)->send(new AnnouncementNotification($announcement, $student));
            } catch (\Exception $e) {
                // Log error but continue with other emails
                \Log::error('Failed to send announcement email to ' . $student->email . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Get announcements for student dashboard
     */
    public function getStudentAnnouncements()
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Access denied.');
        }

        $enrolledCourseIds = $user->enrollments->pluck('course_id')->toArray();
        
        $announcements = Announcement::whereIn('course_id', $enrolledCourseIds)
            ->where('is_published', true)
            ->with(['course', 'instructor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($announcements);
    }
}
