<?php

declare(strict_types=1);

namespace App\Tests\Game\Domain;

use App\Collision\GameObject;
use App\Game\Entity\SpaceShip;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SpaceShipTest extends TestCase
{
    private SpaceShip $ship;
    private GameObject $gameObject;

    protected function setUp(): void
    {
        $this->gameObject = new GameObject('ship-1', new Point(0, 0));
        $this->ship = new SpaceShip(
            'ship-1',
            'TEAM_A',
            $this->gameObject,
            new Point(100, 200),
            5,
            90,
            45,
            100,
            1,
        );
    }

    public function testShipStartsAlive(): void
    {
        self::assertTrue($this->ship->getProperty('isAlive'));
        self::assertTrue($this->ship->isAlive());
    }

    public function testDestroyMakesShipNotAlive(): void
    {
        $this->ship->destroy();

        self::assertFalse($this->ship->isAlive());
    }

    public function testSetLocationUpdatesPositionAndGameObject(): void
    {
        $newPosition = new Point(300, 400);
        $this->ship->setLocation($newPosition);

        self::assertSame(300, $this->ship->getLocation()->x);
        self::assertSame(400, $this->ship->getLocation()->y);
        self::assertSame(300, $this->gameObject->getPosition()->x);
        self::assertSame(400, $this->gameObject->getPosition()->y);
    }

    public function testSetDirectionUpdatesDirection(): void
    {
        self::assertSame(90, $this->ship->getDirection());

        $this->ship->setDirection(180);

        self::assertSame(180, $this->ship->getDirection());
    }

    public function testSetFuelLevelUpdatesValue(): void
    {
        self::assertSame(100, $this->ship->getFuelLevel());

        $this->ship->setFuelLevel(50);

        self::assertSame(50, $this->ship->getFuelLevel());
    }

    public function testSetPropertyUpdatesValue(): void
    {
        $this->ship->setProperty('velocity', 10);

        self::assertSame(10, $this->ship->getProperty('velocity'));
    }

    public function testGetPropertyThrowsExceptionForUnknownProperty(): void
    {
        $this->expectException(RuntimeException::class);

        $this->ship->getProperty('unknownProperty');
    }
}
