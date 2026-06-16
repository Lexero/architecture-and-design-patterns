<?php

declare(strict_types=1);

namespace App\Tests\Game\Service;

use App\Collision\GameObject;
use App\Game\Entity\SpaceShip;
use App\Game\Factory\MacroCommandFactory;
use App\Game\Service\CommandDefinitionRegistry;
use App\IoC\IoC;
use App\SpaceObject\Command\BurnFuelCommand;
use App\SpaceObject\Command\ChangeVelocityCommand;
use App\SpaceObject\Command\CheckFuelCommand;
use App\SpaceObject\Command\MoveCommand;
use App\SpaceObject\Command\RotateCommand;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class GameInitializerDslIntegrationTest extends TestCase
{
    private CommandDefinitionRegistry $registry;
    private MacroCommandFactory $factory;
    private SpaceShip $ship;

    protected function setUp(): void
    {
        IoC::reset();
        IoC::resolve('Scopes.New', 'dsl-test')->execute();
        IoC::resolve('Scopes.Current', 'dsl-test')->execute();

        $this->registry = new CommandDefinitionRegistry();
        $this->factory = new MacroCommandFactory($this->registry);

        IoC::resolve('IoC.Register', 'CheckFuel', static fn(SpaceShip $s) => new CheckFuelCommand($s))->execute();
        IoC::resolve('IoC.Register', 'Move', static fn(SpaceShip $s) => new MoveCommand($s))->execute();
        IoC::resolve('IoC.Register', 'BurnFuel', static fn(SpaceShip $s) => new BurnFuelCommand($s))->execute();
        IoC::resolve('IoC.Register', 'Rotate', static fn(SpaceShip $s) => new RotateCommand($s))->execute();
        IoC::resolve('IoC.Register', 'ChangeVelocity', static fn(SpaceShip $s) => new ChangeVelocityCommand($s))->execute();

        $this->registry->define('Ship.MoveWithFuel', ['CheckFuel', 'Move', 'BurnFuel']);
        $this->registry->define('Ship.RotateWithVelocity', ['Rotate', 'ChangeVelocity']);

        $gameObject = new GameObject('ship-1', new Point(0, 0));
        $this->ship = new SpaceShip(
            'ship-1',
            'TEAM_A',
            $gameObject,
            new Point(0, 0),
            10,
            0,
            45,
            100,
            5,
        );
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testMoveWithFuelMovesShipAndBurnsFuel(): void
    {
        $command = $this->factory->build('Ship.MoveWithFuel', $this->ship);
        $command->execute();

        self::assertSame(10, $this->ship->getLocation()->x);
        self::assertSame(95, $this->ship->getFuelLevel());
    }

    public function testMoveWithFuelThrowsWhenNotEnoughFuel(): void
    {
        $gameObject = new GameObject('ship-2', new Point(0, 0));
        $depletedShip = new SpaceShip(
            'ship-2',
            'TEAM_A',
            $gameObject,
            new Point(0, 0),
            10,
            0,
            45,
            fuelLevel: 2,
            fuelConsumptionRate: 5,
        );

        $command = $this->factory->build('Ship.MoveWithFuel', $depletedShip);

        $this->expectException(CommandException::class);
        $command->execute();

        self::assertSame(0, $depletedShip->getLocation()->x);
    }

    public function testRotateWithVelocityRotatesShip(): void
    {
        $command = $this->factory->build('Ship.RotateWithVelocity', $this->ship);
        $command->execute();
        self::assertSame(45, $this->ship->getDirection());
    }
}
