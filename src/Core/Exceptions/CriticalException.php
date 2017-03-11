<?php

namespace Core\Exceptions;

use Exception;

class CriticalException extends \Exception
{

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code ?: 500, $previous);
    }
}