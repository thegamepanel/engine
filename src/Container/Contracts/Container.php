<?php

namespace Engine\Container\Contracts;

use Engine\Container\Attributes\Named;
use Engine\Container\Bindings\Binding;

/**
 * Container
 *
 * The base container contract.
 */
interface Container
{
    /**
     * Get the binding for the given class.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $class
     *
     * @return \Engine\Container\Bindings\Binding<TAbstract>|null
     */
    public function binding(string $class, ?Named $name = null, ?Qualifier $qualifier = null): ?Binding;

    /**
     * Check if the given class has a binding.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $class
     *
     * @return bool
     */
    public function bound(string $class): bool;

    /**
     * Check if the given class has been resolved.
     *
     * @param class-string                               $class
     * @param \Engine\Container\Attributes\Named|null    $name
     * @param \Engine\Container\Contracts\Qualifier|null $qualifier
     *
     * @return bool
     */
    public function hasResolved(string $class, ?Named $name = null, ?Qualifier $qualifier = null): bool;

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
    public function getResolved(string $class, ?Named $name = null, ?Qualifier $qualifier = null): ?object;

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
    public function lazy(string $class): object;

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
    public function resolve(string $class, array $arguments = [], ?Named $name = null, ?Qualifier $qualifier = null, bool $liminal = false): object;

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
    public function invoke(string|object $class, string $method, array $arguments = []): mixed;

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
    public function call(callable $callable, array $arguments = []): mixed;
}
