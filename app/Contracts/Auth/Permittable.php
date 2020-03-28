<?php

namespace App\Contracts\Auth;

interface Permittable
{
    public function isAccountable();
    public function setAccountable($accountable);
    public function isAnonymous();
    public function permission($permission, $board = NULL);
    public function permissionAny($permission);
}
