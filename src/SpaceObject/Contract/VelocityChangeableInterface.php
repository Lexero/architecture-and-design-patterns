<?php

declare(strict_types=1);

namespace App\SpaceObject\Contract;

interface VelocityChangeableInterface
{
    public function getDirection(): int;

    public function getVelocity(): int;

    public function setVelocity(int $velocity): void;

    public function setVelocityDirection(int $direction): void;
}
