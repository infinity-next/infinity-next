<?php

namespace App\Filesystem;

use Exception;

class ThumbnailingException extends Exception
{
    protected $message = "Server Can't Thumbnail This";
    protected $code = 500;
}
