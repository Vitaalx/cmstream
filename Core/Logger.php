<?php

namespace Core;

class Logger
{
    private static $disabled = false;
    private static $allowRequestDB = false;

    /**
     * @param mixed $message
     * @param string $header
     * @return void
     * 
     * Insert a message in the log file.
     * Set Log with request method, uri, response info, response code and message.
     */
    private static function insert(mixed $message, string $header): void
    {
        if(self::$disabled === true) return;

        if(gettype($message) === "array"){
            $result = "";
            foreach($message as $key => $value){
                $result .= "$key: $value, ";
            }
            $message = $result;
        }
        else if(gettype($message) === "boolean"){
            $message = $message ? "true" : "false";
        }

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
    /**
     * @param mixed $message
     * @return void
     * 
     * Set auto heading of log in function of the response code.
     */
    public static function auto(mixed $message): void
    {
        $code = Response::getCurrentResponse()->getCode();
        if($code >= 500)self::error($message);
        else if($code >= 400)self::warning($message);
        else if($code >= 200)self::info($message);
    }

    public static function info(mixed $message): void
    {
        self::insert($message, "INFO");
    }

    public static function warning(mixed $message): void
    {
        self::insert($message, "WARNING");
    }

    public static function error(mixed $message): void
    {
        self::insert($message, "ERROR");
    }

    public static function debug(mixed $message): void
    {
        self::insert($message, "DEBUG");
    }

    public static function disable(): void
    {
        self::$disabled = true;
    }

    public static function allowRequestDB(): void
    {
        self::$allowRequestDB = true;
    }
    public static function getAllowRequestDB(): bool
    {
        return self::$allowRequestDB;
    }
}
