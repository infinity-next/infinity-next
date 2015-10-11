<?php namespace App\Events;

use App\Contracts\PermissionUser;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class UserRolesModified extends Event
{
	use SerializesModels;
	
	/**
	 * Users which are affected by this change.
	 *
	 * @var Collection
	 */
	public $user;
	
	/**
	 * Create a new event instance.
	 *
	 * @param  \App\Contracts\PermissionUser  $user
	 * @return void
	 */
	public function __construct(PermissionUser $user)
	{
		$this->user = $user;
	}
}
