<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\VelocityChangeableInterface;

class ChangeVelocityCommand implements CommandInterface
{
    public function __construct(
        private readonly VelocityChangeableInterface $object,
    ) {
    }

    public function execute(): void
    {
        $currentVelocity = $this->object->getVelocity();

        if ($currentVelocity === 0) {
            return;
        }

        $currentDirection = $this->object->getDirection();
        $this->object->setVelocityDirection($currentDirection);
    }
}
