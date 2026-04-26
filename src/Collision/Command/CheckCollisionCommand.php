<?php

declare(strict_types=1);

namespace App\Collision\Command;

use App\Collision\Contract\CollisionDetectorInterface;
use App\Collision\GameObject;
use App\SpaceObject\Contract\CommandInterface;

class CheckCollisionCommand implements CommandInterface
{
    public function __construct(
        private readonly GameObject $object1,
        private readonly GameObject $object2,
        private readonly CollisionDetectorInterface $detector,
    ) {
    }

    public function execute(): void
    {
        $this->detector->checkCollision($this->object1, $this->object2);
    }
}
