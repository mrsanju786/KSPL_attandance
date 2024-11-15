<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // $schedule->command('monthly:attendence')->dailyAt('08:00')->when(function () {
        //     return Carbon::now()->endOfMonth()->isToday();
        // });
        $schedule->command('daily:daily_attendance')->dailyAt('11:00');
        // $schedule->command('absent:Attendance')->dailyAt('16:00');
        $schedule->command('daily:update_status')->dailyAt('23:59:00');
        $schedule->command('daily:update_status_for_leave_and_regularization')->dailyAt('23:59:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
