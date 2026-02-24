<?php

declare(strict_types=1);

namespace App\DesignPatterns;

use InvalidArgumentException;

class QuadraticEquation
{
    private const float EPSILON = 1e-9;

    public function solve(float $a, float $b, float $c): array
    {
        // Пункт 14: исключение если коэффициенты NaN или INF
        if (!is_finite($a) || !is_finite($b) || !is_finite($c)) {
            throw new InvalidArgumentException('Coefficients must be a finite numbers.');
        }

        // Пункт 10: исключение если коэффициет "а" равен нулю
        if (abs($a) < self::EPSILON) {
            throw new InvalidArgumentException('Coefficient a must not be zero.');
        }

        $discriminant = $b * $b - 4.0 * $a * $c;

        // Пункт 4: отрицательный дискриминант
        if ($discriminant < -self::EPSILON) {
            return [];
        }

        // Пункт 8: один корень если дискриминант маленький
        // Пункт 12: сравнение дискриминанта с эпсилон, а не с нулем
        if (abs($discriminant) <= self::EPSILON) {
            return [-$b / (2.0 * $a)];
        }

        $sqrtD = sqrt($discriminant);

        // Пункт 6: 2 корня
        return [
            (-$b - $sqrtD) / (2.0 * $a),
            (-$b + $sqrtD) / (2.0 * $a),
        ];
    }
}
