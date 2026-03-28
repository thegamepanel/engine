<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a union-typed constructor parameter containing two resolvable
 * class types (ClassWithMethods|ClassWithProperty). Used to exercise the GenericResolver
 * path where multiple class types are present in a union and the resolver cannot
 * determine which one to use, falling back to a default or throwing.
 */
class ClassWithMultiClassUnionParam
{
    public function __construct(public readonly ClassWithMethods|ClassWithProperty $dep)
    {
    }
}
