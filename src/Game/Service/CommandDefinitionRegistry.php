<?php

declare(strict_types=1);

namespace App\Game\Service;

use InvalidArgumentException;

final class CommandDefinitionRegistry
{
    /** @var array<string, string[]> */
    private array $definitions = [];

    public function define(string $macroName, array $operationNames): void
    {
        if (empty($operationNames)) {
            throw new InvalidArgumentException("Macro '{$macroName}' must have at least one operation");
        }

        $this->definitions[$macroName] = $operationNames;
    }

    public function get(string $macroName): array
    {
        if (!$this->has($macroName)) {
            throw new InvalidArgumentException("Macro '{$macroName}' is not defined");
        }

        return $this->definitions[$macroName];
    }

    public function has(string $macroName): bool
    {
        return isset($this->definitions[$macroName]);
    }

    public function all(): array
    {
        return $this->definitions;
    }
}
