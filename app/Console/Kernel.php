<?php

// المسار: app/Console/Kernel.php

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
        // Daily database backup at 2:00 AM (Khartoum time)
        $schedule->command('erp:backup-database')->dailyAt('02:00');

        // Check low stock every morning at 8:00 AM
        $schedule->command('erp:check-low-stock')->dailyAt('08:00');

        // Check overdue invoices every morning at 8:30 AM
        $schedule->command('erp:check-overdue-invoices')->dailyAt('08:30');
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
