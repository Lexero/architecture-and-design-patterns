<?php

declare(strict_types=1);

namespace App\Game\Service;

use App\Game\Entity\Fleet;

interface WinConditionCheckerInterface
{
    public function check(Fleet $fleetA, Fleet $fleetB, array $ships): ?string;
}
