<?php

declare(strict_types=1);

namespace App\Tests\Game\Security;

use App\Auth\DTO\AuthRequest;
use App\Auth\Exception\JwtValidationException;
use App\Game\Model\GameRecord;
use App\Auth\Service\JwtService;
use App\Game\Security\JwtGuard;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JwtGuardTest extends TestCase
{
    private JwtService $jwtService;
    private JwtGuard $guard;
    private const SECRET = 'test-secret-key-that-is-long-enough-for-algorithm-hhhhhhh';

    protected function setUp(): void
    {
        $this->jwtService = new JwtService(self::SECRET);
        $this->guard = new JwtGuard($this->jwtService);
    }

    public function testExtractAndValidateSucceedsWithValidToken(): void
    {
        $token = $this->issueToken();
        $request = $this->makeRequest($token);
        $payload = $this->guard->extractAndValidate($request, 'game-1');

        self::assertSame('user-2', $payload->userId);
        self::assertSame('game-1', $payload->gameId);
    }

    public function testExtractAndValidateThrowsException(): void
    {
        $request = Request::create('/api/game/message', 'POST');

        $this->expectException(JwtValidationException::class);
        $this->expectExceptionMessage('Authorization header missing');

        $this->guard->extractAndValidate($request, 'game-1');
    }

    public function testExtractAndValidateThrowsExceptionWhenGameIdMismatch(): void
    {
        $token = $this->issueToken();
        $request = $this->makeRequest($token);

        $this->expectException(JwtValidationException::class);
        $this->expectExceptionMessage("does not match requested game 'game-2'");

        $this->guard->extractAndValidate($request, 'game-2');
    }

    public function testExtractAndValidateThrowsExceptionWhenTokenInvalid(): void
    {
        $request = $this->makeRequest('invalid.token.here');
        $this->expectException(JwtValidationException::class);
        $this->guard->extractAndValidate($request, 'game-1');
    }

    private function issueToken(): string
    {
        $request = new AuthRequest('game-1', 'user-2');
        $game = new GameRecord(
            'game-1',
            'organizer-1',
            ['user-2'],
            new DateTimeImmutable(),
        );

        return $this->jwtService->issueToken($request, $game)->token;
    }

    private function makeRequest(string $token): Request
    {
        $request = Request::create('/api/game/message', 'POST');
        $request->headers->set('Authorization', 'Bearer ' . $token);

        return $request;
    }
}
