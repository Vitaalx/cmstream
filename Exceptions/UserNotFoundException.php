<?php

namespace Exceptions;

class UserNotFoundException extends \Exception implements \Throwable
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        $message = "User not found";
        $code = 404;
        parent::__construct($message, $code, $previous);
    }
}