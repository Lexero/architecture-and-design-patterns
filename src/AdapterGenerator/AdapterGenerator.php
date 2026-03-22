<?php

declare(strict_types=1);

namespace App\AdapterGenerator;

class AdapterGenerator
{
    public function __construct(
        private readonly InterfaceParser $parser,
        private readonly AdapterCodeGenerator $codeGenerator
    ) {
    }

    public function generateAdapters(array $interfaceNames, string $outputDir): array
    {
        $generatedFiles = [];

        foreach ($interfaceNames as $interfaceName) {
            $generatedFiles[] = $this->generateAdapter($interfaceName, $outputDir);
        }

        return $generatedFiles;
    }

    public function generateAdapter(string $interfaceName, string $outputDir): string
    {
        $interfaceData = $this->parser->parse($interfaceName);
        $code = $this->codeGenerator->generate($interfaceData);
        $outputFile = $outputDir . '/' . $interfaceData['name'] . 'Adapter.php';
        file_put_contents($outputFile, $code);

        return $outputFile;
    }
}
