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




        // Push quality data shift 1 setiap hari jam 07:35 WIB
        // $schedule->command('quality:push-shift1')
        //     ->dailyAt('07:35')
        //     ->timezone('Asia/Jakarta')
        //     ->onOneServer();

        // // Push quality data shift 2 setiap hari jam 15:35 WIB
        // $schedule->command('quality:push-shift2')
        //     ->dailyAt('15:35')
        //     ->timezone('Asia/Jakarta')
        //     ->onOneServer();

        // // Push quality data shift 3 setiap hari jam 22:35 WIB
        // $schedule->command('quality:push-shift3')
        //     ->dailyAt('22:35')
        //     ->timezone('Asia/Jakarta')
        //     ->onOneServer();


        //START HARI SENIN 10 menit setelah update
    $schedule->command('delivery:send')
        ->dailyAt('08:00')
        ->timezone('Asia/Jakarta')
        ->withoutOverlapping();

    $schedule->command('delivery:send')
        ->dailyAt('15:00')
        ->timezone('Asia/Jakarta')
        ->withoutOverlapping();



        $schedule->command('summary:generate')->hourly();
        $schedule->command('sync:delivery-data')->dailyAt('08:00')->timezone('Asia/Jakarta');
        $schedule->command('sync:delivery-data')->dailyAt('13:00')->timezone('Asia/Jakarta');
        $schedule->command('sync:delivery-data')->dailyAt('16:30')->timezone('Asia/Jakarta');
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
