<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Ghost;

/**
 * A fixture class whose constructor declares a parameter decorated with {@see Ghost} on
 * a built-in scalar type (string), to support testing that the GhostResolver throws a
 * DependencyResolutionException when the type is neither a class nor an interface and
 * therefore cannot be instantiated as a ghost object.
 */
class ClassWithGhostScalarDependency
{
    public function __construct(
        #[Ghost] public readonly string $name = 'default',
    ) {
    }
}
