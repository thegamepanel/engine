<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with two constructor parameters: a scalar string and a
 * class-typed dependency. Used to test that when the container pre-supplies
 * the first (scalar) argument it still resolves all remaining parameters
 * rather than stopping at the first pre-supplied one.
 */
class ClassWithMixedParams
{
    public function __construct(
        public readonly string $name,
        public readonly ClassWithMethods $dep,
    ) {
    }
}
