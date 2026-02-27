<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\ChangeVelocityCommand;
use App\SpaceObject\Contract\VelocityChangeableInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChangeVelocityCommandTest extends TestCase
{
    public static function changeVelocityForMovingObjectProvider(): array
    {
        return [
            'velocity with direction 45' => [10, 45],
            'velocity with direction 90' => [15, 90],
            'velocity with direction 0' => [20, 0],
        ];
    }

    #[DataProvider('changeVelocityForMovingObjectProvider')]
    public function testChangeVelocityForMovingObject(
        int $velocity,
        int $currentDirection
    ): void {
        $object = $this->createMock(VelocityChangeableInterface::class);
        $object->expects(self::once())->method('getVelocity')->willReturn($velocity);
        $object->expects(self::once())->method('getDirection')->willReturn($currentDirection);
        $object->expects(self::once())->method('setVelocityDirection')->with($currentDirection);

        $changeVelocity = new ChangeVelocityCommand($object);
        $changeVelocity->execute();
    }

    public function testChangeVelocityForNotMovingObject(): void
    {
        $object = $this->createMock(VelocityChangeableInterface::class);
        $object->expects(self::once())->method('getVelocity')->willReturn(0);

        $object->expects(self::never())->method('getDirection');
        $object->expects(self::never())->method('setVelocity');
        $object->expects(self::never())->method('setVelocityDirection');

        $changeVelocity = new ChangeVelocityCommand($object);
        $changeVelocity->execute();
    }
}
