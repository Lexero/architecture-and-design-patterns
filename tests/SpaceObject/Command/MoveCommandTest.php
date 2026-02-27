<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\MoveCommand;
use App\SpaceObject\Contract\MovableInterface;
use App\SpaceObject\Exception\DirectionReadException;
use App\SpaceObject\Exception\LocationReadException;
use App\SpaceObject\Exception\LocationWriteException;
use App\SpaceObject\Exception\VelocityReadException;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MoveCommandTest extends TestCase
{
    private MovableInterface $movable;
    protected function setUp(): void
    {
        $this->movable = $this->createMock(MovableInterface::class);
    }

    // Для объекта, находящегося в точке (12, 5) и движущегося со скоростью (-7, 3) движение меняет положение объекта на (5, 8)
    public function testSuccessfulMove(): void
    {
        $this->movable->expects(self::once())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $this->movable->expects(self::once())
            ->method('getVelocity')
            ->willReturn(8);

        $this->movable->expects(self::once())
            ->method('getDirection')
            ->willReturn(157);

        $this->movable->expects(self::once())
            ->method('setLocation')
            ->with(self::callback(function (Point $location): bool {
                return $location->x === 5 && $location->y === 8;
            }));

        $move = new MoveCommand($this->movable);
        $move->execute();
    }

    public function testMoveWhenCannotReadLocation(): void
    {
        $this->movable->expects(self::once())
            ->method('getLocation')
            ->willThrowException(new RuntimeException('Location unavailable'));

        $move = new MoveCommand($this->movable);

        $this->expectException(LocationReadException::class);
        $move->execute();
    }

    public function testMoveWhenCannotReadVelocity(): void
    {
        $this->movable->expects(self::once())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $this->movable->expects(self::once())
            ->method('getVelocity')
            ->willThrowException(new RuntimeException('Velocity unavailable'));

        $move = new MoveCommand($this->movable);

        $this->expectException(VelocityReadException::class);
        $move->execute();
    }

    public function testMoveWhenCannotReadDirection(): void
    {
        $this->movable->expects(self::once())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $this->movable->expects(self::once())
            ->method('getVelocity')
            ->willReturn(10);

        $this->movable->expects(self::once())
            ->method('getDirection')
            ->willThrowException(new RuntimeException('Direction unavailable'));

        $move = new MoveCommand($this->movable);

        $this->expectException(DirectionReadException::class);
        $move->execute();
    }

    public function testMoveWhenCannotWriteLocation(): void
    {
        $this->movable->expects(self::once())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $this->movable->expects(self::once())
            ->method('getVelocity')
            ->willReturn(10);

        $this->movable->expects(self::once())
            ->method('getDirection')
            ->willReturn(90);

        $this->movable->expects(self::once())
            ->method('setLocation')
            ->willThrowException(new RuntimeException('Location write failed'));

        $move = new MoveCommand($this->movable);

        $this->expectException(LocationWriteException::class);
        $move->execute();
    }
}
