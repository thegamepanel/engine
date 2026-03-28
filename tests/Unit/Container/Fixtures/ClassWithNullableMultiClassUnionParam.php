<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a nullable union-typed constructor parameter containing two
 * resolvable class types and null (ClassWithMethods|ClassWithProperty|null). Used to
 * exercise the GenericResolver path where multiple class types are ambiguous but the
 * union allows null, causing the resolver to return null rather than throwing.
 */
class ClassWithNullableMultiClassUnionParam
{
    public function __construct(public readonly ClassWithMethods|ClassWithProperty|null $dep)
    {
    }
}
