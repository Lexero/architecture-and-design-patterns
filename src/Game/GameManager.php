<?php

declare(strict_types=1);

namespace App\Game;

use App\Game\Exception\GameNotFoundException;

final class GameManager
{
    private array $games = [];

    public function addGame(Game $game): void
    {
        $this->games[$game->getId()] = $game;
    }

    public function getGame(string $gameId): Game
    {
        if (!isset($this->games[$gameId])) {
            throw new GameNotFoundException("Game with ID '{$gameId}' not found");
        }

        return $this->games[$gameId];
    }

    public function hasGame(string $gameId): bool
    {
        return isset($this->games[$gameId]);
    }

    public function removeGame(string $gameId): void
    {
        unset($this->games[$gameId]);
    }
}
