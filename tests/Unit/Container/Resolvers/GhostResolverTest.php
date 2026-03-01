<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Resolvers;

use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingRegistry;
use Engine\Container\Container;
use Engine\Container\Dependency;
use Engine\Container\Exceptions\DependencyResolutionException;
use Engine\Container\Exceptions\InvalidInvocationException;
use Engine\Container\Resolvers\GenericResolver;
use Engine\Container\Resolvers\GhostResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use Tests\Unit\Container\Fixtures\ClassWithScalarDefaults;
use Tests\Unit\Container\Fixtures\InterfaceImpl;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;

final class GhostResolverTest extends TestCase
{
    private GhostResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new GhostResolver();
    }

    #[Test]
    public function it_creates_lazy_ghost_object(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (ClassWithScalarDefaults $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(ClassWithScalarDefaults::class, $result);

        // Verify it's a lazy ghost (uninitialised until accessed)
        $reflector = new ReflectionClass($result);
        $this->assertTrue($reflector->isUninitializedLazyObject($result));
    }

    #[Test]
    public function ghost_of_class_without_properties_is_immediately_initialised(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (SimpleClass $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(SimpleClass::class, $result);

        // Classes with no properties are considered immediately initialised
        // because there are no properties to trigger lazy initialisation
        $reflector = new ReflectionClass($result);
        $this->assertFalse($reflector->isUninitializedLazyObject($result));
    }

    #[Test]
    public function ghost_constructor_invocation_throws_during_initialisation(): void
    {
        // When a ghost with a constructor is accessed, the initialiser calls
        // Container::invoke() which checks isUninitializedLazyObject(). During
        // ghost initialisation this returns false, causing an exception.
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (ClassWithScalarDefaults $v): void {});

        $ghost = $this->resolver->resolve($dependency, $container);

        $this->expectException(InvalidInvocationException::class);

        // Accessing a property triggers the ghost initialiser
        $ghost->name;
    }

    #[Test]
    public function it_throws_for_non_class_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (string $v): void {});

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    #[Test]
    public function it_throws_when_type_is_null(): void
    {
        $container  = $this->makeContainer();
        $dependency = new Dependency('param', null);

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    #[Test]
    public function it_uses_concrete_from_binding(): void
    {
        $binding   = new Binding(
            'core',
            SimpleInterface::class,
            concrete: InterfaceImpl::class,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $dependency = $this->makeDependency(static function (SimpleInterface $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        // The ghost should be an InterfaceImpl (the concrete), not the interface
        $reflector = new ReflectionClass($result);

        // Force initialisation
        $this->assertInstanceOf(InterfaceImpl::class, $result);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * @param array<class-string, Binding<*>> $bindings
     */
    private function makeContainer(array $bindings = []): Container
    {
        $registry        = new BindingRegistry($bindings);
        $genericResolver = new GenericResolver();

        return new Container($registry, [], $genericResolver);
    }

    private function makeDependency(\Closure $fn): Dependency
    {
        $ref   = new ReflectionFunction($fn);
        $param = $ref->getParameters()[0];
        $type  = $param->getType();

        return new Dependency(
            parameter: $param->getName(),
            type:      $type,
        );
    }
}
