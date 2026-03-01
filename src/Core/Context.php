<?php
declare(strict_types=1);

namespace Engine\Core;

use Engine\Auth\User;
use Engine\Server\Server;

final class Context
{
    public readonly OperatingContext $operatingContext;

    public readonly ?User $user;

    public readonly ?Server $server;

    /**
     * @var array<class-string, object>
     */
    private array $resolved = [];

    public function __construct(
        OperatingContext $operatingContext,
        ?User            $user,
        ?Server          $server
    )
    {
        $this->operatingContext = $operatingContext;
        $this->user             = $user;
        $this->server           = $server;
    }

    /**
     * Check if the context has a resolved instance of the given class.
     *
     * @param class-string $class
     *
     * @return bool
     *
     * @phpstan-assert-if-true object $this->get()
     */
    public function has(string $class): bool
    {
        return $this->get($class) !== null;
    }

    /**
     * Get a resolved instance of the given class.
     *
     * @template TResolved of object
     *
     * @param class-string<TResolved> $class
     *
     * @return TResolved|null
     */
    public function get(string $class): ?object
    {
        /** @var TResolved|null $instance */
        $instance = $this->resolved[$class] ?? null;

        return $instance;
    }
}
