<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Engine\Container\Bindings\Binding;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;
use Tests\Unit\Container\Fixtures\TestQualifier;

final class BindingTest extends TestCase
{
    #[Test]
    public function it_stores_all_properties(): void
    {
        $instance = new SimpleClass();
        $factory  = static fn () => new SimpleClass();
        $sub      = new Binding('core', SimpleClass::class);

        $binding = new Binding(
            scope:        'core',
            abstract:     SimpleInterface::class,
            concrete:     SimpleClass::class,
            instance:     $instance,
            nameMap:      ['primary' => $sub],
            qualifierMap: [TestQualifier::class => $sub],
            aliases:      [SimpleClass::class],
            shared:       false,
            lazily:       true,
            liminal:      true,
            factory:      $factory,
        );

        $this->assertSame('core', $binding->scope);
        $this->assertSame(SimpleInterface::class, $binding->abstract);
        $this->assertSame(SimpleClass::class, $binding->concrete);
        $this->assertSame($instance, $binding->instance);
        $this->assertSame(['primary' => $sub], $binding->nameMap);
        $this->assertSame([TestQualifier::class => $sub], $binding->qualifierMap);
        $this->assertSame([SimpleClass::class], $binding->aliases);
        $this->assertFalse($binding->shared);
        $this->assertTrue($binding->lazily);
        $this->assertTrue($binding->liminal);
        $this->assertSame($factory, $binding->factory);
    }

    #[Test]
    public function default_shared_is_true(): void
    {
        $binding = new Binding('core', SimpleClass::class);

        $this->assertTrue($binding->shared);
    }

    #[Test]
    public function is_bound_to_instance_returns_true_when_instance_set(): void
    {
        $binding = new Binding('core', SimpleClass::class, instance: new SimpleClass());

        $this->assertTrue($binding->isBoundToInstance());
    }

    #[Test]
    public function is_bound_to_instance_returns_false_when_no_instance(): void
    {
        $binding = new Binding('core', SimpleClass::class);

        $this->assertFalse($binding->isBoundToInstance());
    }

    #[Test]
    public function is_shared_returns_true_when_shared(): void
    {
        $binding = new Binding('core', SimpleClass::class, shared: true);

        $this->assertTrue($binding->isShared());
    }

    #[Test]
    public function is_shared_returns_false_when_not_shared(): void
    {
        $binding = new Binding('core', SimpleClass::class, shared: false);

        $this->assertFalse($binding->isShared());
    }

    #[Test]
    public function should_resolve_lazily_returns_true_when_lazily(): void
    {
        $binding = new Binding('core', SimpleClass::class, lazily: true);

        $this->assertTrue($binding->shouldResolveLazily());
    }

    #[Test]
    public function should_resolve_lazily_returns_false_when_not_lazily(): void
    {
        $binding = new Binding('core', SimpleClass::class, lazily: false);

        $this->assertFalse($binding->shouldResolveLazily());
    }

    #[Test]
    public function has_factory_returns_true_when_factory_set(): void
    {
        $binding = new Binding('core', SimpleClass::class, factory: static fn () => new SimpleClass());

        $this->assertTrue($binding->hasFactory());
    }

    #[Test]
    public function has_factory_returns_false_when_no_factory(): void
    {
        $binding = new Binding('core', SimpleClass::class);

        $this->assertFalse($binding->hasFactory());
    }

    #[Test]
    public function by_name_returns_sub_binding_when_exists(): void
    {
        $sub     = new Binding('core', SimpleClass::class);
        $binding = new Binding('core', SimpleClass::class, nameMap: ['primary' => $sub]);

        $this->assertSame($sub, $binding->byName('primary'));
    }

    #[Test]
    public function by_name_returns_null_when_not_exists(): void
    {
        $binding = new Binding('core', SimpleClass::class);

        $this->assertNull($binding->byName('nonexistent'));
    }

    #[Test]
    public function for_qualifier_returns_sub_binding_when_exists(): void
    {
        $sub     = new Binding('core', SimpleClass::class);
        $binding = new Binding('core', SimpleClass::class, qualifierMap: [TestQualifier::class => $sub]);

        $this->assertSame($sub, $binding->forQualifier(TestQualifier::class));
    }

    #[Test]
    public function for_qualifier_returns_null_when_not_exists(): void
    {
        $binding = new Binding('core', SimpleClass::class);

        $this->assertNull($binding->forQualifier(TestQualifier::class));
    }
}
