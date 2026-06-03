<?php

declare(strict_types=1);

namespace App\Game\Controller;

use App\Auth\Exception\JwtValidationException;
use App\Game\Entity\Rocket;
use App\Game\Entity\SpaceShip;
use App\Game\Exception\GameNotFoundException;
use App\Game\GameManager;
use App\Game\Security\JwtGuard;
use App\IoC\IoC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class GameStateController extends AbstractController
{
    public function __construct(
        private readonly GameManager $gameManager,
        private readonly JwtGuard $jwtGuard,
    ) {
    }

    #[Route('/api/games/{gameId}/state', methods: ['GET'])]
    public function getState(string $gameId, Request $request): JsonResponse
    {
        try {
            $this->jwtGuard->extractAndValidate($request, $gameId);
        } catch (JwtValidationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->gameManager->getGame($gameId);
        } catch (GameNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        try {
            IoC::resolve('Scopes.Current', $gameId)->execute();

            /** @var array<string, SpaceShip> $ships */
            $ships = IoC::resolve('Game.Ships');
            /** @var array<string, Rocket> $rockets */
            $rockets = IoC::resolve('Game.Rockets');
            $winner = IoC::resolve('Game.Winner');
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } finally {
            try {
                IoC::resolve('Scopes.Current', 'root')->execute();
            } catch (Throwable) {
            }
        }

        $shipsData = array_map(
            static fn(SpaceShip $ship) => [
                'shipId'    => $ship->getShipId(),
                'team'      => $ship->getTeam(),
                'isAlive'   => $ship->isAlive(),
                'position'  => ['x' => $ship->getLocation()->x, 'y' => $ship->getLocation()->y],
                'direction' => $ship->getDirection(),
                'velocity'  => $ship->getVelocity(),
            ],
            $ships,
        );

        $rocketsData = array_values(array_map(
            static fn(Rocket $rocket) => [
                'rocketId' => $rocket->getRocketId(),
                'position' => ['x' => $rocket->getLocation()->x, 'y' => $rocket->getLocation()->y],
            ],
            array_filter($rockets, static fn(Rocket $r) => $r->isActive()),
        ));

        return new JsonResponse([
            'gameId'    => $gameId,
            'winner'    => $winner,
            'status'    => $winner !== null ? 'finished' : 'in_progress',
            'ships'     => array_values($shipsData),
            'rockets'   => $rocketsData,
            'timestamp' => time(),
        ]);
    }
}
