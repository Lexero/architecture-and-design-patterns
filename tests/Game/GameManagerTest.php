<?php

declare(strict_types=1);

namespace App\Tests\Game;

use App\Game\Exception\GameNotFoundException;
use App\Game\Game;
use App\Game\GameManager;
use App\ServerThread\Queue\ThreadSafeQueue;
use App\ServerThread\ServerThread;
use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    private GameManager $gameManager;

    protected function setUp(): void
    {
        $this->gameManager = new GameManager();
    }

    public function testAddGameAndGetGame(): void
    {
        $game = $this->createGame('game-1');

        $this->gameManager->addGame($game);

        $retrievedGame = $this->gameManager->getGame('game-1');
        self::assertSame($game, $retrievedGame);
        self::assertTrue($this->gameManager->hasGame('game-1'));
    }

    public function testHasGameReturnsFalseWhenGameDoesNotExist(): void
    {
        self::assertFalse($this->gameManager->hasGame('non-existent'));
    }

    public function testGetGameThrowsExceptionWhenGameNotFound(): void
    {
        $this->expectException(GameNotFoundException::class);
        $this->expectExceptionMessage("Game with ID 'non-existent' not found");

        $this->gameManager->getGame('non-existent');
    }

    public function testRemoveGame(): void
    {
        $game = $this->createGame('game-1');
        $this->gameManager->addGame($game);

        $this->gameManager->removeGame('game-1');

        self::assertFalse($this->gameManager->hasGame('game-1'));
    }

    private function createGame(string $id): Game
    {
        $queue = new ThreadSafeQueue();
        $thread = new ServerThread($queue);
        return new Game($id, "scope.{$id}", $thread);
    }
}
