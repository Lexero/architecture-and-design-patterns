<?php

declare(strict_types=1);

namespace App\Tests\Collision;

use App\Collision\Command\CollisionDetectionCommand;
use App\Collision\Contract\CollisionDetectorInterface;
use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class CollisionDetectionCommandTest extends TestCase
{
    private CollisionDetectorInterface $detector;
    private array $collisionMacros;

    protected function setUp(): void
    {
        $this->detector = $this->createMock(CollisionDetectorInterface::class);
        $this->collisionMacros = [];
    }

    public function testExecuteChecksCollisionWithEachNeighbor(): void
    {
        $system = new NeighborhoodSystem(10);

        $moving = new GameObject('mover', new Point(1, 1));
        $neighbor1 = new GameObject('n1', new Point(2, 2));
        $neighbor2 = new GameObject('n2', new Point(3, 3));

        $system->addObject($moving);
        $system->addObject($neighbor1);
        $system->addObject($neighbor2);

        $this->detector->expects(self::exactly(2))
            ->method('checkCollision')
            ->with($moving, self::isInstanceOf(GameObject::class));

        $command = new CollisionDetectionCommand($moving, $system, $this->detector, $this->collisionMacros);
        $command->execute();
    }

    public function testExecuteDoesNotCheckCollisionWithSelf(): void
    {
        $system = new NeighborhoodSystem(10);

        $moving = new GameObject('mover', new Point(1, 1));
        $system->addObject($moving);

        $this->detector->expects(self::never())->method('checkCollision');

        $command = new CollisionDetectionCommand($moving, $system, $this->detector, $this->collisionMacros);
        $command->execute();
    }

    public function testExecuteStoresMacroCommandForMovingObject(): void
    {
        $system = new NeighborhoodSystem(10);

        $moving = new GameObject('mover', new Point(1, 1));
        $system->addObject($moving);

        $command = new CollisionDetectionCommand($moving, $system, $this->detector, $this->collisionMacros);
        $command->execute();

        self::assertArrayHasKey('mover', $this->collisionMacros);
    }

    public function testExecuteUpdatesNeighborhoodWhenObjectMoves(): void
    {
        $system = new NeighborhoodSystem(10);

        $moving = new GameObject('mover', new Point(1, 1));
        $stayingOld = new GameObject('old', new Point(2, 2));
        $stayingNew = new GameObject('new', new Point(12, 12));

        $system->addObject($moving);
        $system->addObject($stayingOld);
        $system->addObject($stayingNew);

        $this->detector->expects(self::exactly(2))
            ->method('checkCollision');

        $command = new CollisionDetectionCommand($moving, $system, $this->detector, $this->collisionMacros);
        $command->execute();

        $moving->setPosition(new Point(15, 15));
        $command->execute();
    }
}
