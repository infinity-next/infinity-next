<?php namespace App\Listeners;

use App\Post;
use App\Listeners\Listener;
use Cache;

class ThreadRecache extends Listener
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
		if ($event->post instanceof Post)
		{
			$event->post->clearThreadCache();
		}
	}
	
}