<?php

namespace App\Support\Tripcode;

use Exception;

class NoPublicKey extends Exception
{
    protected $message = 'Publickey missing.';
    protected $code = 400;
}
