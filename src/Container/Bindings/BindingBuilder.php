<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

use Closure;
use Engine\Container\Contracts\Qualifier;

/**
 * @template TAbstract of object
 */
final class BindingBuilder
{
    /**
     * @var string
     */
    public readonly string $scope;

    /**
     * @var class-string<TAbstract>
     */
    public readonly string $abstract;

    /**
     * @var class-string<TAbstract>|null
     */
    private(set) ?string $concrete = null;

    /**
     * @var object|null
     *
     * @phpstan-var TAbstract|null
     */
    private(set) ?object $instance = null;

    /**
     * @var string|null
     */
    private(set) ?string $name = null;

    /**
     * @var \Engine\Container\Contracts\Qualifier|null
     */
    private(set) ?Qualifier $qualifier = null;

    /**
     * @var bool
     */
    private(set) bool $lazily = false;

    /**
     * @var bool
     */
    private(set) bool $liminal = false;

    /**
     * @var array<class-string>
     */
    private(set) array $aliases = [];

    /**
     * @var (\Closure(): TAbstract)|null
     */
    private(set) ?Closure $factory = null;

    /**
     * @param class-string<TAbstract> $abstract
     */
    public function __construct(string $scope, string $abstract)
    {
        $this->scope    = $scope;
        $this->abstract = $abstract;
    }

    /**
     * Bind to the given concrete.
     *
     * @param class-string<TAbstract>|object            $concrete
     *
     * @phpstan-param class-string<TAbstract>|TAbstract $concrete
     *
     * @return $this
     */
    public function to(string|object $concrete): self
    {
        if (is_object($concrete)) {
            $this->instance = $concrete;
        } else {
            $this->concrete = $concrete;
        }

        return $this;
    }

    /**
     * Set the name for qualification.
     *
     * @param string $name
     *
     * @return $this
     */
    public function named(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the qualifier for qualification.
     *
     * @param \Engine\Container\Contracts\Qualifier $qualifier
     *
     * @return $this
     */
    public function qualifiedBy(Qualifier $qualifier): self
    {
        $this->qualifier = $qualifier;

        return $this;
    }

    /**
     * Always resolve lazily.
     *
     * @return $this
     */
    public function lazily(): self
    {
        $this->lazily = true;

        return $this;
    }

    /**
     * Make instances liminal, allowing them to be garbage collected.
     *
     * @return $this
     */
    public function liminal(): self
    {
        $this->liminal = true;

        return $this;
    }

    /**
     * Set aliases for the binding.
     *
     * @param class-string $alias
     *
     * @return $this
     */
    public function as(string $alias): self
    {
        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * Resolve using the given factory.
     *
     * @param callable(): TAbstract $factory
     *
     * @return $this
     */
    public function using(callable $factory): self
    {
        $this->factory = $factory(...);

        return $this;
    }
}
