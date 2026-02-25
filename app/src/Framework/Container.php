<?php
declare(strict_types=1);

namespace App\Framework;

final class Container
{
    private array $bindings = [];

    public function set(string $abstract, callable $factory): void
    {
        // Register a factory for a given class or interface name.
        $this->bindings[$abstract] = $factory;
    }

    public function get(string $abstract): mixed
    {
        // If a factory is registered, use it to build the instance.
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        // Otherwise, construct the class directly.
        return new $abstract();
    }
}
