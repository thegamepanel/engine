<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a scalar-typed constructor parameter that carries a default value,
 * to support testing that the generic resolver falls back to the parameter default when
 * the type is neither a class nor an interface (i.e. a built-in type such as string).
 */
class ClassWithScalarDefault
{
    public function __construct(
        public readonly string $name = 'default',
    ) {
    }
}
