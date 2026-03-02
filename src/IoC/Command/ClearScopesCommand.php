<?php

declare(strict_types=1);

namespace App\IoC\Command;

use App\IoC\Scope\ScopeManager;
use App\SpaceObject\Contract\CommandInterface;

class ClearScopesCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopeManager $scopeManager,
    ) {
    }

    public function execute(): void
    {
        $this->scopeManager->clearScopes();
    }
}
