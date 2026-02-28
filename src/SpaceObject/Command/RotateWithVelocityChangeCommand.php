<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\RotatableInterface;
use App\SpaceObject\Contract\VelocityChangeableInterface;

class RotateWithVelocityChangeCommand implements CommandInterface
{
    private readonly MacroCommand $macroCommand;

    public function __construct(
        RotatableInterface&VelocityChangeableInterface $object,
    ) {
        $this->macroCommand = new MacroCommand([
            new RotateCommand($object),
            new ChangeVelocityCommand($object),
        ]);
    }

    public function execute(): void
    {
        $this->macroCommand->execute();
    }
}
