<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

use Closure;
use Engine\Container\Contracts\Qualifier;

/**
 * Binding Builder
 * ---------------
 *
 * Used to programmatically build bindings.
 *
 * @template TAbstract of object
 */
final class BindingBuilder
{
    /**
     * The module scope of the binding.
     *
     * @var string
     */
    public readonly string $scope;

    /**
     * The main class being bound.
     *
     * @var class-string<TAbstract>
     */
    public readonly string $abstract;

    /**
     * The concrete class being bound to.
     *
     * @var class-string<TAbstract>|null
     */
    private(set) ?string $concrete = null;

    /**
     * The instance being bound to.
     *
     * @var TAbstract|null
     */
    private(set) ?object $instance = null;

    /**
     * Other classes to alias this binding.
     *
     * @var array<class-string<TAbstract>>
     */
    private(set) array $aliases = [];

    /**
     * The factory to use when resolving this binding.
     *
     * @var (Closure(): TAbstract)|null
     */
    private(set) Closure|null $factory = null;

    /**
     * The name to identify this binding by.
     *
     * @var string|null
     */
    private(set) ?string $named = null;

    /**
     * The qualifier class to identify this binding by.
     *
     * @var class-string<Qualifier>|null
     */
    private(set) ?string $qualifier = null;

    /**
     * Flag indicating whether this binding should be resolved lazily.
     *
     * @var bool
     */
    private(set) bool $liminal = false;

    /**
     * Flag indicating whether this binding should be resolved lazily.
     *
     * @var bool
     */
    private(set) bool $lazily = false;

    /**
     * Flag indicating whether this binding should be shared.
     *
     * @var bool
     */
    private(set) bool $shared = true;

    /**
     * @param string                  $scope
     * @param class-string<TAbstract> $abstract
     */
    public function __construct(string $scope, string $abstract)
    {
        $this->scope    = $scope;
        $this->abstract = $abstract;
    }

    /**
     * Bind to the given concrete class and/or instance.
     *
     * @param class-string<TAbstract>|TAbstract $concrete
     *
     * @return static
     */
    public function to(object|string $concrete): self
    {
        $this->concrete = is_object($concrete) ? $concrete::class : $concrete;
        $this->instance = is_object($concrete) ? $concrete : null;

        return $this;
    }

    /**
     * Also bind to the given aliases.
     *
     * @param class-string<TAbstract> ...$aliases
     *
     * @return static
     */
    public function as(string ...$aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Use the given factory to resolve the binding.
     *
     * @param Closure(): TAbstract $factory
     *
     * @return static
     */
    public function using(Closure $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Identify the binding by the given name.
     *
     * @param string $name
     *
     * @return static
     */
    public function named(string $name): self
    {
        $this->named = $name;

        return $this;
    }

    /**
     * Identify the binding by the given qualifier.
     *
     * @param class-string<Qualifier> $qualifier
     *
     * @return static
     */
    public function qualifier(string $qualifier): self
    {
        $this->qualifier = $qualifier;

        return $this;
    }

    /**
     * Flag the binding as liminal.
     *
     * @return static
     */
    public function liminal(): self
    {
        $this->liminal = true;

        return $this;
    }

    /**
     * Flag the binding as lazily resolved.
     *
     * @return static
     */
    public function lazily(): self
    {
        $this->lazily = true;

        return $this;
    }

    /**
     * Flag the binding as not shared.
     *
     * @return static
     */
    public function notShared(): self
    {
        $this->shared = false;

        return $this;
    }
}
