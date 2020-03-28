<?php

namespace App\Events;

use App\Contracts\Auth\Permittable;
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
     * @param \App\Contracts\Auth\Permittable $user
     */
    public function __construct(Permittable $user)
    {
        $this->user = $user;
    }
}
