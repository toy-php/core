<?php

namespace Core\Exceptions;

use Throwable;

class Http404Exception extends HttpException
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code ?: 404, $previous);
    }
}