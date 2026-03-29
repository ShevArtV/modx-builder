<?php

declare(strict_types=1);

namespace Modx3TestUtils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionHelper
{
    protected static function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $method = new ReflectionMethod($object, $methodName);

        return $method->invoke($object, ...$parameters);
    }

    protected static function setProperty(object $object, string $propertyName, mixed $value): void
    {
        $property = new ReflectionProperty($object, $propertyName);
        $property->setValue($object, $value);
    }

    protected static function getProperty(object $object, string $propertyName): mixed
    {
        $property = new ReflectionProperty($object, $propertyName);

        return $property->getValue($object);
    }

    protected static function createWithoutConstructor(string $className): object
    {
        $reflection = new ReflectionClass($className);

        return $reflection->newInstanceWithoutConstructor();
    }
}
