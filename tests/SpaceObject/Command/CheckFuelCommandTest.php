<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\CheckFuelCommand;
use App\SpaceObject\Contract\FuelableInterface;
use App\SpaceObject\Exception\CommandException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckFuelCommandTest extends TestCase
{
    public static function successCasesProvider(): array
    {
        return [
            'enough fuel' => [100, 50],
            'exact fuel amount' => [50, 50],
        ];
    }

    #[DataProvider('successCasesProvider')]
    public function testCheckFuelSuccess(int $fuelLevel, int $consumptionRate): void
    {
        $fuelable = $this->createMock(FuelableInterface::class);

        $fuelable->expects(self::once())->method('getFuelLevel')->willReturn($fuelLevel);
        $fuelable->expects(self::once())->method('getFuelConsumptionRate')->willReturn($consumptionRate);

        $checkFuel = new CheckFuelCommand($fuelable);
        $checkFuel->execute();
    }

    public static function failureCasesProvider(): array
    {
        return [
            'not enough fuel' => [30, 50],
            'no fuel' => [0, 10],
        ];
    }

    #[DataProvider('failureCasesProvider')]
    public function testCheckFuelThrowsException(int $fuelLevel, int $consumptionRate): void
    {
        $fuelable = $this->createMock(FuelableInterface::class);

        $fuelable->expects(self::once())->method('getFuelLevel')->willReturn($fuelLevel);
        $fuelable->expects(self::once())->method('getFuelConsumptionRate')->willReturn($consumptionRate);

        $checkFuel = new CheckFuelCommand($fuelable);
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Need more fuel for complete command');
        $checkFuel->execute();
    }
}
