<?php

namespace App\Events;

use App\Role;
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
     * Users which are affected by this change.
     *
     * @var Collection
     */
    public $users;

    /**
     * Create a new event instance.
     *
     * @param \App\Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
        $this->users = $role->users;
    }
}
