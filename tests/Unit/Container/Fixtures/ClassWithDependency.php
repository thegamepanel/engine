<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a single typed constructor parameter to support testing that
 * the container's auto-wiring invokes the constructor via reflection rather than
 * bypassing it with direct instantiation.
 */
class ClassWithDependency
{
    public function __construct(
        public readonly ClassWithMethods $dependency,
    ) {
    }
}
