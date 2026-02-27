<?php

declare(strict_types=1);

namespace App\SpaceObject\Contract;

interface FuelableInterface
{
    public function getFuelLevel(): int;

    public function getFuelConsumptionRate(): int;

    public function setFuelLevel(int $level): void;
}
