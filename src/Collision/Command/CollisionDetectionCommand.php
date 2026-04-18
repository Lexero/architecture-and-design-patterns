<?php

declare(strict_types=1);

namespace App\Collision\Command;

use App\Collision\Contract\CollisionDetectorInterface;
use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\SpaceObject\Command\MacroCommand;
use App\SpaceObject\Contract\CommandInterface;

class CollisionDetectionCommand implements CommandInterface
{
    public function __construct(
        private readonly GameObject $movingObject,
        private readonly NeighborhoodSystem $neighborhoodSystem,
        private readonly CollisionDetectorInterface $detector,
        private array &$collisionMacros,
    ) {
    }

    public function execute(): void
    {
        $this->neighborhoodSystem->updateObjectNeighborhood($this->movingObject);
        $neighbors = $this->neighborhoodSystem->getObjectsInSameNeighborhood($this->movingObject);

        $commands = [];
        foreach ($neighbors as $neighbor) {
            if ($neighbor->getId() === $this->movingObject->getId()) {
                continue;
            }
            $commands[] = new CheckCollisionCommand($this->movingObject, $neighbor, $this->detector);
        }

        $this->collisionMacros[$this->movingObject->getId()] = new MacroCommand($commands);
        $this->collisionMacros[$this->movingObject->getId()]->execute();
    }
}
