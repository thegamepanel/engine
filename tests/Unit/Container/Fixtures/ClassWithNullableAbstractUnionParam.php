<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a union-typed constructor parameter that includes one
 * unresolvable interface type, one scalar type, and null
 * (AbstractInterface|string|null). Used to test that when the single resolvable
 * class type fails to resolve, the GenericResolver throws a
 * DependencyResolutionException::union rather than silently returning null via
 * the allowsNull fallback path.
 */
class ClassWithNullableAbstractUnionParam
{
    public function __construct(public readonly AbstractInterface|string|null $dep)
    {
    }
}
