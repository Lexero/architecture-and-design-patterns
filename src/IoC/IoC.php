<?php

declare(strict_types=1);

namespace App\IoC;

use App\IoC\Command\ClearScopesCommand;
use App\IoC\Command\CurrentScopeCommand;
use App\IoC\Command\NewScopeCommand;
use App\IoC\Command\RegisterDependencyCommand;
use App\IoC\Scope\ScopeManager;
use App\IoC\Scope\ThreadLocalScopeStorage;
use App\SpaceObject\Contract\CommandInterface;
use InvalidArgumentException;

class IoC
{
    public static function resolve(string $key, mixed ...$args): mixed
    {
        $scopeManager = ThreadLocalScopeStorage::get();

        return match ($key) {
            'IoC.Register' => self::createRegisterCommand($scopeManager, $args),
            'IoC.ScopeManager' => $scopeManager,

            'Scopes.New' => self::createNewScopeCommand($scopeManager, $args),
            'Scopes.Current' => self::createCurrentScopeCommand($scopeManager, $args),
            'Scopes.Clear' => new ClearScopesCommand($scopeManager),

            default => $scopeManager->getCurrentScope()->resolve($key, ...$args),
        };
    }

    private static function createRegisterCommand(ScopeManager $scopeManager, array $args): CommandInterface
    {
        if (count($args) < 2) {
            throw new InvalidArgumentException(
                'IoC.Register requires at least 2 arguments: key and resolver'
            );
        }

        [$key, $resolver] = $args;

        if (!is_callable($resolver)) {
            throw new InvalidArgumentException('Resolver must be callable');
        }

        return new RegisterDependencyCommand($scopeManager, $key, $resolver);
    }

    private static function createNewScopeCommand(ScopeManager $scopeManager, array $args): CommandInterface
    {
        if (count($args) < 1) {
            throw new InvalidArgumentException('Scopes.New requires scope ID');
        }

        $scopeId = $args[0];
        $parentId = $args[1] ?? null;

        return new NewScopeCommand($scopeManager, $scopeId, $parentId);
    }

    private static function createCurrentScopeCommand(ScopeManager $scopeManager, array $args): CommandInterface
    {
        if (count($args) < 1) {
            throw new InvalidArgumentException('Scopes.Current requires scope ID');
        }

        $scopeId = $args[0];

        return new CurrentScopeCommand($scopeManager, $scopeId);
    }

    public static function reset(): void
    {
        ThreadLocalScopeStorage::clearAll();
    }
}
