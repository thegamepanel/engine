<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Exceptions\BindingNotFoundException;
use Engine\Container\Exceptions\DependencyResolutionException;
use Engine\Container\Exceptions\InvalidClassException;
use Engine\Container\Exceptions\InvalidFunctionException;
use Engine\Container\Exceptions\InvalidInvocationException;
use Engine\Container\Exceptions\InvalidMethodException;
use Engine\Container\Exceptions\InvalidResolverException;
use Engine\Container\Exceptions\MethodCallException;
use Engine\Container\Exceptions\NotInstantiableException;
use Engine\Container\Exceptions\UnresolvableClassException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('container'), Group('exceptions')]
class ExceptionsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // BindingNotFoundException
    // -------------------------------------------------------------------------

    /**
     * - A binding lookup failure for a plain class produces a message identifying
     *   the class that could not be found.
     */
    #[Test]
    public function bindingNotFoundForClassContainsClassName(): void
    {
        $e = BindingNotFoundException::class('SomeClass');

        $this->assertInstanceOf(BindingNotFoundException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
    }

    /**
     * - A named binding lookup failure produces a message containing both the
     *   class name and the binding name so the caller can identify the exact
     *   missing configuration.
     */
    #[Test]
    public function bindingNotFoundForNamedContainsClassAndName(): void
    {
        $e = BindingNotFoundException::named('SomeClass', 'primary');

        $this->assertInstanceOf(BindingNotFoundException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
        $this->assertStringContainsString('primary', $e->getMessage());
    }

    /**
     * - A qualified binding lookup failure produces a message containing both the
     *   class name and the qualifier type so the caller can identify the exact
     *   missing configuration.
     */
    #[Test]
    public function bindingNotFoundForQualifiedContainsClassAndQualifier(): void
    {
        $e = BindingNotFoundException::qualified('SomeClass', 'SomeQualifier');

        $this->assertInstanceOf(BindingNotFoundException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
        $this->assertStringContainsString('SomeQualifier', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // InvalidClassException
    // -------------------------------------------------------------------------

    /**
     * - An invalid class error includes the offending class name in its message
     *   so it is immediately clear which class caused the failure.
     */
    #[Test]
    public function invalidClassMakeContainsClassName(): void
    {
        $e = InvalidClassException::make('NonExistentClass');

        $this->assertInstanceOf(InvalidClassException::class, $e);
        $this->assertStringContainsString('NonExistentClass', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // InvalidFunctionException
    // -------------------------------------------------------------------------

    /**
     * - An invalid function error includes the offending function name in its
     *   message so the caller can identify which function could not be found.
     */
    #[Test]
    public function invalidFunctionMakeContainsFunctionName(): void
    {
        $e = InvalidFunctionException::make('nonExistentFunction');

        $this->assertInstanceOf(InvalidFunctionException::class, $e);
        $this->assertStringContainsString('nonExistentFunction', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // InvalidMethodException
    // -------------------------------------------------------------------------

    /**
     * - An invalid method error includes both the class name and the method name
     *   so the caller can locate the missing method at a glance.
     */
    #[Test]
    public function invalidMethodMakeContainsClassAndMethod(): void
    {
        $e = InvalidMethodException::make('SomeClass', 'badMethod');

        $this->assertInstanceOf(InvalidMethodException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
        $this->assertStringContainsString('badMethod', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // MethodCallException
    // -------------------------------------------------------------------------

    /**
     * - A method call failure error includes both the class name and the method
     *   name to pinpoint which call could not be completed.
     */
    #[Test]
    public function methodCallExceptionMakeContainsClassAndMethod(): void
    {
        $e = MethodCallException::make('SomeClass', 'someMethod');

        $this->assertInstanceOf(MethodCallException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
        $this->assertStringContainsString('someMethod', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // NotInstantiableException
    // -------------------------------------------------------------------------

    /**
     * - A not-instantiable error includes the class name so the caller knows
     *   which abstract or interface was incorrectly passed to the container.
     */
    #[Test]
    public function notInstantiableMakeContainsClassName(): void
    {
        $e = NotInstantiableException::make('AbstractThing');

        $this->assertInstanceOf(NotInstantiableException::class, $e);
        $this->assertStringContainsString('AbstractThing', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // UnresolvableClassException
    // -------------------------------------------------------------------------

    /**
     * - An unresolvable class error includes the class name in its message, making
     *   it clear which class carries the `#[NoResolution]` attribute.
     */
    #[Test]
    public function unresolvableClassMakeContainsClassNameAndAttribute(): void
    {
        $e = UnresolvableClassException::make('LockedClass');

        $this->assertInstanceOf(UnresolvableClassException::class, $e);
        $this->assertStringContainsString('LockedClass', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // DependencyResolutionException
    // -------------------------------------------------------------------------

    /**
     * - A cannot-resolve error identifies the type that could not be resolved,
     *   so the user can trace which dependency is missing.
     */
    #[Test]
    public function dependencyCannotResolveContainsType(): void
    {
        $e = DependencyResolutionException::cannotResolve('string');

        $this->assertInstanceOf(DependencyResolutionException::class, $e);
        $this->assertStringContainsString('string', $e->getMessage());
    }

    /**
     * - An intersection resolution failure error names the intersection type
     *   so the caller can see which multi-interface dependency could not be satisfied.
     */
    #[Test]
    public function dependencyIntersectionContainsType(): void
    {
        $e = DependencyResolutionException::intersection('FooInterface&BarInterface');

        $this->assertInstanceOf(DependencyResolutionException::class, $e);
        $this->assertStringContainsString('FooInterface&BarInterface', $e->getMessage());
    }

    /**
     * - An intersection-no-binding error identifies the intersection type that has
     *   no registered binding, distinguishing it from a general resolution failure.
     */
    #[Test]
    public function dependencyIntersectionNoBindingContainsType(): void
    {
        $e = DependencyResolutionException::intersectionNoBinding('FooInterface&BarInterface');

        $this->assertInstanceOf(DependencyResolutionException::class, $e);
        $this->assertStringContainsString('FooInterface&BarInterface', $e->getMessage());
    }

    /**
     * - A union resolution failure error names the union type so the user can
     *   identify which multi-type parameter could not be satisfied.
     */
    #[Test]
    public function dependencyUnionContainsType(): void
    {
        $e = DependencyResolutionException::union('Foo|Bar');

        $this->assertInstanceOf(DependencyResolutionException::class, $e);
        $this->assertStringContainsString('Foo|Bar', $e->getMessage());
    }

    /**
     * - A ghost resolution failure error names the type that could not be turned
     *   into a ghost object, confirming the message carries the problematic type.
     */
    #[Test]
    public function dependencyGhostContainsType(): void
    {
        $e = DependencyResolutionException::ghost('string');

        $this->assertInstanceOf(DependencyResolutionException::class, $e);
        $this->assertStringContainsString('string', $e->getMessage());
    }

    // -------------------------------------------------------------------------
    // InvalidResolverException
    // -------------------------------------------------------------------------

    /**
     * - An unregistered resolvable error names the resolvable class so the user
     *   can identify which attribute has no corresponding resolver registered.
     */
    #[Test]
    public function invalidResolverUnregisteredContainsResolvable(): void
    {
        $e = InvalidResolverException::unregistered('SomeResolvable');

        $this->assertInstanceOf(InvalidResolverException::class, $e);
        $this->assertStringContainsString('SomeResolvable', $e->getMessage());
    }

    /**
     * - An invalid-resolvable error names the class that does not satisfy the
     *   resolvable contract, aiding diagnosis of misconfigured resolver maps.
     */
    #[Test]
    public function invalidResolverResolvableContainsClass(): void
    {
        $e = InvalidResolverException::resolvable('NotAResolvable');

        $this->assertInstanceOf(InvalidResolverException::class, $e);
        $this->assertStringContainsString('NotAResolvable', $e->getMessage());
    }

    /**
     * - An invalid-resolver error names the class that does not implement the
     *   resolver contract, so the developer can fix the registration.
     */
    #[Test]
    public function invalidResolverResolverContainsClass(): void
    {
        $e = InvalidResolverException::resolver('NotAResolver');

        $this->assertInstanceOf(InvalidResolverException::class, $e);
        $this->assertStringContainsString('NotAResolver', $e->getMessage());
    }

    /**
     * - A no-default-resolver error produces a non-empty message indicating
     *   the catalogue was not configured with a default.
     */
    #[Test]
    public function invalidResolverNoDefaultHasMessage(): void
    {
        $e = InvalidResolverException::noDefault();

        $this->assertInstanceOf(InvalidResolverException::class, $e);
        $this->assertNotEmpty($e->getMessage());
    }

    // -------------------------------------------------------------------------
    // InvalidInvocationException
    // -------------------------------------------------------------------------

    /**
     * - A not-public invocation error includes both the class name and method name
     *   so the caller immediately knows which non-public method was targeted.
     */
    #[Test]
    public function invalidInvocationNotPublicContainsClassAndMethod(): void
    {
        $e = InvalidInvocationException::notPublic('SomeClass', 'privateMethod');

        $this->assertInstanceOf(InvalidInvocationException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
        $this->assertStringContainsString('privateMethod', $e->getMessage());
    }

    /**
     * - An already-initialised error includes the class name so the caller knows
     *   which class's constructor was illegally targeted on an existing instance.
     */
    #[Test]
    public function invalidInvocationAlreadyInitialisedContainsClass(): void
    {
        $e = InvalidInvocationException::alreadyInitialised('SomeClass');

        $this->assertInstanceOf(InvalidInvocationException::class, $e);
        $this->assertStringContainsString('SomeClass', $e->getMessage());
    }
}
