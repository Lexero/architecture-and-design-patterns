<?php

declare(strict_types=1);

namespace App\Tests\Game\Domain;

use App\Collision\GameObject;
use App\Game\Entity\Fleet;
use App\Game\Entity\SpaceShip;
use App\SpaceObject\ValueObject\Point;
use PHPUnit\Framework\TestCase;

class FleetTest extends TestCase
{
    private function createShip(string $id): SpaceShip
    {
        return new SpaceShip(
            $id,
            'TEAM_A',
            new GameObject($id, new Point(0, 0)),
            new Point(0, 0),
            5,
            0,
            45,
            100,
            1,
        );
    }

    public function testIsDefeatedWhenAllShipsAreDead(): void
    {
        $ship1 = $this->createShip('ship-1');
        $ship2 = $this->createShip('ship-2');

        $fleet = new Fleet('TEAM_A', ['ship-1', 'ship-2']);
        $ships = ['ship-1' => $ship1, 'ship-2' => $ship2];

        $ship1->destroy();
        $ship2->destroy();

        self::assertTrue($fleet->isDefeated($ships));
    }

    public function testIsNotDefeatedWhenShipIsAlive(): void
    {
        $ship1 = $this->createShip('ship-1');
        $ship2 = $this->createShip('ship-2');
        $ship1->destroy();

        $fleet = new Fleet('TEAM_A', ['ship-1', 'ship-2']);
        $ships = ['ship-1' => $ship1, 'ship-2' => $ship2];

        $ship1->destroy();

        self::assertFalse($fleet->isDefeated($ships));
    }
}
