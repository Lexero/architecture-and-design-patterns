<?php

declare(strict_types=1);

namespace App\Game\Factory;

use App\Game\Service\CommandDefinitionRegistry;
use App\IoC\IoC;
use App\SpaceObject\Command\MacroCommand;

final class MacroCommandFactory
{
    public function __construct(
        private readonly CommandDefinitionRegistry $registry,
    ) {
    }

    public function build(string $macroName, mixed ...$args): MacroCommand
    {
        $operationNames = $this->registry->get($macroName);
        $commands = [];

        foreach ($operationNames as $operationName) {
            $commands[] = IoC::resolve($operationName, ...$args);
        }

        return new MacroCommand($commands);
    }
}
