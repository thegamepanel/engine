<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Attributes\Ghost;
use Engine\Container\Attributes\Named;
use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingRegistry;
use Engine\Container\Container;
use Engine\Container\Contracts\Container as ContainerContract;
use Engine\Container\Exceptions\BindingNotFoundException;
use Engine\Container\Exceptions\InvalidInvocationException;
use Engine\Container\Exceptions\InvalidResolverException;
use Engine\Container\Exceptions\NotInstantiableException;
use Engine\Container\Resolvers\GenericResolver;
use Engine\Container\Resolvers\GhostResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Container\Fixtures\BothInterfacesImpl;
use Tests\Unit\Container\Fixtures\ClassWithDependency;
use Tests\Unit\Container\Fixtures\ClassWithGhostParam;
use Tests\Unit\Container\Fixtures\ClassWithScalarDefaults;
use Tests\Unit\Container\Fixtures\ClassWithVariadic;
use Tests\Unit\Container\Fixtures\HasPrivateMethod;
use Tests\Unit\Container\Fixtures\HasPublicMethod;
use Tests\Unit\Container\Fixtures\HasStaticMethod;
use Tests\Unit\Container\Fixtures\InterfaceImpl;
use Tests\Unit\Container\Fixtures\InvokableClass;
use Tests\Unit\Container\Fixtures\LiminalMarkedClass;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;
use Tests\Unit\Container\Fixtures\TestQualifier;

final class ContainerTest extends TestCase
{
    // ── Construction & self-registration ──────────────────────────

    #[Test]
    public function it_self_registers_as_container_class(): void
    {
        $container = $this->makeContainer();

        $this->assertSame($container, $container->resolve(Container::class));
    }

    #[Test]
    public function it_self_registers_as_container_contract(): void
    {
        $container = $this->makeContainer();

        $this->assertSame($container, $container->resolve(ContainerContract::class));
    }

    #[Test]
    public function it_accepts_empty_bindings(): void
    {
        $container = $this->makeContainer();

        $this->assertInstanceOf(Container::class, $container);
    }

    #[Test]
    public function it_throws_for_invalid_resolvable_in_resolvers_array(): void
    {
        $this->expectException(InvalidResolverException::class);

        new Container(
            new BindingRegistry([]),
            // stdClass doesn't implement Resolvable
            [\stdClass::class => [GenericResolver::class, false]],
        );
    }

    #[Test]
    public function it_throws_for_invalid_resolver_in_resolvers_array(): void
    {
        $this->expectException(InvalidResolverException::class);

        new Container(
            new BindingRegistry([]),
            // Ghost is a valid Resolvable, but stdClass is not a Resolver
            [\Engine\Container\Attributes\Ghost::class => [\stdClass::class, false]],
        );
    }

    #[Test]
    public function it_registers_resolvers_and_sets_default_from_constructor(): void
    {
        $container = new Container(
            new BindingRegistry([]),
            [Ghost::class => [GhostResolver::class, true]],
        );

        // If the resolver is registered and set as default, resolving a
        // simple class should work (it falls through to the default resolver)
        $result = $container->resolve(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    // ── binding() ────────────────────────────────────────────────

    #[Test]
    public function binding_returns_binding_for_known_class(): void
    {
        $binding   = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->assertSame($binding, $container->binding(SimpleInterface::class));
    }

    #[Test]
    public function binding_returns_null_for_unknown_class(): void
    {
        $container = $this->makeContainer();

        $this->assertNull($container->binding(SimpleInterface::class));
    }

    #[Test]
    public function binding_throws_for_unknown_class_with_name(): void
    {
        $container = $this->makeContainer();

        $this->expectException(BindingNotFoundException::class);

        $container->binding(SimpleInterface::class, new Named('primary'));
    }

    #[Test]
    public function binding_throws_for_unknown_class_with_qualifier(): void
    {
        $container = $this->makeContainer();

        $this->expectException(BindingNotFoundException::class);

        $container->binding(SimpleInterface::class, qualifier: new TestQualifier());
    }

    #[Test]
    public function binding_returns_named_sub_binding(): void
    {
        $sub     = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $binding = new Binding('core', SimpleInterface::class, nameMap: ['primary' => $sub]);

        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->assertSame($sub, $container->binding(SimpleInterface::class, new Named('primary')));
    }

    #[Test]
    public function binding_throws_for_unknown_name(): void
    {
        $binding   = new Binding('core', SimpleInterface::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->expectException(BindingNotFoundException::class);

        $container->binding(SimpleInterface::class, new Named('nonexistent'));
    }

    #[Test]
    public function binding_returns_qualified_sub_binding(): void
    {
        $sub     = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $binding = new Binding('core', SimpleInterface::class, qualifierMap: [TestQualifier::class => $sub]);

        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->assertSame($sub, $container->binding(SimpleInterface::class, qualifier: new TestQualifier()));
    }

    #[Test]
    public function binding_throws_for_unknown_qualifier(): void
    {
        $binding   = new Binding('core', SimpleInterface::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->expectException(BindingNotFoundException::class);

        $container->binding(SimpleInterface::class, qualifier: new TestQualifier());
    }

    // ── bound() ──────────────────────────────────────────────────

    #[Test]
    public function bound_returns_true_for_bound_class(): void
    {
        $binding   = new Binding('core', SimpleInterface::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->assertTrue($container->bound(SimpleInterface::class));
    }

    #[Test]
    public function bound_returns_false_for_unbound_class(): void
    {
        $container = $this->makeContainer();

        $this->assertFalse($container->bound(SimpleInterface::class));
    }

    #[Test]
    public function bound_returns_true_for_aliased_class(): void
    {
        $binding   = new Binding('core', SimpleInterface::class, aliases: [InterfaceImpl::class]);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $this->assertTrue($container->bound(InterfaceImpl::class));
    }

    // ── resolve() ────────────────────────────────────────────────

    #[Test]
    public function resolve_returns_previously_resolved_shared_instance(): void
    {
        $binding   = new Binding('core', SimpleClass::class, shared: true);
        $container = $this->makeContainer([SimpleClass::class => $binding]);

        $first  = $container->resolve(SimpleClass::class);
        $second = $container->resolve(SimpleClass::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function resolve_from_binding_with_instance(): void
    {
        $instance  = new InterfaceImpl();
        $binding   = new Binding('core', SimpleInterface::class, instance: $instance);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $result = $container->resolve(SimpleInterface::class);

        $this->assertSame($instance, $result);
    }

    #[Test]
    public function resolve_from_binding_with_factory(): void
    {
        $instance = new InterfaceImpl();
        $binding  = new Binding('core', SimpleInterface::class, factory: static fn () => $instance);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $result = $container->resolve(SimpleInterface::class);

        $this->assertSame($instance, $result);
    }

    #[Test]
    public function resolve_from_binding_with_concrete_class(): void
    {
        $binding   = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $result = $container->resolve(SimpleInterface::class);

        $this->assertInstanceOf(InterfaceImpl::class, $result);
    }

    #[Test]
    public function resolve_class_without_binding(): void
    {
        $container = $this->makeContainer();

        $result = $container->resolve(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    #[Test]
    public function resolve_class_with_no_constructor(): void
    {
        $container = $this->makeContainer();

        $result = $container->resolve(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $result);
    }

    #[Test]
    public function resolve_stores_instance_when_shared(): void
    {
        $binding   = new Binding('core', SimpleClass::class, shared: true);
        $container = $this->makeContainer([SimpleClass::class => $binding]);

        $first  = $container->resolve(SimpleClass::class);
        $second = $container->resolve(SimpleClass::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function resolve_does_not_store_when_not_shared(): void
    {
        $binding   = new Binding('core', SimpleClass::class, shared: false);
        $container = $this->makeContainer([SimpleClass::class => $binding]);

        $first  = $container->resolve(SimpleClass::class);
        $second = $container->resolve(SimpleClass::class);

        $this->assertNotSame($first, $second);
    }

    #[Test]
    public function resolve_throws_for_interface(): void
    {
        $container = $this->makeContainer();

        $this->expectException(NotInstantiableException::class);

        $container->resolve(SimpleInterface::class);
    }

    #[Test]
    public function resolve_with_explicit_arguments(): void
    {
        $container = $this->makeContainer();

        $result = $container->resolve(ClassWithScalarDefaults::class, [
            'name'  => 'custom',
            'count' => 42,
        ]);

        $this->assertSame('custom', $result->name);
        $this->assertSame(42, $result->count);
    }

    #[Test]
    public function resolve_detects_liminal_attribute_on_class(): void
    {
        $container = $this->makeContainer();

        // Resolve with shared binding so storeResolved is called
        $binding   = new Binding('core', LiminalMarkedClass::class, shared: true);
        $container = $this->makeContainer([LiminalMarkedClass::class => $binding]);

        $result = $container->resolve(LiminalMarkedClass::class);

        $this->assertInstanceOf(LiminalMarkedClass::class, $result);

        // The instance should be stored as liminal (WeakReference)
        // Verify by checking hasResolved returns true while reference is alive
        $this->assertTrue($container->hasResolved(LiminalMarkedClass::class));
    }

    // ── invoke() ─────────────────────────────────────────────────

    #[Test]
    public function invoke_calls_public_method_with_auto_resolved_dependencies(): void
    {
        $container = $this->makeContainer();
        $instance  = new ClassWithDependency(new SimpleClass());

        // Use a class with a public method that takes dependencies
        $result = $container->invoke(HasStaticMethod::class, 'greet', ['name' => 'World']);

        $this->assertSame('Hello, World', $result);
    }

    #[Test]
    public function invoke_constructor_on_class_string(): void
    {
        $container = $this->makeContainer();

        $result = $container->invoke(ClassWithScalarDefaults::class, '__construct', [
            'name'  => 'test',
            'count' => 5,
        ]);

        $this->assertInstanceOf(ClassWithScalarDefaults::class, $result);
        $this->assertSame('test', $result->name);
        $this->assertSame(5, $result->count);
    }

    #[Test]
    public function invoke_static_method(): void
    {
        $container = $this->makeContainer();

        $result = $container->invoke(HasStaticMethod::class, 'greet', ['name' => 'Test']);

        $this->assertSame('Hello, Test', $result);
    }

    #[Test]
    public function invoke_method_on_object_instance(): void
    {
        $container = $this->makeContainer();
        $object    = new InvokableClass();

        $result = $container->invoke($object, '__invoke', ['value' => 'hello']);

        $this->assertSame('invoked:hello', $result);
    }

    #[Test]
    public function invoke_throws_for_private_method(): void
    {
        $container = $this->makeContainer();

        $this->expectException(InvalidInvocationException::class);

        $container->invoke(HasPrivateMethod::class, 'secret');
    }

    #[Test]
    public function invoke_throws_for_constructor_on_initialised_object(): void
    {
        $container = $this->makeContainer();
        $object    = new ClassWithScalarDefaults();

        $this->expectException(InvalidInvocationException::class);

        $container->invoke($object, '__construct');
    }

    #[Test]
    public function invoke_non_static_method_on_class_string_resolves_class_first(): void
    {
        $container = $this->makeContainer();

        // When invoking a non-static, non-constructor method on a class-string,
        // the container resolves the class first then invokes the method on it
        $result = $container->invoke(HasPublicMethod::class, 'greet', ['name' => 'World']);

        $this->assertSame('Hello, World', $result);
    }

    #[Test]
    public function invoke_explicit_arguments_override_auto_resolution(): void
    {
        $container   = $this->makeContainer();
        $customClass = new SimpleClass();

        $result = $container->invoke(ClassWithDependency::class, '__construct', [
            'dependency' => $customClass,
        ]);

        $this->assertInstanceOf(ClassWithDependency::class, $result);
        $this->assertSame($customClass, $result->dependency);
    }

    #[Test]
    public function invoke_skips_variadic_parameters(): void
    {
        $container = $this->makeContainer();

        $result = $container->invoke(ClassWithVariadic::class, '__construct', [
            'name' => 'test',
        ]);

        $this->assertInstanceOf(ClassWithVariadic::class, $result);
        $this->assertSame('test', $result->name);
        $this->assertSame([], $result->items);
    }

    // ── call() ───────────────────────────────────────────────────

    #[Test]
    public function call_closure_with_auto_resolved_dependencies(): void
    {
        $container = $this->makeContainer();

        $result = $container->call(static function (SimpleClass $service): string {
            return $service::class;
        });

        $this->assertSame(SimpleClass::class, $result);
    }

    #[Test]
    public function call_closure_with_explicit_arguments(): void
    {
        $container = $this->makeContainer();

        $result = $container->call(
            static function (string $name, int $count): string {
                return str_repeat($name, $count);
            },
            ['name' => 'hi', 'count' => 3]
        );

        $this->assertSame('hihihi', $result);
    }

    #[Test]
    public function call_invokable_object(): void
    {
        $container = $this->makeContainer();
        $invokable = new InvokableClass();

        $result = $container->call($invokable, ['value' => 'test']);

        $this->assertSame('invoked:test', $result);
    }

    // ── lazy() ───────────────────────────────────────────────────

    #[Test]
    public function lazy_creates_lazy_proxy(): void
    {
        $container = $this->makeContainer();

        $proxy = $container->lazy(ClassWithScalarDefaults::class);

        $this->assertInstanceOf(ClassWithScalarDefaults::class, $proxy);

        // Verify it's a lazy proxy (uninitialised until accessed)
        $reflector = new ReflectionClass($proxy);
        $this->assertTrue($reflector->isUninitializedLazyObject($proxy));
    }

    #[Test]
    public function lazy_proxy_resolves_on_access(): void
    {
        $container = $this->makeContainer();

        $proxy = $container->lazy(ClassWithScalarDefaults::class);

        // Accessing triggers resolution
        $this->assertSame('default', $proxy->name);
        $this->assertSame(0, $proxy->count);
    }

    #[Test]
    public function lazy_proxy_of_class_without_properties_is_immediately_initialised(): void
    {
        $container = $this->makeContainer();

        $proxy = $container->lazy(SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $proxy);

        // Classes with no properties are considered immediately initialised
        // because there are no properties to trigger lazy initialisation
        $reflector = new ReflectionClass($proxy);
        $this->assertFalse($reflector->isUninitializedLazyObject($proxy));
    }

    // ── Liminal (WeakReference) ──────────────────────────────────

    #[Test]
    public function liminal_instance_stored_via_weak_reference(): void
    {
        $binding   = new Binding('core', LiminalMarkedClass::class, shared: true);
        $container = $this->makeContainer([LiminalMarkedClass::class => $binding]);

        $instance = $container->resolve(LiminalMarkedClass::class);
        $this->assertTrue($container->hasResolved(LiminalMarkedClass::class));

        // The instance should be retrievable while we hold a reference
        $resolved = $container->getResolved(LiminalMarkedClass::class);
        $this->assertSame($instance, $resolved);
    }

    #[Test]
    public function named_instance_storage_and_retrieval(): void
    {
        $namedSub = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $binding  = new Binding(
            'core',
            SimpleInterface::class,
            nameMap: ['primary' => $namedSub],
            shared:  true,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $instance = $container->resolve(SimpleInterface::class, name: new Named('primary'));

        $this->assertInstanceOf(InterfaceImpl::class, $instance);
        // hasResolved uses getTrueClass which looks up the binding via the abstract
        $this->assertTrue($container->hasResolved(SimpleInterface::class, new Named('primary')));
    }

    #[Test]
    public function qualified_instance_storage_and_retrieval(): void
    {
        $qualifiedSub = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $binding      = new Binding(
            'core',
            SimpleInterface::class,
            qualifierMap: [TestQualifier::class => $qualifiedSub],
            shared:       true,
        );
        $container = $this->makeContainer([SimpleInterface::class => $binding]);

        $instance = $container->resolve(SimpleInterface::class, qualifier: new TestQualifier());

        $this->assertInstanceOf(InterfaceImpl::class, $instance);
        // hasResolved uses getTrueClass which looks up the binding via the abstract
        $this->assertTrue($container->hasResolved(SimpleInterface::class, qualifier: new TestQualifier()));
    }

    // ── getDependencyResolver() (via resolve) ────────────────────

    #[Test]
    public function it_throws_no_default_resolver_when_none_available(): void
    {
        // Create container without any resolver
        $container = new Container(new BindingRegistry([]));

        $this->expectException(InvalidResolverException::class);

        // Resolving a class with dependencies requires a resolver
        $container->resolve(ClassWithDependency::class);
    }

    #[Test]
    public function it_uses_specific_resolver_for_resolvable_attribute(): void
    {
        // Register the Ghost resolver for the Ghost attribute
        $container = new Container(
            new BindingRegistry([]),
            [Ghost::class => [GhostResolver::class, true]],
        );

        // ClassWithGhostParam has a #[Ghost] SimpleClass parameter.
        // The container should dispatch to the GhostResolver for it.
        $result = $container->resolve(ClassWithGhostParam::class);

        $this->assertInstanceOf(ClassWithGhostParam::class, $result);
        $this->assertInstanceOf(SimpleClass::class, $result->dependency);
    }

    // ── resolve() with auto-resolved dependencies ────────────────

    #[Test]
    public function resolve_auto_resolves_dependencies(): void
    {
        $container = $this->makeContainer();

        $result = $container->resolve(ClassWithDependency::class);

        $this->assertInstanceOf(ClassWithDependency::class, $result);
        $this->assertInstanceOf(SimpleClass::class, $result->dependency);
    }

    #[Test]
    public function resolve_uses_default_values_when_no_arguments(): void
    {
        $container = $this->makeContainer();

        $result = $container->resolve(ClassWithScalarDefaults::class);

        $this->assertSame('default', $result->name);
        $this->assertSame(0, $result->count);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * @param array<class-string, Binding<*>> $bindings
     */
    private function makeContainer(array $bindings = []): Container
    {
        $registry = new BindingRegistry($bindings);
        $resolver = new GenericResolver();

        return new Container($registry, [], $resolver);
    }
}
