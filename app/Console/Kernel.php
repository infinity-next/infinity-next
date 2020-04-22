<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use File;

class Kernel extends ConsoleKernel
{
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

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $now = Carbon::now();

        $this->runRecordStats($schedule, $now);
        $this->runAutoprune($schedule, $now);
        $this->runAutocache($schedule, $now);
        $this->runTorPull($schedule, $now);
    }


    private function runTorPull(Schedule $schedule, Carbon $now)
    {
        $logdir = storage_path('logs/torpull');

        if (!File::exists($logdir)) {
            File::makeDirectory($logdir);
        }

        $schedule->command('tor:pull')
            ->twiceDaily()
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H')}.txt");
    }

    private function runAutocache(Schedule $schedule, Carbon $now)
    {
        $logdir = storage_path('logs/autocache');

        if (!File::exists($logdir)) {
            File::makeDirectory($logdir);
        }

        $schedule->command('autocache:gnav')
            ->everyTenMinutes()
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H')}.txt");

        $schedule->command('autocache:boardlist')
            ->everyTenMinutes()
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H')}.txt");
    }

    private function runAutoprune(Schedule $schedule, Carbon $now)
    {
        $logdir = storage_path('logs/autoprune');

        if (!File::exists($logdir)) {
            File::makeDirectory($logdir);
        }

        $schedule->command('autoprune')
            ->hourly()
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H')}.txt");
    }


    private function runInspire(Schedule $schedule, Carbon $now)
    {
        $logdir = storage_path('logs/inspire');

        if (!File::exists($logdir)) {
            File::makeDirectory($logdir);
        }

        $schedule->command('inspire')
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H:m')}.txt");
    }


    private function runRecordStats(Schedule $schedule, Carbon $now)
    {
        $logdir = storage_path('logs/recordstats');

        if (!File::exists($logdir)) {
            File::makeDirectory($logdir);
        }

        $schedule->command('stats:update')
            ->hourly()
            ->sendOutputTo("{$logdir}/{$now->format('Y-m-d_H')}.txt.txt");
    }
}
