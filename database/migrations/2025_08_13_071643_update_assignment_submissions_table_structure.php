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
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Add new columns
            $table->text('content')->nullable()->after('student_id'); // For text submissions
            $table->text('comments')->nullable()->after('submission_files'); // Student comments
            $table->decimal('grade', 5, 2)->nullable()->after('status'); // Use 'grade' instead of 'score'
            $table->string('file_path')->nullable()->after('attempt_number'); // Legacy file path support
            
            // Drop old columns that we're renaming/replacing
            $table->dropColumn(['submission_text', 'score', 'is_late']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // Restore old columns
            $table->text('submission_text')->nullable()->after('student_id');
            $table->decimal('score', 5, 2)->nullable()->after('status');
            $table->boolean('is_late')->default(false)->after('attempt_number');
            
            // Drop new columns
            $table->dropColumn(['content', 'comments', 'grade', 'file_path']);
        });
    }
};
