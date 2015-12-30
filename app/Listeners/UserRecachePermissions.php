<?php namespace App\Listeners;

use App\Role;
use App\RoleCache;
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
			$event->user->forgetPermissions();
		}
		else if (isset($event->users))
		{
			foreach ($event->users as $user)
			{
				$user->forgetPermissions();
			}
		}
		else
		{
			RoleCache::delete();
		}
	}
}
