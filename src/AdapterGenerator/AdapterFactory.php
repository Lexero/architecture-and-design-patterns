<?php

declare(strict_types=1);

namespace App\AdapterGenerator;

use RuntimeException;

class AdapterFactory
{
    public function createAdapter(string $interfaceClass, object $object): object
    {
        $parts = explode('\\', $interfaceClass);
        $interfaceName = end($parts);

        $adapterClass = "App\\AdapterGenerator\\GeneratedAdapters\\{$interfaceName}Adapter";

        if (!class_exists($adapterClass)) {
            throw new RuntimeException(
                sprintf(
                    'Adapter class "%s" not found. Please run: php bin/generate-adapters',
                    $adapterClass
                )
            );
        }

        return new $adapterClass($object);
    }
}
