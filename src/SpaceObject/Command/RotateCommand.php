<?php

declare(strict_types=1);

namespace App\SpaceObject\Command;

use App\SpaceObject\Adapter\RotatableAdapter;
use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Contract\RotatableInterface;

class RotateCommand implements CommandInterface
{
    public function __construct(
        private readonly RotatableInterface $rotatable,
    ) {
    }

    public function execute(): void
    {
        $adapter = new RotatableAdapter($this->rotatable);
        $adapter->rotate();
    }
}
