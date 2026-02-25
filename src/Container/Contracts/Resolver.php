<?php

namespace Engine\Container\Contracts;

use Engine\Container\Container;
use Engine\Container\Dependency;

/**
 * @template TAttribute of \Engine\Container\Contracts\Resolvable|null
 */
interface Resolver
{
    /**
     * Resolve a dependency.
     *
     * @template TType of mixed
     *
     * @param \Engine\Container\Dependency<TType, *, TAttribute> $dependency
     * @param \Engine\Container\Container                        $container
     * @param array<string, mixed>                               $arguments
     *
     * @return TType
     */
    public function resolve(Dependency $dependency, Container $container, array $arguments = []): mixed;
}
