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
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('prevent_navigation')->default(false)->after('show_results_immediately');
            $table->boolean('shuffle_questions')->default(false)->after('prevent_navigation');
            $table->integer('max_attempts')->default(1)->after('shuffle_questions');
            $table->integer('passing_score')->default(60)->after('max_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['prevent_navigation', 'shuffle_questions', 'max_attempts', 'passing_score']);
        });
    }
};
