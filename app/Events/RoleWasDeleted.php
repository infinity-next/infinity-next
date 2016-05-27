<?php

namespace App\Events;

use App\Role;
use Illuminate\Queue\SerializesModels;

class RoleWasDeleted extends Event
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
     * @param \App\Role  $role
     * @param Collection $users
     */
    public function __construct(Role $role, $users)
    {
        $this->role = $role;
        $this->users = $users;
    }
}
