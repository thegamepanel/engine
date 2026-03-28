<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Countable;
use Stringable;

/**
 * A fixture class whose constructor parameters cover every reflection type variant:
 * a single named type, a union type, and an intersection type. Used to drive tests
 * for ReflectionHelper's type-inspection helpers without needing to mock
 * ReflectionType objects directly.
 */
class ClassWithTypedParameters
{
    public function __construct(
        public readonly ClassWithMethods $namedType,
        public readonly string|int $unionType,
        public readonly Stringable&Countable $intersectionType,
    ) {
    }
}
