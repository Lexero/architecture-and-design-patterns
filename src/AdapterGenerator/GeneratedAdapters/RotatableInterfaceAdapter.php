<?php

declare(strict_types=1);

namespace App\AdapterGenerator\GeneratedAdapters;

use App\IoC\IoC;
use App\SpaceObject\Contract\RotatableInterface;


/**
 * Auto-generated adapter for App\SpaceObject\Contract\RotatableInterface
 *
 * @generated This class is automatically generated.
 */
class RotatableInterfaceAdapter implements RotatableInterface
{
    public function __construct(
        private readonly object $object
    ) {
    }

    public function getDirection(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\RotatableInterface:direction.get', $this->object);
    }

    public function getAngularVelocity(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\RotatableInterface:angularVelocity.get', $this->object);
    }

    public function setDirection(int $direction): void
    {
        IoC::resolve('App\SpaceObject\Contract\RotatableInterface:direction.set', $this->object, $direction)->execute();
    }
}
