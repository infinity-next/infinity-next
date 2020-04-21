<?php

namespace App\Support\Tripcode;

use Exception;

class InvalidPgpTripcode extends Exception
{
    protected $message = 'Bad PGP Message';
    protected $code = 400;
}
