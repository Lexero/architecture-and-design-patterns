<?php

declare(strict_types=1);

namespace App\Tests\Game\Domain;

use App\Collision\GameObject;
use App\Game\Entity\Rocket;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RocketTest extends TestCase
{
    private Rocket $rocket;
    private GameObject $gameObject;

    protected function setUp(): void
    {
        $this->gameObject = new GameObject('rocket-1', new Point(0, 0));
        $this->rocket = new Rocket(
            'rocket-1',
            $this->gameObject,
            new Point(100, 200),
            10,
            0,
        );
    }

    public function testRocketStartsActive(): void
    {
        self::assertTrue($this->rocket->isActive());
        self::assertTrue($this->rocket->getProperty('isActive'));
    }

    public function testDeactivateMakesRocketInactive(): void
    {
        $this->rocket->deactivate();

        self::assertFalse($this->rocket->isActive());
    }

    public function testSetLocationUpdatesPositionAndGameObject(): void
    {
        $newPosition = new Point(150, 250);
        $this->rocket->setLocation($newPosition);

        self::assertSame(150, $this->rocket->getLocation()->x);
        self::assertSame(250, $this->rocket->getLocation()->y);
        self::assertSame(150, $this->gameObject->getPosition()->x);
        self::assertSame(250, $this->gameObject->getPosition()->y);
    }

    public function testSetPropertyUpdatesValue(): void
    {
        $this->rocket->setProperty('velocity', 20);

        self::assertSame(20, $this->rocket->getProperty('velocity'));
    }

    public function testGetPropertyThrowsExceptionForUnknownProperty(): void
    {
        $this->expectException(RuntimeException::class);

        $this->rocket->getProperty('unknownProperty');
    }
}
