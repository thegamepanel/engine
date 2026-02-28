<?php
declare(strict_types=1);

namespace Engine\Modules\Resolvers;

use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Dependency;
use Engine\Modules\Attributes\Manifest;
use Engine\Modules\ModuleManifest;
use Engine\Modules\ModuleRegistry;
use ReflectionNamedType;
use RuntimeException;

/**
 * @implements \Engine\Container\Contracts\Resolver<\Engine\Modules\Attributes\Manifest>
 */
final class ManifestResolver implements Resolver
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
     * @param \Engine\Container\Dependency<TType, *, \Engine\Modules\Attributes\Manifest> $dependency
     * @param \Engine\Container\Container                                                 $container
     * @param array<string, mixed>                                                        $arguments
     *
     * @return \Engine\Modules\ModuleManifest|null
     */
    public function resolve(Dependency $dependency, Container $container, array $arguments = []): ?ModuleManifest
    {
        $manifest = $dependency->resolvable;

        if (! $manifest instanceof Manifest) {
            throw new RuntimeException('Module manifest resolver can only resolver for the manifest attribute');
        }

        if (
            ! $dependency->type instanceof ReflectionNamedType
            || $dependency->type->getName() !== ModuleManifest::class
        ) {
            throw new RuntimeException('Module manifest resolver can only resolve for ModuleManifest type');
        }

        $instance = $this->registry->manifest($manifest->ident);

        if ($instance === null) {
            if ($dependency->type->allowsNull()) {
                return null;
            }

            throw new RuntimeException(sprintf(
                'Cannot resolve the module manifest "%s".', $manifest->ident
            ));
        }

        return $instance;
    }
}
