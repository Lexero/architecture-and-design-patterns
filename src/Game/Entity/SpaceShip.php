<?php

declare(strict_types=1);

namespace App\Game\Entity;

use App\Collision\GameObject;
use App\Game\Object\UObject;
use App\SpaceObject\Contract\FuelableInterface;
use App\SpaceObject\Contract\MovableInterface;
use App\SpaceObject\Contract\RotatableInterface;
use App\SpaceObject\ValueObject\Point;
use RuntimeException;

final class SpaceShip implements UObject, MovableInterface, RotatableInterface, FuelableInterface
{
    private array $properties;

    public function __construct(
        private readonly string $shipId,
        private readonly string $team,
        private readonly GameObject $gameObject,
        Point $position,
        int $velocity,
        int $direction,
        int $angularVelocity,
        int $fuelLevel,
        int $fuelConsumptionRate,
    ) {
        $this->properties = [
            'position'            => $position,
            'velocity'            => $velocity,
            'direction'           => $direction,
            'angularVelocity'     => $angularVelocity,
            'fuelLevel'           => $fuelLevel,
            'fuelConsumptionRate' => $fuelConsumptionRate,
            'isAlive'             => true,
        ];
    }

    public function getProperty(string $name): mixed
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new RuntimeException("Property '{$name}' not found on SpaceShip");
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

    public function getAngularVelocity(): int
    {
        return $this->properties['angularVelocity'];
    }

    public function setDirection(int $direction): void
    {
        $this->properties['direction'] = $direction;
    }

    public function getFuelLevel(): int
    {
        return $this->properties['fuelLevel'];
    }

    public function getFuelConsumptionRate(): int
    {
        return $this->properties['fuelConsumptionRate'];
    }

    public function setFuelLevel(int $level): void
    {
        $this->properties['fuelLevel'] = $level;
    }

    public function getShipId(): string
    {
        return $this->shipId;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function isAlive(): bool
    {
        return (bool) $this->properties['isAlive'];
    }

    public function destroy(): void
    {
        $this->properties['isAlive'] = false;
    }

    public function getGameObject(): GameObject
    {
        return $this->gameObject;
    }
}
