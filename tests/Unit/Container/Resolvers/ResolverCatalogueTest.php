<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Resolvers;

use Engine\Container\Bindings\BindingCatalogue;
use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Exceptions\InvalidResolverException;
use Engine\Container\Resolvers\GenericResolver;
use Engine\Container\Resolvers\GhostResolver;
use Engine\Container\Resolvers\ResolverCatalogue;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\TestResolvable;

#[Group('unit'), Group('container'), Group('resolvers')]
class ResolverCatalogueTest extends TestCase
{
    private function buildContainer(ResolverCatalogue $catalogue): Container
    {
        return new Container($catalogue, new BindingCatalogue([], [], []));
    }

    /**
     * - The catalogue stores both the resolvable-to-resolver map and the default
     *   resolver class-string for later instantiation.
     */
    #[Test]
    public function constructorStoresResolversAndDefault(): void
    {
        $catalogue = new ResolverCatalogue(
            [TestResolvable::class => GhostResolver::class],
            GenericResolver::class,
        );

        $this->assertSame(GenericResolver::class, $catalogue->default);
        $this->assertSame([TestResolvable::class => GhostResolver::class], $catalogue->resolvers);
    }

    /**
     * - `default()` instantiates and returns the configured default resolver,
     *   ready for use when no specific resolvable attribute is present.
     */
    #[Test]
    public function defaultReturnsInstanceOfDefaultResolverClass(): void
    {
        $catalogue = new ResolverCatalogue([], GenericResolver::class);
        $container = $this->buildContainer($catalogue);

        $resolver = $catalogue->default($container);

        $this->assertInstanceOf(Resolver::class, $resolver);
        $this->assertInstanceOf(GenericResolver::class, $resolver);
    }

    /**
     * - `get()` with a known resolvable instantiates and returns the resolver mapped
     *   to that resolvable attribute type.
     */
    #[Test]
    public function getReturnsInstanceOfRegisteredResolverForResolvable(): void
    {
        $catalogue = new ResolverCatalogue(
            [TestResolvable::class => GhostResolver::class],
            GenericResolver::class,
        );
        $container = $this->buildContainer($catalogue);

        $resolver = $catalogue->get($container, new TestResolvable());

        $this->assertInstanceOf(Resolver::class, $resolver);
        $this->assertInstanceOf(GhostResolver::class, $resolver);
    }

    /**
     * - `get()` with a resolvable that has no registered resolver throws rather than
     *   returning null, so misconfigurations surface immediately.
     */
    #[Test]
    public function getThrowsForUnregisteredResolvable(): void
    {
        $catalogue = new ResolverCatalogue([], GenericResolver::class);
        $container = $this->buildContainer($catalogue);

        $this->expectException(InvalidResolverException::class);

        $catalogue->get($container, new TestResolvable());
    }

    /**
     * - The default resolver is only instantiated once; subsequent calls return the
     *   same cached instance to avoid redundant object creation.
     */
    #[Test]
    public function defaultReturnsCachedInstanceOnSubsequentCalls(): void
    {
        $catalogue = new ResolverCatalogue([], GenericResolver::class);
        $container = $this->buildContainer($catalogue);

        $first  = $catalogue->default($container);
        $second = $catalogue->default($container);

        $this->assertSame($first, $second);
    }

    /**
     * - A resolvable's resolver is only instantiated once; subsequent calls for the
     *   same resolvable type return the same cached instance.
     */
    #[Test]
    public function getReturnsCachedInstanceOnSubsequentCalls(): void
    {
        $catalogue = new ResolverCatalogue(
            [TestResolvable::class => GhostResolver::class],
            GenericResolver::class,
        );
        $container = $this->buildContainer($catalogue);

        $first  = $catalogue->get($container, new TestResolvable());
        $second = $catalogue->get($container, new TestResolvable());

        $this->assertSame($first, $second);
    }
}
