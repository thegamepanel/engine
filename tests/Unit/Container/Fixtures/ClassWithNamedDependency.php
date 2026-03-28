<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Named;

/**
 * A fixture class with a constructor parameter decorated with {@see Named}, requesting
 * the 'primary' named binding for ClassWithMethods. Used to test that the container's
 * auto-wiring reads the Named attribute and resolves the correct named instance, and
 * that GenericResolver's configureResolution passes the name to the inner resolution.
 */
class ClassWithNamedDependency
{
    public function __construct(
        #[Named('primary')] public readonly ClassWithMethods $dep,
    ) {
    }
}
