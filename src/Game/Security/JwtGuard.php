<?php

declare(strict_types=1);

namespace App\Game\Security;

use App\Auth\DTO\JwtPayload;
use App\Auth\Exception\JwtValidationException;
use App\Auth\Service\JwtService;
use Symfony\Component\HttpFoundation\Request;

final class JwtGuard
{
    public function __construct(
        private readonly JwtService $jwtService,
    ) {
    }

    public function extractAndValidate(Request $request, string $expectedGameId): JwtPayload
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            throw new JwtValidationException('Authorization header missing');
        }

        $payload = $this->jwtService->validateToken($token);

        if ($payload->gameId !== $expectedGameId) {
            throw new JwtValidationException(
                "Token game_id '{$payload->gameId}' does not match requested game '{$expectedGameId}'"
            );
        }

        return $payload;
    }

    private function extractBearerToken(Request $request): ?string
    {
        $authorization = $request->headers->get('Authorization', '');

        if (!str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        $token = substr($authorization, 7);

        return $token !== '' ? $token : null;
    }
}
