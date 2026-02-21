<?php

declare(strict_types=1);

namespace App\SpaceObject\ValueObject;

readonly class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }
}
