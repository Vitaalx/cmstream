<?php

namespace Services\token;

use Core\Token;

class ResetToken extends Token
{
    static protected function name(): string
    {
        return "ResetToken";
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