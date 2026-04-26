<?php

declare(strict_types=1);

namespace App\Tests\Auth\Service;

use App\Auth\DTO\AuthRequest;
use App\Auth\Exception\JwtValidationException;
use App\Game\Model\GameRecord;
use App\Auth\Service\JwtService;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

final class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;
    private const SECRET = 'test-secret-key-that-is-long-enough-for-algorithm-hhhhhhh';

    protected function setUp(): void
    {
        $this->jwtService = new JwtService(self::SECRET);
    }

    public function testReturnsTokenWithCorrectFields(): void
    {
        $request = new AuthRequest('game-1', 'user-2');
        $game = $this->makeGame();

        $response = $this->jwtService->issueToken($request, $game);

        self::assertSame('game-1', $response->gameId);
        self::assertSame('user-2', $response->userId);
        self::assertNotEmpty($response->token);
        self::assertGreaterThan(new DateTimeImmutable(), $response->expiresAt);
    }

    public function testValidateTokenReturnsCorrectPayload(): void
    {
        $request = new AuthRequest('game-1', 'user-2');
        $game = $this->makeGame();

        $response = $this->jwtService->issueToken($request, $game);
        $payload = $this->jwtService->validateToken($response->token);

        self::assertSame('user-2', $payload->userId);
        self::assertSame('game-1', $payload->gameId);
        self::assertSame('user-1', $payload->organizerId);
    }

    public function testValidateTokenThrowsExceptionOnInvalidSignature(): void
    {
        $otherService = new JwtService('other-secret-key-that-is-long-enough-for-algorithm-hhhhhhh');
        $request = new AuthRequest('game-1', 'user-2');
        $game = $this->makeGame();

        $response = $otherService->issueToken($request, $game);

        $this->expectException(JwtValidationException::class);
        $this->jwtService->validateToken($response->token);
    }

    public function testValidateTokenThrowsExceptionOnExpiredToken(): void
    {
        $payload = [
            'iss' => 'space-battle-auth',
            'iat' => time() - 7200,
            'exp' => time() - 3600,
            'sub' => 'user-2',
            'game_id' => 'game-1',
            'organizer_id' => 'user-1',
        ];
        $token = JWT::encode($payload, self::SECRET, 'HS256');

        $this->expectException(JwtValidationException::class);
        $this->jwtService->validateToken($token);
    }

    public function testValidateTokenThrowsExceptionOnBrokenToken(): void
    {
        $this->expectException(JwtValidationException::class);
        $this->jwtService->validateToken('not.a.jwt');
    }

    private function makeGame(): GameRecord
    {
        $participants = ['user-1', 'user-2'];
        return new GameRecord(
            'game-1',
            'user-1',
            $participants,
             new DateTimeImmutable(),
        );
    }
}
