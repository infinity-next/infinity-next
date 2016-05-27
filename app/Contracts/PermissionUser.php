<?php

namespace App\Contracts;

interface PermissionUser
{
    /**
     * Getter for the $accountable property.
     *
     * @return bool
     */
    public function isAccountable();

    /**
     * Getter for the $anonymous property.
     *
     * @return bool
     */
    public function isAnonymous();
}
