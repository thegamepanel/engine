<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Liminal;

/**
 * A fixture class whose constructor declares a parameter decorated with {@see Liminal},
 * to support testing that the container stores the resolved dependency as a weak reference
 * so it can be garbage collected when no other strong references remain.
 */
class ClassWithLiminalDependency
{
    public function __construct(
        #[Liminal] public readonly ClassWithProperty $dependency,
    ) {
    }
}
