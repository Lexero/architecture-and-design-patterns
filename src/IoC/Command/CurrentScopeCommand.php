<?php

declare(strict_types=1);

namespace App\IoC\Command;

use App\IoC\Scope\ScopeManager;
use App\SpaceObject\Contract\CommandInterface;

class CurrentScopeCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopeManager $scopeManager,
        private readonly string $scopeId,
    ) {
    }

    public function execute(): void
    {
        $this->scopeManager->setCurrentScope($this->scopeId);
    }
}
