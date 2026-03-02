<?php

declare(strict_types=1);

namespace App\IoC;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerAdapter
{
    private ContainerBuilder $container;
    /** @var array<string, callable> */
    private array $factories = [];

    public function __construct()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.debug', false);
    }

    public function register(string $key, callable $factory): void
    {
        $this->factories[$key] = $factory;
    }

    public function resolve(string $key, mixed ...$args): mixed
    {
        if (!isset($this->factories[$key])) {
            throw new RuntimeException(
                sprintf('Service "%s" is not registered in the container', $key)
            );
        }

        $factory = $this->factories[$key];
        return $factory(...$args);
    }

    public function has(string $key): bool
    {
        return isset($this->factories[$key]);
    }
}
