<?php

declare(strict_types=1);

namespace App\Game\Service;

use App\Game\Model\GameRecord;
use RuntimeException;

final class GameRegistry
{
    private array $games = [];

    public function addGame(GameRecord $game): void
    {
        $this->games[$game->gameId] = $game;
    }

    public function getGame(string $gameId): GameRecord
    {
        if (!isset($this->games[$gameId])) {
            throw new RuntimeException("Game '{$gameId}' not found");
        }

        return $this->games[$gameId];
    }

    public function hasGame(string $gameId): bool
    {
        return isset($this->games[$gameId]);
    }
}
