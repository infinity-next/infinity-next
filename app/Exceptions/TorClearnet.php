<?php

namespace App\Exceptions;

use Exception;

class TorClearnet extends Exception
{
    protected $message = 'Tor over clearnet';
    protected $code = 403;
}
