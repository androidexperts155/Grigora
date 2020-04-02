<?php

namespace App\Console;

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
        // 'App\Console\Commands\AssignDriver',
        // 'App\Console\Commands\QuizOfTheDay',
        // 'App\Console\Commands\ClearNotification',
        Commands\scheduleOrder::class,
        Commands\ClearNotification::class,
        Commands\QuizOfTheday::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('Assign:driver')
        //           ->everyMinute();
        $schedule->command('schedule:order')
                    ->everyMinute();
        $schedule->command('quiz:day')
                    ->everyMinute();
        $schedule->command('notification:clear')
                    ->everyMinute();
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
