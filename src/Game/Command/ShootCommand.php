<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\Game\Entity\Rocket;
use App\Game\Entity\SpaceShip;
use App\SpaceObject\Contract\CommandInterface;

final class ShootCommand implements CommandInterface
{
    public function __construct(
        private readonly SpaceShip $ship,
        /** @param array<string, Rocket> $rockets */
        private array &$rockets,
        private readonly NeighborhoodSystem $neighborhood,
    ) {
    }

    public function execute(): void
    {
        if (!$this->ship->isAlive()) {
            return;
        }

        $rocketId = 'rocket-' . uniqid('', true);
        $startPosition = $this->ship->getLocation();

        $velocity = $this->ship->getVelocity() + 5;
        $direction = $this->ship->getDirection();

        $gameObject = new GameObject($rocketId, $startPosition);
        $rocket = new Rocket($rocketId, $gameObject, $startPosition, $velocity, $direction);

        $this->rockets[$rocketId] = $rocket;
        $this->neighborhood->addObject($gameObject);
    }
}
