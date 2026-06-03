<?php

declare(strict_types=1);

namespace App\Game\Entity;

use App\Collision\GameObject;
use App\Game\Object\UObject;
use App\SpaceObject\Contract\MovableInterface;
use App\SpaceObject\ValueObject\Point;
use RuntimeException;

final class Rocket implements UObject, MovableInterface
{
    private array $properties;

    public function __construct(
        private readonly string $rocketId,
        private readonly GameObject $gameObject,
        Point $position,
        int $velocity,
        int $direction,
    ) {
        $this->properties = [
            'position'  => $position,
            'velocity'  => $velocity,
            'direction' => $direction,
            'isActive'  => true,
        ];
    }

    public function getProperty(string $name): mixed
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new RuntimeException("Property '{$name}' not found on Rocket");
        }

        return $this->properties[$name];
    }

    public function setProperty(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    public function getLocation(): Point
    {
        return $this->properties['position'];
    }

    public function getVelocity(): int
    {
        return $this->properties['velocity'];
    }

    public function getDirection(): int
    {
        return $this->properties['direction'];
    }

    public function setLocation(Point $location): void
    {
        $this->properties['position'] = $location;
        $this->gameObject->setPosition($location);
    }

    public function getRocketId(): string
    {
        return $this->rocketId;
    }

    public function isActive(): bool
    {
        return (bool) $this->properties['isActive'];
    }

    public function deactivate(): void
    {
        $this->properties['isActive'] = false;
    }

    public function getGameObject(): GameObject
    {
        return $this->gameObject;
    }
}
