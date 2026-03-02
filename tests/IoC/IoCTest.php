<?php

declare(strict_types=1);

namespace App\Tests\IoC;

use App\IoC\Command\ClearScopesCommand;
use App\IoC\Command\CurrentScopeCommand;
use App\IoC\Command\NewScopeCommand;
use App\IoC\Command\RegisterDependencyCommand;
use App\IoC\IoC;
use App\IoC\Scope\ScopeManager;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class IoCTest extends TestCase
{
    protected function setUp(): void
    {
        IoC::reset();
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public static function provideIoCCommands(): array
    {
        return [
            'IoC.Register returns RegisterDependencyCommand' => [
                'key' => 'IoC.Register',
                'args' => ['test', fn() => 'value'],
                'expectedClass' => RegisterDependencyCommand::class,
            ],
            'IoC.ScopeManager returns ScopeManager' => [
                'key' => 'IoC.ScopeManager',
                'args' => [],
                'expectedClass' => ScopeManager::class,
            ],
            'Scopes.New returns NewScopeCommand' => [
                'key' => 'Scopes.New',
                'args' => ['test-scope'],
                'expectedClass' => NewScopeCommand::class,
            ],
            'Scopes.Current returns CurrentScopeCommand' => [
                'key' => 'Scopes.Current',
                'args' => ['test-scope'],
                'expectedClass' => CurrentScopeCommand::class,
            ],
            'Scopes.Clear returns ClearScopesCommand' => [
                'key' => 'Scopes.Clear',
                'args' => [],
                'expectedClass' => ClearScopesCommand::class,
            ],
        ];
    }

    #[DataProvider('provideIoCCommands')]
    public function testResolveReturnsCorrectCommand(string $key, array $args, string $expectedClass): void
    {
        $result = IoC::resolve($key, ...$args);

        self::assertInstanceOf($expectedClass, $result);
    }

    public static function provideInvalidIoCOperations(): array
    {
        return [
            'Unregistered dependency throws RuntimeException' => [
                'key' => 'unknown-dependency',
                'args' => [],
                'expectedException' => RuntimeException::class,
                'expectedMessage' => null,
            ],
            'IoC.Register with insufficient arguments' => [
                'key' => 'IoC.Register',
                'args' => ['only-key'],
                'expectedException' => InvalidArgumentException::class,
                'expectedMessage' => 'IoC.Register requires at least 2 arguments',
            ],
            'IoC.Register with non-callable resolver' => [
                'key' => 'IoC.Register',
                'args' => ['test', 'not-callable'],
                'expectedException' => InvalidArgumentException::class,
                'expectedMessage' => 'Resolver must be callable',
            ],
        ];
    }

    #[DataProvider('provideInvalidIoCOperations')]
    public function testResolveThrowsExceptionForInvalidOperation(
        string $key,
        array $args,
        string $expectedException,
        ?string $expectedMessage = null
    ): void {
        $this->expectException($expectedException);

        if ($expectedMessage !== null) {
            $this->expectExceptionMessage($expectedMessage);
        }

        IoC::resolve($key, ...$args);
    }

    public function testRegisterAndResolveWithArguments(): void
    {
        IoC::resolve('IoC.Register', 'multiply', fn(int $a, int $b) => $a * $b)->execute();

        $result = IoC::resolve('multiply', 4, 5);

        self::assertSame(20, $result);
    }

    public function testResetClearsAllThreadLocalData(): void
    {
        IoC::resolve('IoC.Register', 'test', fn() => 'value')->execute();

        IoC::reset();

        $this->expectException(RuntimeException::class);
        IoC::resolve('test');
    }
}
