<?php

declare(strict_types=1);

namespace App\Game\Controller;

use App\Auth\Exception\JwtValidationException;
use App\Game\Endpoint\MessageEndpoint;
use App\Game\Exception\GameNotFoundException;
use App\Game\GameManager;
use App\Game\Security\JwtGuard;
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
        private readonly JwtGuard $jwtGuard,
    ) {
    }

    #[Route('/api/game/message', methods: ['POST'])]
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

            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $gameId = $data['gameId'] ?? '';
            $this->jwtGuard->extractAndValidate($request, $gameId);

            $endpoint = new MessageEndpoint($this->gameManager, $this->whitelist);
            $endpoint->handleJsonMessage($content);

            return new JsonResponse(
                ['status' => 'success', 'message' => 'Command queued'],
                Response::HTTP_OK
            );
        } catch (JwtValidationException $e) {
            return new JsonResponse(
                ['error' => 'Unauthorized', 'details' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
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
