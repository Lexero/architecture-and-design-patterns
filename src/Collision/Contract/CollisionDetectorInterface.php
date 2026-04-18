<?php

declare(strict_types=1);

namespace App\Collision\Contract;

use App\Collision\GameObject;

interface CollisionDetectorInterface
{
    public function checkCollision(GameObject $object1, GameObject $object2): bool;
}
