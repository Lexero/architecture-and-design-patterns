<?php

declare(strict_types=1);

namespace App\AdapterGenerator\GeneratedAdapters;

use App\IoC\IoC;
use App\Tests\AdapterGenerator\TestMovableInterface;

use App\SpaceObject\ValueObject\Point;

/**
 * Auto-generated adapter for App\Tests\AdapterGenerator\TestMovableInterface
 *
 * @generated This class is automatically generated.
 */
class TestMovableInterfaceAdapter implements TestMovableInterface
{
    public function __construct(
        private readonly object $object
    ) {
    }

    public function getPosition(): Point
    {
        return IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:position.get', $this->object);
    }

    public function setPosition(Point $newValue): void
    {
        IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:position.set', $this->object, $newValue)->execute();
    }

    public function getVelocity(): int
    {
        return IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:velocity.get', $this->object);
    }

    public function finish(): void
    {
        IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:finish', $this->object)->execute();
    }
}
