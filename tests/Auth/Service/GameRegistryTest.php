<?php

declare(strict_types=1);

namespace App\Tests\Auth\Service;

use App\Game\Model\GameRecord;
use App\Game\Service\GameRegistry;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GameRegistryTest extends TestCase
{
    private GameRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new GameRegistry();
    }

    public function testAddAndGetGame(): void
    {
        $game = new GameRecord(
            'game-1',
            'organizer-1',
            ['user-1', 'user-2'],
            new DateTimeImmutable(),
        );;
        $this->registry->addGame($game);

        self::assertSame($game, $this->registry->getGame('game-1'));
        self::assertTrue($this->registry->hasGame('game-1'));
    }

    public function testHasGameReturnsFalseForMissingGame(): void
    {
        self::assertFalse($this->registry->hasGame('unknown'));
    }

    public function testGetGameThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Game 'unknown' not found");

        $this->registry->getGame('unknown');
    }
}
