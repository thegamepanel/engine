<?php
declare(strict_types=1);

namespace Engine\Modules\Resolvers;

use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Dependency;
use Engine\Modules\Attributes\Registrar;
use Engine\Modules\ModuleRegistrar;
use Engine\Modules\ModuleRegistry;
use ReflectionNamedType;
use RuntimeException;

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
            throw new RuntimeException('Module registrar resolver can only resolver for the registrar attribute');
        }

        if (
            ! $dependency->type instanceof ReflectionNamedType
            || $dependency->type->getName() !== ModuleRegistrar::class
        ) {
            throw new RuntimeException('Module registrar resolver can only resolve for ModuleRegistrar type');
        }

        /** @var \Engine\Modules\ModuleRegistrar<*>|null $instance */
        $instance = $this->registry->$registrar($registrar->ident);

        if ($instance === null) {
            if ($dependency->type->allowsNull()) {
                return null;
            }

            throw new RuntimeException(sprintf(
                'Cannot resolve the module registrar "%s".', $registrar->ident
            ));
        }

        return $instance;
    }
}
