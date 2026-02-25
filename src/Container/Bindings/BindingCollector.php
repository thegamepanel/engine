<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

use Engine\Collectors\Contracts\Collector;

final class BindingCollector implements Collector
{
    /**
     * @var string
     */
    public readonly string $scope;

    /**
     * @var array<class-string, array<\Engine\Container\Bindings\BindingBuilder<*>>>
     */
    private(set) array $bindings = [];

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $abstract
     *
     * @return \Engine\Container\Bindings\BindingBuilder<TAbstract>
     */
    public function bind(string $abstract): BindingBuilder
    {
        return $this->bindings[$abstract][] = new BindingBuilder($this->scope, $abstract);
    }
}
