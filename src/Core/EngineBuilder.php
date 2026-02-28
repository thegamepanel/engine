<?php
declare(strict_types=1);

namespace Engine\Core;

use;
use Engine\Container\Bindings\BindingBuilder;

final class EngineBuilder
{
    /**
     * @var string
     */
    public readonly string $scope;

    /**
     * @var BindingBuilder
     */
    private(set) array $bindings = [];

    /**
     * @var array<class-string<\Engine\Container\Contracts\Resolvable>, class-string<\Engine\Container\Contracts\Resolver<*>>>
     */
    private(set) array $resolvers = [];

    private(set) ?string $defaultResolver = null;

    /**
     * @var array<class-string<\Engine\Collectors\Contracts\CollectorHandler>>
     */
    private(set) array $collectors = [];

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Register a binding.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $abstract
     *
     * @return \Engine\Container\Bindings\BindingBuilder<TAbstract>
     */
    public function bind(string $abstract): BindingBuilder
    {
        return $this->bindings[$abstract][] = new BindingBuilder($this->scope, $abstract);
    }

    /**
     * Register a dependency resolver.
     *
     * @template TResolvable of \Engine\Container\Contracts\Resolvable
     *
     * @param class-string<TResolvable>                                       $resolvable
     * @param class-string<\Engine\Container\Contracts\Resolver<TResolvable>> $resolver
     * @param bool                                                            $default
     *
     * @return $this
     */
    public function resolver(string $resolvable, string $resolver, bool $default = false): self
    {
        $this->resolvers[$resolvable] = $resolver;

        if ($default) {
            $this->defaultResolver = $resolvable;
        }

        return $this;
    }

    /**
     * Register a custom collector.
     *
     * @param class-string<\Engine\Collectors\Contracts\CollectorHandler> $collector
     *
     * @return $this
     */
    public function collector(string $collector): self
    {
        $this->collectors[] = $collector;

        return $this;
    }
}
