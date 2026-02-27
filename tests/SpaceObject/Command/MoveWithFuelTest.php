<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\MoveWithFuelCommand;
use App\SpaceObject\Contract\FuelableInterface;
use App\SpaceObject\Contract\MovableInterface;
use App\SpaceObject\Exception\CommandException;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

interface MovableAndFuelable extends MovableInterface, FuelableInterface
{
}

class MoveWithFuelTest extends TestCase
{
    public function testMoveWithFuelSuccessWhenEnoughFuel(): void
    {
        $object = $this->createMock(MovableAndFuelable::class);

        $object->expects(self::exactly(2))->method('getFuelLevel')->willReturn(100);
        $object->expects(self::exactly(2))->method('getFuelConsumptionRate')->willReturn(10);

        $object->expects(self::once())->method('getLocation')->willReturn(new Point(0, 0));
        $object->expects(self::once())->method('getVelocity')->willReturn(10);
        $object->expects(self::once())->method('getDirection')->willReturn(0);
        $object->expects(self::once())->method('setLocation')->with(self::isInstanceOf(Point::class));

        $object->expects(self::once())->method('setFuelLevel')->with(90);

        $moveWithFuel = new MoveWithFuelCommand($object);
        $moveWithFuel->execute();
    }

    public function testMoveWithFuelThrowsExceptionWhenNotEnoughFuel(): void
    {
        $object = $this->createMock(MovableAndFuelable::class);
        $object->expects(self::once())->method('getFuelLevel')->willReturn(5);
        $object->expects(self::once())->method('getFuelConsumptionRate')->willReturn(10);

        $object->expects(self::never())->method('getLocation');
        $object->expects(self::never())->method('setLocation');
        $object->expects(self::never())->method('setFuelLevel');

        $moveWithFuel = new MoveWithFuelCommand($object);

        $this->expectException(CommandException::class);
        $moveWithFuel->execute();
    }
}
