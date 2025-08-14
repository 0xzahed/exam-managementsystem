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
        Schema::table('grades', function (Blueprint $table) {
            $table->integer('points_possible')->nullable()->after('points_earned');
            $table->decimal('percentage', 5, 2)->nullable()->after('points_possible');
            $table->foreignId('graded_by')->nullable()->constrained('users')->after('graded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['points_possible', 'percentage', 'graded_by']);
        });
    }
};
