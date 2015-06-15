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
		'App\Events\ThreadWasBumped' => [
			'App\Events\BoardRecachePages',
		],
		'App\Events\ThreadWasCreated' => [
			'App\Events\BoardRecachePages',
		],
		'App\Events\ThreadWasDeleted' => [
			'App\Events\BoardRecachePages',
		],
		'App\Events\ThreadWasModified' => [
			'App\Events\BoardRecachePages',
		],
		'App\Events\ThreadWasStickied' => [
			'App\Events\BoardRecachePages',
		],
		
		// Post (Reply or OP) Events
		'App\Events\PostWasAdded' => [
			'App\Events\ThreadRecache',
		],
		'App\Events\PostWasBanned' => [
			'App\Events\ThreadRecache',
		],
		'App\Events\PostWasDeleted' => [
			'App\Events\ThreadRecache',
		],
		'App\Events\PostWasModified' => [
			'App\Events\ThreadRecache',
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
