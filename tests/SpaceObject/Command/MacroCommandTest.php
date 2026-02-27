<?php

declare(strict_types=1);

namespace App\Tests\SpaceObject\Command;

use App\SpaceObject\Command\MacroCommand;
use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\Exception\CommandException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MacroCommandTest extends TestCase
{
    public function testMacroCommandExecutesAllCommands(): void
    {
        $command1 = $this->createMock(CommandInterface::class);
        $command1->expects(self::once())->method('execute');

        $command2 = $this->createMock(CommandInterface::class);
        $command2->expects(self::once())->method('execute');

        $command3 = $this->createMock(CommandInterface::class);
        $command3->expects(self::once())->method('execute');

        $macroCommand = new MacroCommand([$command1, $command2, $command3]);
        $macroCommand->execute();
    }

    public function testMacroCommandStopsOnCommandException(): void
    {
        $command1 = $this->createMock(CommandInterface::class);
        $command1->expects(self::once())->method('execute');

        $command2 = $this->createMock(CommandInterface::class);
        $command2->expects(self::once())
            ->method('execute')
            ->willThrowException(new RuntimeException('Second command error'));

        $command3 = $this->createMock(CommandInterface::class);
        $command3->expects(self::never())->method('execute');

        $macroCommand = new MacroCommand([$command1, $command2, $command3]);

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Second command error');
        $macroCommand->execute();
    }
}
