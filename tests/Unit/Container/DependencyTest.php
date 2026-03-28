<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Dependency;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Container\Fixtures\ClassWithTypedParameters;

#[Group('unit'), Group('container'), Group('dependency')]
class DependencyTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Defaults
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Setting flags
    // -------------------------------------------------------------------------

    /**
     * - When $optional is set to true the property stores that value, allowing the
     *   container to skip resolution when the dependency cannot be satisfied.
     */
    #[Test]
    public function optionalCanBeSetToTrue(): void
    {
        $dependency = new Dependency('param', null, optional: true);

        $this->assertTrue($dependency->optional);
    }

    /**
     * - When $hasDefault is set to true and a default value is supplied, both
     *   properties reflect those values so the container can fall back without
     *   attempting resolution.
     */
    #[Test]
    public function hasDefaultCanBeSetToTrueWithDefaultValue(): void
    {
        $dependency = new Dependency('param', null, hasDefault: true, default: 'fallback');

        $this->assertTrue($dependency->hasDefault);
        $this->assertSame('fallback', $dependency->default);
    }

    /**
     * - When $liminal is set to true the property stores that value, signalling
     *   that the resolved instance should be held as a weak reference.
     */
    #[Test]
    public function liminalCanBeSetToTrue(): void
    {
        $dependency = new Dependency('param', null, liminal: true);

        $this->assertTrue($dependency->liminal);
    }

    // -------------------------------------------------------------------------
    // Construction
    // -------------------------------------------------------------------------

    /**
     * - The parameter name and type passed to the constructor are stored exactly
     *   as given, so consumers can retrieve them without any transformation.
     */
    #[Test]
    public function parameterAndTypeAreStoredAsGiven(): void
    {
        $type = (new ReflectionClass(ClassWithTypedParameters::class))
            ->getConstructor()
            ->getParameters()[0]
            ->getType();

        $dependency = new Dependency('namedType', $type);

        $this->assertSame('namedType', $dependency->parameter);
        $this->assertSame($type, $dependency->type);
    }
}
