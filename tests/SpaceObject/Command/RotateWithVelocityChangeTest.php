<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\RotateWithVelocityChangeCommand;
use App\SpaceObject\Contract\RotatableInterface;
use App\SpaceObject\Contract\VelocityChangeableInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

interface RotatableAndVelocityChangeable extends RotatableInterface, VelocityChangeableInterface
{
}

class RotateWithVelocityChangeTest extends TestCase
{
    public static function rotateWithVelocityProvider(): array
    {
        return [
            'basic rotation' => [45, 0, 45, 10],
            'negative angular velocity' => [-30, 20, 350, 15],
            'full circle rotation' => [360, 0, 0, 20],
        ];
    }

    #[DataProvider('rotateWithVelocityProvider')]
    public function testRotateWithVelocityChange(
        int $angularVelocity,
        int $currentDirection,
        int $expectedDirection,
        int $velocity
    ): void {
        $object = $this->createMock(RotatableAndVelocityChangeable::class);

        $object->expects(self::once())->method('getAngularVelocity')->willReturn($angularVelocity);
        $object->expects(self::exactly(2))
            ->method('getDirection')
            ->willReturnOnConsecutiveCalls($currentDirection, $expectedDirection);

        $object->expects(self::once())->method('setDirection')->with($expectedDirection);
        $object->expects(self::once())->method('getVelocity')->willReturn($velocity);
        $object->expects(self::once())->method('setVelocityDirection')->with($expectedDirection);

        $rotateWithVelocity = new RotateWithVelocityChangeCommand($object);
        $rotateWithVelocity->execute();
    }

    public function testRotateWithVelocityForNotMovingObject(): void
    {
        $object = $this->createMock(RotatableAndVelocityChangeable::class);

        $object->expects(self::once())->method('getAngularVelocity')->willReturn(90);
        $object->expects(self::once())->method('getDirection')->willReturn(180);
        $object->expects(self::once())->method('setDirection')->with(270);
        $object->expects(self::once())->method('getVelocity')->willReturn(0);

        $object->expects(self::never())->method('setVelocity');
        $object->expects(self::never())->method('setVelocityDirection');

        $rotateWithVelocity = new RotateWithVelocityChangeCommand($object);
        $rotateWithVelocity->execute();
    }
}
