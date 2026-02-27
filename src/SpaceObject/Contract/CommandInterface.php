<?php

declare(strict_types=1);

namespace App\SpaceObject\Contract;

interface CommandInterface
{
    public function execute(): void;
}
