<?php

namespace Core;

use function checker\category\name;

class Logger
{
    private static $disabled = false;

    private static function insert(string $message, string $header): void
    {
        if(self::$disabled === true) return;

        error_log(
            date("h:i:s") . " " 
            . $header . " " 
            . Request::getCurrentRequest()->getMethod() . " " 
            . Request::getCurrentRequest()->getUri() . " "
            . (Response::getCurrentResponse()->getInfo() ?? "NONE") . " "
            . Response::getCurrentResponse()->getCode() . " : " 
            . $message . "\n",
            LOG_ERR,
            "./../logs/" . date("d-m-y") . ".txt",
            $header
        );
    }

    public static function auto($message): void
    {
        $code = Response::getCurrentResponse()->getCode();
        if($code >= 500)self::error($message);
        else if($code >= 400)self::warning($message);
        else if($code >= 200)self::info($message);
    }

    public static function info(string $message): void
    {
        self::insert($message, "INFO");
    }

    public static function warning(string $message): void
    {
        self::insert($message, "WARNING");
    }

    public static function error(string $message): void
    {
        self::insert($message, "ERROR");
    }

    public static function debug(string $message): void
    {
        self::insert($message, "DEBUG");
    }

    public static function disable(): void
    {
        self::$disabled = true;
    }
}
