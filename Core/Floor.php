<?php
namespace Core;

class Floor{
    private array $state = [];

    public function droped(string $key, $value): void
    {
        $this->state[$key] = $value;
    }

    public function pickup(string $key)
    {
        return $this->state[$key];
    }
}