<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a union-typed constructor parameter containing one
 * unresolvable interface type and one scalar type (AbstractInterface|string). Used
 * to exercise GenericResolver's union resolution path where the single resolvable
 * type (AbstractInterface) fails to resolve, triggering the Throwable catch and a
 * DependencyResolutionException::union re-throw.
 */
class ClassWithAbstractUnionParam
{
    public function __construct(public readonly AbstractInterface|string $dep)
    {
    }
}
