<?php namespace App\Listeners;

use App\Listeners\Listener;
use Cache;

class SiteSettingsRecache extends Listener
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
		Cache::forget('site.settings');
	}
	
}
