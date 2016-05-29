<?php

namespace App\Services;

use App;
use App\Contracts\PermissionUser;
use Illuminate\Contracts\Auth\Guard;

class UserManager
{
    /**
     * Create a new authentication controller instance.
     * Don't overwrite __construct in any children. Use ::boot.
     *
     * @param \Illuminate\Auth\Guard     $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->user = app(PermissionUser::class);
    }

    /**
     * Returns our user object.
     *
     * @return \App\Contracts\PermissionUser
     */
    public function user()
    {
        return $this->user;
    }
}
