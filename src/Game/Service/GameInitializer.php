<?php

declare(strict_types=1);

namespace App\Game\Service;

use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\Game\Command\GameTickCommand;
use App\Game\Command\ShootCommand;
use App\Game\Entity\Fleet;
use App\Game\Entity\SpaceShip;
use App\Game\Game;
use App\Game\GameManager;
use App\IoC\IoC;
use App\IoC\Scope\ThreadLocalScopeStorage;
use App\SpaceObject\Contract\CommandInterface;
use RuntimeException;
use Throwable;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use App\SpaceObject\Command\MoveCommand;
use App\SpaceObject\Command\RotateCommand;
use App\SpaceObject\ValueObject\Point;

final class GameInitializer
{
    private const array TEAM_A_POSITIONS = [
        ['x' => 100, 'y' => 100],
        ['x' => 100, 'y' => 300],
        ['x' => 100, 'y' => 500],
    ];

    private const array TEAM_B_POSITIONS = [
        ['x' => 900, 'y' => 100],
        ['x' => 900, 'y' => 300],
        ['x' => 900, 'y' => 500],
    ];

    private const int DEFAULT_VELOCITY = 5;
    private const int DEFAULT_ANGULAR_VELOCITY = 45;
    private const int DEFAULT_FUEL = 100;
    private const int DEFAULT_FUEL_RATE = 1;

    private const int TEAM_A_DIRECTION = 0;
    private const int TEAM_B_DIRECTION = 180;
    private const int SHIPS_NUMBER = 3;

    public function __construct(
        private readonly GameManager $gameManager,
        private readonly CommandDefinitionRegistry $definitionRegistry,
        private readonly WinConditionCheckerInterface $winChecker,
    ) {
    }

    public function initialize(string $gameId, array $participantIds): Game
    {
        IoC::resolve('Scopes.New', $gameId)->execute();
        IoC::resolve('Scopes.Current', $gameId)->execute();

        $neighborhood = new NeighborhoodSystem(gridSize: 50);

        $ships = [];
        $teamAIds = [];
        $teamBIds = [];

        for ($i = 0; $i < self::SHIPS_NUMBER; $i++) {
            $shipA = $this->createShip(
                "ship-{$gameId}-A-{$i}",
                'TEAM_A',
                new Point(self::TEAM_A_POSITIONS[$i]['x'], self::TEAM_A_POSITIONS[$i]['y']),
                self::TEAM_A_DIRECTION,
                $neighborhood,
            );
            $shipB = $this->createShip(
                "ship-{$gameId}-B-{$i}",
                'TEAM_B',
                new Point(self::TEAM_B_POSITIONS[$i]['x'], self::TEAM_B_POSITIONS[$i]['y']),
                self::TEAM_B_DIRECTION,
                $neighborhood,
            );

            $ships[$shipA->getShipId()] = $shipA;
            $ships[$shipB->getShipId()] = $shipB;
            $teamAIds[] = $shipA->getShipId();
            $teamBIds[] = $shipB->getShipId();
        }

        $fleetA = new Fleet('TEAM_A', $teamAIds);
        $fleetB = new Fleet('TEAM_B', $teamBIds);

        $rockets = [];

        IoC::resolve('IoC.Register', 'Game.Ships', static fn() => $ships)->execute();
        IoC::resolve('IoC.Register', 'Game.Rockets', static function () use (&$rockets): array {
            return $rockets;
        })->execute();
        IoC::resolve('IoC.Register', 'Game.Fleets', static fn() => [$fleetA, $fleetB])->execute();
        IoC::resolve('IoC.Register', 'Game.NeighborhoodSystem', static fn() => $neighborhood)->execute();

        IoC::resolve('IoC.Register', 'Game.Objects', static function (string $id) use (&$ships): SpaceShip {
            if (!isset($ships[$id])) {
                throw new RuntimeException("Ship '{$id}' not found in game");
            }
            return $ships[$id];
        })->execute();

        IoC::resolve('IoC.Register', 'Game.Actions.move', static function (SpaceShip $ship) {
            return new MoveCommand($ship);
        })->execute();

        IoC::resolve('IoC.Register', 'Game.Actions.rotate', static function (SpaceShip $ship) {
            return new RotateCommand($ship);
        })->execute();

        IoC::resolve('IoC.Register', 'Game.Actions.shoot',
            static function (SpaceShip $ship) use (&$rockets, $neighborhood) {
                return new ShootCommand($ship, $rockets, $neighborhood);
            }
        )->execute();

        $winner = null;
        IoC::resolve('IoC.Register', 'Game.Winner.Set', static function (string $team) use (&$winner) {
            return new class ($winner, $team) {
                public function __construct(private mixed &$winnerRef, private readonly string $team)
                {
                }
                public function execute(): void
                {
                    $this->winnerRef = $this->team;
                }
            };
        })->execute();

        IoC::resolve('IoC.Register', 'Game.Winner', static fn() => $winner)->execute();

        $this->definitionRegistry->define('Ship.MoveWithFuel', ['CheckFuel', 'Move', 'BurnFuel']);
        $this->definitionRegistry->define('Ship.RotateWithVelocity', ['Rotate', 'ChangeVelocity']);


        $queue = new ThreadSafeQueue();

        IoC::resolve('IoC.Register', 'Game.Queue.Enqueue', static function ($command) use ($queue) {
            return new class ($queue, $command) implements CommandInterface {
                public function __construct(
                    private readonly ThreadSafeQueue $queue,
                    private readonly CommandInterface $cmd,
                ) {
                }
                public function execute(): void
                {
                    $this->queue->enqueue($this->cmd);
                }
            };
        })->execute();

        $tickCommand = new GameTickCommand(
            $ships,
            $rockets,
            $neighborhood,
            $fleetA,
            $fleetB,
            $this->winChecker,
            $gameId,
            $queue,
        );
        $queue->enqueue($tickCommand);

        if (isset($participantIds[0])) {
            $this->registerPlayerScope($participantIds[0], $gameId, $teamAIds, $ships);
        }
        if (isset($participantIds[1])) {
            $this->registerPlayerScope($participantIds[1], $gameId, $teamBIds, $ships);
        }

        IoC::resolve('Scopes.Current', $gameId)->execute();
        $scopeManager = IoC::resolve('IoC.ScopeManager');

        $thread = new ServerThread($queue);
        $thread->changeBehaviour(static function () use ($queue, $scopeManager): void {
            ThreadLocalScopeStorage::set($scopeManager);

            $command = $queue->take(100);
            if ($command === null) {
                return;
            }

            try {
                $command->execute();
            } catch (Throwable) {
            }
        });
        $thread->start();

        IoC::resolve('Scopes.Current', 'root')->execute();

        $game = new Game($gameId, $gameId, $thread);
        $this->gameManager->addGame($game);

        return $game;
    }

    private function createShip(
        string $shipId,
        string $team,
        Point $position,
        int $direction,
        NeighborhoodSystem $neighborhood,
    ): SpaceShip {
        $gameObject = new GameObject($shipId, $position);
        $ship = new SpaceShip(
            $shipId,
            $team,
            $gameObject,
            $position,
            self::DEFAULT_VELOCITY,
            $direction,
            self::DEFAULT_ANGULAR_VELOCITY,
            self::DEFAULT_FUEL,
            self::DEFAULT_FUEL_RATE,
        );

        $neighborhood->addObject($gameObject);

        return $ship;
    }

    private function registerPlayerScope(
        string $userId,
        string $gameId,
        array $shipIds,
        array &$ships,
    ): void {
        $playerScopeId = "player.{$userId}";
        IoC::resolve('Scopes.New', $playerScopeId, $gameId)->execute();
        IoC::resolve('Scopes.Current', $playerScopeId)->execute();

        $allowedShipIds = array_flip($shipIds);
        IoC::resolve('IoC.Register', 'Game.Objects', static function (string $id) use ($allowedShipIds, &$ships): SpaceShip {
            if (!isset($allowedShipIds[$id])) {
                throw new RuntimeException("Access denied: ship '{$id}' does not belong to this player");
            }
            if (!isset($ships[$id])) {
                throw new RuntimeException("Ship '{$id}' not found");
            }
            return $ships[$id];
        })->execute();
    }
}
