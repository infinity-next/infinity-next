<?php namespace App\Listeners;

use App\Listeners\Listener;
use Cache;

class OverboardRecache extends Listener
{
	
	/**
	 * Handle the event.
	 *
	 * @param  Event  $event
	 * @return void
	 */
	public function handle($event)
	{
		Cache::forget("overboard");
	}
	
}