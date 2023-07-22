<?php
namespace Core;

class Floor{
    private array $state = [];

    /**
     * @param string $key
     * @param [type] $value
     * @return void
     * 
     * Drop value in the floor in function of the key.
     */
    public function droped(string $key, $value): void
    {
        $this->state[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     * 
     * Pickup value in the floor in function of the key.
     */
    public function pickup(string $key)
    {
        return $this->state[$key] ?? null;
    }
}