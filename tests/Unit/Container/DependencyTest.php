<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Dependency;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('container'), Group('dependency')]
class DependencyTest extends TestCase
{
    /**
     * - When no $optional argument is supplied, the dependency defaults to required,
     *   so callers are not silently treated as optional unless the flag is set explicitly.
     */
    #[Test]
    public function dependencyDefaultsOptionalToFalse(): void
    {
        $dependency = new Dependency('param', null);

        $this->assertFalse($dependency->optional);
    }

    /**
     * - When no $hasDefault argument is supplied, the dependency is assumed to have
     *   no fallback value, so the container does not attempt to return a default
     *   unless the flag is set explicitly.
     */
    #[Test]
    public function dependencyDefaultsHasDefaultToFalse(): void
    {
        $dependency = new Dependency('param', null);

        $this->assertFalse($dependency->hasDefault);
    }

    /**
     * - When no $liminal argument is supplied, the dependency is not marked for
     *   weak-reference storage, so the container treats it as a normal (strongly held)
     *   dependency unless the flag is set explicitly.
     */
    #[Test]
    public function dependencyDefaultsLiminalToFalse(): void
    {
        $dependency = new Dependency('param', null);

        $this->assertFalse($dependency->liminal);
    }
}
