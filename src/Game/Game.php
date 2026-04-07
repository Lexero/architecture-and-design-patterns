<?php

declare(strict_types=1);

namespace App\Game;

use App\ServerThread\ServerThread;

final class Game
{
    public function __construct(
        private readonly string $id,
        private readonly string $scopeId,
        private readonly ServerThread $thread,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getScopeId(): string
    {
        return $this->scopeId;
    }

    public function getThread(): ServerThread
    {
        return $this->thread;
    }
}
