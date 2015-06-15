<?php namespace App\Listeners;

use App\Post;
use App\Events\PostWasUpdated;
use App\Events\ThreadWasUpdated;
use App\Listeners\Listener;

class BoardRecachePages extends Listener
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
	 * @param  Event  $event
	 * @return void
	 */
	public function handle($event)
	{
		if ($event->pages === true)
		{
			$event->board->clearCachedPages();
		}
	}
}