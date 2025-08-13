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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->string('assignment_type')->default('assignment');
            $table->text('short_description')->nullable();
            $table->longText('instructions');
            $table->datetime('assign_date');
            $table->datetime('due_date');
            $table->enum('submission_type', ['both', 'file', 'text'])->default('both');
            $table->integer('max_attempts')->default(1);
            $table->json('allowed_file_types')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('notify_on_assign')->default(true);
            $table->integer('marks')->default(100);
            $table->enum('grading_type', ['points', 'percentage', 'letter'])->default('points');
            $table->enum('grade_display', ['immediately', 'after_due', 'manual'])->default('immediately');
            $table->string('assign_to')->default('all');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('instructor_files')->nullable();
            $table->boolean('limit_attempts')->default(false);
            $table->boolean('notify_on_submission')->default(false);
            $table->boolean('send_reminders')->default(false);
            $table->boolean('notify_late_submission')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
