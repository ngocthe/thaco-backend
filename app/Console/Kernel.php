<?php

namespace App\Console;

use App\Jobs\CalculateBwhOrderRequest;
use App\Jobs\DeleteOldMrpResult;
use App\Jobs\UpdateLogicalInventory;
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
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Purge revoked/expired tokens
        $schedule->command('passport:purge')->hourly();

        $schedule->call(function () {
            CalculateBwhOrderRequest::dispatch();
            DeleteOldMrpResult::dispatch();
            UpdateLogicalInventory::dispatch();
        })->dailyAt('01:00');
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
