<?php namespace App\Listeners;

use App\Events\PostWasUpdated;
use App\Events\ThreadWasUpdated;
use App\Listeners\Listener;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BoardCacheManager extends Listener
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  PodcastWasPurchased  $event
	 * @return void
	 */
	public function handle(PodcastWasPurchased $event)
	{
		// Access the podcast using $event->podcast...
	}
}