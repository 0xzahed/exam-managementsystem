<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\ExamCohort;
use Illuminate\Support\Facades\DB;

class FixExamCohorts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:fix-cohorts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create exam cohorts for existing exams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cohort system has been removed from the exam functionality.');
        $this->info('Exams now work directly with course enrollments.');
        $this->info('No action needed - the system is now simplified!');
    }
}
