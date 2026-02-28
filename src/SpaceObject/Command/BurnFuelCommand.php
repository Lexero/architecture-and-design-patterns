<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\FuelableInterface;

class BurnFuelCommand implements CommandInterface
{
    public function __construct(
        private readonly FuelableInterface $fuelable,
    ) {
    }

    public function execute(): void
    {
        $fuelLevel = $this->fuelable->getFuelLevel();
        $consumptionRate = $this->fuelable->getFuelConsumptionRate();
        $this->fuelable->setFuelLevel($fuelLevel - $consumptionRate);
    }
}
