<?php namespace App\Events;

use App\Role;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class RoleWasModified extends Event
{
	use SerializesModels;
	
	/**
	 * The role the event is being fired on.
	 *
	 * @var \App\Role
	 */
	public $role;
	
	/**
	 * The board page which must be cleared as a result of this event.
	 *
	 * @var integer|true
	 */
	public $page;
	
	/**
	 * Create a new event instance.
	 *
	 * @param  \App\Role  $role
	 * @return void
	 */
	public function __construct(Role $role)
	{
		$this->role = $role;
	}
}
