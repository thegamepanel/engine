<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with two typed constructor parameters to support testing that the
 * container collects and resolves all constructor dependencies, not just the first one.
 */
class ClassWithMultipleDependencies
{
    public function __construct(
        public readonly ClassWithMethods $first,
        public readonly ClassWithProperty $second,
    ) {
    }
}
