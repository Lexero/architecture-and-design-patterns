<?php

declare(strict_types=1);

namespace App\Tests\Game\Command;

use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\Game\Command\ShootCommand;
use App\Game\Entity\Rocket;
use App\Game\Entity\SpaceShip;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class ShootCommandTest extends TestCase
{
    private SpaceShip $ship;
    private array $rockets;
    private NeighborhoodSystem $neighborhood;

    protected function setUp(): void
    {
        $this->ship = new SpaceShip(
            'ship-1',
            'TEAM_A',
            new GameObject('ship-1', new Point(100, 100)),
            new Point(100, 100),
            5,
            0,
            45,
            100,
            1,
        );
        $this->rockets = [];
        $this->neighborhood = new NeighborhoodSystem(50);
        $this->neighborhood->addObject($this->ship->getGameObject());
    }

    public function testShootCommandCreatesRocket(): void
    {
        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();

        self::assertCount(1, $this->rockets);
        self::assertInstanceOf(Rocket::class, array_values($this->rockets)[0]);
    }

    public function testNewRocketHasShipDirectionAndHigherVelocity(): void
    {
        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();

        $rocket = array_values($this->rockets)[0];

        self::assertSame($this->ship->getVelocity() + 5, $rocket->getVelocity());
        self::assertSame($this->ship->getDirection(), $rocket->getDirection());
    }

    public function testRocketStartsAtShipPosition(): void
    {
        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();

        $rocket = array_values($this->rockets)[0];

        self::assertSame(100, $rocket->getLocation()->x);
        self::assertSame(100, $rocket->getLocation()->y);
    }

    public function testDeadShipCannotShoot(): void
    {
        $this->ship->destroy();

        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();
        self::assertCount(0, $this->rockets);
    }

    public function testRocketIsAddedToNeighborhood(): void
    {
        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();

        $rocket = array_values($this->rockets)[0];
        $neighbors = $this->neighborhood->getObjectsInSameNeighborhood($rocket->getGameObject());
        self::assertGreaterThanOrEqual(1, count($neighbors));
    }

    public function testMultipleShootsCreateMultipleRockets(): void
    {
        $command = new ShootCommand($this->ship, $this->rockets, $this->neighborhood);
        $command->execute();
        $command->execute();
        $command->execute();

        self::assertCount(3, $this->rockets);
    }
}
