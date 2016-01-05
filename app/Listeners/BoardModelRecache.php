<?php namespace App\Listeners;

use App\Board;
use App\Listeners\Listener;

class BoardModelRecache extends Listener
{
	
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
		
		if (isset($board))
		{
			$board->clearCachedModel();
		}
	}
	
}
