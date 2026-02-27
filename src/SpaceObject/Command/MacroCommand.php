<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Exception\CommandException;
use Throwable;

class MacroCommand implements CommandInterface
{
    public function __construct(
        /** @param CommandInterface[] $commands */
        private readonly array $commands,
    ) {
    }

    /** @throws CommandException */
    public function execute(): void
    {
        foreach ($this->commands as $command) {
            try {
                $command->execute();
            } catch (Throwable $e) {
                throw new CommandException(
                    $e->getMessage(),
                    0,
                    $e
                );
            }
        }
    }
}
