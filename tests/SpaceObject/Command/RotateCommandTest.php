<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\RotateCommand;
use App\SpaceObject\Contract\RotatableInterface;
use App\SpaceObject\Exception\AngularVelocityReadException;
use App\SpaceObject\Exception\DirectionReadException;
use App\SpaceObject\Exception\DirectionWriteException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RotateCommandTest extends TestCase
{
    private RotatableInterface $rotatable;
    protected function setUp(): void
    {
        $this->rotatable = $this->createMock(RotatableInterface::class);
    }

    #[DataProvider('successfulRotationProvider')]
    public function testSuccessfulRotation(int $currentAngle, int $angularVelocity, int $expectedAngle): void
    {
        $this->rotatable->expects(self::once())
            ->method('getDirection')
            ->willReturn($currentAngle);

        $this->rotatable->expects(self::once())
            ->method('getAngularVelocity')
            ->willReturn($angularVelocity);

        $this->rotatable->expects(self::once())
            ->method('setDirection')
            ->with($expectedAngle);

        $rotate = new RotateCommand($this->rotatable);
        $rotate->execute();
    }

    public static function successfulRotationProvider(): array
    {
        return [
            'simple rotation'        => [0, 45, 45],
            'negative rotation'      => [10, -20, 350],
            'full circle'            => [0, 360, 0],
        ];
    }

    public function testRotateWhenCannotReadDirection(): void
    {
        $this->rotatable->expects(self::once())
            ->method('getDirection')
            ->willThrowException(new RuntimeException('Direction unavailable'));

        $rotate = new RotateCommand($this->rotatable);

        $this->expectException(DirectionReadException::class);
        $rotate->execute();
    }

    public function testRotateWhenCannotReadAngularVelocity(): void
    {
        $this->rotatable->expects(self::once())
            ->method('getDirection')
            ->willReturn(0);

        $this->rotatable->expects(self::once())
            ->method('getAngularVelocity')
            ->willThrowException(new RuntimeException('Angular velocity unavailable'));

        $rotate = new RotateCommand($this->rotatable);

        $this->expectException(AngularVelocityReadException::class);
        $rotate->execute();
    }

    public function testRotateWhenCannotWriteDirection(): void
    {
        $this->rotatable->expects(self::once())
            ->method('getDirection')
            ->willReturn(0);

        $this->rotatable->expects(self::once())
            ->method('getAngularVelocity')
            ->willReturn(45);

        $this->rotatable->expects(self::once())
            ->method('setDirection')
            ->willThrowException(new RuntimeException('Direction write failed'));

        $rotate = new RotateCommand($this->rotatable);

        $this->expectException(DirectionWriteException::class);
        $rotate->execute();
    }
}
