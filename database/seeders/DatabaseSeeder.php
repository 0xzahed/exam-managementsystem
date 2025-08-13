<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test instructor if not exists
        $instructor = User::firstOrCreate([
            'email' => 'instructor@test.com'
        ], [
            'name' => 'Test Instructor',
            'first_name' => 'Test',
            'last_name' => 'Instructor', 
            'role' => 'instructor',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        // Create test students
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::firstOrCreate([
                'email' => "student{$i}@test.com"
            ], [
                'name' => "Test Student {$i}",
                'first_name' => 'Test',
                'last_name' => "Student {$i}",
                'role' => 'student',
                'password' => Hash::make('password'),
                'is_verified' => true,
            ]);
        }

        // Create test courses
        $courses = [];
        for ($i = 1; $i <= 3; $i++) {
            $courses[] = Course::firstOrCreate([
                'code' => "CS-10{$i}"
            ], [
                'title' => "Computer Science Course {$i}",
                'description' => "This is a test course for Computer Science {$i}",
                'credits' => 3,
                'department' => 'Computer Science',
                'semester_type' => 'Fall',
                'year' => 2025,
                'max_students' => 30,
                'password' => '',
                'instructor_id' => $instructor->id,
                'is_active' => true,
            ]);
        }

        // Enroll students in courses
        foreach ($students as $student) {
            // Randomly enroll each student in courses
            $randomCourses = array_rand($courses, rand(1, 3));
            if (!is_array($randomCourses)) {
                $randomCourses = [$randomCourses];
            }
            
            foreach ($randomCourses as $courseIndex) {
                $course = $courses[$courseIndex];
                // Check if not already enrolled
                if (!$course->students()->where('user_id', $student->id)->exists()) {
                    $course->students()->attach($student->id, [
                        'enrolled_at' => now(),
                        'status' => 'enrolled',
                    ]);
                }
            }
        }
    }
}
