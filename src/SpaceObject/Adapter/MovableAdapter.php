<?php

declare(strict_types=1);

namespace App\SpaceObject\Adapter;

use App\SpaceObject\Contract\MovableInterface;
use App\SpaceObject\Exception\DirectionReadException;
use App\SpaceObject\Exception\LocationReadException;
use App\SpaceObject\Exception\LocationWriteException;
use App\SpaceObject\Exception\VelocityReadException;
use App\SpaceObject\ValueObject\Point;
use Throwable;
class MovableAdapter
{
    public function __construct(
        private readonly MovableInterface $movable,
    ) {
    }

    public function move(): void
    {
        try {
            $location = $this->movable->getLocation();
        } catch (Throwable) {
            throw new LocationReadException();
        }

        try {
            $velocity = $this->movable->getVelocity();
        } catch (Throwable) {
            throw new VelocityReadException();
        }

        try {
            $angle = $this->movable->getDirection();
        } catch (Throwable) {
            throw new DirectionReadException();
        }

        $angleRadians = deg2rad($angle);

        $dx = (int)round($velocity * cos($angleRadians));
        $dy = (int)round($velocity * sin($angleRadians));

        $newLocation = new Point(
            $location->x + $dx,
            $location->y + $dy,
        );

        try {
            $this->movable->setLocation($newLocation);
        } catch (Throwable) {
            throw new LocationWriteException();
        }
    }
}
