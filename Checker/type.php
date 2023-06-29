<?php

namespace checker\type;

use Core\Floor;
use Core\Response;

function notEmpty($value): void
{
    if (empty($value)) throw new \TypeError("Value is empty");
}

function string(string $string): string
{
    return $string;
}

function arrayCheck(array $array): array
{
    return $array;
}

function flawless(string $string): string
{
    return htmlspecialchars($string);
}

function int(int $int): int
{
    return $int;
}

function float(float $float): float
{
    return $float;
}

function bool(bool $bool): bool
{
    return $bool;
}

function file(string $file)
{
    return $_FILES[$file];
}
