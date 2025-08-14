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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('cohort_id')->nullable(); // Will add foreign key later
            $table->datetime('started_at');
            $table->datetime('submitted_at')->nullable();
            $table->integer('time_spent_minutes')->nullable();
            $table->integer('total_score')->nullable();
            $table->integer('max_score');
            $table->enum('status', ['in_progress', 'submitted', 'auto_submitted', 'graded'])->default('in_progress');
            $table->json('answers')->nullable(); // Store all answers
            $table->timestamps();
            
            $table->unique(['exam_id', 'student_id']); // One attempt per student per exam
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
