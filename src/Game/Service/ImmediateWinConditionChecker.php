<?php

declare(strict_types=1);

namespace App\Game\Service;

use App\Game\Entity\Fleet;

final class ImmediateWinConditionChecker implements WinConditionCheckerInterface
{
    public function check(Fleet $fleetA, Fleet $fleetB, array $ships): ?string
    {
        if ($fleetA->isDefeated($ships)) {
            return $fleetB->team;
        }

        if ($fleetB->isDefeated($ships)) {
            return $fleetA->team;
        }

        return null;
    }
}
