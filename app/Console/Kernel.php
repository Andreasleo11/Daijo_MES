<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('summary:generate')->everyFiveMinutes();
        $schedule->command('app:send-daily-waiting-purchase-orders')->dailyAt('01:00'); // Adjust time as needed
        $schedule->command('report:send-outstanding')
            ->dailyAt('09:00')
            ->timezone('Asia/Jakarta'); // or your preferred timezone
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
