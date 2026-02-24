<?php

declare(strict_types=1);

namespace App\Tests\DesignPatterns;

use App\DesignPatterns\QuadraticEquation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QuadraticEquationTest extends TestCase
{
    private QuadraticEquation $equation;

    protected function setUp(): void
    {
        $this->equation = new QuadraticEquation();
    }

    //Пункт 3: тест, который проверяет, что для уравнения x^2+1 = 0 корней нет (возвращается пустой массив)
    public function testNoRootsWithNegativeDiscriminant(): void
    {
        $roots = $this->equation->solve(1.0, 0.0, 1.0);

        self::assertCount(0, $roots);
    }

    // Пункт 5: тест, который проверяет, что для уравнения x^2-1 = 0 есть два корня кратности 1 (x1=1, x2=-1)
    public function testTwoRootsOfMultiplicityOne(): void
    {
        $roots = $this->equation->solve(1.0, 0.0, -1.0);

        self::assertCount(2, $roots);
        self::assertEqualsWithDelta(-1.0, $roots[0], 1e-9);
        self::assertEqualsWithDelta(1.0, $roots[1], 1e-9);
    }

    // Пункт 7: тест, который проверяет, что для уравнения x^2+2x+1 = 0 есть один корень кратности 2 (x1= x2 = -1) (изначально коэффициет 'c' равен 0)
    // Пункт 11: замена коэффициента 'c' на 0.9999999999
    public function testOneRootOfMultiplicityTwo(): void
    {
        $roots = $this->equation->solve(1.0, 2.0, 0.9999999999);

        self::assertCount(1, $roots);
        self::assertEqualsWithDelta(-1.0, $roots[0], 1e-6);
    }

    // Пункт 9: тест, который проверяет, что коэффициент 'a' не может быть равен 0. Выбрасывается исключение
    #[DataProvider('coefficientAIsZeroProvider')]
    public function testCoefficientAIsZero(float $a): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->equation->solve($a, 1.0, 1.0);
    }

    public static function coefficientAIsZeroProvider(): array
    {
        return [
            'a is a zero'         => [0.0],
            'a is a small number' => [5e-10],
        ];
    }

    // Пункт 13: NaN и INF в коэффициентах. Выбрасываем эксепшн
    #[DataProvider('nonFiniteCoefficientsProvider')]
    public function testNonFiniteCoefficients(float $a, float $b, float $c): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->equation->solve($a, $b, $c);
    }

    public static function nonFiniteCoefficientsProvider(): array
    {
        return [
            'NaN in a'  => [NAN,  1.0,  1.0],
            'NaN in b'  => [1.0,  NAN,  1.0],
            'NaN in c'  => [1.0,  1.0,  NAN],
            'INF in a'  => [INF,  1.0,  1.0],
            'INF in b'  => [1.0,  INF,  1.0],
            'INF in c'  => [1.0,  1.0,  INF],
            '-INF in a' => [-INF,  1.0,  1.0],
            '-INF in b' => [1.0,  -INF,  1.0],
            '-INF in c' => [1.0,  1.0,  -INF],
        ];
    }
}
