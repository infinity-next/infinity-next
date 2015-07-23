<?php namespace App\Listeners;

use App\Role;
use App\Listeners\Listener;
use App\Contracts\PermissionUser;

use Cache;
use DB;

class UserRecachePermissions extends Listener
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
		if (isset($event->user))
		{
			Cache::forget("user.{$event->user->user_id}.permissions");
		}
		else
		{
			switch (env('CACHE_DRIVER'))
			{
				case "file" :
					Cache::flush();
					break;
				
				case "database" :
					DB::table('cache')
						->where('key', 'like', "%user.%.permissions")
						->delete();
					break;
				
				default :
					Cache::tags("permissions")->flush();
					break;
			}
		}
	}
}