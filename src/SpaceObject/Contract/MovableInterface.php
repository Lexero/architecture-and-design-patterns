<?php

declare(strict_types=1);

namespace App\SpaceObject\Contract;

use App\SpaceObject\ValueObject\Point;

interface MovableInterface
{
    public function getLocation(): Point;

    public function getVelocity(): int;

    public function getDirection(): int;

    public function setLocation(Point $location): void;
}
