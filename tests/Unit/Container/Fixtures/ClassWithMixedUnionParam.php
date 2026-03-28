<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a union-typed constructor parameter containing one resolvable
 * class type and one built-in scalar type (ClassWithMethods|string). PHP represents
 * this as a true ReflectionUnionType rather than a nullable named type, making it
 * suitable for testing GenericResolver's union-type resolution path where exactly
 * one resolvable class type is present.
 */
class ClassWithMixedUnionParam
{
    public function __construct(public readonly ClassWithMethods|string $dep)
    {
    }
}
