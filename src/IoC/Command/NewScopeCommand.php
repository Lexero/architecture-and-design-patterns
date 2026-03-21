<?php

declare(strict_types=1);

namespace App\IoC\Command;

use App\IoC\Scope\ScopeManager;
use App\SpaceObject\Contract\CommandInterface;

class NewScopeCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopeManager $scopeManager,
        private readonly string $scopeId,
        private readonly ?string $parentId = null,
    ) {
    }

    public function execute(): void
    {
        if (!$this->scopeManager->hasScope($this->scopeId)) {
            $this->scopeManager->createScope($this->scopeId, $this->parentId);
        }
    }
}
