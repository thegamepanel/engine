<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\InterfaceImpl;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;

final class BindingRegistryTest extends TestCase
{
    #[Test]
    public function it_stores_bindings(): void
    {
        $binding  = new Binding('core', SimpleInterface::class, concrete: InterfaceImpl::class);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertSame([SimpleInterface::class => $binding], $registry->bindings);
    }

    #[Test]
    public function it_groups_bindings_by_scope(): void
    {
        $binding1 = new Binding('core', SimpleInterface::class);
        $binding2 = new Binding('module', SimpleClass::class);
        $registry = new BindingRegistry([
            SimpleInterface::class => $binding1,
            SimpleClass::class     => $binding2,
        ]);

        $this->assertArrayHasKey('core', $registry->scopedBindings);
        $this->assertArrayHasKey('module', $registry->scopedBindings);
        $this->assertSame([$binding1], $registry->scopedBindings['core']);
        $this->assertSame([$binding2], $registry->scopedBindings['module']);
    }

    #[Test]
    public function it_collects_aliases(): void
    {
        $binding  = new Binding('core', SimpleInterface::class, aliases: [InterfaceImpl::class]);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertSame([InterfaceImpl::class => SimpleInterface::class], $registry->aliases);
    }

    #[Test]
    public function get_returns_binding_by_class(): void
    {
        $binding  = new Binding('core', SimpleInterface::class);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertSame($binding, $registry->get(SimpleInterface::class));
    }

    #[Test]
    public function get_returns_binding_via_alias(): void
    {
        $binding  = new Binding('core', SimpleInterface::class, aliases: [InterfaceImpl::class]);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertSame($binding, $registry->get(InterfaceImpl::class));
    }

    #[Test]
    public function get_returns_null_for_unknown_class(): void
    {
        $registry = new BindingRegistry([]);

        $this->assertNull($registry->get(SimpleInterface::class));
    }

    #[Test]
    public function has_returns_true_for_direct_binding(): void
    {
        $binding  = new Binding('core', SimpleInterface::class);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertTrue($registry->has(SimpleInterface::class));
    }

    #[Test]
    public function has_returns_true_for_alias(): void
    {
        $binding  = new Binding('core', SimpleInterface::class, aliases: [InterfaceImpl::class]);
        $registry = new BindingRegistry([SimpleInterface::class => $binding]);

        $this->assertTrue($registry->has(InterfaceImpl::class));
    }

    #[Test]
    public function has_returns_false_for_unknown_class(): void
    {
        $registry = new BindingRegistry([]);

        $this->assertFalse($registry->has(SimpleInterface::class));
    }

    #[Test]
    public function empty_registry_works(): void
    {
        $registry = new BindingRegistry([]);

        $this->assertSame([], $registry->bindings);
        $this->assertSame([], $registry->scopedBindings);
        $this->assertSame([], $registry->aliases);
        $this->assertNull($registry->get(SimpleInterface::class));
        $this->assertFalse($registry->has(SimpleInterface::class));
    }
}
