<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

final class BindingRegistry
{
    /**
     * @var array<class-string, array<\Engine\Container\Bindings\BindingBuilder<*>>>
     */
    private(set) array $bindings = [];

    /**
     * @var string
     */
    private(set) string $scope;

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Create and register a new binding builder.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $abstract
     *
     * @return BindingBuilder<TAbstract>
     */
    public function bind(string $abstract): BindingBuilder
    {
        return $this->bindings[$abstract][] = new BindingBuilder($this->scope, $abstract);
    }
}
