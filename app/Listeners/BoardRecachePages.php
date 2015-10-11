<?php namespace App\Listeners;

use App\Board;
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
		if (isset($event->board) && $event->board instanceof Board)
		{
			$board = $event->board;
		}
		else if (isset($event->post) && $event->post instanceof Post)
		{
			$board = $event->post->board;
		}
		
		if (isset($board))
		{
			$board->clearCachedPages();
		}
	}
}