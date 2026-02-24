<?php

declare(strict_types=1);

namespace App\SpaceObject\Adapter;

use App\SpaceObject\Contract\RotatableInterface;
use App\SpaceObject\Exception\AngularVelocityReadException;
use App\SpaceObject\Exception\DirectionReadException;
use App\SpaceObject\Exception\DirectionWriteException;
use Throwable;

class RotatableAdapter
{
    public function __construct(
        private readonly RotatableInterface $rotatable,
    ) {
    }

    public function rotate(): void
    {
        try {
            $angle = $this->rotatable->getDirection();
        } catch (Throwable) {
            throw new DirectionReadException();
        }

        try {
            $angularVelocity = $this->rotatable->getAngularVelocity();
        } catch (Throwable) {
            throw new AngularVelocityReadException();
        }

        // Нормализацией угла в диапазон 0-359 градусов
        $newAngle = $angle + $angularVelocity;
        $newAngle = $newAngle % 360;
        if ($newAngle < 0) {
            $newAngle += 360;
        }

        try {
            $this->rotatable->setDirection($newAngle);
        } catch (Throwable) {
            throw new DirectionWriteException();
        }
    }
}
