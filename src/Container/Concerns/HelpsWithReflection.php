<?php
declare(strict_types=1);

namespace Engine\Container\Concerns;

use Closure;
use Engine\Container\Dependency;
use Engine\Container\Exceptions\InvalidClassException;
use Engine\Container\Exceptions\InvalidFunctionException;
use Engine\Container\Exceptions\InvalidMethodException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

trait HelpsWithReflection
{
    /**
     * @param \Engine\Container\Dependency<*, *, *> $dependency
     *
     * @return bool
     *
     * @phpstan-assert-if-true ReflectionNamedType $dependency->type
     */
    protected function isSingleType(Dependency $dependency): bool
    {
        return $dependency->type instanceof ReflectionNamedType;
    }

    /**
     * @param \Engine\Container\Dependency<*, *, *> $dependency
     *
     * @return bool
     *
     * @phpstan-assert-if-true ReflectionIntersectionType $dependency->type
     */
    protected function isIntersection(Dependency $dependency): bool
    {
        return $dependency->type instanceof ReflectionIntersectionType;
    }

    /**
     * @param \Engine\Container\Dependency<*, *, *> $dependency
     *
     * @return bool
     *
     * @phpstan-assert-if-true \ReflectionUnionType $dependency->type
     */
    protected function isUnionType(Dependency $dependency): bool
    {
        return $dependency->type instanceof ReflectionUnionType;
    }

    /**
     * @param \Engine\Container\Dependency<*, *, *> $dependency
     *
     * @return string|null
     */
    protected function getTypeClassName(Dependency $dependency): ?string
    {
        if ($this->isSingleType($dependency)) {
            /** @var ReflectionNamedType $type */
            $type = $dependency->type;

            return $type->getName();
        }

        return null;
    }

    /**
     * Get the reflection class for the given class/object.
     *
     * @template TClass of object
     *
     * @param class-string<TClass>|TClass $class
     *
     * @return \ReflectionClass<TClass>
     *
     * @throws \Engine\Container\Exceptions\InvalidClassException
     *
     * @phpstan-ignore throws.unusedType
     */
    protected function getClassReflector(string|object $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
            /** @phpstan-ignore catch.neverThrown */
        } catch (ReflectionException $e) {
            throw InvalidClassException::make(
                is_object($class) ? $class::class : $class,
                $e
            );
        }
    }

    /**
     * Get the reflection method for the given class/object and method.
     *
     * @param class-string|object $class
     * @param string              $method
     *
     * @return \ReflectionMethod
     *
     * @throws \Engine\Container\Exceptions\InvalidMethodException
     */
    protected function getMethodReflector(string|object $class, string $method): ReflectionMethod
    {
        try {
            if ($class instanceof ReflectionClass) {
                $className = $class->getName();

                return $class->getMethod($method);
            }

            $className = is_object($class) ? $class::class : $class;

            return new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            throw InvalidMethodException::make(
                $className,
                $method,
                $e
            );
        }
    }

    /**
     * Get the reflection function for the given function.
     *
     * @param callable $function
     *
     * @return \ReflectionFunction
     */
    protected function getFunctionReflector(callable $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function(...));
        } catch (ReflectionException $e) { // @codeCoverageIgnoreStart
            // Unreachable — the spread operator ($function(...)) always produces
            // a valid Closure, so ReflectionFunction cannot fail here.
            throw InvalidFunctionException::make(
                $this->getFunctionName($function),
                $e
            );
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get the name of the function from the given callable.
     *
     * @param callable $function
     *
     * @return string
     */
    protected function getFunctionName(callable $function): string
    {
        if (is_string($function)) {
            return $function;
        }

        if ($function instanceof Closure) {
            return '\Closure{' . spl_object_hash($function) . '}';
        }

        if (is_object($function)) {
            return $function::class . '::__invoke';
        }

        if (is_array($function)) {
            /** @var array{0: class-string|object, 1: string} $function */
            if (is_object($function[0])) {
                return $function[0]::class . '::' . $function[1];
            }

            return $function[0] . '::' . $function[1];
        }

        // Unreachable — all callable forms are handled above (string, Closure, invokable object, array).
        return 'function'; // @codeCoverageIgnore
    }

    /**
     * @template TAttribute of object
     *
     * @param ReflectionClass<*>|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflector
     * @param class-string<TAttribute>                                                   $class
     * @param bool                                                                       $instanceOf
     *
     * @return object|null
     *
     * @phpstan-return TAttribute|null
     */
    protected function getAttributeInstance(
        ReflectionClass|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflector,
        string                                                                  $class,
        bool                                                                    $instanceOf = false
    ): ?object
    {
        $attribute = $reflector->getAttributes($class, $instanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0)[0] ?? null;

        /** @var TAttribute|null $instance */
        $instance = $attribute?->newInstance();

        return $instance;
    }
}
