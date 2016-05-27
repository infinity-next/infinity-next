<?php

namespace App\Events;

use App\Contracts\PermissionUser;
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
     * @param \App\Contracts\PermissionUser $user
     */
    public function __construct(PermissionUser $user)
    {
        $this->user = $user;
    }
}
