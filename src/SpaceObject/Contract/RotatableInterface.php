<?php

declare(strict_types=1);

namespace App\SpaceObject\Contract;

interface RotatableInterface
{
    public function getDirection(): int;

    public function getAngularVelocity(): int;

    public function setDirection(int $direction): void;
}
