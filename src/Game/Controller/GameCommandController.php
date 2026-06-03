<?php

declare(strict_types=1);

namespace App\Game\Controller;

use App\Auth\DTO\JwtPayload;
use App\Game\Exception\GameNotFoundException;
use App\Game\Exception\OrderAccessDeniedException;
use App\Game\GameManager;
use App\Game\Command\OrderInterpreterCommand;
use App\Game\Object\UObjectImpl;
use App\IoC\IoC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class GameCommandController extends AbstractController
{
    public function __construct(
        private readonly GameManager $gameManager,
    ) {
    }

    #[Route('/api/games/{gameId}/command', methods: ['POST'])]
    public function sendCommand(string $gameId, Request $request): JsonResponse
    {
        /** @var JwtPayload $payload */
        $payload = $request->attributes->get('jwt_payload');

        try {
            $game = $this->gameManager->getGame($gameId);
        } catch (GameNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $type = $data['type'] ?? null;
        $shipId = $data['shipId'] ?? null;

        if ($type === null || $shipId === null) {
            return new JsonResponse(['error' => 'type and shipId are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            IoC::resolve('Scopes.Current', $gameId)->execute();

            $order = new UObjectImpl(['id' => $shipId, 'action' => $type]);
            $interpreterCmd = new OrderInterpreterCommand($order, $payload->userId);
            $interpreterCmd->execute();

            $fiber = $game->getThread()->getFiber();
            if ($fiber !== null && $fiber->isSuspended()) {
                $fiber->resume();
            }
        } catch (OrderAccessDeniedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } finally {
            try {
                IoC::resolve('Scopes.Current', 'root')->execute();
            } catch (Throwable) {
            }
        }

        return new JsonResponse(['status' => 'queued'], Response::HTTP_OK);
    }
}
