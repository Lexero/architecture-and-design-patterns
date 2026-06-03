<?php

declare(strict_types=1);

namespace App\Game\Security;

use App\Auth\Exception\JwtValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
final class JwtAuthMiddleware
{
    private const array PROTECTED_ROUTES = [
        '#^/api/games/[^/]+/command$#',
        '#^/api/games/[^/]+/state$#',
    ];

    public function __construct(
        private readonly JwtGuardInterface $jwtGuard,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!$this->isProtected($path)) {
            return;
        }

        $gameId = $this->extractGameId($path);

        try {
            $payload = $this->jwtGuard->extractAndValidate($request, $gameId);

            $request->attributes->set('jwt_payload', $payload);
            $request->attributes->set('jwt_game_id', $gameId);
        } catch (JwtValidationException $e) {
            $event->setResponse(
                new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED)
            );
        }
    }

    private function isProtected(string $path): bool
    {
        foreach (self::PROTECTED_ROUTES as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    private function extractGameId(string $path): string
    {
        $parts = explode('/', trim($path, '/'));

        return $parts[2] ?? '';
    }
}
