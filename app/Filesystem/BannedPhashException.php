<?php

namespace App\Filesystem;

use Exception;

class BannedPhashException extends Exception
{
    protected $message = 'File Perceptually Banned';
    protected $code = 403;
}
