<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\Collision\NeighborhoodSystem;
use App\Game\Entity\Fleet;
use App\Game\Entity\Rocket;
use App\Game\Entity\SpaceShip;
use App\Game\Service\WinConditionCheckerInterface;
use App\IoC\IoC;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\SpaceObject\Command\MoveCommand;
use App\SpaceObject\Contract\CommandInterface;

final class GameTickCommand implements CommandInterface
{
    public function __construct(
        /** @param array<string, SpaceShip> $ships */
        private array &$ships,
        /** @param array<string, Rocket> $rockets */
        private array &$rockets,
        private readonly NeighborhoodSystem $neighborhood,
        private readonly Fleet $fleetA,
        private readonly Fleet $fleetB,
        private readonly WinConditionCheckerInterface $winChecker,
        private readonly string $gameId,
        private readonly ThreadSafeQueue $queue,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->ships as $ship) {
            if (!$ship->isAlive()) {
                continue;
            }

            new MoveCommand($ship)->execute();
        }

        foreach ($this->rockets as $rocket) {
            if (!$rocket->isActive()) {
                continue;
            }

            new MoveCommand($rocket)->execute();

            $this->neighborhood->updateObjectNeighborhood($rocket->getGameObject());
            $neighbors = $this->neighborhood->getObjectsInSameNeighborhood($rocket->getGameObject());

            foreach ($neighbors as $neighbor) {
                if ($neighbor->getId() === $rocket->getGameObject()->getId()) {
                    continue;
                }

                $shipId = $neighbor->getId();
                if (!isset($this->ships[$shipId])) {
                    continue;
                }

                $targetShip = $this->ships[$shipId];

                if (!$targetShip->isAlive()) {
                    continue;
                }

                $targetShip->destroy();
                $rocket->deactivate();
                $this->neighborhood->removeObject($rocket->getGameObject());
                break;
            }
        }

        $winner = $this->winChecker->check($this->fleetA, $this->fleetB, $this->ships);

        if ($winner !== null) {
            IoC::resolve('Scopes.Current', $this->gameId)->execute();
            IoC::resolve('Game.Winner.Set', $winner)->execute();

            return;
        }

        $this->queue->enqueue($this);
    }
}
