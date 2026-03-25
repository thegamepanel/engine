<?php
declare(strict_types=1);

namespace Engine\Container\Resolvers;

use Engine\Container\Container;
use Engine\Container\Contracts\Resolvable;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Exceptions\InvalidResolverException;
use Engine\Container\Resolution;
use InvalidArgumentException;

/**
 * @template TDefaultResolver of \Engine\Container\Contracts\Resolver<null>
 */
class ResolverCatalogue
{
    /**
     * @var array<class-string<\Engine\Container\Contracts\Resolvable>, class-string<\Engine\Container\Contracts\Resolver<*>>>
     */
    private(set) array $resolvers;

    /**
     * @var class-string<TDefaultResolver>
     */
    private(set) string $default;

    /**
     * @var array<class-string<\Engine\Container\Contracts\Resolver<*>>, \Engine\Container\Contracts\Resolver<*>>
     */
    private array $instances = [];

    /**
     * @param array<class-string<\Engine\Container\Contracts\Resolvable>, class-string<\Engine\Container\Contracts\Resolver<*>>> $resolvers
     * @param class-string<TDefaultResolver>                                                                                     $default
     */
    public function __construct(array $resolvers, string $default)
    {
        $this->resolvers = $resolvers;
        $this->default   = $default;
    }

    /**
     * Get the default resolver.
     *
     * @param \Engine\Container\Container $container
     *
     * @return TDefaultResolver
     */
    public function default(Container $container): Resolver
    {
        return $this->resolver($container, $this->default);
    }

    /**
     * Get the resolver for the given resolvable.
     *
     * @template TResolvable of \Engine\Container\Contracts\Resolvable
     *
     * @param \Engine\Container\Container<TDefaultResolver> $container
     * @param TResolvable                                   $resolvable
     *
     * @return \Engine\Container\Contracts\Resolver<TResolvable>
     */
    public function get(Container $container, Resolvable $resolvable): Resolver
    {
        /** @var class-string<\Engine\Container\Contracts\Resolver<TResolvable>>|null $resolver */
        $resolver = $this->resolvers[$resolvable::class] ?? null;

        if ($resolver === null) {
            throw InvalidResolverException::unregistered($resolvable::class);
        }

        return $this->resolver($container, $resolver);
    }

    /**
     * Get an instance of the given resolver.
     *
     * @template TResolvable of \Engine\Container\Contracts\Resolvable|null
     * @template TResolver of \Engine\Container\Contracts\Resolver<TResolvable>
     *
     * @param \Engine\Container\Container<TDefaultResolver> $container
     * @param class-string<TResolver>                       $class
     *
     * @return TResolver
     */
    private function resolver(Container $container, string $class): Resolver
    {
        if (isset($this->instances[$class])) {
            /** @var TResolver */
            return $this->instances[$class];
        }

        /** @var TResolver $instance */
        $instance = $container->resolve(Resolution::for($class)->lazily());

        return $this->instances[$class] = $instance;
    }
}
