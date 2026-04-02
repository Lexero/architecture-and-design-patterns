<?php

declare(strict_types=1);

namespace App\Game\Security;

use App\Game\Exception\OperationNotAllowedException;
use App\SpaceObject\Contract\CommandInterface;
use Closure;
use RuntimeException;

final class OperationWhitelist
{
    private array $allowedOperations = [];

    public function register(string $operationId, Closure $commandFactory): void
    {
        $this->allowedOperations[$operationId] = $commandFactory;
    }

    public function isAllowed(string $operationId): bool
    {
        return isset($this->allowedOperations[$operationId]);
    }

    public function createCommand(string $operationId, mixed $gameObject, array $args): CommandInterface
    {
        if (!$this->isAllowed($operationId)) {
            throw new OperationNotAllowedException(
                "Operation '{$operationId}' is not allowed. Only whitelisted operations are permitted."
            );
        }

        $factory = $this->allowedOperations[$operationId];
        $command = $factory($gameObject, $args);

        if (!$command instanceof CommandInterface) {
            throw new RuntimeException(
                "Factory for operation '{$operationId}' did not return a CommandInterface"
            );
        }

        return $command;
    }

    public function getAllowedOperations(): array
    {
        return array_keys($this->allowedOperations);
    }
}
