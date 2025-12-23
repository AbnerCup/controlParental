<?php

namespace App\Console;

use App\Jobs\NightlyAbsenceJob;
use App\Models\School;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        foreach (School::all() as $school) {
            $schedule->job(new NightlyAbsenceJob($school))
                     ->dailyAt('23:59')
                     ->timezone($school->timezone);
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
