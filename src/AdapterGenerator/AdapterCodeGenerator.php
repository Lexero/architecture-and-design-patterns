<?php

declare(strict_types=1);

namespace App\AdapterGenerator;

class AdapterCodeGenerator
{
    public function generate(array $interfaceData): string
    {
        $adapterName = $interfaceData['name'] . 'Adapter';
        $interfaceFullName = $interfaceData['fullName'];

        $imports = $this->prepareImports($interfaceData);
        $methods = $this->generateMethods($interfaceData);

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\AdapterGenerator\\GeneratedAdapters;

use App\\IoC\\IoC;
use {$interfaceFullName};
{$imports}

/**
 * Auto-generated adapter for {$interfaceFullName}
 *
 * @generated This class is automatically generated.
 */
class {$adapterName} implements {$interfaceData['name']}
{
    public function __construct(
        private readonly object \$object
    ) {
    }

{$methods}
}

PHP;
    }

    private function prepareImports(array $interfaceData): string
    {
        $imports = [];

        foreach ($interfaceData['methods'] as $method) {
            if ($this->needsImport($method['returnType'])) {
                $imports[$method['returnType']] = true;
            }

            foreach ($method['parameters'] as $param) {
                if ($this->needsImport($param['type'])) {
                    $imports[$param['type']] = true;
                }
            }
        }

        if (empty($imports)) {
            return '';
        }

        $importStatements = [];
        foreach (array_keys($imports) as $type) {
            $importStatements[] = "use {$type};";
        }

        return "\n" . implode("\n", $importStatements);
    }

    private function needsImport(string $type): bool
    {
        $builtInTypes = ['int', 'string', 'bool', 'float', 'array', 'void', 'mixed', 'object', 'callable'];

        if (in_array($type, $builtInTypes, true)) {
            return false;
        }

        return str_contains($type, '\\');
    }

    private function generateMethods(array $interfaceData): string
    {
        $methods = [];

        foreach ($interfaceData['methods'] as $method) {
            $methods[] = $this->generateMethod($method, $interfaceData['fullName']);
        }

        return implode("\n\n", $methods);
    }

    private function generateMethod(array $method, string $interfaceFullName): string
    {
        $methodName = $method['name'];
        $returnType = $this->formatReturnType($method['returnType']);
        $parameters = $this->formatParameters($method['parameters']);
        $body = $this->generateMethodBody($method, $interfaceFullName);

        return <<<PHP
    public function {$methodName}({$parameters}){$returnType}
    {
{$body}
    }
PHP;
    }

    private function formatReturnType(string $returnType): string
    {
        if ($returnType === 'void' || $returnType === 'int' || $returnType === 'string' ||
            $returnType === 'bool' || $returnType === 'float' || $returnType === 'array' ||
            $returnType === 'mixed'
        ) {
            return ': ' . $returnType;
        }

        $parts = explode('\\', $returnType);
        $shortName = end($parts);

        return ': ' . $shortName;
    }

    private function formatParameters(array $parameters): string
    {
        if (empty($parameters)) {
            return '';
        }

        $formatted = [];
        foreach ($parameters as $param) {
            $parts = explode('\\', $param['type']);
            $shortName = end($parts);
            $formatted[] = $shortName . ' $' . $param['name'];
        }

        return implode(', ', $formatted);
    }

    private function generateMethodBody(array $method, string $interfaceFullName): string
    {
        if ($method['isGetter']) {
            return $this->generateGetterBody($method, $interfaceFullName);
        }

        if ($method['isSetter']) {
            return $this->generateSetterBody($method, $interfaceFullName);
        }

        return $this->generateCommandBody($method, $interfaceFullName);
    }

    private function generateGetterBody(array $method, string $interfaceFullName): string
    {
        $property = $method['property'];
        $iocKey = "{$interfaceFullName}:{$property}.get";

        return <<<PHP
        return IoC::resolve('{$iocKey}', \$this->object);
PHP;
    }

    private function generateSetterBody(array $method, string $interfaceFullName): string
    {
        $property = $method['property'];
        $paramName = $method['parameters'][0]['name'];
        $iocKey = "{$interfaceFullName}:{$property}.set";

        return <<<PHP
        IoC::resolve('{$iocKey}', \$this->object, \${$paramName})->execute();
PHP;
    }

    private function generateCommandBody(array $method, string $interfaceFullName): string
    {
        $methodName = $method['name'];
        $iocKey = "{$interfaceFullName}:{$methodName}";

        $args = ['$this->object'];
        foreach ($method['parameters'] as $param) {
            $args[] = '$' . $param['name'];
        }
        $argsList = implode(', ', $args);

        return <<<PHP
        IoC::resolve('{$iocKey}', {$argsList})->execute();
PHP;
    }
}
