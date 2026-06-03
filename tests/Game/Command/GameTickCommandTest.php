<?php

declare(strict_types=1);

namespace App\Tests\Game\Command;

use App\Collision\GameObject;
use App\Collision\NeighborhoodSystem;
use App\Game\Command\GameTickCommand;
use App\Game\Entity\Fleet;
use App\Game\Entity\Rocket;
use App\Game\Entity\SpaceShip;
use App\Game\Service\WinConditionCheckerInterface;
use App\IoC\IoC;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class GameTickCommandTest extends TestCase
{
    private array $ships;
    private array $rockets;
    private NeighborhoodSystem $neighborhood;
    private Fleet $fleetA;
    private Fleet $fleetB;
    private ThreadSafeQueue $queue;
    private string $gameId;

    protected function setUp(): void
    {
        IoC::reset();
        IoC::resolve('Scopes.New', 'game-test')->execute();
        IoC::resolve('Scopes.Current', 'game-test')->execute();

        $winner = null;
        IoC::resolve('IoC.Register', 'Game.Winner.Set', static function (string $team) use (&$winner) {
            return new class ($winner, $team) {
                public function __construct(private mixed &$winnerRef, private readonly string $team) {}
                public function execute(): void { $this->winnerRef = $this->team; }
            };
        })->execute();

        $this->neighborhood = new NeighborhoodSystem(gridSize: 50);
        $this->ships = [];
        $this->rockets = [];
        $this->fleetA = new Fleet('TEAM_A', ['ship-A-1', 'ship-A-2']);
        $this->fleetB = new Fleet('TEAM_B', ['ship-B-1', 'ship-B-2']);
        $this->queue = new ThreadSafeQueue();
        $this->gameId = 'game-test';
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    private function createShip(string $id, string $team, Point $pos): SpaceShip
    {
        $go = new GameObject($id, $pos);
        $ship = new SpaceShip(
            $id,
            $team,
            $go,
            $pos,
            10,
            0,
            45,
            100,
            1,
        );
        $this->neighborhood->addObject($go);

        return $ship;
    }

    private function createTick(?WinConditionCheckerInterface $winChecker = null): GameTickCommand
    {
        if ($winChecker === null) {
            $winChecker = $this->createMock(WinConditionCheckerInterface::class);
            $winChecker->method('check')->willReturn(null);
        }

        return new GameTickCommand(
            $this->ships,
            $this->rockets,
            $this->neighborhood,
            $this->fleetA,
            $this->fleetB,
            $winChecker,
            $this->gameId,
            $this->queue,
        );
    }

    public function testTickMovesLivingShips(): void
    {
        $ship = $this->createShip('ship-A-1', 'TEAM_A', new Point(0, 0));
        $this->ships['ship-A-1'] = $ship;

        $tick = $this->createTick();
        $tick->execute();

        self::assertSame(10, $ship->getLocation()->x);
        self::assertSame(0, $ship->getLocation()->y);
    }

    public function testTickDoesNotMoveDeadShips(): void
    {
        $ship = $this->createShip('ship-A-1', 'TEAM_A', new Point(0, 0));
        $ship->destroy();
        $this->ships['ship-A-1'] = $ship;

        $tick = $this->createTick();
        $tick->execute();
        self::assertSame(0, $ship->getLocation()->x);
        self::assertSame(0, $ship->getLocation()->y);
    }

    public function testTickQueueItself(): void
    {
        $this->ships['ship-A-1'] = $this->createShip('ship-A-1', 'TEAM_A', new Point(0, 0));
        $this->ships['ship-B-1'] = $this->createShip('ship-B-1', 'TEAM_B', new Point(500, 0));

        $tick = $this->createTick();
        $tick->execute();
        self::assertSame(1, $this->queue->count());
    }

    public function testTickStopsWhenWinnerFound(): void
    {
        $ship = $this->createShip('ship-A-1', 'TEAM_A', new Point(0, 0));
        $this->ships['ship-A-1'] = $ship;

        $winChecker = $this->createMock(WinConditionCheckerInterface::class);
        $winChecker->method('check')->willReturn('TEAM_B');

        $tick = $this->createTick($winChecker);
        $tick->execute();
        self::assertSame(0, $this->queue->count());
    }

    public function testRocketDestroyedShip(): void
    {
        $ship = $this->createShip('ship-B-1', 'TEAM_B', new Point(10, 10));
        $this->ships['ship-B-1'] = $ship;

        $rocketGo = new GameObject('rocket-1', new Point(10, 10));
        $rocket = new Rocket('rocket-1', $rocketGo, new Point(10, 10), 0, 0);
        $this->rockets['rocket-1'] = $rocket;
        $this->neighborhood->addObject($rocketGo);

        $tick = $this->createTick();
        $tick->execute();
        self::assertFalse($ship->isAlive());
        self::assertFalse($rocket->isActive());
    }
}
