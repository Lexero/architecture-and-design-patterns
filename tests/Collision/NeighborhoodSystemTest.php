<?php

declare(strict_types=1);

namespace App\Tests\Collision;

use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class NeighborhoodSystemTest extends TestCase
{
    private NeighborhoodSystem $system;

    protected function setUp(): void
    {
        $this->system = new NeighborhoodSystem(10);
    }

    public function testAddObjectPlacesObjectInCorrectNeighborhood(): void
    {
        $obj = new GameObject('a', new Point(5, 5));
        $this->system->addObject($obj);

        $neighbors = $this->system->getObjectsInSameNeighborhood($obj);

        self::assertCount(1, $neighbors);
        self::assertSame('a', $neighbors[0]->getId());
    }

    public function testObjectsInSameNeighborhoodAreGroupedTogether(): void
    {
        $obj1 = new GameObject('a', new Point(1, 1));
        $obj2 = new GameObject('b', new Point(9, 9));

        $this->system->addObject($obj1);
        $this->system->addObject($obj2);

        $neighbors = $this->system->getObjectsInSameNeighborhood($obj1);

        self::assertCount(2, $neighbors);
        $ids = array_map(fn(GameObject $o) => $o->getId(), $neighbors);
        self::assertContains('a', $ids);
        self::assertContains('b', $ids);
    }

    public function testObjectsInDifferentNeighborhoodsAreNotGrouped(): void
    {
        $obj1 = new GameObject('a', new Point(0, 0));
        $obj2 = new GameObject('b', new Point(10, 10));

        $this->system->addObject($obj1);
        $this->system->addObject($obj2);

        $neighbors1 = $this->system->getObjectsInSameNeighborhood($obj1);
        $neighbors2 = $this->system->getObjectsInSameNeighborhood($obj2);

        self::assertCount(1, $neighbors1);
        self::assertCount(1, $neighbors2);
        self::assertSame('a', $neighbors1[0]->getId());
        self::assertSame('b', $neighbors2[0]->getId());
    }

    public function testUpdateObjectNeighborhoodMovesObjectToNewNeighborhood(): void
    {
        $obj = new GameObject('a', new Point(5, 5));
        $this->system->addObject($obj);

        $obj->setPosition(new Point(15, 15));
        $this->system->updateObjectNeighborhood($obj);

        $neighborhoods = $this->system->getNeighborhoods();

        self::assertArrayNotHasKey('0,0', $neighborhoods);
        self::assertArrayHasKey('1,1', $neighborhoods);
        self::assertCount(1, $neighborhoods['1,1']);
    }

    public function testRemoveObjectRemovesItFromNeighborhood(): void
    {
        $obj = new GameObject('a', new Point(5, 5));
        $this->system->addObject($obj);
        $this->system->removeObject($obj);

        $neighborhoods = $this->system->getNeighborhoods();
        self::assertEmpty($neighborhoods);
    }

    public function testNeighborhoodKeyForNegativeCoordinates(): void
    {
        $key = $this->system->getNeighborhoodKey(new Point(-1, -1));
        self::assertSame('-1,-1', $key);
    }
}
