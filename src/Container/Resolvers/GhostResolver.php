<?php
declare(strict_types=1);

namespace Engine\Container\Resolvers;

use Engine\Container\Concerns\HelpsWithReflection;
use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Dependency;
use RuntimeException;

/**
 * @implements \Engine\Container\Contracts\Resolver<\Engine\Container\Attributes\Ghost>
 */
final class GhostResolver implements Resolver
{
    use HelpsWithReflection;

    /**
     * Resolve a dependency.
     *
     * @template TType of mixed
     *
     * @param \Engine\Container\Dependency<TType, *, \Engine\Container\Attributes\Ghost> $dependency
     * @param \Engine\Container\Container                                                $container
     * @param array<string, mixed>                                                       $arguments
     *
     * @return TType&object
     */
    public function resolve(Dependency $dependency, Container $container, array $arguments = []): mixed
    {
        /** @var string|null $class */
        $class = $this->getTypeClassName($dependency);

        if ($class === null || (! class_exists($class) && ! interface_exists($class))) {
            throw new RuntimeException('Cannot create a ghost object for a non-class');
        }

        /**
         * @var class-string<TType&object> $class
         * @var class-string<TType&object> $concreteClass
         */
        $concreteClass = $container->binding($class, $dependency->name, $dependency->qualifier)->concrete ?? $class;

        /** @var TType&object $instance */
        $instance = $this->getClassReflector($concreteClass)
                         ->newLazyGhost(function (object $lazy) use ($container, $arguments): void {
                             // Invoke the constructor for this class if it has one.
                             if (method_exists($lazy, '__construct')) {
                                 $container->invoke($lazy, '__construct', $arguments);
                             }
                         });

        return $instance;
    }
}
