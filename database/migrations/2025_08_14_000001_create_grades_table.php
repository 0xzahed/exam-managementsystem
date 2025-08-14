<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->morphs('gradeable'); // For assignment or exam
            $table->decimal('score', 5, 2)->nullable(); // Percentage score
            $table->integer('points_earned')->nullable(); // Points earned
            $table->integer('total_points')->nullable(); // Total possible points
            $table->string('letter_grade', 3)->nullable(); // A, B, C, D, F
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->string('grade_type')->default('manual'); // manual, auto, imported
            $table->timestamps();
            
            // Ensure one grade per student per assignment/exam
            $table->unique(['student_id', 'course_id', 'gradeable_type', 'gradeable_id']);
            
            // Indexes for performance
            $table->index(['course_id', 'instructor_id']);
            $table->index(['student_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
