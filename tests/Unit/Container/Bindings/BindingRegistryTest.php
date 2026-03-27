<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Bindings;

use Engine\Container\Bindings\BindingBuilder;
use Engine\Container\Bindings\BindingRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\AbstractInterface;
use Tests\Unit\Container\Fixtures\ClassWithMethods;
use Tests\Unit\Container\Fixtures\ConcreteClass;

#[Group('unit'), Group('container'), Group('bindings')]
class BindingRegistryTest extends TestCase
{
    /**
     * - A new registry starts with the given scope name and no accumulated bindings.
     */
    #[Test]
    public function constructorSetsScope(): void
    {
        $registry = new BindingRegistry('my-scope');

        $this->assertSame('my-scope', $registry->scope);
        $this->assertEmpty($registry->bindings);
    }

    /**
     * - `bind()` returns a builder pre-configured with the registry's scope and the
     *   requested abstract class, ready for further fluent configuration.
     */
    #[Test]
    public function bindReturnsABuilderWithTheScopedAndAbstract(): void
    {
        $registry = new BindingRegistry('my-scope');
        $builder  = $registry->bind(ClassWithMethods::class);

        $this->assertInstanceOf(BindingBuilder::class, $builder);
        $this->assertSame('my-scope', $builder->scope);
        $this->assertSame(ClassWithMethods::class, $builder->abstract);
    }

    /**
     * - The builder returned by `bind()` is retained inside the registry so it can
     *   be consumed when the catalogue is built.
     */
    #[Test]
    public function bindStoresTheBuilderInBindings(): void
    {
        $registry = new BindingRegistry('my-scope');
        $builder  = $registry->bind(ClassWithMethods::class);

        $this->assertArrayHasKey(ClassWithMethods::class, $registry->bindings);
        $this->assertContains($builder, $registry->bindings[ClassWithMethods::class]);
    }

    /**
     * - Multiple calls to `bind()` for the same abstract accumulate separate builders,
     *   supporting named, qualified, and default bindings for the same type.
     */
    #[Test]
    public function bindAccumulatesMultipleBuildersForTheSameAbstract(): void
    {
        $registry = new BindingRegistry('my-scope');
        $first    = $registry->bind(ClassWithMethods::class);
        $second   = $registry->bind(ClassWithMethods::class);

        $this->assertCount(2, $registry->bindings[ClassWithMethods::class]);
        $this->assertContains($first, $registry->bindings[ClassWithMethods::class]);
        $this->assertContains($second, $registry->bindings[ClassWithMethods::class]);
    }

    /**
     * - Bindings for different abstract classes are stored independently so they
     *   do not collide during catalogue construction.
     */
    #[Test]
    public function bindStoresBuildersForDifferentAbstractsSeparately(): void
    {
        $registry = new BindingRegistry('my-scope');
        $registry->bind(AbstractInterface::class);
        $registry->bind(ConcreteClass::class);

        $this->assertArrayHasKey(AbstractInterface::class, $registry->bindings);
        $this->assertArrayHasKey(ConcreteClass::class, $registry->bindings);
        $this->assertCount(1, $registry->bindings[AbstractInterface::class]);
        $this->assertCount(1, $registry->bindings[ConcreteClass::class]);
    }
}
