<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\BurnFuelCommand;
use App\SpaceObject\Contract\FuelableInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BurnFuelCommandTest extends TestCase
{
    public static function burnFuelProvider(): array
    {
        return [
            'default fuel consumption' => [100, 25, 75],
            'zero consumption rate' => [50, 0, 50],
            'burn all fuel' => [30, 30, 0],
        ];
    }

    #[DataProvider('burnFuelProvider')]
    public function testBurnFuelCommand(
        int $initialFuel,
        int $consumptionRate,
        int $expectedFuel
    ): void {
        $fuelable = $this->createMock(FuelableInterface::class);

        $fuelable->expects(self::once())->method('getFuelLevel')->willReturn($initialFuel);
        $fuelable->expects(self::once())->method('getFuelConsumptionRate')->willReturn($consumptionRate);
        $fuelable->expects(self::once())->method('setFuelLevel')->with($expectedFuel);

        $burnFuel = new BurnFuelCommand($fuelable);
        $burnFuel->execute();
    }
}
