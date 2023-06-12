<?php

namespace Services\token;
use Core\Token;

class AccessToken extends Token{
    static protected function duration(): ?int
    {
        return CONFIG["TOKEN_DURATION"];
    }
    
    static protected function secretKey(): string
    {
        return CONFIG["SECRET_KEY"];
    }
}