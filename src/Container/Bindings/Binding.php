<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

use Closure;

/**
 * Binding
 * -------
 *
 * Represents the binding of an abstract within the container. The abstract can
 * be bound to a concrete implementation, by either class name or instance, or
 * to a factory containing explicit resolution logic.
 *
 * @package Container\Bindings
 *
 * @template TAbstract of object
 */
final readonly class Binding
{
    /**
     * Create a new binding from a builder instance.
     *
     * @param \Engine\Container\Bindings\BindingBuilder<TAbstract>                        $builder
     * @param array<string, self<TAbstract>>                                              $namedBindings
     * @param array<class-string<\Engine\Container\Contracts\Qualifier>, self<TAbstract>> $qualifiedBindings
     *
     * @return self<TAbstract>
     */
    public static function from(
        BindingBuilder $builder,
        array          $namedBindings = [],
        array          $qualifiedBindings = [],
    ): self
    {
        // If there's no concrete, use the instance if it's set, otherwise null.
        $concrete = $builder->concrete ?? ($builder->instance ? $builder->instance::class : null);

        /**
         * Ensure that the concrete is also an alias.
         * @var array<class-string<TAbstract>> $aliases
         */
        $aliases = array_merge([$concrete], $builder->aliases);

        return new self(
            $builder->abstract,
            $concrete,
            $builder->instance,
            $aliases,
            $builder->factory,
            $namedBindings,
            $qualifiedBindings,
            $builder->liminal,
            $builder->lazily,
            $builder->shared,
        );
    }

    /**
     * The main class being bound.
     *
     * @var class-string<TAbstract>
     */
    public string $abstract;

    /**
     * The concrete class being bound to.
     *
     * @var class-string<TAbstract>|null
     */
    public ?string $concrete;

    /**
     * The instance being bound to.
     *
     * @var TAbstract|null
     */
    public ?object $instance;

    /**
     * Other classes to alias this binding.
     *
     * @var array<class-string<TAbstract>>
     */
    public array $aliases;

    /**
     * The factory to use when resolving this binding.
     *
     * @var (\Closure(): TAbstract)|null
     */
    public ?Closure $factory;

    /**
     * A mapping of child bindings based on their name.
     *
     * @var array<string, self<TAbstract>>
     */
    public array $namedMap;

    /**
     * A mapping of child bindings based on their qualifiers.
     *
     * @var array<class-string<\Engine\Container\Contracts\Qualifier>, self<TAbstract>>
     */
    public array $qualifiedMap;

    /**
     * Flag indicating whether this binding should be resolved with a liminal instance.
     *
     * @var bool
     */
    public bool $liminal;

    /**
     * Flag indicating whether this binding should be resolved lazily.
     *
     * @var bool
     */
    public bool $lazily;

    /**
     * Flag indicating whether this binding should be shared.
     *
     * @var bool
     */
    public bool $shared;

    /**
     * @param class-string<TAbstract>                                                     $abstract
     * @param class-string<TAbstract>|null                                                $concrete
     * @param TAbstract|null                                                              $instance
     * @param array<class-string<TAbstract>>                                              $aliases
     * @param (\Closure(): TAbstract)|null                                                $factory
     * @param array<string, self<TAbstract>>                                              $namedMap
     * @param array<class-string<\Engine\Container\Contracts\Qualifier>, self<TAbstract>> $qualifiedMap
     * @param bool                                                                        $liminal
     * @param bool                                                                        $lazily
     * @param bool                                                                        $shared
     */
    public function __construct(
        string   $abstract,
        ?string  $concrete = null,
        ?object  $instance = null,
        array    $aliases = [],
        ?Closure $factory = null,
        array    $namedMap = [],
        array    $qualifiedMap = [],
        bool     $liminal = false,
        bool     $lazily = false,
        bool     $shared = true,
    )
    {
        $this->abstract     = $abstract;
        $this->concrete     = $concrete;
        $this->instance     = $instance;
        $this->aliases      = $aliases;
        $this->factory      = $factory;
        $this->namedMap     = $namedMap;
        $this->qualifiedMap = $qualifiedMap;
        $this->liminal      = $liminal;
        $this->lazily       = $lazily;
        $this->shared       = $shared;
    }
}
