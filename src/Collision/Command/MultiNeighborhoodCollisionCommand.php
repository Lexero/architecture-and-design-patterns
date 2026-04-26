<?php

declare(strict_types=1);

namespace App\Collision\Command;

use App\Collision\Contract\CollisionDetectorInterface;
use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\SpaceObject\Command\MacroCommand;
use App\SpaceObject\Contract\CommandInterface;

class MultiNeighborhoodCollisionCommand implements CommandInterface
{
    /** @var array<string, MacroCommand> */
    private array $collisionMacros = [];

    /** @param list<NeighborhoodSystem> $neighborhoodSystems */
    public function __construct(
        private readonly GameObject $movingObject,
        private readonly array $neighborhoodSystems,
        private readonly CollisionDetectorInterface $detector,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->neighborhoodSystems as $system) {
            $command = new CollisionDetectionCommand(
                $this->movingObject,
                $system,
                $this->detector,
                $this->collisionMacros,
            );
            $command->execute();
        }
    }
}
