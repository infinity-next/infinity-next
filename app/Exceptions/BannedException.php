<?php

namespace App\Exceptions;

use App\Ban;
use Illuminate\Validation\ValidationException;

class BannedException extends ValidationException
{
    /**
     * The ban received.
     *
     * @var App\Ban
     */
    protected $ban;

    public function ban(Ban $ban)
    {
        $this->ban = $ban;

        return $this;
    }
}
