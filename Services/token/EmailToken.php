<?php

namespace Services\token;
use Core\Token;

class EmailToken extends Token{
    static protected function name(): string
    {
        return "EmailToken";
    }

    static protected function duration(): ?int
    {
        return null;
    }
    
    static protected function secretKey(): string
    {
        return CONFIG["SECRET_KEY"];
    }
}