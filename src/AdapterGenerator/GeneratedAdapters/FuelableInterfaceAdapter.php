<?php

declare(strict_types=1);

namespace App\AdapterGenerator\GeneratedAdapters;

use App\IoC\IoC;
use App\SpaceObject\Contract\FuelableInterface;


/**
 * Auto-generated adapter for App\SpaceObject\Contract\FuelableInterface
 *
 * @generated This class is automatically generated.
 */
class FuelableInterfaceAdapter implements FuelableInterface
{
    public function __construct(
        private readonly object $object
    ) {
    }

    public function getFuelLevel(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\FuelableInterface:fuelLevel.get', $this->object);
    }

    public function getFuelConsumptionRate(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\FuelableInterface:fuelConsumptionRate.get', $this->object);
    }

    public function setFuelLevel(int $level): void
    {
        IoC::resolve('App\SpaceObject\Contract\FuelableInterface:fuelLevel.set', $this->object, $level)->execute();
    }
}
