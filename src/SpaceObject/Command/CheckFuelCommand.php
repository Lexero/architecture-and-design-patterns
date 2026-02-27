<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\FuelableInterface;
use App\SpaceObject\Exception\CommandException;

class CheckFuelCommand implements CommandInterface
{
    public function __construct(
        private readonly FuelableInterface $fuelable,
    ) {
    }

    /** @throws CommandException */
    public function execute(): void
    {
        $fuelLevel = $this->fuelable->getFuelLevel();
        $consumptionRate = $this->fuelable->getFuelConsumptionRate();

        if ($fuelLevel < $consumptionRate) {
            throw new CommandException('Need more fuel for complete command');
        }
    }
}
