<?php

declare(strict_types=1);

namespace App\Game\Object;

use RuntimeException;

final class UObjectImpl implements UObject
{
    private array $properties;

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function getProperty(string $name): mixed
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new RuntimeException("Property '{$name}' not found");
        }

        return $this->properties[$name];
    }

    public function setProperty(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }
}
