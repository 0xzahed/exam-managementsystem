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
        Schema::table('users', function (Blueprint $table) {
            // Common fields
            // Add department after avatar
            $table->string('department')->nullable()->after('avatar');
            // Add student contact fields
            $table->string('phone')->nullable()->after('department');
            $table->unsignedTinyInteger('year_of_study')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('year_of_study');
            $table->enum('gender', ['male','female','other'])->nullable()->after('date_of_birth');
            $table->string('bio', 500)->nullable()->after('gender');
            // Preferences
            $table->boolean('email_notifications')->default(false)->after('bio');
            $table->boolean('assignment_reminders')->default(false)->after('email_notifications');
            // Instructor specific
            $table->string('title')->nullable()->after('avatar');
            $table->string('specialization')->nullable()->after('title');
            $table->string('office_location')->nullable()->after('specialization');
            $table->string('office_hours')->nullable()->after('office_location');
            $table->string('website')->nullable()->after('office_hours');
            $table->string('linkedin')->nullable()->after('website');
            $table->text('education')->nullable()->after('linkedin');
            $table->text('research_interests')->nullable()->after('education');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone','department','bio','date_of_birth','gender',
                'year_of_study','email_notifications','assignment_reminders',
                'title','specialization','office_location','office_hours',
                'website','linkedin','education','research_interests'
            ]);
        });
    }
};
