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
		// Post specific events
		'App\Events\PostWasAdded' => [
			'App\Listeners\BoardRecachePages',
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasBanned' => [
			'App\Listeners\BoardRecachePages',
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasDeleted' => [
			'App\Listeners\BoardRecachePages',
			'App\Listeners\ThreadRecache',
		],
		'App\Events\PostWasModified' => [
			'App\Listeners\BoardRecachePages',
			'App\Listeners\ThreadRecache',
		],
		
		// Thread (OP) specific Events
		'App\Events\ThreadWasStickied' => [
			'App\Listeners\BoardRecachePages',
			'App\Listeners\ThreadRecache',
		],
		
		// Board specific events
		'App\Events\ThreadWasStickied' => [
			'App\Listeners\BoardListRecache',
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
