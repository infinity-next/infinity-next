<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		// Thread (OP) Events
		'App\Events\ThreadWasStickied' => [
			'App\Listeners\BoardRecachePages',
		],
		
		// Post (Reply or OP) Events
		'App\Events\PostWasAdded' => [
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasBanned' => [
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasDeleted' => [
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasModified' => [
			'App\Listeners\ThreadRecache',
		],
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);
	}

}
