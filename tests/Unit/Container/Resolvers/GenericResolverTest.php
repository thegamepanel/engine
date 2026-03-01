<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Resolvers;

use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingRegistry;
use Engine\Container\Container;
use Engine\Container\Dependency;
use Engine\Container\Exceptions\DependencyResolutionException;
use Engine\Container\Resolvers\GenericResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Tests\Unit\Container\Fixtures\BothInterfacesImpl;
use Tests\Unit\Container\Fixtures\InterfaceImpl;
use Tests\Unit\Container\Fixtures\SecondInterface;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;

final class GenericResolverTest extends TestCase
{
    private GenericResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new GenericResolver();
    }

    // ── resolve() dispatch ───────────────────────────────────────

    #[Test]
    public function it_resolves_named_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (SimpleClass $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    #[Test]
    public function it_falls_back_to_default_when_no_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = new Dependency('param', null, hasDefault: true, default: 'fallback');

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame('fallback', $result);
    }

    #[Test]
    public function it_throws_when_no_type_and_no_default(): void
    {
        $container  = $this->makeContainer();
        $dependency = new Dependency('param', null);

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    // ── resolveType() — named type ───────────────────────────────

    #[Test]
    public function it_resolves_class_via_container(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (SimpleClass $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    #[Test]
    public function it_falls_back_to_default_for_non_class_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (string $v): void {}, hasDefault: true, default: 'hello');

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame('hello', $result);
    }

    #[Test]
    public function it_returns_null_for_nullable_non_class_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (?string $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_for_non_class_type_without_default(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (string $v): void {});

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    // ── resolveIntersectionType() ────────────────────────────────

    #[Test]
    public function it_resolves_intersection_when_binding_satisfies_all_types(): void
    {
        $binding   = new Binding(
            'core',
            SimpleInterface::class,
            concrete: BothInterfacesImpl::class,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(BothInterfacesImpl::class, $result);
    }

    #[Test]
    public function it_falls_back_to_default_for_intersection_with_no_bindings(): void
    {
        $container  = $this->makeContainer();
        $default    = new BothInterfacesImpl();
        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {},
            hasDefault: true,
            default:    $default,
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($default, $result);
    }

    #[Test]
    public function it_throws_for_intersection_with_no_bindings_and_no_default(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    #[Test]
    public function it_resolves_intersection_from_instance_binding(): void
    {
        $instance = new BothInterfacesImpl();
        $binding  = new Binding(
            'core',
            SimpleInterface::class,
            instance: $instance,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($instance, $result);
    }

    #[Test]
    public function it_resolves_intersection_from_factory_binding(): void
    {
        $instance = new BothInterfacesImpl();
        $binding  = new Binding(
            'core',
            SimpleInterface::class,
            factory: static fn () => $instance,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($instance, $result);
    }

    #[Test]
    public function it_skips_binding_not_satisfying_all_intersection_types(): void
    {
        // InterfaceImpl only implements SimpleInterface, not SecondInterface
        $badBinding  = new Binding(
            'core',
            SimpleInterface::class,
            concrete: InterfaceImpl::class,
        );
        // BothInterfacesImpl implements both
        $goodBinding = new Binding(
            'core',
            SecondInterface::class,
            concrete: BothInterfacesImpl::class,
        );
        $container   = $this->makeContainer([
            SimpleInterface::class => $badBinding,
            SecondInterface::class => $goodBinding,
        ]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(BothInterfacesImpl::class, $result);
    }

    #[Test]
    public function it_throws_when_no_binding_satisfies_all_intersection_types(): void
    {
        // InterfaceImpl only implements SimpleInterface
        $binding   = new Binding(
            'core',
            SimpleInterface::class,
            concrete: InterfaceImpl::class,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    // ── resolveUnionType() ───────────────────────────────────────

    #[Test]
    public function it_resolves_union_with_exactly_one_resolvable_type(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (SimpleClass|string $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    #[Test]
    public function it_falls_back_to_default_for_union_with_no_resolvable_types(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(
            static function (string|int $v): void {},
            hasDefault: true,
            default:    42,
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame(42, $result);
    }

    #[Test]
    public function it_falls_back_to_default_for_union_with_multiple_resolvable_types(): void
    {
        $container  = $this->makeContainer();
        $default    = new SimpleClass();
        $dependency = $this->makeDependency(
            static function (SimpleClass|SimpleInterface $v): void {},
            hasDefault: true,
            default:    $default,
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($default, $result);
    }

    #[Test]
    public function it_throws_for_union_with_no_resolvable_types_and_no_default(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (string|int $v): void {});

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    #[Test]
    public function it_returns_null_for_nullable_union_with_no_resolvable_types(): void
    {
        $container  = $this->makeContainer();
        $dependency = $this->makeDependency(static function (string|int|null $v): void {});

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertNull($result);
    }

    // ── Intersection edge cases ─────────────────────────────────

    #[Test]
    public function it_skips_intersection_binding_with_no_concrete_or_instance_or_factory(): void
    {
        // A binding with no concrete, instance, or factory is a "pass"
        $emptyBinding = new Binding('core', SimpleInterface::class);
        // This one has a concrete that satisfies both
        $goodBinding  = new Binding('core', SecondInterface::class, concrete: BothInterfacesImpl::class);
        $container    = $this->makeContainer([
            SimpleInterface::class => $emptyBinding,
            SecondInterface::class => $goodBinding,
        ]);

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(BothInterfacesImpl::class, $result);
    }

    #[Test]
    public function it_falls_back_to_default_after_all_intersection_bindings_tried(): void
    {
        // InterfaceImpl only satisfies SimpleInterface, not both
        $binding   = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);
        $default   = new BothInterfacesImpl();

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {},
            hasDefault: true,
            default:    $default,
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($default, $result);
    }

    #[Test]
    public function it_catches_invalid_class_exception_during_intersection_resolution(): void
    {
        // Bind to a class that doesn't exist — resolving it throws InvalidClassException
        $binding   = new Binding('core', SimpleInterface::class, concrete: 'NonExistent\\BadClass');
        $container = $this->makeContainer([SimpleInterface::class => $binding]);
        $default   = new BothInterfacesImpl();

        $dependency = $this->makeDependency(
            static function (SimpleInterface&SecondInterface $v): void {},
            hasDefault: true,
            default:    $default,
        );

        // Should catch the InvalidClassException and fall through to default
        $result = $this->resolver->resolve($dependency, $container);

        $this->assertSame($default, $result);
    }

    // ── Union with intersection subtype ──────────────────────────

    #[Test]
    public function it_resolves_union_containing_intersection_subtype(): void
    {
        $binding   = new Binding('core', SimpleInterface::class, concrete: BothInterfacesImpl::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        // Union of (intersection) | scalar — one resolvable intersection type
        $dependency = $this->makeDependency(
            static function ((SimpleInterface&SecondInterface)|string $v): void {}
        );

        $result = $this->resolver->resolve($dependency, $container);

        $this->assertInstanceOf(BothInterfacesImpl::class, $result);
    }

    #[Test]
    public function it_wraps_intersection_error_in_union_exception(): void
    {
        // Union with one resolvable intersection, but the intersection fails
        $container = $this->makeContainer();

        $dependency = $this->makeDependency(
            static function ((SimpleInterface&SecondInterface)|string $v): void {}
        );

        $this->expectException(DependencyResolutionException::class);

        $this->resolver->resolve($dependency, $container);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * @param array<class-string, Binding<*>> $bindings
     */
    private function makeContainer(array $bindings = []): Container
    {
        $registry = new BindingRegistry($bindings);

        return new Container($registry, [
            // Register the GenericResolver as the default
        ], $this->resolver);
    }

    private function makeDependency(
        \Closure $fn,
        bool     $hasDefault = false,
        mixed    $default = null,
    ): Dependency
    {
        $ref   = new ReflectionFunction($fn);
        $param = $ref->getParameters()[0];
        $type  = $param->getType();

        return new Dependency(
            parameter:  $param->getName(),
            type:       $type,
            optional:   $param->isOptional(),
            hasDefault: $hasDefault || $param->isDefaultValueAvailable(),
            default:    $hasDefault ? $default : ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null),
        );
    }
}
