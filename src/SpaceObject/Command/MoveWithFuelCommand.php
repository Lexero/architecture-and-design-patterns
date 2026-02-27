<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\FuelableInterface;
use App\SpaceObject\Contract\MovableInterface;

class MoveWithFuelCommand implements CommandInterface
{
    private readonly MacroCommand $macroCommand;

    public function __construct(
        MovableInterface&FuelableInterface $object,
    ) {
        $this->macroCommand = new MacroCommand([
            new CheckFuelCommand($object),
            new MoveCommand($object),
            new BurnFuelCommand($object),
        ]);
    }

    public function execute(): void
    {
        $this->macroCommand->execute();
    }
}
