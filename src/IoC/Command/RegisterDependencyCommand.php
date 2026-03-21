<?php

declare(strict_types=1);

namespace App\IoC\Command;

use App\IoC\Scope\ScopeManager;
use App\SpaceObject\Contract\CommandInterface;

class RegisterDependencyCommand implements CommandInterface
{
    public function __construct(
        private readonly ScopeManager $scopeManager,
        private readonly string $key,
        private readonly mixed $resolver,
    ) {
    }

    public function execute(): void
    {
        $this->scopeManager->getCurrentScope()->register($this->key, $this->resolver);
    }
}
