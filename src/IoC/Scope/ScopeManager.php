<?php

declare(strict_types=1);

namespace App\IoC\Scope;

use RuntimeException;

class ScopeManager
{
    private array $scopes = [];

    private ?Scope $currentScope;

    private readonly Scope $rootScope;

    public function __construct()
    {
        $this->rootScope = new Scope('root');
        $this->scopes['root'] = $this->rootScope;
        $this->currentScope = $this->rootScope;
    }

    public function createScope(string $id, ?string $parentId = null): Scope
    {
        $parent = $parentId !== null ? $this->getScope($parentId) : $this->rootScope;

        $scope = new Scope($id, $parent);
        $this->scopes[$id] = $scope;

        return $scope;
    }

    public function getScope(string $id): Scope
    {
        if (!isset($this->scopes[$id])) {
            throw new RuntimeException(
                sprintf('Scope "%s" not found', $id)
            );
        }

        return $this->scopes[$id];
    }

    public function setCurrentScope(string $id): void
    {
        $this->currentScope = $this->getScope($id);
    }

    public function getCurrentScope(): Scope
    {
        return $this->currentScope ?? $this->rootScope;
    }

    public function hasScope(string $id): bool
    {
        return isset($this->scopes[$id]);
    }

    public function clearScopes(): void
    {
        $this->scopes = ['root' => $this->rootScope];
        $this->currentScope = $this->rootScope;
    }
}
