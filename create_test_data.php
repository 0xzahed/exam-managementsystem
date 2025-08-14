<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__ . '/bootstrap/app.php';

// Create instructor
$instructor = User::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'instructor@test.com',
    'password' => Hash::make('password'),
    'role' => 'instructor',
    'email_verified_at' => now()
]);

echo "Instructor created with ID: " . $instructor->id . PHP_EOL;

// Create course
$course = Course::create([
    'title' => 'Web Development Fundamentals',
    'code' => 'CS101',
    'description' => 'Learn the basics of web development',
    'instructor_id' => $instructor->id,
    'password' => '123456',
    'max_students' => 30,
    'is_active' => true
]);

echo "Course created with ID: " . $course->id . PHP_EOL;

// Create test material
$material = CourseMaterial::create([
    'course_id' => $course->id,
    'title' => 'Introduction to HTML',
    'description' => 'Basic HTML concepts and structure',
    'content' => 'HTML (HyperText Markup Language) is the standard markup language for creating web pages.',
    'section' => 'Week 1',
    'type' => 'text',
    'uploaded_by' => $instructor->id,
    'is_private' => false
]);

echo "Material created with ID: " . $material->id . PHP_EOL;

// Create student for testing
$student = User::create([
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'student@test.com',
    'password' => Hash::make('password'),
    'role' => 'student',
    'email_verified_at' => now()
]);

echo "Student created with ID: " . $student->id . PHP_EOL;

echo "Test data created successfully!" . PHP_EOL;
