<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Carbon\Carbon;

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
        $systemTimestamp = new Carbon();
        $year = $systemTimestamp->year;
        $month = $systemTimestamp->month;

        $schedule
            ->command("autobilling:nationalhealth ${year} ${month}")
            // ->onOneServer()
            // ->runInBackground()
            ->monthlyOn(25, '0:00');

        $schedule
            ->command("autobilling:uninsured ${year} ${month}")
            // ->onOneServer()
            // ->runInBackground()
            ->monthlyOn(25, '0:00');

        $schedule->command("watchdog:check")->everyFiveMinutes();

        // HOSPITACのデータコンバートバッチ
        $schedule->command('hospitac:convert')->everyFiveMinutes();
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
