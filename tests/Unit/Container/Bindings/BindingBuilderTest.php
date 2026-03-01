<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Closure;
use Engine\Container\Bindings\BindingBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\InterfaceImpl;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;
use Tests\Unit\Container\Fixtures\TestQualifier;

final class BindingBuilderTest extends TestCase
{
    #[Test]
    public function it_stores_scope_and_abstract(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);

        $this->assertSame('core', $builder->scope);
        $this->assertSame(SimpleInterface::class, $builder->abstract);
    }

    #[Test]
    public function it_has_sensible_defaults(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);

        $this->assertNull($builder->concrete);
        $this->assertNull($builder->instance);
        $this->assertNull($builder->name);
        $this->assertNull($builder->qualifier);
        $this->assertFalse($builder->lazily);
        $this->assertFalse($builder->liminal);
        $this->assertSame([], $builder->aliases);
        $this->assertNull($builder->factory);
    }

    #[Test]
    public function to_with_class_string_sets_concrete(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->to(InterfaceImpl::class);

        $this->assertSame(InterfaceImpl::class, $builder->concrete);
        $this->assertNull($builder->instance);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function to_with_object_sets_instance(): void
    {
        $object  = new InterfaceImpl();
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->to($object);

        $this->assertSame($object, $builder->instance);
        $this->assertNull($builder->concrete);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function named_sets_name_and_returns_self(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->named('primary');

        $this->assertSame('primary', $builder->name);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function qualified_by_sets_qualifier_and_returns_self(): void
    {
        $qualifier = new TestQualifier();
        $builder   = new BindingBuilder('core', SimpleInterface::class);
        $result    = $builder->qualifiedBy($qualifier);

        $this->assertSame($qualifier, $builder->qualifier);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function lazily_sets_flag_and_returns_self(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->lazily();

        $this->assertTrue($builder->lazily);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function liminal_sets_flag_and_returns_self(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->liminal();

        $this->assertTrue($builder->liminal);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function as_appends_alias_and_returns_self(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $result  = $builder->as(SimpleClass::class);

        $this->assertSame([SimpleClass::class], $builder->aliases);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function as_called_multiple_times_accumulates_aliases(): void
    {
        $builder = new BindingBuilder('core', SimpleInterface::class);
        $builder->as(SimpleClass::class);
        $builder->as(InterfaceImpl::class);

        $this->assertSame([SimpleClass::class, InterfaceImpl::class], $builder->aliases);
    }

    #[Test]
    public function using_sets_factory_as_closure(): void
    {
        $callable = static fn () => new InterfaceImpl();
        $builder  = new BindingBuilder('core', SimpleInterface::class);
        $result   = $builder->using($callable);

        $this->assertInstanceOf(Closure::class, $builder->factory);
        $this->assertSame($builder, $result);
    }

    #[Test]
    public function full_fluent_chain_works(): void
    {
        $qualifier = new TestQualifier();

        $builder = (new BindingBuilder('core', SimpleInterface::class))
            ->to(InterfaceImpl::class)
            ->named('primary')
            ->qualifiedBy($qualifier)
            ->lazily()
            ->liminal()
            ->as(SimpleClass::class)
            ->using(static fn () => new InterfaceImpl());

        $this->assertSame(InterfaceImpl::class, $builder->concrete);
        $this->assertSame('primary', $builder->name);
        $this->assertSame($qualifier, $builder->qualifier);
        $this->assertTrue($builder->lazily);
        $this->assertTrue($builder->liminal);
        $this->assertSame([SimpleClass::class], $builder->aliases);
        $this->assertInstanceOf(Closure::class, $builder->factory);
    }
}
