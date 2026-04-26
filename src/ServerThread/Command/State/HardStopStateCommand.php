<?php

declare(strict_types=1);

namespace App\ServerThread\Command\State;

use App\SpaceObject\Contract\CommandInterface;

final class HardStopStateCommand implements CommandInterface
{
    public function execute(): void
    {
    }
}
