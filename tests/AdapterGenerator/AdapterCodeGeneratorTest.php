<?php

declare(strict_types=1);

namespace App\Tests\AdapterGenerator;

use App\AdapterGenerator\AdapterCodeGenerator;
use App\AdapterGenerator\InterfaceParser;
use PHPUnit\Framework\TestCase;

class AdapterCodeGeneratorTest extends TestCase
{
    private AdapterCodeGenerator $generator;
    private InterfaceParser $parser;

    protected function setUp(): void
    {
        $this->generator = new AdapterCodeGenerator();
        $this->parser = new InterfaceParser();
    }

    public function testGenerateAdapterCode(): void
    {
        $interfaceData = $this->parser->parse(TestMovableInterface::class);
        $code = $this->generator->generate($interfaceData);

        self::assertStringStartsWith('<?php', $code);
        self::assertStringContainsString('declare(strict_types=1);', $code);
        self::assertStringContainsString('Auto-generated adapter', $code);

        self::assertStringContainsString('use App\IoC\IoC;', $code);
        self::assertStringContainsString('use App\Tests\AdapterGenerator\TestMovableInterface;', $code);
        self::assertStringContainsString('use App\SpaceObject\ValueObject\Point;', $code);

        self::assertStringContainsString('class TestMovableInterfaceAdapter', $code);
        self::assertStringContainsString('implements TestMovableInterface', $code);
        self::assertStringContainsString('public function __construct(', $code);
        self::assertStringContainsString('private readonly object $object', $code);

        self::assertStringContainsString('public function getPosition(): Point', $code);
        self::assertStringContainsString("IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:position.get'", $code);

        self::assertStringContainsString('public function setPosition(Point $newValue): void', $code);
        self::assertStringContainsString("IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:position.set'", $code);

        self::assertStringContainsString('public function finish(): void', $code);
        self::assertStringContainsString("IoC::resolve('App\Tests\AdapterGenerator\TestMovableInterface:finish'", $code);
    }
}
