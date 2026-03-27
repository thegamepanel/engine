<?php
declare(strict_types=1);

namespace Engine\Container\Resolvers;

final class ResolverRegistry
{
    /**
     * @var class-string<\Engine\Container\Contracts\Resolver<null>>|null
     */
    private(set) ?string $default = null;

    /**
     * @var array<class-string<\Engine\Container\Contracts\Resolvable>, class-string<\Engine\Container\Contracts\Resolver<*>>>
     */
    private(set) array $resolvers = [];

    /**
     * @param class-string<\Engine\Container\Contracts\Resolver<null>> $resolver
     *
     * @return static
     */
    public function default(string $resolver): self
    {
        $this->default = $resolver;

        return $this;
    }

    /**
     * @template TResolvable of \Engine\Container\Contracts\Resolvable
     *
     * @param class-string<TResolvable>                                       $resolvable
     * @param class-string<\Engine\Container\Contracts\Resolver<TResolvable>> $resolver
     *
     * @return self
     */
    public function register(string $resolvable, string $resolver): self
    {
        $this->resolvers[$resolvable] = $resolver;

        return $this;
    }
}
