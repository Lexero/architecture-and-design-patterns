<?php

declare(strict_types=1);

namespace App\Game\Controller;

use App\Game\Endpoint\MessageEndpoint;
use App\Game\Exception\GameNotFoundException;
use App\Game\GameManager;
use App\Game\Security\OperationWhitelist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class MessageController extends AbstractController
{
    public function __construct(
        private readonly GameManager $gameManager,
        private readonly OperationWhitelist $whitelist,
    ) {
    }

    #[Route('/api/game/message', name: 'game_message', methods: ['POST'])]
    public function receiveMessage(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();

            if (empty($content)) {
                return new JsonResponse(
                    ['error' => 'Empty request body'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $endpoint = new MessageEndpoint($this->gameManager, $this->whitelist);
            $endpoint->handleJsonMessage($content);

            return new JsonResponse(
                ['status' => 'success', 'message' => 'Command queued'],
                Response::HTTP_OK
            );
        } catch (GameNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Game not found', 'details' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (Throwable $e) {
            return new JsonResponse(
                ['error' => 'Internal server error', 'details' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
