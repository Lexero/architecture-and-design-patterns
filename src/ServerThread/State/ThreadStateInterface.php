<?php

declare(strict_types=1);

namespace App\ServerThread\State;

use App\SpaceObject\Contract\CommandInterface;

interface ThreadStateInterface
{
    public function handle(CommandInterface $command): ?ThreadStateInterface;
}
