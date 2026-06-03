<?php

declare(strict_types=1);

namespace App\Game\Entity;

final readonly class Fleet
{
    public function __construct(
        public string $team,
        /** @param string[] $shipIds */
        public array $shipIds,
    ) {
    }

    public function isDefeated(array $ships): bool
    {
        foreach ($this->shipIds as $shipId) {
            if (isset($ships[$shipId]) && $ships[$shipId]->isAlive()) {
                return false;
            }
        }

        return true;
    }
}
