<?php

namespace App\Exceptions;

use Exception;
use ErrorException;
use Lang;

class TorClearnet extends Exception
{
    protected $message = "Tor over clearnet";
    protected $code    = 403;
}
