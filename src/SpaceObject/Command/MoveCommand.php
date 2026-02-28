<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Adapter\MovableAdapter;
use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\MovableInterface;

class MoveCommand implements CommandInterface
{
    public function __construct(
        private readonly MovableInterface $movable,
    ) {
    }

    public function execute(): void
    {
        $adapter = new MovableAdapter($this->movable);
        $adapter->move();
    }
}
