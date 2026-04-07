<?php

declare(strict_types=1);

namespace App\Game\Command;

use App\IoC\IoC;
use App\Game\DTO\GameState;
use App\SpaceObject\Contract\CommandInterface;
use ReflectionObject;

final class SendGameStateCommand implements CommandInterface
{
    public function __construct(
        private readonly string $gameId,
    ) {
    }

    public function execute(): void
    {
        $gameObjects = IoC::resolve('Game.Objects.All');

        if (!is_array($gameObjects)) {
            $gameObjects = [];
        }

        $serializedObjects = [];
        foreach ($gameObjects as $objectId => $object) {
            $serializedObjects[$objectId] = $this->serializeObject($object);
        }

        $gameState = new GameState(
            $this->gameId,
            $serializedObjects,
            time(),
        );

        $sender = IoC::resolve('Game.MessageSender');

        if (is_callable($sender)) {
            $sender($gameState->toJson());
        }
    }

    private function serializeObject(mixed $object): array
    {
        if (is_object($object)) {
            $reflection = new ReflectionObject($object);
            $data = [];

            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($object);

                if (is_scalar($value) || is_null($value)) {
                    $data[$property->getName()] = $value;
                } elseif (is_array($value)) {
                    $data[$property->getName()] = $value;
                } elseif (is_object($value) && method_exists($value, '__toString')) {
                    $data[$property->getName()] = (string) $value;
                }
            }

            return $data;
        }

        return [];
    }
}
