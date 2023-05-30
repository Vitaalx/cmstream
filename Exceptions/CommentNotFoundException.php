<?php

namespace Exceptions;

class CommentNotFoundException extends \Exception implements \Throwable
{
    public function __construct($messageProvided = "", $codeProvided = 0, \Throwable $previous = null)
    {
        if(trim($messageProvided) != "" || $messageProvided != NULL) {
            $message = $messageProvided;
        } else {
            $message = "Comment not found";
        }
        if($codeProvided != NULL) {
            $code = $codeProvided;
        } else {
            $code = 404;
        }
        parent::__construct($message, $code, $previous);
    }
}