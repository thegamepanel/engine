<?php
declare(strict_types=1);

namespace Engine\Container;

/**
 * Invocation
 * ----------
 *
 * Represents a deferred invocation of a callable, class method, or constructor,
 * with optional pre-supplied arguments for dependency injection.
 */
final class Invocation
{
    /**
     * @param callable $function
     *
     * @return static
     */
    public static function callable(callable $function): self
    {
        return new self($function);
    }

    /**
     * @param object|class-string $on
     * @param string              $method
     *
     * @return static
     */
    public static function method(object|string $on, string $method): self
    {
        return new self($method, $on);
    }

    /**
     * @param class-string|object $class
     *
     * @return static
     */
    public static function constructor(object|string $class): self
    {
        return self::method($class, '__construct');
    }

    /**
     * @var callable|string
     */
    public readonly mixed $invokable;

    /**
     * @var object|class-string|null
     */
    public readonly object|string|null $class;

    /**
     * @var array<string, mixed>
     */
    private(set) array $arguments = [];

    /**
     * @param callable|string          $invoke
     * @param object|class-string|null $on
     */
    private function __construct(callable|string $invoke, object|string|null $on = null)
    {
        $this->invokable = $invoke;
        $this->class     = $on;
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return static
     */
    public function with(array $arguments): self
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true callable $this->invokable
     */
    public function isCallable(): bool
    {
        return is_callable($this->invokable);
    }

    public function isClassMethodCall(): bool
    {
        return $this->class !== null && is_string($this->invokable);
    }
}
