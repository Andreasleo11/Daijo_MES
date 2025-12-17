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

        // $schedule->command('sap:dispatch-receipt')->hourly()->withoutOverlapping()->appendOutputTo(storage_path('logs/sap_dispatch.log')); 

        $schedule->command('summary:generate')->hourly();
        $schedule->command('sync:delivery-data')->dailyAt('06:00');
        // $schedule->command('app:send-daily-waiting-purchase-orders')->dailyAt('01:00'); // Adjust time as needed
        // $schedule->command('report:send-outstanding')
        //     ->dailyAt('09:00')
        //     ->timezone('Asia/Jakarta'); // or your preferred timezone

        $schedule->command('spk:sync')->dailyAt('07:40')->timezone('Asia/Jakarta');
        $schedule->command('spk:sync')->dailyAt('12:00')->timezone('Asia/Jakarta');
        $schedule->command('spk:sync')->dailyAt('17:00')->timezone('Asia/Jakarta');
        $schedule->command('spk:sync')->dailyAt('23:00')->timezone('Asia/Jakarta');
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
