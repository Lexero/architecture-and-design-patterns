<?php

declare(strict_types=1);

namespace App\Tests\AdapterGenerator;

use App\SpaceObject\ValueObject\Point;

interface TestMovableInterface
{
    public function getPosition(): Point;

    public function setPosition(Point $newValue): void;

    public function getVelocity(): int;

    public function finish(): void;
}
