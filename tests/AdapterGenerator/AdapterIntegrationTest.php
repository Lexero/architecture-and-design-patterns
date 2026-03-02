<?php

declare(strict_types=1);

namespace App\Tests\AdapterGenerator;

use App\IoC\IoC;
use App\SpaceObject\Contract\CommandInterface;
use App\SpaceObject\ValueObject\Point;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class AdapterIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        IoC::reset();
    }

    protected function tearDown(): void
    {
        IoC::reset();
    }

    public function testAdapterGetterCallsIoCResolve(): void
    {
        $object = new stdClass();
        $expectedPoint = new Point(10, 20);

        IoC::resolve(
            'IoC.Register',
            'App\Tests\AdapterGenerator\TestMovableInterface:position.get',
            fn($o) => $expectedPoint
        )->execute();

        $adapter = IoC::resolve('Adapter', TestMovableInterface::class, $object);
        $result = $adapter->getPosition();

        self::assertSame($expectedPoint, $result);
    }

    public function testAdapterMethodCallsIoCExecutesCommand(): void
    {
        $object = new stdClass();
        $executedCommands = [];

        IoC::resolve(
            'IoC.Register',
            'App\Tests\AdapterGenerator\TestMovableInterface:finish',
            function($o) use (&$executedCommands) {
                return new class($executedCommands) implements CommandInterface {
                    public function __construct(
                        private array &$executedCommands
                    ) {
                    }

                    public function execute(): void
                    {
                        $this->executedCommands[] = 'finish';
                    }
                };
            }
        )->execute();

        $adapter = IoC::resolve('Adapter', TestMovableInterface::class, $object);

        $adapter->finish();

        self::assertCount(1, $executedCommands);
        self::assertSame('finish', $executedCommands[0]);
    }

    public static function provideInvalidAdapterArguments(): array
    {
        return [
            'missing arguments' => [
                'Adapter requires 2 arguments',
                TestMovableInterface::class,
            ],
            'non-string interface class' => [
                'First argument must be interface class name',
                123,
                new stdClass(),
            ],
            'non-object second argument' => [
                'Second argument must be an object',
                TestMovableInterface::class,
                'not-an-object',
            ],
        ];
    }

    #[DataProvider('provideInvalidAdapterArguments')]
    public function testIoCAdapterKeyValidation(
        string $exceptionMessage,
        mixed ...$args
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        IoC::resolve('Adapter', ...$args);
    }

    public function testAdapterWhenDependencyNotRegistered(): void
    {
        $adapter = IoC::resolve('Adapter', TestMovableInterface::class, new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not found');

        $adapter->getPosition();
    }
}
