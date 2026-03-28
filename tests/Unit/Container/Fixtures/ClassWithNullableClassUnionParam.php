<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a union-typed constructor parameter containing exactly one
 * resolvable class type and null (ClassWithMethods|null). Used to exercise the
 * GenericResolver path where a single class type is extracted from a union and
 * resolved normally.
 */
class ClassWithNullableClassUnionParam
{
    public function __construct(public readonly ClassWithMethods|null $dep)
    {
    }
}
