<?php

declare(strict_types=1);

namespace App\IoC\Scope;

use App\IoC\ContainerAdapter;
use RuntimeException;

class Scope
{
    private readonly ContainerAdapter $container;

    public function __construct(
        private readonly string $id,
        private readonly ?Scope $parent = null,
    ) {
        $this->container = new ContainerAdapter();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContainer(): ContainerAdapter
    {
        return $this->container;
    }

    public function getParent(): ?Scope
    {
        return $this->parent;
    }

    public function resolve(string $key, mixed ...$args): mixed
    {
        if ($this->container->has($key)) {
            return $this->container->resolve($key, ...$args);
        }

        if ($this->parent !== null) {
            return $this->parent->resolve($key, ...$args);
        }

        throw new RuntimeException(
            sprintf('Dependency "%s" not found in scope "%s" or parent scopes', $key, $this->id)
        );
    }

    public function register(string $key, callable $resolver): void
    {
        $this->container->register($key, $resolver);
    }
}
