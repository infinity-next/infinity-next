<?php

namespace App\Filesystem;

use Exception;

class BannedHashException extends Exception
{
    protected $message = 'File Explicitly Banned';
    protected $code = 403;
}
