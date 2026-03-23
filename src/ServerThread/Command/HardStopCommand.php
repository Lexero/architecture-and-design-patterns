<?php

declare(strict_types=1);

namespace App\ServerThread\Command;

use App\ServerThread\ServerThread;
use App\SpaceObject\Contract\CommandInterface;

final class HardStopCommand implements CommandInterface
{
    public function __construct(
        private readonly ServerThread $serverThread,
    ) {
    }

    public function execute(): void
    {
        $this->serverThread->stop();
    }
}
