<?php namespace App\Listeners;

use App\Board;
use App\Post;

use App\Listeners\Listener;
use Cache;

class BoardListRecache extends Listener
{
	
	/**
	 * Handle the event.
	 *
	 * @param  Event  $event
	 * @return void
	 */
	public function handle($event)
	{
		Cache::forget('site.boardlist');
	}
	
}
