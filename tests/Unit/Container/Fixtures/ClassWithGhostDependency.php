<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Ghost;

/**
 * A fixture class whose constructor declares a parameter decorated with {@see Ghost},
 * to support testing that the container routes the dependency through the GhostResolver
 * and produces an uninitialised lazy ghost proxy rather than a fully resolved instance.
 */
class ClassWithGhostDependency
{
    public function __construct(
        #[Ghost] public readonly ClassWithProperty $dependency,
    ) {
    }
}
