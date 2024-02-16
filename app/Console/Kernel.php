<?php

namespace App\Console;

use Ensi\LaravelInitialEventPropagation\SetInitialEventArtisanMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    public function bootstrap(): void
    {
        parent::bootstrap();
        (new SetInitialEventArtisanMiddleware())->handle();
    }

    /**
     * Define the application's command schedule.
     *
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
