<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Container\Container;
use Engine\Container\Contracts\Resolver;
use Engine\Container\Dependency;
use Engine\Database\Attributes\Database;
use Engine\Database\Exceptions\DatabaseException;
use ReflectionNamedType;

/**
 * Database Resolver
 * -----------------
 *
 * Custom {@see Resolver} implementation to inject database connections.
 *
 * @implements \Engine\Container\Contracts\Resolver<\Engine\Database\Attributes\Database>
 */
final readonly class DatabaseResolver implements Resolver
{
    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $factory;

    public function __construct(ConnectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Resolve a dependency.
     *
     * @template TType of \Engine\Database\Connection
     *
     * @param \Engine\Container\Dependency<TType, *, *> $dependency
     * @param Container            $container
     * @param array<string, mixed> $arguments
     *
     * @return Connection
     */
    public function resolve(Dependency $dependency, Container $container, array $arguments = []): Connection
    {
        $database = $dependency->resolvable;

        if (! $database instanceof Database) {
            throw new DatabaseException(sprintf(
                'The database connection resolver can only resolve parameters using the "%s" attribute.',
                Database::class,
            ));
        }

        if (
            ! $dependency->type instanceof ReflectionNamedType
            || $dependency->type->getName() !== Connection::class
        ) {
            throw new DatabaseException(sprintf(
                'The database connection resolver can only resolve parameters of the type "%s".',
                Connection::class,
            ));
        }

        return $this->factory->make($database->name);
    }
}
