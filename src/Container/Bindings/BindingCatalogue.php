<?php
declare(strict_types=1);

namespace Engine\Container\Bindings;

final readonly class BindingCatalogue
{
    /**
     * The collection of registered bindings.
     *
     * @var array<class-string, \Engine\Container\Bindings\Binding<*>>
     */
    public array $bindings;

    /**
     * The collection of aliases mapped to their bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $aliases;

    /**
     * The collection of scopes mapped to their bindings.
     *
     * @var array<string, array<class-string>>
     */
    public array $scoped;

    /**
     * @param array<class-string, \Engine\Container\Bindings\Binding<*>> $bindings
     * @param array<class-string, class-string>                          $aliases
     * @param array<string, array<class-string>>                         $scoped
     */
    public function __construct(
        array $bindings,
        array $aliases,
        array $scoped,
    )
    {
        $this->bindings = $bindings;
        $this->aliases  = $aliases;
        $this->scoped   = $scoped;
    }
}
