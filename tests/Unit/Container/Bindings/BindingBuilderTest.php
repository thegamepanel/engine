<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Engine\Container\Bindings\BindingBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\ClassWithMethods;
use Tests\Unit\Container\Fixtures\TestQualifier;

#[Group('unit'), Group('container'), Group('bindings')]
class BindingBuilderTest extends TestCase
{
    /**
     * - A new binding defaults to shared, eager, non-liminal, with no concrete,
     *   instance, factory, name, qualifier or aliases.
     */
    #[Test]
    public function constructorSetsDefaultsCorrectly(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);

        $this->assertSame('my-scope', $builder->scope);
        $this->assertSame(ClassWithMethods::class, $builder->abstract);
        $this->assertNull($builder->concrete);
        $this->assertNull($builder->instance);
        $this->assertEmpty($builder->aliases);
        $this->assertNull($builder->factory);
        $this->assertNull($builder->named);
        $this->assertNull($builder->qualifier);
        $this->assertFalse($builder->liminal);
        $this->assertFalse($builder->lazily);
        $this->assertTrue($builder->shared);
    }

    /**
     * - Binding to a class-string sets the concrete class without capturing a
     *   pre-built instance, and returns the same builder for fluent chaining.
     */
    #[Test]
    public function toWithClassStringMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->to(ClassWithMethods::class);

        $this->assertSame($builder, $result);
        $this->assertSame(ClassWithMethods::class, $builder->concrete);
        $this->assertNull($builder->instance);
    }

    /**
     * - Binding to a pre-built object captures both the concrete class and the
     *   object as an instance, so the container can return it directly.
     */
    #[Test]
    public function toWithObjectMutatesAndReturnsTheSameInstance(): void
    {
        $object  = new ClassWithMethods();
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->to($object);

        $this->assertSame($builder, $result);
        $this->assertSame(ClassWithMethods::class, $builder->concrete);
        $this->assertSame($object, $builder->instance);
    }

    /**
     * - Registering aliases allows the binding to be resolved under alternative
     *   class or interface names, and returns the same builder for fluent chaining.
     */
    #[Test]
    public function asMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->as(TestQualifier::class);

        $this->assertSame($builder, $result);
        $this->assertSame([TestQualifier::class], $builder->aliases);
    }

    /**
     * - Setting a factory closure overrides auto-wiring; the container will call the
     *   closure instead of reflecting the class, and returns the same builder.
     */
    #[Test]
    public function usingMutatesAndReturnsTheSameInstance(): void
    {
        $factory = static fn () => new ClassWithMethods();
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->using($factory);

        $this->assertSame($builder, $result);
        $this->assertSame($factory, $builder->factory);
    }

    /**
     * - Assigning a name scopes the binding to named lookups so it does not
     *   interfere with default resolution of the same type.
     */
    #[Test]
    public function namedMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->named('my-binding');

        $this->assertSame($builder, $result);
        $this->assertSame('my-binding', $builder->named);
    }

    /**
     * - Assigning a qualifier class-string restricts the binding to qualifier-based
     *   lookups, and returns the same builder for fluent chaining.
     */
    #[Test]
    public function qualifierMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->qualifier(TestQualifier::class);

        $this->assertSame($builder, $result);
        $this->assertSame(TestQualifier::class, $builder->qualifier);
    }

    /**
     * - Marking a binding as liminal means the resolved instance is held weakly and
     *   can be garbage collected when no strong references remain.
     */
    #[Test]
    public function liminalMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->liminal();

        $this->assertSame($builder, $result);
        $this->assertTrue($builder->liminal);
    }

    /**
     * - Marking a binding as lazy defers instantiation until the resolved proxy is
     *   first accessed.
     */
    #[Test]
    public function lazilyMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->lazily();

        $this->assertSame($builder, $result);
        $this->assertTrue($builder->lazily);
    }

    /**
     * - Marking a binding as not-shared means a new instance is created on each
     *   resolution rather than reusing a cached one.
     */
    #[Test]
    public function notSharedMutatesAndReturnsTheSameInstance(): void
    {
        $builder = new BindingBuilder('my-scope', ClassWithMethods::class);
        $result  = $builder->notShared();

        $this->assertSame($builder, $result);
        $this->assertFalse($builder->shared);
    }
}
