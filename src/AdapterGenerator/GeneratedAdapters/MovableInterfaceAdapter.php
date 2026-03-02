<?php

declare(strict_types=1);

namespace App\AdapterGenerator\GeneratedAdapters;

use App\IoC\IoC;
use App\SpaceObject\Contract\MovableInterface;

use App\SpaceObject\ValueObject\Point;

/**
 * Auto-generated adapter for App\SpaceObject\Contract\MovableInterface
 *
 * @generated This class is automatically generated.
 */
class MovableInterfaceAdapter implements MovableInterface
{
    public function __construct(
        private readonly object $object
    ) {
    }

    public function getLocation(): Point
    {
        return IoC::resolve('App\SpaceObject\Contract\MovableInterface:location.get', $this->object);
    }

    public function getVelocity(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\MovableInterface:velocity.get', $this->object);
    }

    public function getDirection(): int
    {
        return IoC::resolve('App\SpaceObject\Contract\MovableInterface:direction.get', $this->object);
    }

    public function setLocation(Point $location): void
    {
        IoC::resolve('App\SpaceObject\Contract\MovableInterface:location.set', $this->object, $location)->execute();
    }
}
