<?php
declare(strict_types=1);

namespace Engine\Container;

use Engine\Container\Attributes\Lazy;
use Engine\Container\Attributes\Liminal;
use Engine\Container\Attributes\Named;
use Engine\Container\Bindings\BindingCatalogue;
use Engine\Container\Contracts\Qualifier;
use Engine\Container\Contracts\Resolvable;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Exceptions\InvalidInvocationException;
use Engine\Container\Exceptions\MethodCallException;
use Engine\Container\Exceptions\NotInstantiableException;
use Engine\Container\Resolvers\ResolverCatalogue;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use RuntimeException;
use WeakReference;

final class Container
{
    /**
     * @var \Engine\Container\Resolvers\ResolverCatalogue
     */
    private ResolverCatalogue $resolvers;

    private(set) BindingCatalogue $bindings;

    /**
     * @var array<class-string, object>
     */
    private array $instances = [];

    /**
     * @var array<class-string, \WeakReference<object>>
     */
    private array $liminalInstances = [];

    /**
     * @var array<class-string, array<string, object>>
     */
    private array $namedInstances = [];

    /**
     * @var array<class-string, array{\Engine\Container\Contracts\Qualifier, object}>
     */
    private array $qualifiedInstances = [];

    /**
     * @param \Engine\Container\Resolvers\ResolverCatalogue $resolvers
     * @param \Engine\Container\Bindings\BindingCatalogue   $bindings
     */
    public function __construct(
        ResolverCatalogue $resolvers,
        BindingCatalogue  $bindings,
    )
    {
        $this->resolvers = $resolvers;
        $this->bindings  = $bindings;
    }

    /**
     * Get a previously resolved instance.
     *
     * @template TClass of object
     *
     * @param \Engine\Container\Resolution<TClass> $resolution
     *
     * @return TClass|null
     */
    private function getResolved(Resolution $resolution): ?object
    {
        if ($resolution->isNamed()) {
            /** @var TClass|null */
            return $this->namedInstances[$resolution->class][$resolution->name];
        }

        if ($resolution->isQualified()) {
            /** @var array{\Engine\Container\Contracts\Qualifier, object} $instances */
            $instances = $this->qualifiedInstances[$resolution->class];

            /**
             * @var \Engine\Container\Contracts\Qualifier $qualifier
             * @var object                                $instance
             *
             * @noinspection PhpLoopCanBeConvertedToArrayFindInspection
             */
            foreach ($instances as [$qualifier, $instance]) {
                if (
                    $qualifier::class === $resolution->qualifier::class
                    && $qualifier->equals($resolution->qualifier)
                ) {
                    /** @var TClass */
                    return $instance;
                }
            }

            return null;
        }

        if ($resolution->isLiminal()) {
            /** @var TClass|null */
            return $this->liminalInstances[$resolution->class]->get();
        }

        /** @var TClass|null */
        return $this->instances[$resolution->class];
    }

    /**
     * Create a lazy proxy for a resolution.
     *
     * @template TClass of object
     *
     * @param \Engine\Container\Resolution<TClass> $resolution
     *
     * @return TClass
     */
    private function lazy(Resolution $resolution): object
    {
        return ReflectionHelper::getLazyProxy(
            $resolution->class,
            fn () => $this->resolve($resolution, true),
        );
    }

    /**
     * Store a resolved instance and return it.
     *
     * @template TClass of object
     *
     * @param \Engine\Container\Resolution<TClass>            $resolution
     * @param \Engine\Container\Bindings\Binding<TClass>|null $binding
     * @param TClass                                          $instance
     * @param bool|int                                        $liminal
     *
     * @return TClass
     */
    private function storeResolved(Resolution $resolution, ?Bindings\Binding $binding, object $instance, bool|int $liminal): object
    {
        $class = $binding->abstract ?? $resolution->class;

        if ($liminal) {
            $this->liminalInstances[$class] = WeakReference::create($instance);

            return $instance;
        }

        if ($resolution->isNamed()) {
            $this->namedInstances[$class][$resolution->name] = $instance;
        } else if ($resolution->isQualified()) {
            $this->qualifiedInstances[$class][] = [$resolution->qualifier, $instance];
        } else {
            $this->instances[$class] = $instance;
        }

        return $instance;
    }

    /**
     * Resolve a class.
     *
     * @template TClass of object
     *
     * @param \Engine\Container\Resolution<TClass> $resolution
     * @param bool                                 $skipLazy
     *
     * @return TClass
     */
    public function resolve(Resolution $resolution, bool $skipLazy = false): object
    {
        // If it has already been resolved, return it.
        $instance = $this->getResolved($resolution);

        if ($instance !== null) {
            return $instance;
        }

        // If it should be resolved lazily, return a lazy proxy, deferring the
        // resolution until needed.
        if ($skipLazy === false && $resolution->shouldResolveLazily()) {
            return $this->lazy($resolution);
        }

        // If we're here, see if there's a binding.
        $binding = $this->bindings->get(
            $resolution->class,
            $resolution->isNamed() ? new Named($resolution->name) : null,
            $resolution->qualifier,
        );

        // Grab the values and flags.
        $instance       = $binding?->instance;
        $shared         = $binding->shared ?? false;
        $liminal        = ($binding->liminal ?? false) || $resolution->isLiminal();
        $resolvingClass = $resolution->class;

        // If we have no instance but a binding, we can either invoke the
        // factory from the binding if one exists or use the concrete class.
        if ($instance === null && $binding !== null) {
            if ($binding->factory !== null) {
                $instance = $this->invoke(Invocation::callable($binding->factory));
            } else if ($binding->concrete !== null) {
                $resolvingClass = $binding->concrete;
            }
        }

        // If we still don't have an instance, we need to create one ourselves.
        if ($instance === null) {
            $reflector = ReflectionHelper::getClassReflector($resolvingClass);

            // If it has the lazy attribute, it needs a lazy resolution.
            if ($skipLazy === false && ReflectionHelper::getAttributeInstance($reflector, Lazy::class) !== null) {
                return $this->lazy($resolution);
            }

            // We first need to make sure the class is instantiable.
            if ($reflector->isInstantiable() === false) {
                throw NotInstantiableException::make($resolvingClass);
            }

            // Then we check if there's a constructor...
            if ($reflector->hasMethod('__construct') === false) {
                // Because if there isn't, we instantiate like normal...
                $instance = new $resolvingClass();
            } else {
                // But if there is, we need to invoke the constructor.
                $instance = $this->invoke(Invocation::constructor($resolvingClass));
            }

            // Finally, if the liminal flag isn't already set, we set it based
            // on the presence of the Liminal attribute.
            $liminal |= ReflectionHelper::getAttributeInstance($reflector, Liminal::class) !== null;
        }

        /**
         * This is just here to ensure that everything knows the type correctly.
         *
         * @var TClass $instance
         */

        // If it's shared, we need to store it and return it.
        if ($shared) {
            return $this->storeResolved($resolution, $binding, $instance, $liminal);
        }

        // Otherwise, we just return the instance.
        return $instance;
    }

    public function invoke(Invocation $invocation): mixed
    {
        // If there's no class, it's a callable.
        if ($invocation->class === null) {
            $callable = $invocation->invokable;

            // Make sure that it's actually callable.
            if (! is_callable($callable)) {
                throw new RuntimeException('Cannot invoke non-callable');
            }

            return $this->invokeCallable($callable, $invocation->arguments);
        }

        $object = null;
        $class  = $invocation->class;
        $method = $invocation->invokable;

        // Make sure we're dealing with a proper method.
        if (! is_string($method)) {
            throw new RuntimeException('Cannot invoke non-string method');
        }

        // If the class is an object, we're calling a method on it.
        if (is_object($class)) {
            $object = $class;
            $class  = $object::class;
        }

        return $this->invokeClassMethod($class, $method, $invocation, $object);
    }

    /**
     * @param callable             $callable
     * @param array<string, mixed> $arguments
     *
     * @return mixed
     */
    private function invokeCallable(callable $callable, array $arguments): mixed
    {
        return $callable(...$this->collectDependencies(
            ReflectionHelper::getFunctionReflector($callable),
            $arguments
        ));
    }

    /**
     * @template TClass of object
     *
     * @param class-string<TClass>         $class
     * @param string                       $method
     * @param \Engine\Container\Invocation $invocation
     * @param TClass|null                  $object
     *
     * @return mixed
     */
    private function invokeClassMethod(string $class, string $method, Invocation $invocation, ?object $object = null): mixed
    {
        $classReflector  = ReflectionHelper::getClassReflector($class);
        $methodReflector = ReflectionHelper::getMethodReflector($class, $method);

        // If it's not public, we can't call it.
        if ($methodReflector->isPublic() === false) {
            throw InvalidInvocationException::notPublic($class, $method);
        }

        // If it's a constructor, and we've been given an object that isn't an
        // uninitialized lazy object, we can't call it.
        if (is_object($object) && $methodReflector->isConstructor() && $classReflector->isUninitializedLazyObject($object)) {
            throw InvalidInvocationException::alreadyInitialised($class);
        }

        $dependencies = $this->collectDependencies($methodReflector, $invocation->arguments);

        try {
            // If it's static, we just call it, regardless of whether we've been
            // give an object or not.
            if ($methodReflector->isStatic() === true) {
                return $methodReflector->invokeArgs(null, $dependencies);
            }

            // If there's no object, there are two options...
            if ($object === null) {
                // If the method is the constructor, we're creating a new
                // instance, so we'll do just that.
                if ($methodReflector->isConstructor()) {
                    return $classReflector->newInstanceArgs($dependencies);
                }

                // Otherwise, we should resolve the object ready for the
                // catch-all and final option below.
                $object = $this->resolve(Resolution::for($class));
            }

            // If we're here, we're calling a method on an object.
            return $methodReflector->invokeArgs($object, $dependencies);
        } catch (ReflectionException $e) {
            // Unreachable — the method was already successfully reflected above,
            // so invokeArgs() cannot throw a ReflectionException.
            throw MethodCallException::make($class, $method, $e);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Collect the dependencies for the given method or function.
     *
     * @param \ReflectionFunctionAbstract $reflector
     * @param array<string, mixed>        $arguments
     *
     * @return array<string, mixed>
     */
    private function collectDependencies(ReflectionFunctionAbstract $reflector, array $arguments = []): array
    {
        $dependencies = [];

        foreach ($reflector->getParameters() as $parameter) {
            // If the parameter is already provided, we can just use that
            // value and skip its resolution.
            if (array_key_exists($parameter->getName(), $arguments)) {
                $dependencies[$parameter->getName()] = $arguments[$parameter->getName()];
                continue;
            }

            // We don't want to try and auto-resolve variadic parameters, so
            // we can break here. PHP also requires that variadic parameters
            // are the last ones, so this won't cause any to be skipped.
            if ($parameter->isVariadic()) {
                break;
            }

            // Create a representation of the dependency and then resolve it.
            $dependency = $this->createDependency($parameter);
            /** @phpstan-ignore argument.type */
            $dependencies[$dependency->parameter] = $this->resolveDependency($dependency);
        }

        return $dependencies;
    }

    /**
     * Create a dependency representation from the given parameter.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return \Engine\Container\Dependency<*, *, *>
     */
    private function createDependency(ReflectionParameter $parameter): Dependency
    {
        /**
         * This has to be here, otherwise PHPStan will have wobbler. In reality,
         * it is always one of these types.
         *
         * @var \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type
         */
        $type = $parameter->getType();

        return new Dependency(
            $parameter->getName(),
            $type,
            $parameter->isOptional(),
            ReflectionHelper::getAttributeInstance($parameter, Named::class),
            ReflectionHelper::getAttributeInstance($parameter, Qualifier::class),
            ReflectionHelper::getAttributeInstance($parameter, Resolvable::class, true),
            $parameter->isDefaultValueAvailable(),
            $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ReflectionHelper::getAttributeInstance($parameter, Liminal::class) !== null,
        );
    }

    /**
     * Resolve the given dependency.
     *
     * @template TType of mixed
     * @template TQualifier of \Engine\Container\Contracts\Qualifier|null = null
     * @template TResolvable of \Engine\Container\Contracts\Resolvable|null = null
     *
     * @param \Engine\Container\Dependency<TType, TQualifier, TResolvable> $dependency
     *
     * @return TType
     */
    private function resolveDependency(Dependency $dependency): mixed
    {
        return $this->getDependencyResolver($dependency)->resolve($dependency, $this);
    }

    /**
     * Get the resolver for the given dependency.
     *
     * @template TType of mixed
     * @template TResolvable of \Engine\Container\Contracts\Resolvable|null = null
     *
     * @param \Engine\Container\Dependency<TType, *, TResolvable> $dependency
     *
     * @return \Engine\Container\Contracts\Resolver<TResolvable>
     */
    private function getDependencyResolver(Dependency $dependency): Resolver
    {
        // If the dependency has a resolvable attribute, we need to get its
        // resolver.
        if ($dependency->resolvable !== null) {
            $resolver = $this->resolvers->get($this, $dependency->resolvable);
        } else {
            $resolver = $this->resolvers->default($this);
        }

        /**
         * This has to be here because it complains about TDefaultResolver,
         * even though the types are the same.
         *
         * @var \Engine\Container\Contracts\Resolver<TResolvable> $resolver
         */
        return $resolver;
    }
}
