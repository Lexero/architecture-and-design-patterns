<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\DTO\AuthRequest;
use App\Auth\DTO\AuthResponse;
use App\Auth\DTO\JwtPayload;
use App\Auth\Exception\JwtValidationException;
use App\Game\Model\GameRecord;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

final class JwtService
{
    private const ALGORITHM = 'HS256';
    private const EXPIRATION_HOURS = 24;
    private const ISSUER = 'space-battle-auth';

    public function __construct(
        private readonly string $secret,
    ) {
    }

    public function issueToken(AuthRequest $request, GameRecord $game): AuthResponse
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify(sprintf('+%d hours', self::EXPIRATION_HOURS));

        $payload = [
            'iss' => self::ISSUER,
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'sub' => $request->userId,
            'game_id' => $request->gameId,
            'organizer_id' => $game->organizerId,
        ];

        $token = JWT::encode($payload, $this->secret, self::ALGORITHM);

        return new AuthResponse(
            $token,
            $expiresAt,
            $request->gameId,
            $request->userId,
        );
    }

    public function validateToken(string $token): JwtPayload
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));

            $userId = $decoded->sub ?? null;
            $gameId = $decoded->game_id ?? null;
            $organizerId = $decoded->organizer_id ?? null;

            if ($userId === null || $gameId === null || $organizerId === null) {
                throw new JwtValidationException('Invalid token payload: missing required fields');
            }

            return new JwtPayload(
                $userId,
                $gameId,
                $organizerId,
                new DateTimeImmutable('@' . $decoded->iat),
                new DateTimeImmutable('@' . $decoded->exp),
            );
        } catch (JwtValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new JwtValidationException('Token validation failed: ' . $e->getMessage(), $e);
        }
    }
}
