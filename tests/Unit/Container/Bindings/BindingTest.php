<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\ClassWithMethods;
use Tests\Unit\Container\Fixtures\ConcreteClass;
use Tests\Unit\Container\Fixtures\TestQualifier;

#[Group('unit'), Group('container'), Group('bindings')]
class BindingTest extends TestCase
{
    /**
     * - A bare binding defaults to shared, eager, non-liminal, with no concrete,
     *   instance, factory, aliases, named children or qualified children.
     */
    #[Test]
    public function constructorSetsDefaultsCorrectly(): void
    {
        $binding = new Binding(ClassWithMethods::class);

        $this->assertSame(ClassWithMethods::class, $binding->abstract);
        $this->assertNull($binding->concrete);
        $this->assertNull($binding->instance);
        $this->assertEmpty($binding->aliases);
        $this->assertNull($binding->factory);
        $this->assertEmpty($binding->namedMap);
        $this->assertEmpty($binding->qualifiedMap);
        $this->assertFalse($binding->liminal, 'Bindings should not be liminal by default.');
        $this->assertFalse($binding->lazily, 'Bindings should be eager by default.');
        $this->assertTrue($binding->shared, 'Bindings should be shared by default.');
    }

    /**
     * - Passing an instance directly to the constructor does not automatically derive
     *   or set the concrete class; concrete must be set explicitly.
     */
    #[Test]
    public function constructorDoesNotDerivesConcreteFromInstance(): void
    {
        $binding = new Binding(ClassWithMethods::class, instance: new ClassWithMethods());

        $this->assertNull($binding->concrete);
    }

    /**
     * - Building from a class-string binding transfers the concrete class and
     *   automatically includes it as an alias so it can be resolved under either name.
     */
    #[Test]
    public function fromBuilderWithClassStringBinding(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)
            ->to(ClassWithMethods::class)
        ;

        $binding = Binding::from($builder);

        $this->assertSame(ClassWithMethods::class, $binding->abstract);
        $this->assertSame(ClassWithMethods::class, $binding->concrete);
        $this->assertNull($binding->instance);
        $this->assertContains(ClassWithMethods::class, $binding->aliases);
    }

    /**
     * - Building from an object binding transfers both the concrete class and the
     *   pre-built instance so the container can return it directly without instantiation.
     */
    #[Test]
    public function fromBuilderWithObjectBindingPassesThroughBothConcreteAndInstance(): void
    {
        $object  = new ConcreteClass();
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)->to($object);

        $binding = Binding::from($builder);

        $this->assertSame(ConcreteClass::class, $binding->concrete);
        $this->assertSame($object, $binding->instance);
    }

    /**
     * - When a concrete class is set, it is prepended to the alias list alongside any
     *   explicitly registered aliases so resolution works under all registered names.
     */
    #[Test]
    public function fromBuilderIncludesConcreteInAliasesWhenConcreteIsSet(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)
            ->to(ClassWithMethods::class)
            ->as(TestQualifier::class)
        ;

        $binding = Binding::from($builder);

        $this->assertContains(ClassWithMethods::class, $binding->aliases);
        $this->assertContains(TestQualifier::class, $binding->aliases);
    }

    /**
     * - When no concrete class is set, only the explicitly registered aliases are
     *   included; no extra entries are injected.
     */
    #[Test]
    public function fromBuilderDoesNotModifyAliasesWhenConcreteIsNotSet(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)
            ->as(TestQualifier::class)
        ;

        $binding = Binding::from($builder);

        $this->assertSame([TestQualifier::class], $binding->aliases);
    }

    /**
     * - Building from a factory binding transfers the closure so the container
     *   calls it instead of reflecting and auto-wiring the class.
     */
    #[Test]
    public function fromBuilderWithFactory(): void
    {
        $factory = static fn () => new ClassWithMethods();
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)
            ->using($factory)
        ;

        $binding = Binding::from($builder);

        $this->assertSame($factory, $binding->factory);
    }

    /**
     * - Named child bindings passed to `from()` are stored under their name key,
     *   making them available for named lookup on the parent binding.
     */
    #[Test]
    public function fromBuilderWithNamedBindings(): void
    {
        $named   = new Binding(ClassWithMethods::class);
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);

        $binding = Binding::from($builder, ['my-name' => $named]);

        $this->assertSame(['my-name' => $named], $binding->namedMap);
    }

    /**
     * - Qualified child bindings passed to `from()` are stored under their qualifier
     *   class key, making them available for qualifier-based lookup on the parent binding.
     */
    #[Test]
    public function fromBuilderWithQualifiedBindings(): void
    {
        $qualified = new Binding(ClassWithMethods::class);
        $builder   = new BindingBuilder('my-scope', ClassWithMethods::class);

        $binding = Binding::from($builder, [], [TestQualifier::class => $qualified]);

        $this->assertSame([TestQualifier::class => $qualified], $binding->qualifiedMap);
    }

    /**
     * - The liminal flag is preserved when building from a builder, so instances
     *   resolved from this binding will be held weakly.
     */
    #[Test]
    public function fromBuilderTransfersLiminalFlag(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)->liminal();

        $this->assertTrue(Binding::from($builder)->liminal);
    }

    /**
     * - The lazily flag is preserved when building from a builder, so resolution
     *   will be deferred until the proxy is first accessed.
     */
    #[Test]
    public function fromBuilderTransfersLazilyFlag(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)->lazily();

        $this->assertTrue(Binding::from($builder)->lazily);
    }

    /**
     * - The shared flag is preserved when building from a builder, so a non-shared
     *   binding produces a new instance on each resolution.
     */
    #[Test]
    public function fromBuilderTransfersSharedFlag(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class)->notShared();

        $this->assertFalse(Binding::from($builder)->shared);
    }

    /**
     * - A binding that holds a pre-built instance reports itself as bound to an
     *   instance, signalling the container to return it directly.
     */
    #[Test]
    public function isBoundToInstanceReturnsTrueWhenInstanceIsSet(): void
    {
        $binding = new Binding(ClassWithMethods::class, instance: new ClassWithMethods());

        $this->assertTrue($binding->isBoundToInstance());
    }

    /**
     * - A binding without a pre-built instance reports it is not bound to an instance,
     *   so the container will instantiate or invoke a factory instead.
     */
    #[Test]
    public function isBoundToInstanceReturnsFalseWhenInstanceIsNotSet(): void
    {
        $binding = new Binding(ClassWithMethods::class);

        $this->assertFalse($binding->isBoundToInstance());
    }

    /**
     * - A binding with a factory closure reports that a factory is available,
     *   so the container knows to call it instead of auto-wiring.
     */
    #[Test]
    public function hasFactoryReturnsTrueWhenFactoryIsSet(): void
    {
        $binding = new Binding(ClassWithMethods::class, factory: static fn () => new ClassWithMethods());

        $this->assertTrue($binding->hasFactory());
    }

    /**
     * - A binding without a factory closure reports no factory is available,
     *   so the container falls back to auto-wiring.
     */
    #[Test]
    public function hasFactoryReturnsFalseWhenFactoryIsNotSet(): void
    {
        $binding = new Binding(ClassWithMethods::class);

        $this->assertFalse($binding->hasFactory());
    }
}
