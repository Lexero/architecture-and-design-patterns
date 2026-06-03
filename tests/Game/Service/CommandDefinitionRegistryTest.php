<?php

declare(strict_types=1);

namespace App\Tests\Game\Service;

use App\Game\Service\CommandDefinitionRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CommandDefinitionRegistryTest extends TestCase
{
    private CommandDefinitionRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new CommandDefinitionRegistry();
    }

    public function testDefineAndGetMacro(): void
    {
        $this->registry->define('Ship.MoveWithFuel', ['CheckFuel', 'Move', 'BurnFuel']);
        $operations = $this->registry->get('Ship.MoveWithFuel');

        self::assertSame(['CheckFuel', 'Move', 'BurnFuel'], $operations);
    }

    public function testReturnsTrueForDefinedMacro(): void
    {
        $this->registry->define('Ship.Rotate', ['Rotate']);

        self::assertTrue($this->registry->has('Ship.Rotate'));
    }

    public function testReturnsFalseForUndefinedMacro(): void
    {
        self::assertFalse($this->registry->has('UndefinedMacro'));
    }

    public function testThrowsExceptionForUndefinedMacro(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->registry->get('UndefinedMacro');
    }

    public function testDefineEmptyOperationsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->registry->define('EmptyMacro', []);
    }

    public function testAllReturnsAllDefinedMacros(): void
    {
        $this->registry->define('Macro1', ['Op1']);
        $this->registry->define('Macro2', ['Op2', 'Op3']);

        $all = $this->registry->all();

        self::assertArrayHasKey('Macro1', $all);
        self::assertArrayHasKey('Macro2', $all);
        self::assertCount(2, $all);
    }
}
