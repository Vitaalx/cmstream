<?php

namespace Exceptions;

class VideoNotFoundException extends \Exception implements \Throwable
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        $message = "Video not found";
        $code = 404;
        parent::__construct($message, $code, $previous);
    }
}