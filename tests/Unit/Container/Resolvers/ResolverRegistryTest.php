<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Resolvers;

use Engine\Container\Resolvers\GenericResolver;
use Engine\Container\Resolvers\GhostResolver;
use Engine\Container\Resolvers\ResolverRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\AnotherTestResolvable;
use Tests\Unit\Container\Fixtures\TestResolvable;

#[Group('unit'), Group('container'), Group('resolvers')]
class ResolverRegistryTest extends TestCase
{
    /**
     * - A new registry starts with no default resolver and no registered
     *   resolvable-to-resolver mappings.
     */
    #[Test]
    public function constructorSetsDefaultsCorrectly(): void
    {
        $registry = new ResolverRegistry();

        $this->assertNull($registry->default);
        $this->assertEmpty($registry->resolvers);
    }

    /**
     * - `default()` stores the given resolver class as the fallback, modifies the
     *   registry in place, and returns the same instance for fluent chaining.
     */
    #[Test]
    public function defaultMutatesAndReturnsTheSameInstance(): void
    {
        $registry = new ResolverRegistry();
        $result   = $registry->default(GenericResolver::class);

        $this->assertSame($registry, $result);
        $this->assertSame(GenericResolver::class, $registry->default);
    }

    /**
     * - `register()` stores the resolvable-to-resolver mapping, modifies the registry
     *   in place, and returns the same instance for fluent chaining.
     */
    #[Test]
    public function registerMutatesAndReturnsTheSameInstance(): void
    {
        $registry = new ResolverRegistry();
        $result   = $registry->register(TestResolvable::class, GhostResolver::class);

        $this->assertSame($registry, $result);
        $this->assertArrayHasKey(TestResolvable::class, $registry->resolvers);
        $this->assertSame(GhostResolver::class, $registry->resolvers[TestResolvable::class]);
    }

    /**
     * - Registering different resolvable types stores them under independent keys so
     *   each resolvable attribute maps to its own resolver.
     */
    #[Test]
    public function registerStoresDifferentResolvablesSeparately(): void
    {
        $registry = new ResolverRegistry();
        $registry->register(TestResolvable::class, GenericResolver::class);
        $registry->register(AnotherTestResolvable::class, GhostResolver::class);

        $this->assertCount(2, $registry->resolvers);
        $this->assertSame(GenericResolver::class, $registry->resolvers[TestResolvable::class]);
        $this->assertSame(GhostResolver::class, $registry->resolvers[AnotherTestResolvable::class]);
    }

    /**
     * - Registering a new resolver for an already-registered resolvable replaces the
     *   previous mapping, so the last registration wins.
     */
    #[Test]
    public function registerOverwritesPreviousResolverForSameResolvable(): void
    {
        $registry = new ResolverRegistry();
        $registry->register(TestResolvable::class, GenericResolver::class);
        $registry->register(TestResolvable::class, GhostResolver::class);

        $this->assertCount(1, $registry->resolvers);
        $this->assertSame(GhostResolver::class, $registry->resolvers[TestResolvable::class]);
    }
}
