<?php
declare(strict_types=1);

namespace Engine\Modules\Resolvers;

use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Dependency;
use Engine\Modules\Attributes\Registrar;
use Engine\Modules\Exceptions\ModuleResolutionException;
use Engine\Modules\ModuleRegistrar;
use Engine\Modules\ModuleRegistry;
use ReflectionNamedType;

/**
 * @implements \Engine\Container\Contracts\Resolver<\Engine\Modules\Attributes\Registrar>
 */
final class RegistrarResolver implements Resolver
{
    /**
     * @var \Engine\Modules\ModuleRegistry
     */
    private ModuleRegistry $registry;

    public function __construct(ModuleRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Resolve a dependency.
     *
     * @template TType of mixed
     *
     * @param \Engine\Container\Dependency<TType, *, \Engine\Modules\Attributes\Registrar> $dependency
     * @param \Engine\Container\Container                                                  $container
     * @param array<string, mixed>                                                         $arguments
     *
     * @return \Engine\Modules\ModuleRegistrar<*>|null
     */
    public function resolve(Dependency $dependency, Container $container, array $arguments = []): ?ModuleRegistrar
    {
        $registrar = $dependency->resolvable;

        if (! $registrar instanceof Registrar) {
            throw ModuleResolutionException::registrarResolver();
        }

        if (
            ! $dependency->type instanceof ReflectionNamedType
            || $dependency->type->getName() !== ModuleRegistrar::class
        ) {
            throw ModuleResolutionException::notRegistrarType();
        }

        /** @var \Engine\Modules\ModuleRegistrar<*>|null $instance */
        $instance = $this->registry->$registrar($registrar->ident);

        if ($instance === null) {
            if ($dependency->type->allowsNull()) {
                return null;
            }

            throw ModuleResolutionException::unresolvableRegistrar($registrar->ident);
        }

        return $instance;
    }
}
