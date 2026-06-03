<?php

declare(strict_types=1);

namespace App\Tests\Game\Factory;

use App\Game\Factory\MacroCommandFactory;
use App\Game\Service\CommandDefinitionRegistry;
use App\IoC\IoC;
use App\SpaceObject\Contract\CommandInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MacroCommandFactoryTest extends TestCase
{
    private CommandDefinitionRegistry $registry;
    private MacroCommandFactory $factory;

    protected function setUp(): void
    {
        IoC::reset();
        $this->registry = new CommandDefinitionRegistry();
        $this->factory = new MacroCommandFactory($this->registry);
        IoC::resolve('Scopes.New', 'test')->execute();
        IoC::resolve('Scopes.Current', 'test')->execute();
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testBuildsMacroCommandFromDefinition(): void
    {
        $executed = [];

        IoC::resolve('IoC.Register', 'Op1', static function () use (&$executed) {
            return new class ($executed) implements CommandInterface {
                public function __construct(private array &$log) {}
                public function execute(): void { $this->log[] = 'Op1'; }
            };
        })->execute();

        IoC::resolve('IoC.Register', 'Op2', static function () use (&$executed) {
            return new class ($executed) implements CommandInterface {
                public function __construct(private array &$log) {}
                public function execute(): void { $this->log[] = 'Op2'; }
            };
        })->execute();

        $this->registry->define('TestMacro', ['Op1', 'Op2']);

        $macro = $this->factory->build('TestMacro');
        $macro->execute();

        self::assertSame(['Op1', 'Op2'], $executed);
    }

    public function testBuiltCommandsExecuteInDefinedOrder(): void
    {
        $order = [];

        IoC::resolve('IoC.Register', 'First', static function () use (&$order) {
            return new class ($order) implements CommandInterface {
                public function __construct(private array &$log) {}
                public function execute(): void { $this->log[] = 1; }
            };
        })->execute();

        IoC::resolve('IoC.Register', 'Second', static function () use (&$order) {
            return new class ($order) implements CommandInterface {
                public function __construct(private array &$log) {}
                public function execute(): void { $this->log[] = 2; }
            };
        })->execute();

        IoC::resolve('IoC.Register', 'Third', static function () use (&$order) {
            return new class ($order) implements CommandInterface {
                public function __construct(private array &$log) {}
                public function execute(): void { $this->log[] = 3; }
            };
        })->execute();

        $this->registry->define('OrderedMacro', ['First', 'Second', 'Third']);

        $macro = $this->factory->build('OrderedMacro');
        $macro->execute();

        self::assertSame([1, 2, 3], $order);
    }

    public function testBuildThrowsExceptionForUndefinedMacro(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->build('UndefinedMacro');
    }
}
