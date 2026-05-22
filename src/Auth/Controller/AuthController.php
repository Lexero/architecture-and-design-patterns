<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\DTO\AuthRequest;
use App\Game\Service\GameRegistry;
use App\Auth\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly GameRegistry $gameRegistry,
        private readonly JwtService $jwtService,
    ) {
    }

    #[Route('/api/auth/token', methods: ['POST'])]
    public function issueToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (empty($data['gameId'])) {
                return new JsonResponse(['error' => 'gameId is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($data['userId'])) {
                return new JsonResponse(['error' => 'userId is required'], Response::HTTP_BAD_REQUEST);
            }

            $authRequest = AuthRequest::fromArray($data);

            if (!$this->gameRegistry->hasGame($authRequest->gameId)) {
                return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
            }

            $game = $this->gameRegistry->getGame($authRequest->gameId);

            if (!$game->isParticipant($authRequest->userId)) {
                return new JsonResponse(['error' => 'User is not a participant of this game'], Response::HTTP_FORBIDDEN);
            }

            $authResponse = $this->jwtService->issueToken($authRequest, $game);

            return new JsonResponse($authResponse->toArray(), Response::HTTP_OK);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
