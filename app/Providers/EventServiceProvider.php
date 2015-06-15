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
			
		],
		'App\Events\ThreadWasCreated' => [
			
		],
		'App\Events\ThreadWasDeleted' => [
			
		],
		'App\Events\ThreadWasModified' => [
			
		],
		'App\Events\ThreadWasStickied' => [
			
		],
		
		// Post (Reply or OP) Events
		'App\Events\PostWasAdded' => [
			
		],
		'App\Events\PostWasBanned' => [
			
		],
		'App\Events\PostWasDeleted' => [
			
		],
		'App\Events\PostWasModified' => [
			
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
