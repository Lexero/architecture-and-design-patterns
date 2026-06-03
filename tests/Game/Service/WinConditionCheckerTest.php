<?php

declare(strict_types=1);

namespace App\Tests\Game\Service;

use App\Collision\GameObject;
use App\Game\Entity\Fleet;
use App\Game\Entity\SpaceShip;
use App\Game\Service\ImmediateWinConditionChecker;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class WinConditionCheckerTest extends TestCase
{
    private ImmediateWinConditionChecker $checker;
    private Fleet $fleetA;
    private Fleet $fleetB;

    protected function setUp(): void
    {
        $this->checker = new ImmediateWinConditionChecker();
        $this->fleetA = new Fleet('TEAM_A', ['ship-A-1', 'ship-A-2']);
        $this->fleetB = new Fleet('TEAM_B', ['ship-B-1', 'ship-B-2']);
    }

    private function createShip(string $id, string $team): SpaceShip
    {
        return new SpaceShip(
            $id,
            $team,
            new GameObject($id, new Point(0, 0)),
            new Point(0, 0),
            5,
            0,
            45,
            100,
            1,
        );
    }

    public function testNoWinnerWhenBothFleetsHaveLivingShips(): void
    {
        $ships = [
            'ship-A-1' => $this->createShip('ship-A-1', 'TEAM_A'),
            'ship-A-2' => $this->createShip('ship-A-2', 'TEAM_A'),
            'ship-B-1' => $this->createShip('ship-B-1', 'TEAM_B'),
            'ship-B-2' => $this->createShip('ship-B-2', 'TEAM_B'),
        ];

        self::assertNull($this->checker->check($this->fleetA, $this->fleetB, $ships));
    }

    public function testTeamBWinsWhenAllTeamAShipsAreDead(): void
    {
        $shipA1 = $this->createShip('ship-A-1', 'TEAM_A');
        $shipA2 = $this->createShip('ship-A-2', 'TEAM_A');
        $shipA1->destroy();
        $shipA2->destroy();

        $ships = [
            'ship-A-1' => $shipA1,
            'ship-A-2' => $shipA2,
            'ship-B-1' => $this->createShip('ship-B-1', 'TEAM_B'),
            'ship-B-2' => $this->createShip('ship-B-2', 'TEAM_B'),
        ];

        self::assertSame('TEAM_B', $this->checker->check($this->fleetA, $this->fleetB, $ships));
    }
}
