<?php
declare(strict_types=1);

namespace Engine\Database\Config;

use Engine\Config\BaseConfigObject;

/**
 * Database Config
 * ---------------
 *
 * Represents the configuration for the database component.
 *
 * @phpstan-type DatabaseConfigArray array{
 *     primary: string,
 *     connections: array<string, ConnectionConfig>,
 * }
 *
 * @extends BaseConfigObject<DatabaseConfigArray>
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class DatabaseConfig extends BaseConfigObject
{
    /**
     * @param string                          $primary
     * @param array<string, ConnectionConfig> $connections
     *
     * @return DatabaseConfig
     */
    public static function make(string $primary, array $connections): self
    {
        return new self($primary, $connections);
    }

    /**
     * @param string                          $primary
     * @param array<string, ConnectionConfig> $connections
     */
    private function __construct(
        public string $primary,
        public array $connections,
    ) {
        assert(! empty($this->primary), 'Primary connection is not defined.');
        assert(! empty($this->connections), 'No connections defined.');
        assert(isset($this->connections[$this->primary]), 'Primary connection is not defined.');
    }
}
