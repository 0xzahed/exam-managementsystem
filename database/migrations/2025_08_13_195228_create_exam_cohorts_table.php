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
        Schema::create('exam_cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->string('cohort_name'); // e.g., "Section A", "Group 1"
            $table->text('description')->nullable();
            $table->datetime('start_time'); // Specific start time for this cohort
            $table->datetime('end_time'); // Specific end time for this cohort
            $table->json('student_ids'); // Array of student IDs assigned to this cohort
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_cohorts');
    }
};
