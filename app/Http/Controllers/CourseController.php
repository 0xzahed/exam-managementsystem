<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Traits\FlashMessageTrait;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CourseController extends Controller
{
    use FlashMessageTrait;
    public function create()
    {
        return view('courses.create');
    }

    public function edit(Course $course)
    {
        // Check if user is instructor of this course
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course.');
        }

        return view('courses.edit', compact('course'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:courses,code',
            'description' => 'required|string',
            'credits' => 'required|integer|min:1|max:4',
            'department' => 'required|string',
            'semester_type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2024|max:2030',
            'max_students' => 'required|integer|min:1|max:200',
            'prerequisites' => 'nullable|string',
            'password' => 'required|string|min:4',
        ]);

        try {
            Course::create([
                'title' => $request->title,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'credits' => $request->credits,
                'department' => $request->department,
                'semester_type' => $request->semester_type,
                'year' => $request->year,
                'max_students' => $request->max_students,
                'prerequisites' => $request->prerequisites,
                'password' => $request->password,
                'instructor_id' => Auth::id(),
                'is_active' => true,
            ]);

            return $this->flashSuccess('Course created successfully!', 'courses.manage');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create course. Please try again.'])
                ->withInput();
        }
    }

    public function index()
    {
        $courses = Course::where('instructor_id', Auth::id())
            ->with('students')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('courses.index', compact('courses'));
    }

    public function manage()
    {
        $courses = Course::where('instructor_id', Auth::id())
            ->with('students')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'totalCourses' => $courses->count(),
            'activeCourses' => $courses->where('is_active', true)->count(),
            'totalStudents' => $courses->sum(fn($course) => $course->students->count()),
            'currentSemester' => 'Spring 2025'
        ];

        return view('courses.manage', compact('courses', 'stats'));
    }



    public function update(Request $request, Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:courses,code,' . $course->id,
            'description' => 'required|string',
            'credits' => 'required|integer|min:1|max:4',
            'department' => 'required|string',
            'semester_type' => 'required|in:Spring,Summer,Fall',
            'year' => 'required|integer|min:2024|max:2030',
            'max_students' => 'required|integer|min:1|max:200',
            'prerequisites' => 'nullable|string',
            'password' => 'required|string|min:4',
        ]);

        try {
            $course->update([
                'title' => $request->title,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'credits' => $request->credits,
                'department' => $request->department,
                'semester_type' => $request->semester_type,
                'year' => $request->year,
                'max_students' => $request->max_students,
                'prerequisites' => $request->prerequisites,
                'password' => $request->password,
            ]);

            return $this->flashSuccess('Course updated successfully!', 'courses.manage');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update course. Please try again.'])
                ->withInput();
        }
    }

    public function destroy(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course.');
        }

        try {
            $course->students()->detach();
            $course->delete();

            return $this->flashSuccess('Course deleted successfully!', 'courses.manage');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete course. Please try again.']);
        }
    }

    public function materials(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course.');
        }

        $materials = $course->courseMaterials()
            ->orderBy('created_at', 'desc')
            ->get();

        $storedSections = json_decode($course->sections ?? '[]', true);

        return view('courses.materials', compact('course', 'materials', 'storedSections'));
    }

    public function getStudents(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = $course->students()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'course_enrollments.enrolled_at as enrolled_at')
            ->orderBy('users.first_name')
            ->get()
            ->map(function ($student, $index) {
                return [
                    'id' => $student->id,
                    'serial' => $index + 1,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => 'STU-' . str_pad($student->id, 4, '0', STR_PAD_LEFT),
                    'email' => $student->email,
                    'enrolled_at' => $student->enrolled_at
                        ? Carbon::parse($student->enrolled_at)->format('M d, Y')
                        : 'N/A'
                ];
            });

        return response()->json([
            'students' => $students,
            'count' => $students->count(),
            'course_title' => $course->title,
            'course_code' => $course->code
        ]);
    }

    public function removeStudent(Course $course, User $student)
    {
        if ($course->instructor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$course->students()->where('users.id', $student->id)->exists()) {
            return response()->json(['error' => 'Student not found in this course'], 404);
        }

        try {
            $course->students()->detach($student->id);

            return response()->json([
                'success' => true,
                'message' => "Student {$student->name} has been removed from the course successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to remove student from course',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
