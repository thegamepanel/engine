<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

final readonly class BindingRegistry
{
    /**
     * @param array<\Engine\Container\Bindings\Binding<*>> $bindings
     *
     * @return array<string, array<\Engine\Container\Bindings\Binding<*>>>
     */
    private static function groupBindingsByScope(array $bindings): array
    {
        $scoped = [];

        foreach ($bindings as $binding) {
            $scoped[$binding->scope][] = $binding;
        }

        return $scoped;
    }

    /**
     * @param array<\Engine\Container\Bindings\Binding<*>> $bindings
     *
     * @return array<class-string, class-string>
     */
    private static function collectBindingAliases(array $bindings): array
    {
        $aliases = [];

        foreach ($bindings as $binding) {
            foreach ($binding->aliases as $alias) {
                $aliases[$alias] = $binding->abstract;
            }
        }

        return $aliases;
    }

    /**
     * @var array<class-string, \Engine\Container\Bindings\Binding<*>>
     */
    public array $bindings;

    /**
     * @var array<string, array<\Engine\Container\Bindings\Binding<*>>>
     */
    public array $scopedBindings;

    /**
     * @var array<class-string, class-string>
     */
    public array $aliases;

    /**
     * @param array<class-string, \Engine\Container\Bindings\Binding<*>> $bindings
     */
    public function __construct(array $bindings)
    {
        $this->bindings       = $bindings;
        $this->scopedBindings = self::groupBindingsByScope($bindings);
        $this->aliases        = self::collectBindingAliases($bindings);
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
    public function get(string $class): ?Binding
    {
        if (isset($this->bindings[$class])) {
            /** @var \Engine\Container\Bindings\Binding<TAbstract> */
            return $this->bindings[$class];
        }

        /** @var class-string<TAbstract>|null $alias */
        $alias = $this->aliases[$class] ?? null;

        if ($alias !== null) {
            /** @var \Engine\Container\Bindings\Binding<TAbstract> */
            return $this->bindings[$alias];
        }

        return null;
    }

    /**
     * Check if the container has a binding for the given class.
     *
     * @template TAbstract of object
     *
     * @param class-string<TAbstract> $class
     *
     * @return bool
     */
    public function has(string $class): bool
    {
        return isset($this->bindings[$class])
               || isset($this->aliases[$class], $this->bindings[$this->aliases[$class]]);
    }
}
