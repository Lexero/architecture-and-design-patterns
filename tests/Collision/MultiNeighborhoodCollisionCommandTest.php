<?php

declare(strict_types=1);

namespace App\Tests\Collision;

use App\Collision\Command\MultiNeighborhoodCollisionCommand;
use App\Collision\Contract\CollisionDetectorInterface;
use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class MultiNeighborhoodCollisionCommandTest extends TestCase
{
    private CollisionDetectorInterface $detector;

    protected function setUp(): void
    {
        $this->detector = $this->createMock(CollisionDetectorInterface::class);
    }

    public function testExecuteRunsDetectionForEachNeighborhoodSystem(): void
    {
        $system1 = new NeighborhoodSystem(10, 0, 0);
        $system2 = new NeighborhoodSystem(10, 5, 5);

        $moving = new GameObject('mover', new Point(1, 1));
        $neighbor = new GameObject('n1', new Point(2, 2));

        $system1->addObject($moving);
        $system1->addObject($neighbor);

        $system2->addObject($moving);
        $system2->addObject($neighbor);

        $this->detector->expects(self::exactly(2))
            ->method('checkCollision')
            ->with($moving, $neighbor);

        $command = new MultiNeighborhoodCollisionCommand($moving, [$system1, $system2], $this->detector);
        $command->execute();
    }

    public function testBoundaryObjectsDetectedInOffsetSystem(): void
    {
        $gridSize = 10;
        $system1 = new NeighborhoodSystem($gridSize, 0, 0);
        $system2 = new NeighborhoodSystem($gridSize, 5, 5);

        $obj1 = new GameObject('a', new Point(9, 9));
        $obj2 = new GameObject('b', new Point(10, 10));

        $system1->addObject($obj1);
        $system1->addObject($obj2);
        $system2->addObject($obj1);
        $system2->addObject($obj2);

        $this->detector->expects(self::once())
            ->method('checkCollision')
            ->with($obj1, $obj2);

        $command = new MultiNeighborhoodCollisionCommand($obj1, [$system1, $system2], $this->detector);
        $command->execute();
    }

    public function testWorksWithArbitraryNumberOfSystems(): void
    {
        $systems = [];
        $gridSize = 10;
        $systemCount = 4;

        $moving = new GameObject('mover', new Point(3, 3));
        $neighbor = new GameObject('n1', new Point(4, 4));

        for ($i = 0; $i < $systemCount; $i++) {
            $offset = (int) (($i * $gridSize) / $systemCount);
            $system = new NeighborhoodSystem($gridSize, $offset, $offset);
            $system->addObject($moving);
            $system->addObject($neighbor);
            $systems[] = $system;
        }

        $this->detector->expects(self::exactly($systemCount))
            ->method('checkCollision')
            ->with($moving, $neighbor);

        $command = new MultiNeighborhoodCollisionCommand($moving, $systems, $this->detector);
        $command->execute();
    }
}
