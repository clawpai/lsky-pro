<?php

namespace App\Exceptions;

use Exception;

class ApplicationNotInstalledException extends Exception
{
    public function __construct($message = "Application is not installed.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
