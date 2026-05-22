<?php

declare(strict_types=1);

namespace App\Game\Controller;

use App\Game\DTO\CreateGameRequest;
use App\Game\DTO\CreateGameResponse;
use App\Game\Model\GameRecord;
use App\Game\Service\GameRegistry;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class GameController extends AbstractController
{
    public function __construct(
        private readonly GameRegistry $gameRegistry,
    ) {
    }

    #[Route('/api/games', methods: ['POST'])]
    public function createGame(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (empty($data['organizerId'])) {
                return new JsonResponse(['error' => 'organizerId is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($data['participants']) || !is_array($data['participants'])) {
                return new JsonResponse(['error' => 'participants must be a non-empty array'], Response::HTTP_BAD_REQUEST);
            }

            $createRequest = CreateGameRequest::fromArray($data);

            $gameId = (string) Uuid::v4();
            $now = new DateTimeImmutable();

            $game = new GameRecord(
                $gameId,
                $createRequest->organizerId,
                $createRequest->participants,
                $now,
            );

            $this->gameRegistry->addGame($game);

            $response = new CreateGameResponse(
                $gameId,
                $now,
                $createRequest->participants,
            );

            return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
