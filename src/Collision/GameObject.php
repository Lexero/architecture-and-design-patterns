<?php

declare(strict_types=1);

namespace App\Collision;

use App\SpaceObject\ValueObject\Point;

class GameObject
{
    public function __construct(
        private readonly string $id,
        private Point $position,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function setPosition(Point $position): void
    {
        $this->position = $position;
    }
}
