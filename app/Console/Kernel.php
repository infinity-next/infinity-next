<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Carbon\Carbon;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Autoprune',
		'App\Console\Commands\Inspire',
		'App\Console\Commands\RecordStats',
		'App\Console\Commands\RecordStatsAll',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$now = Carbon::now();
		
		$schedule->command('recordstats')
			->hourly();
		
		$schedule->command('autoprune')
			->hourly()
			->sendOutputTo("./storage/logs/autoprune-{$now->format('Y-m-d_H')}.txt");
	}
}
