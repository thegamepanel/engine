<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Attributes\Ghost;
use Engine\Container\Attributes\Liminal;
use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingCatalogue;
use Engine\Container\Container;
use Engine\Container\Exceptions\DependencyResolutionException;
use Engine\Container\Exceptions\InvalidInvocationException;
use Engine\Container\Invocation;
use Engine\Container\Resolution;
use Engine\Container\Resolvers\GenericResolver;
use Engine\Container\Resolvers\GhostResolver;
use Engine\Container\Resolvers\ResolverCatalogue;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Container\Fixtures\AbstractInterface;
use Tests\Unit\Container\Fixtures\ClassWithDependency;
use Tests\Unit\Container\Fixtures\ClassWithGhostAbstractDependency;
use Tests\Unit\Container\Fixtures\ClassWithGhostDependency;
use Tests\Unit\Container\Fixtures\ClassWithGhostScalarDependency;
use Tests\Unit\Container\Fixtures\ClassWithLiminalDependency;
use Tests\Unit\Container\Fixtures\ClassWithMethods;
use Tests\Unit\Container\Fixtures\ClassWithMultipleDependencies;
use Tests\Unit\Container\Fixtures\ClassWithProperty;
use Tests\Unit\Container\Fixtures\ClassWithScalarDefault;
use Tests\Unit\Container\Fixtures\ConcreteClass;
use Tests\Unit\Container\Fixtures\LazyClass;
use Tests\Unit\Container\Fixtures\LiminalClass;

#[Group('unit'), Group('container')]
class ContainerTest extends TestCase
{
    private function buildContainer(): Container
    {
        return new Container(
            new ResolverCatalogue([], GenericResolver::class),
            new BindingCatalogue([], [], []),
        );
    }

    private function buildContainerWith(Binding ...$bindings): Container
    {
        $map = [];
        foreach ($bindings as $binding) {
            $map[$binding->abstract] = $binding;
        }

        return new Container(
            new ResolverCatalogue([], GenericResolver::class),
            new BindingCatalogue($map, [], []),
        );
    }

    private function buildContainerWithGhostResolver(): Container
    {
        return new Container(
            new ResolverCatalogue([Ghost::class => GhostResolver::class], GenericResolver::class),
            new BindingCatalogue([], [], []),
        );
    }

    // -------------------------------------------------------------------------
    // Lazy proxy creation
    // -------------------------------------------------------------------------

    /**
     * - A lazy resolution for a class with typed properties returns an uninitialized proxy.
     */
    #[Test]
    public function resolveWithLazilyReturnsUninitializedProxy(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithProperty::class)->lazily());

        $this->assertTrue((new ReflectionClass(ClassWithProperty::class))->isUninitializedLazyObject($result));
    }

    /**
     * - A lazy resolution for a class with no typed properties cannot be deferred and
     *   returns an already-initialized instance instead.
     */
    #[Test]
    public function resolveWithLazilyReturnsInitializedObjectWhenClassHasNoUninitializedProperties(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithMethods::class)->lazily());

        $this->assertFalse((new ReflectionClass(ClassWithMethods::class))->isUninitializedLazyObject($result));
        $this->assertInstanceOf(ClassWithMethods::class, $result);
    }

    /**
     * - Passing `$skipLazy = true` forces eager resolution even when the resolution is
     *   flagged as lazy, bypassing proxy creation entirely.
     */
    #[Test]
    public function resolveWithSkipLazyBypassesLazyProxy(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithProperty::class)->lazily(), true);

        $this->assertFalse((new ReflectionClass(ClassWithProperty::class))->isUninitializedLazyObject($result));
        $this->assertInstanceOf(ClassWithProperty::class, $result);
    }

    /**
     * - Accessing a member on a lazy proxy triggers initialization of the underlying object.
     */
    #[Test]
    public function accessingLazyProxyInitializesTheObject(): void
    {
        $container = $this->buildContainer();
        $proxy     = $container->resolve(Resolution::for(ClassWithProperty::class)->lazily());
        $reflector = new ReflectionClass(ClassWithProperty::class);

        $this->assertTrue(($reflector)->isUninitializedLazyObject($proxy));

        $proxy->value;

        $this->assertFalse(($reflector)->isUninitializedLazyObject($proxy));
    }

    /**
     * - A class decorated with `#[Lazy]` is resolved as an uninitialized proxy even
     *   without `Resolution::lazily()` being set by the caller.
     */
    #[Test]
    public function resolveLazyClassReturnsUninitializedProxy(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(LazyClass::class));

        $this->assertTrue((new ReflectionClass(LazyClass::class))->isUninitializedLazyObject($result));
    }

    /**
     * - Passing `$skipLazy = true` bypasses the `#[Lazy]` class attribute and returns
     *   a fully initialized instance directly.
     */
    #[Test]
    public function resolveLazyClassWithSkipLazyReturnsRealInstance(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(LazyClass::class), true);

        $this->assertFalse((new ReflectionClass(LazyClass::class))->isUninitializedLazyObject($result));
        $this->assertInstanceOf(LazyClass::class, $result);
    }

    // -------------------------------------------------------------------------
    // Shared / non-shared binding
    // -------------------------------------------------------------------------

    /**
     * - A shared binding returns the same instance on every subsequent resolution,
     *   acting as a singleton within the container.
     */
    #[Test]
    public function resolveWithSharedBindingReturnsSameInstanceOnSubsequentCalls(): void
    {
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, shared: true),
        );

        $first  = $container->resolve(Resolution::for(ClassWithMethods::class));
        $second = $container->resolve(Resolution::for(ClassWithMethods::class));

        $this->assertSame($first, $second);
    }

    /**
     * - A non-shared binding creates a fresh instance on each resolution, never
     *   caching the result.
     */
    #[Test]
    public function resolveWithNotSharedBindingReturnsNewInstanceEachTime(): void
    {
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, shared: false),
        );

        $first  = $container->resolve(Resolution::for(ClassWithMethods::class));
        $second = $container->resolve(Resolution::for(ClassWithMethods::class));

        $this->assertNotSame($first, $second);
    }

    // -------------------------------------------------------------------------
    // Binding dispatch: factory / instance / concrete
    // -------------------------------------------------------------------------

    /**
     * - When a binding has a factory closure, the container calls it instead of
     *   auto-wiring, and returns whatever the factory produces.
     */
    #[Test]
    public function resolveWithBindingFactoryCallsFactory(): void
    {
        $expected  = new ClassWithMethods();
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, factory: static fn () => $expected, shared: true),
        );

        $result = $container->resolve(Resolution::for(ClassWithMethods::class));

        $this->assertSame($expected, $result);
    }

    /**
     * - When a binding holds a pre-built instance, the container returns it directly
     *   without invoking a factory or auto-wiring.
     */
    #[Test]
    public function resolveWithBindingInstanceReturnsItDirectly(): void
    {
        $expected  = new ClassWithMethods();
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, instance: $expected, shared: true),
        );

        $result = $container->resolve(Resolution::for(ClassWithMethods::class));

        $this->assertSame($expected, $result);
    }

    /**
     * - When a binding maps an abstract to a concrete class name, the container
     *   resolves and instantiates the concrete class.
     */
    #[Test]
    public function resolveWithBindingConcreteInstantiatesConcreteClass(): void
    {
        $container = $this->buildContainerWith(
            new Binding(AbstractInterface::class, concrete: ConcreteClass::class, shared: true),
        );

        $result = $container->resolve(Resolution::for(AbstractInterface::class));

        $this->assertInstanceOf(ConcreteClass::class, $result);
    }

    // -------------------------------------------------------------------------
    // Liminal scope
    // -------------------------------------------------------------------------

    /**
     * - A binding with `liminal = true` stores the resolved instance in the weak
     *   reference store rather than the shared instance store, so a non-liminal
     *   resolution of the same class produces a fresh instance each time.
     */
    #[Test]
    public function resolveWithLiminalBindingDoesNotCacheInSharedInstances(): void
    {
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, liminal: true, shared: true),
        );

        $first  = $container->resolve(Resolution::for(ClassWithMethods::class));
        $second = $container->resolve(Resolution::for(ClassWithMethods::class));

        $this->assertNotSame($first, $second);
    }

    /**
     * - A liminal resolution stores the instance as a weak reference; once all
     *   strong references are dropped the instance is garbage collected and the
     *   next resolution produces a fresh object.
     */
    #[Test]
    public function resolveWithLiminalResolutionStoresInstanceAsWeakReference(): void
    {
        $container  = $this->buildContainerWith(
            new Binding(ClassWithProperty::class, shared: true),
        );
        $resolution = Resolution::for(ClassWithProperty::class)->liminal();

        $instance = $container->resolve($resolution);
        $weak     = \WeakReference::create($instance);
        unset($instance);
        gc_collect_cycles();

        $fresh = $container->resolve($resolution);

        $this->assertNull($weak->get());
        $this->assertInstanceOf(ClassWithProperty::class, $fresh);
    }

    /**
     * - When the liminal flag is set by the resolution object and the resolved class
     *   has no `#[Liminal]` attribute, the flag is preserved through the auto-wiring
     *   path so the instance is still stored as a weak reference.
     */
    #[Test]
    public function resolveWithLiminalResolutionPreservesLiminalFlagThroughAutoWiring(): void
    {
        // ClassWithMethods has no constructor and no #[Liminal] attribute;
        // the liminal flag must survive the auto-wiring code path unchanged.
        $container  = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, shared: true),
        );
        $resolution = Resolution::for(ClassWithMethods::class)->liminal();

        $first  = $container->resolve($resolution);
        $second = $container->resolve($resolution);

        // Both calls resolve to the same WeakReference target while a strong ref exists.
        $this->assertSame($first, $second);
    }

    /**
     * - A class decorated with `#[Liminal]` is stored via a weak reference, so a
     *   second resolution with a shared binding produces a fresh instance when the
     *   non-liminal lookup path is used.
     */
    #[Test]
    public function resolveLiminalClassStoresInstanceWeakly(): void
    {
        $container = $this->buildContainerWith(
            new Binding(LiminalClass::class, shared: true),
        );

        $first  = $container->resolve(Resolution::for(LiminalClass::class));
        $second = $container->resolve(Resolution::for(LiminalClass::class));

        // Stored in liminalInstances (not instances), so non-liminal resolution
        // cannot retrieve it and creates a fresh object each time.
        $this->assertNotSame($first, $second);
    }

    /**
     * - A liminal resolution for a class that is already cached as a non-liminal shared
     *   instance bypasses the shared instance store and creates a fresh object, so that
     *   liminal and non-liminal resolutions of the same class remain independent.
     */
    #[Test]
    public function resolveWithLiminalResolutionDoesNotReturnNonLiminalSharedInstance(): void
    {
        $container = $this->buildContainerWith(
            new Binding(ClassWithMethods::class, shared: true),
        );

        $sharedInstance  = $container->resolve(Resolution::for(ClassWithMethods::class));
        $liminalInstance = $container->resolve(Resolution::for(ClassWithMethods::class)->liminal());

        $this->assertNotSame($sharedInstance, $liminalInstance);
    }

    // -------------------------------------------------------------------------
    // Alias resolution and storage key
    // -------------------------------------------------------------------------

    /**
     * - When a concrete class is resolved via an alias that maps it to an abstract
     *   binding, the resolved instance is stored under the abstract key so that a
     *   subsequent direct resolution of the abstract retrieves the same shared instance.
     */
    #[Test]
    public function resolveViaAliasStoresInstanceUnderAbstractKey(): void
    {
        $binding   = new Binding(AbstractInterface::class, concrete: ConcreteClass::class, shared: true);
        $container = new Container(
            new ResolverCatalogue([], GenericResolver::class),
            new BindingCatalogue(
                [AbstractInterface::class => $binding],
                [ConcreteClass::class => AbstractInterface::class],
                [],
            ),
        );

        $first  = $container->resolve(Resolution::for(ConcreteClass::class));
        $second = $container->resolve(Resolution::for(AbstractInterface::class));

        $this->assertSame($first, $second);
    }

    // -------------------------------------------------------------------------
    // Parameter-level Ghost and Liminal attributes
    // -------------------------------------------------------------------------

    /**
     * - A constructor parameter decorated with #[Ghost] causes the container to route
     *   the dependency through the GhostResolver, which produces an uninitialised lazy
     *   ghost object rather than a fully resolved instance.
     */
    #[Test]
    public function resolveClassWithGhostParameterCreatesGhostProxy(): void
    {
        $container = $this->buildContainerWithGhostResolver();

        $result = $container->resolve(Resolution::for(ClassWithGhostDependency::class));

        $this->assertTrue(
            (new ReflectionClass(ClassWithProperty::class))->isUninitializedLazyObject($result->dependency),
        );
    }

    /**
     * - A constructor parameter decorated with #[Ghost] on a built-in scalar type cannot
     *   be turned into a ghost object, so the GhostResolver must throw a
     *   DependencyResolutionException rather than silently producing an invalid instance.
     */
    #[Test]
    public function resolveClassWithGhostParameterOnNonClassTypeThrowsDependencyResolutionException(): void
    {
        $container = $this->buildContainerWithGhostResolver();

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage('string');

        $container->resolve(Resolution::for(ClassWithGhostScalarDependency::class));
    }

    /**
     * - When the type of a #[Ghost]-decorated parameter has a binding with a concrete
     *   class, the ghost must be created for the concrete rather than the abstract, so
     *   the initialised object is a valid, usable instance of the concrete type.
     */
    #[Test]
    public function resolveClassWithGhostParameterUsesConcreteclassFromBinding(): void
    {
        $container = new Container(
            new ResolverCatalogue([Ghost::class => GhostResolver::class], GenericResolver::class),
            new BindingCatalogue(
                [AbstractInterface::class => new Binding(AbstractInterface::class, concrete: ConcreteClass::class)],
                [],
                [],
            ),
        );

        $result = $container->resolve(Resolution::for(ClassWithGhostAbstractDependency::class));

        $this->assertInstanceOf(ConcreteClass::class, $result->dep);
    }

    /**
     * - A constructor parameter decorated with #[Liminal] causes the container to store
     *   the resolved dependency as a weak reference, so it is eligible for garbage
     *   collection once all other strong references are released.
     */
    #[Test]
    public function resolveClassWithLiminalParameterStoresDependencyWeakly(): void
    {
        $container = new Container(
            new ResolverCatalogue([Liminal::class => GenericResolver::class], GenericResolver::class),
            new BindingCatalogue(
                [ClassWithProperty::class => new Binding(ClassWithProperty::class, shared: true)],
                [],
                [],
            ),
        );

        $result     = $container->resolve(Resolution::for(ClassWithLiminalDependency::class));
        $dependency = $result->dependency;
        $weak       = \WeakReference::create($dependency);
        unset($result, $dependency);
        gc_collect_cycles();

        $this->assertNull($weak->get());
    }

    // -------------------------------------------------------------------------
    // Constructor auto-wiring
    // -------------------------------------------------------------------------

    /**
     * - A class whose constructor declares a typed parameter has that dependency
     *   auto-wired by the container rather than receiving a direct `new` instantiation.
     */
    #[Test]
    public function resolveClassWithConstructorDependencyAutowiresIt(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithDependency::class));

        $this->assertInstanceOf(ClassWithDependency::class, $result);
        $this->assertInstanceOf(ClassWithMethods::class, $result->dependency);
    }

    /**
     * - All constructor parameters are resolved and injected; the container does
     *   not stop after the first dependency.
     */
    #[Test]
    public function resolveClassWithMultipleDependenciesAutowiresAllParameters(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithMultipleDependencies::class));

        $this->assertInstanceOf(ClassWithMethods::class, $result->first);
        $this->assertInstanceOf(ClassWithProperty::class, $result->second);
    }

    /**
     * - When a constructor parameter has a built-in (non-class, non-interface) type
     *   such as string, the generic resolver falls back to the parameter's declared
     *   default value rather than attempting to resolve the type from the container.
     */
    #[Test]
    public function resolveClassWithScalarDefaultUsesDefaultValue(): void
    {
        $container = $this->buildContainer();

        $result = $container->resolve(Resolution::for(ClassWithScalarDefault::class));

        $this->assertSame('default', $result->name);
    }

    // -------------------------------------------------------------------------
    // invoke()
    // -------------------------------------------------------------------------

    /**
     * - `invoke()` is publicly accessible and returns the result of a plain callable.
     */
    #[Test]
    public function invokeCallableReturnsCallableResult(): void
    {
        $container = $this->buildContainer();

        $result = $container->invoke(Invocation::callable(static fn () => 'hello'));

        $this->assertSame('hello', $result);
    }

    /**
     * - `invoke()` calls a method on an existing object and returns its return value.
     */
    #[Test]
    public function invokeMethodOnExistingObjectCallsMethod(): void
    {
        $container = $this->buildContainer();
        $object    = new ClassWithMethods();

        $result = $container->invoke(Invocation::method($object, 'callableMethod'));

        $this->assertFalse($result);
    }

    /**
     * - Invoking a constructor call on an uninitialized lazy proxy throws
     *   `InvalidInvocationException`, because calling the constructor on an already
     *   allocated lazy object is not permitted.
     */
    #[Test]
    public function invokeConstructorOnUninitializedProxyThrowsInvalidInvocationException(): void
    {
        $container = $this->buildContainer();
        $proxy     = $container->resolve(Resolution::for(ClassWithProperty::class)->lazily());

        $this->expectException(InvalidInvocationException::class);

        $container->invoke(Invocation::constructor($proxy));
    }
}
