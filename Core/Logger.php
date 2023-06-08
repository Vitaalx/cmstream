<?php

namespace Core;

class Logger
{
    public static function insert(string $message, string $header): void
    {
        error_log(
            $message,
            LOG_ERR,
            "./../log/prod.txt",
            $header
        );
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

    public static function fatal(string $message): void
    {
        self::insert($message, "FATAL");
    }

    public static function notice(string $message): void
    {
        self::insert($message, "NOTICE");
    }

    public static function entry(string $message): void
    {
        self::insert($message, "ENTRY");
    }

    public static function exit(string $message): void
    {
        self::insert($message, "EXIT");
    }
}
