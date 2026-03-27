<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Ghost;

/**
 * A fixture class whose constructor declares a parameter decorated with {@see Ghost} on
 * an interface type, to support testing that the GhostResolver resolves the concrete
 * class from the container's binding rather than attempting to ghost the abstract itself.
 */
class ClassWithGhostAbstractDependency
{
    public function __construct(
        #[Ghost] public readonly AbstractInterface $dep,
    ) {
    }
}
