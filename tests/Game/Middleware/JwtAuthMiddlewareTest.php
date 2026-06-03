<?php

declare(strict_types=1);

namespace App\Tests\Game\Middleware;

use App\Auth\DTO\JwtPayload;
use App\Auth\Exception\JwtValidationException;
use App\Game\Security\JwtAuthMiddleware;
use App\Game\Security\JwtGuardInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class JwtAuthMiddlewareTest extends TestCase
{
    private JwtGuardInterface $jwtGuard;
    private JwtAuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->jwtGuard = $this->createMock(JwtGuardInterface::class);
        $this->middleware = new JwtAuthMiddleware($this->jwtGuard);
    }

    private function makeEvent(string $path, bool $mainRequest = true): RequestEvent
    {
        $request = Request::create($path, 'POST');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $type = $mainRequest
            ? HttpKernelInterface::MAIN_REQUEST
            : HttpKernelInterface::SUB_REQUEST;

        return new RequestEvent($kernel, $request, $type);
    }

    public function testPassesUnprotectedRouteWithoutToken(): void
    {
        $this->jwtGuard->expects(self::never())->method('extractAndValidate');

        $event = $this->makeEvent('/api/games');
        ($this->middleware)($event);

        self::assertNull($event->getResponse());
    }

    public function testPassesSubRequestWithoutToken(): void
    {
        $this->jwtGuard->expects(self::never())->method('extractAndValidate');

        $event = $this->makeEvent('/api/games/game-1/command', mainRequest: false);
        ($this->middleware)($event);

        self::assertNull($event->getResponse());
    }

    public function testSetsPayloadAttributeOnValidToken(): void
    {
        $payload = new JwtPayload(
            'user-1',
            'game-1',
            'org-1',
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 day')
        );

        $this->jwtGuard
            ->expects(self::once())
            ->method('extractAndValidate')
            ->willReturn($payload);

        $event = $this->makeEvent('/api/games/game-1/command');
        ($this->middleware)($event);

        self::assertNull($event->getResponse());
        self::assertSame($payload, $event->getRequest()->attributes->get('jwt_payload'));
        self::assertSame('game-1', $event->getRequest()->attributes->get('jwt_game_id'));
    }

    public function testTokenInvalid(): void
    {
        $this->jwtGuard
            ->method('extractAndValidate')
            ->willThrowException(new JwtValidationException('Authorization header missing'));

        $event = $this->makeEvent('/api/games/game-1/command');
        ($this->middleware)($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(Response::HTTP_UNAUTHORIZED, $event->getResponse()->getStatusCode());
    }

    public function testGameIdMismatch(): void
    {
        $this->jwtGuard
            ->method('extractAndValidate')
            ->willThrowException(new JwtValidationException("Token game_id 'other' does not match"));

        $event = $this->makeEvent('/api/games/game-1/command');
        ($this->middleware)($event);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $event->getResponse()->getStatusCode());
    }

    public function testProtectsStateRoute(): void
    {
        $payload = new JwtPayload(
            'user-1',
            'game-42',
            'org-1',
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 day')
        );

        $this->jwtGuard
            ->expects(self::once())
            ->method('extractAndValidate')
            ->willReturn($payload);

        $event = $this->makeEvent('/api/games/game-42/state');
        ($this->middleware)($event);

        self::assertNull($event->getResponse());
        self::assertSame($payload, $event->getRequest()->attributes->get('jwt_payload'));
    }
}
