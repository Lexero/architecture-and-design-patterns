<?php

declare(strict_types=1);

namespace App\AdapterGenerator;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class InterfaceParser
{
    public function parse(string $interfaceName): array
    {
        $reflection = new ReflectionClass($interfaceName);

        if (!$reflection->isInterface()) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" is not an interface', $interfaceName)
            );
        }

        $methods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $this->parseMethod($method);
        }

        return [
            'name' => $reflection->getShortName(),
            'namespace' => $reflection->getNamespaceName(),
            'fullName' => $reflection->getName(),
            'methods' => $methods,
        ];
    }

    private function parseMethod(ReflectionMethod $method): array
    {
        $methodName = $method->getName();
        $returnType = $this->extractReturnType($method);
        $parameters = $this->extractParameters($method);
        $isGetter = str_starts_with($methodName, 'get') && count($parameters) === 0;
        $isSetter = str_starts_with($methodName, 'set') && count($parameters) === 1;

        $property = null;
        if ($isGetter || $isSetter) {
            $property = lcfirst(substr($methodName, 3));
        }

        return [
            'name' => $methodName,
            'returnType' => $returnType,
            'parameters' => $parameters,
            'isGetter' => $isGetter,
            'isSetter' => $isSetter,
            'property' => $property,
        ];
    }

    private function extractReturnType(ReflectionMethod $method): string
    {
        $returnType = $method->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            return $returnType->getName();

        }

        return 'mixed';
    }

    private function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $typeName = 'mixed';

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
            }

            $parameters[] = [
                'name' => $param->getName(),
                'type' => $typeName,
            ];
        }

        return $parameters;
    }
}
