<?php
/** @noinspection PhpUnnecessaryStaticReferenceInspection */
declare(strict_types=1);

namespace Engine\Container;

use Engine\Container\Attributes\Liminal;
use Engine\Container\Attributes\Named;
use Engine\Container\Bindings\Binding;
use Engine\Container\Bindings\BindingRegistry;
use Engine\Container\Concerns\HelpsWithReflection;
use Engine\Container\Contracts\Qualifier;
use Engine\Container\Contracts\Resolvable;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Exceptions\MethodCallException;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use WeakReference;

/**
 * Container
 *
 * The base runtime reflection implementation of the container.
 */
final class Container implements Contracts\Container
{
    use HelpsWithReflection;

    private BindingRegistry $bindings;

    /**
     * @var \Engine\Container\Contracts\Resolver<*>
     */
    private Resolver $defaultResolver;

    /**
     * @var array<class-string, \Engine\Container\Contracts\Resolver<*>>
     */
    private array $resolvers = [];

    /**
     * @var array<class-string, object>
     */
    private array $instances = [];

    /**
     * @var array<class-string, array<string, object>>
     */
    private array $namedInstances = [];

    /**
     * @var array<class-string, array<class-string<\Engine\Container\Contracts\Qualifier>, object>>
     */
    private array $qualifiedInstances = [];

    /**
     * @var array<class-string, \WeakReference>
     */
    private array $liminalInstances = [];

    /**
     * @param \Engine\Container\Bindings\BindingRegistry   $bindings
     * @param \Engine\Container\Contracts\Resolver<*>|null $defaultResolver
     */
    public function __construct(
        BindingRegistry $bindings,
        ?Resolver       $defaultResolver = null
    )
    {
        $this->bindings = $bindings;

        // If there's a default resolver provided, set it.
        if ($defaultResolver !== null) {
            $this->defaultResolver = $defaultResolver;
        }

        // Make sure that the container itself is registered as a singleton,
        // otherwise we're going to get a whole host of weird issues.
        $this->registerSelf();
    }

    /**
     * Register the container itself as a singleton.
     *
     * @return void
     */
    private function registerSelf(): void
    {
        $this->instances[self::class]                = $this;
        $this->instances[Contracts\Container::class] = $this;
    }

    /**
     * Register a resolver for the given resolvable.
     *
     * @template TResolvable of \Engine\Container\Contracts\Resolvable
     * @template TResolver of \Engine\Container\Contracts\Resolver<TResolvable>
     *
     * @param class-string<TResolvable> $resolvable
     * @param class-string<TResolver>   $resolver
     *
     * @return self
     */
    private function registerResolver(string $resolvable, string $resolver): static
    {
        // We need to make sure that the class provided implements the
        // resolvable contract.
        /** @phpstan-ignore function.alreadyNarrowedType */
        if (! is_subclass_of($resolvable, Resolvable::class)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a resolvable.', $resolvable));
        }

        // Same for the resolver.
        /** @phpstan-ignore function.alreadyNarrowedType */
        if (! is_subclass_of($resolver, Resolver::class)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a resolver.', $resolver));
        }

        // Create a lazy object, so that its only initialised when needed.
        /** @var TResolver $instance */
        $instance                     = $this->lazy($resolver);
        $this->resolvers[$resolvable] = $instance;

        return $this;
    }

    /**
     * Get the binding for the given class.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $class
     *
     * @return \Engine\Container\Bindings\Binding<TAbstract>|null
     */
    public function binding(string $class, ?Named $name = null, ?Qualifier $qualifier = null): ?Binding
    {
        $binding = $this->bindings->get($class);

        if ($binding === null) {
            if ($name === null && $qualifier === null) {
                return null;
            }

            throw new InvalidArgumentException(sprintf('No binding found for %s.', $class));
        }

        // If It's named, we'll try and grab that sub-binding.
        if ($name !== null) {
            $binding = $binding->byName($name->name);

            // If we can't find one, error.
            if ($binding === null) {
                throw new InvalidArgumentException(sprintf('No binding found for %s with name %s.', $class, $name->name));
            }

            return $binding;
        }

        if ($qualifier !== null) {
            // Same for the qualifier.
            $binding = $binding->forQualifier($qualifier::class);

            // And again.
            if ($binding === null) {
                throw new InvalidArgumentException(sprintf('No binding found for %s qualified by %s.', $class, $qualifier::class));
            }

            return $binding;
        }

        return $binding;
    }

    /**
     * Check if the given class has a binding.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $class
     *
     * @return bool
     */
    public function bound(string $class): bool
    {
        return $this->bindings->has($class);
    }

    /**
     * Check if the given class has been resolved.
     *
     * @param class-string                               $class
     * @param \Engine\Container\Attributes\Named|null    $name
     * @param \Engine\Container\Contracts\Qualifier|null $qualifier
     *
     * @return bool
     */
    public function hasResolved(string $class, ?Named $name = null, ?Qualifier $qualifier = null): bool
    {
        return $this->getResolved($class, $name, $qualifier) !== null;
    }

    /**
     * Get the resolved instance for the given class.
     *
     * @template TClass of object
     *
     * @param class-string<TClass>                       $class
     * @param \Engine\Container\Attributes\Named|null    $name
     * @param \Engine\Container\Contracts\Qualifier|null $qualifier
     *
     * @return TClass|null
     */
    public function getResolved(string $class, ?Named $name = null, ?Qualifier $qualifier = null): ?object
    {
        $trueClass = $this->getTrueClass($class, $name, $qualifier);

        if (isset($this->liminalInstances[$trueClass]) && $this->liminalInstances[$trueClass]->get() !== null) {
            return $this->liminalInstances[$trueClass]->get();
        }

        if ($name !== null) {
            /** @var TClass|null */
            return $this->namedInstances[$trueClass][$name->name] ?? null;
        }

        if ($qualifier !== null) {
            /** @var TClass|null */
            return $this->qualifiedInstances[$trueClass][$qualifier::class] ?? null;
        }

        /** @var TClass|null */
        return $this->instances[$trueClass] ?? null;
    }

    /**
     * Set the resolved instance for the given class.
     *
     * @template TClass of object
     *
     * @param TClass                                     $instance
     * @param \Engine\Container\Attributes\Named|null    $name
     * @param \Engine\Container\Contracts\Qualifier|null $qualifier
     * @param bool                                       $liminal
     *
     * @return TClass
     */
    protected function storeResolved(object $instance, ?Named $name = null, ?Qualifier $qualifier = null, bool $liminal = false): object
    {
        $trueClass = $instance::class;

        if ($liminal) {
            $this->liminalInstances[$trueClass] = WeakReference::create($instance);

            return $this;
        }

        if ($name !== null) {
            return $this->namedInstances[$trueClass][$name->name] = $instance;
        }

        if ($qualifier !== null) {
            return $this->qualifiedInstances[$trueClass][$qualifier::class] = $instance;
        }

        return $this->instances[$trueClass] = $instance;
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
        $resolver = null;

        // If the dependency has a resolvable attribute, we need to get its
        // resolver.
        if ($dependency->resolvable !== null) {
            $resolver = $this->resolvers[$dependency->resolvable::class] ?? null;
        }

        // If there was no resolver, we can use the default one.
        if ($resolver === null) {
            // But if no default one has been set, it's an error.
            if (! isset($this->defaultResolver)) {
                throw new InvalidArgumentException('No default resolver has been set.');
            }

            $resolver = $this->defaultResolver;
        }

        /** @var \Engine\Container\Contracts\Resolver<TResolvable> $resolver */
        return $resolver;
    }

    /**
     * Create a lazy proxy for the given class.
     *
     * Lazy proxies are resolved when they're initialised, creating either a
     * new instance or using an existing one.
     *
     * @template TClass of object
     *
     * @param class-string<TClass> $class
     *
     * @return TClass
     */
    public function lazy(string $class): object
    {
        $trueClass = $this->getTrueClass($class);

        /** @var TClass $instance */
        $instance = $this->getClassReflector($trueClass)
                         ->newLazyProxy(function (object $proxy) use ($trueClass): object {
                             return $this->resolve($trueClass);
                         });

        return $instance;
    }

    /**
     * Resolve the given class with the given arguments.
     *
     * @template TClass of object
     *
     * @param class-string<TClass>                       $class
     * @param array<string, mixed>                       $arguments
     * @param \Engine\Container\Attributes\Named|null    $name
     * @param \Engine\Container\Contracts\Qualifier|null $qualifier
     * @param bool                                       $liminal
     *
     * @return TClass
     */
    public function resolve(string $class, array $arguments = [], ?Named $name = null, ?Qualifier $qualifier = null, bool $liminal = false): object
    {
        // If the class has already been resolved, use that instance.
        $instance = $this->getResolved($class, $name, $qualifier);

        if ($instance !== null) {
            return $instance;
        }

        // Grab the binding.
        $binding = $this->binding($class, $name, $qualifier);

        // Set some variables so they exist.
        $instance = null;
        $shared   = false;

        // If we have a binding, we can use it to resolve the instance.
        if ($binding !== null) {
            $shared = $binding->shared;

            // Honour the liminal setting in the method call.
            $liminal = $liminal || $binding->liminal;

            if ($binding->isBoundToInstance()) {
                // If it's bound to an instance, the hard work has been done.
                $instance = $binding->instance;
            } else if ($binding->hasFactory()) {
                // If it has a factory, that's also some of the hard work done.
                $instance = $this->call($binding->factory, $arguments);
            } else {
                $trueClass = $binding->concrete;
            }
        }

        $trueClass ??= $class;

        // If there's no instance yet, we can try and instantiate the class
        // ourselves.
        if ($instance === null) {
            $reflector = $this->getClassReflector($trueClass);

            if ($reflector->isInstantiable() === false) {
                throw new InvalidArgumentException(sprintf('Class %s is not instantiable.', $trueClass));
            }

            if ($reflector->hasMethod('__construct') === false) {
                $instance = new $trueClass();
            } else {
                $instance = $this->invoke($trueClass, '__construct', $arguments);
            }

            if ($liminal === false && $this->getAttributeInstance($reflector, Liminal::class) !== null) {
                $liminal = true;
            }
        }

        // If we're all the way down here, we have an instance, so we need to
        // do something with it. If it's marked as being shared, we store it
        // and return it.
        if ($shared) {
            /** @var TClass $instance */
            return $this->storeResolved($instance, $name, $qualifier, $liminal);
        }

        // Otherwise, we just return the instance.
        /** @var TClass $instance */
        return $instance;
    }

    /**
     * Invoke a method on the given class/object.
     *
     * @param class-string|object  $class
     * @param string               $method
     * @param array<string, mixed> $arguments
     *
     * @return mixed
     *
     * @throws \Engine\Container\Exceptions\InvalidClassException
     * @throws \Engine\Container\Exceptions\InvalidMethodException
     * @throws \Engine\Container\Exceptions\MethodCallException
     */
    public function invoke(string|object $class, string $method, array $arguments = []): mixed
    {
        // Use the object class or find the true class, then reflect and find
        // the method.
        $trueClass       = is_object($class) ? $class::class : $this->getTrueClass($class);
        $reflector       = $this->getClassReflector($trueClass);
        $reflectedMethod = $this->getMethodReflector($reflector, $method);

        // We can't call the method if it isn't public.
        if ($reflectedMethod->isPublic() === false) {
            throw new InvalidArgumentException(
                sprintf('Method %s::%s is not public.',
                    $trueClass, $method
                ));
        }

        // If we have an object, and the method is the constructor, we can
        // only call it if the object is an uninitialised lazy object, created
        // using the ghost or proxy methods.
        if (is_object($class) && $reflectedMethod->isConstructor() && $reflector->isUninitializedLazyObject($class) === false) {
            throw new InvalidArgumentException(
                sprintf('Cannot call constructor of %s because it is already initialised.',
                    $reflector->getName()
                ));
        }

        $dependencies = $this->collectDependencies($reflectedMethod, $arguments);

        try {
            // If it's static, we don't need to provide an object for the
            // method call.
            if ($reflectedMethod->isStatic()) {
                return $reflectedMethod->invokeArgs(null, $dependencies);
            }

            // If it's a class, we need to do things differently.
            if (is_string($class)) {
                // If it's a constructor, just call 'new'.
                if ($reflectedMethod->isConstructor()) {
                    return new $class(...$dependencies);
                }

                return $reflectedMethod->invokeArgs($this->resolve($class), $dependencies);
            }

            // For everything else, just invoke. This will catch methods called
            // on objects, including uninitialised lazy objects.
            return $reflectedMethod->invokeArgs($class, $dependencies);
        } catch (ReflectionException $e) {
            throw MethodCallException::make($reflector->getName(), $method, $e);
        }
    }

    /**
     * Call the given function with the given arguments.
     *
     * @param callable             $callable
     * @param array<string, mixed> $arguments
     *
     * @return mixed
     *
     * @throws \Engine\Container\Exceptions\InvalidFunctionException
     */
    public function call(callable $callable, array $arguments = []): mixed
    {
        return $callable(...$this->collectDependencies($this->getFunctionReflector($callable), $arguments));
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
            $this->getAttributeInstance($parameter, Named::class),
            $this->getAttributeInstance($parameter, Qualifier::class),
            $this->getAttributeInstance($parameter, Resolvable::class, true),
            $parameter->isDefaultValueAvailable(),
            $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            $this->getAttributeInstance($parameter, Liminal::class) !== null,
        );
    }

    /**
     * Resolve the given dependency.
     *
     * @template TType of mixed
     *
     * @param \Engine\Container\Dependency<TType, *, *> $dependency
     *
     * @return TType
     */
    private function resolveDependency(Dependency $dependency): mixed
    {
        /** @phpstan-ignore argument.type */
        return $this->getDependencyResolver($dependency)
            /** @phpstan-ignore argument.type */
                    ->resolve($dependency, $this);
    }

    /**
     * Get the true class for the given class.
     *
     * @template TClass of object
     *
     * @param class-string<TClass> $class
     *
     * @return class-string<TClass>
     */
    private function getTrueClass(string $class, ?Named $name = null, ?Qualifier $qualifier = null): string
    {
        return $this->binding($class, $name, $qualifier)->concrete ?? $class;
    }
}
