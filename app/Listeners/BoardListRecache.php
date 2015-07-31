<?php namespace App\Listeners;

use App\Listeners\Listener;
use Cache;

class BoardListRecache extends Listener
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
		if ($event->board instanceof Board)
		{
			$board = $event->post->board;
		}
		else if ($event->post instanceof Post)
		{
			$board = $event->post->board;
		}
		
		## TODO ##
		// There will be board lists that need to be recached after a board is modified.
		// This includes the overboard.
	}
	
}