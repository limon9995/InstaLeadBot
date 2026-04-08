<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run daily at 8:00 PM Bangladesh time (UTC+6 → 14:00 UTC)
        $schedule->command('leadbot:scrape --max=10')
                 ->dailyAt('14:00')   // 14:00 UTC = 20:00 Bangladesh (BST)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/scraper.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
