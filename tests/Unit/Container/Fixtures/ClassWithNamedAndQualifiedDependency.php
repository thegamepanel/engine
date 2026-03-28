<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Named;

/**
 * A fixture class whose constructor parameter carries both a {@see Named} attribute
 * and an {@see AttributeQualifier} attribute. This combination is invalid — a dependency
 * may be resolved by name or by qualifier, but not both simultaneously — and is used to
 * verify that the container throws a DependencyResolutionException when it encounters it.
 */
class ClassWithNamedAndQualifiedDependency
{
    public function __construct(
        #[Named('primary'), AttributeQualifier] public readonly ClassWithMethods $dep,
    ) {
    }
}
