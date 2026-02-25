<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

use Closure;

/**
 * @template TAbstract of object
 */
final readonly class Binding
{
    /**
     * @var string
     */
    public string $scope;

    /**
     * @var class-string<TAbstract>
     */
    public string $abstract;

    /**
     * @var class-string<TAbstract>|null
     */
    public ?string $concrete;

    /**
     * @var TAbstract|null
     */
    public ?object $instance;

    /**
     * @var array<string, self<TAbstract>>
     */
    public array $nameMap;

    /**
     * @var array<class-string<\Engine\Container\Contracts\Qualifier>, self<TAbstract>>
     */
    public array $qualifierMap;

    /**
     * @var array<class-string>
     */
    public array $aliases;

    public bool $shared;

    public bool $lazily;

    /**
     * @var (\Closure(): TAbstract)|null
     */
    public ?Closure $factory;

    /**
     * @param string                                                                      $scope
     * @param class-string<TAbstract>                                                     $abstract
     * @param class-string<TAbstract>|null                                                $concrete
     * @param TAbstract|null                                                              $instance
     * @param array<string, self<TAbstract>>                                              $nameMap
     * @param array<class-string<\Engine\Container\Contracts\Qualifier>, self<TAbstract>> $qualifierMap
     * @param array<class-string>                                                         $aliases
     * @param bool                                                                        $shared
     * @param bool                                                                        $lazily
     * @param (\Closure(): TAbstract)|null                                                $factory
     */
    public function __construct(
        string   $scope,
        string   $abstract,
        ?string  $concrete = null,
        ?object  $instance = null,
        array    $nameMap = [],
        array    $qualifierMap = [],
        array    $aliases = [],
        bool     $shared = true,
        bool     $lazily = false,
        ?Closure $factory = null
    )
    {
        $this->scope        = $scope;
        $this->abstract     = $abstract;
        $this->concrete     = $concrete;
        $this->instance     = $instance;
        $this->nameMap      = $nameMap;
        $this->qualifierMap = $qualifierMap;
        $this->aliases      = $aliases;
        $this->shared       = $shared;
        $this->lazily       = $lazily;
        $this->factory      = $factory;
    }

    /**
     * Check if the binding is bound to an instance.
     *
     * @return bool
     *
     * @phpstan-assert-if-true TAbstract $this->instance
     */
    public function isBoundToInstance(): bool
    {
        return $this->instance !== null;
    }

    /**
     * Check if the binding is shared.
     *
     * @return bool
     *
     * @phpstan-assert-if-true true $this->shared
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Check if the binding should resolve lazily.
     *
     * @return bool
     *
     * @phpstan-assert-if-true true $this->lazily
     */
    public function shouldResolveLazily(): bool
    {
        return $this->lazily;
    }

    /**
     * Check if the binding has a factory.
     *
     * @return bool
     *
     * @phpstan-assert-if-true \Closure(): TAbstract $this->factory
     */
    public function hasFactory(): bool
    {
        return $this->factory !== null;
    }

    /**
     * Get a sub-binding by its name.
     *
     * @param string $name
     *
     * @return \Engine\Container\Bindings\Binding<TAbstract>|null
     */
    public function byName(string $name): ?self
    {
        return $this->nameMap[$name] ?? null;
    }

    /**
     * Get a sub-binding by its qualifier.
     *
     * @param class-string<\Engine\Container\Contracts\Qualifier> $qualifier
     *
     * @return \Engine\Container\Bindings\Binding<TAbstract>|null
     */
    public function forQualifier(string $qualifier): ?self
    {
        return $this->qualifierMap[$qualifier] ?? null;
    }
}
