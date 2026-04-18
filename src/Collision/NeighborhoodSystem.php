<?php

declare(strict_types=1);

namespace App\Collision;

use App\SpaceObject\ValueObject\Point;

class NeighborhoodSystem
{
    /** @var array<string, list<GameObject>> */
    private array $neighborhoods = [];

    /** @var array<string, string> */
    private array $objectNeighborhoods = [];

    public function __construct(
        private readonly int $gridSize,
        private readonly int $offsetX = 0,
        private readonly int $offsetY = 0,
    ) {
    }

    public function getNeighborhoodKey(Point $position): string
    {
        $gridX = (int) floor(($position->x + $this->offsetX) / $this->gridSize);
        $gridY = (int) floor(($position->y + $this->offsetY) / $this->gridSize);

        return $gridX . ',' . $gridY;
    }

    public function addObject(GameObject $gameObject): void
    {
        $newKey = $this->getNeighborhoodKey($gameObject->getPosition());

        $this->neighborhoods[$newKey][] = $gameObject;
        $this->objectNeighborhoods[$gameObject->getId()] = $newKey;
    }

    public function removeObject(GameObject $gameObject): void
    {
        $oldKey = $this->objectNeighborhoods[$gameObject->getId()] ?? null;
        if ($oldKey === null) {
            return;
        }

        $this->neighborhoods[$oldKey] = array_values(
            array_filter(
                $this->neighborhoods[$oldKey] ?? [],
                static fn(GameObject $o) => $o->getId() !== $gameObject->getId(),
            )
        );

        if (empty($this->neighborhoods[$oldKey])) {
            unset($this->neighborhoods[$oldKey]);
        }

        unset($this->objectNeighborhoods[$gameObject->getId()]);
    }

    public function updateObjectNeighborhood(GameObject $gameObject): void
    {
        $oldKey = $this->objectNeighborhoods[$gameObject->getId()] ?? null;
        $newKey = $this->getNeighborhoodKey($gameObject->getPosition());

        if ($oldKey === $newKey) {
            return;
        }

        $this->removeObject($gameObject);
        $this->addObject($gameObject);
    }

    /** @return list<GameObject> */
    public function getObjectsInSameNeighborhood(GameObject $gameObject): array
    {
        $key = $this->objectNeighborhoods[$gameObject->getId()] ?? null;
        if ($key === null) {
            return [];
        }

        return $this->neighborhoods[$key] ?? [];
    }

    /** @return array<string, list<GameObject>> */
    public function getNeighborhoods(): array
    {
        return $this->neighborhoods;
    }
}
