<?php
declare(strict_types=1);

namespace Engine\Database\Config;

use Engine\Config\Contracts\ConfigObject;
use Webmozart\Assert\Assert;

/**
 * Database Config
 * ---------------
 *
 * Represents the configuration for the database component.
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class DatabaseConfig implements ConfigObject
{
    /**
     * @param string                          $primary
     * @param array<string, ConnectionConfig> $connections
     * @param bool                            $persistent
     *
     * @return self
     */
    public static function make(string $primary, array $connections, bool $persistent = false): self
    {
        return new self($primary, $connections, $persistent);
    }

    /**
     * Create a new config object from an array.
     *
     * Creates a new instance of the config object using the provided data
     * pulled from the config storage.
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        // Validate the 'primary' config option is a non-empty string, and
        // present in the data.
        Assert::keyExists($data, 'primary', 'Primary connection is not defined.');
        Assert::stringNotEmpty($data['primary'], 'Primary connection is not defined.');

        // Validate the 'connections' config option is an array, and present in
        // the data.
        Assert::keyExists($data, 'connections', 'No connections defined.');
        Assert::isArray($data['connections'], 'No connections defined.');
        Assert::notEmpty($data['connections'], 'No connections defined.');

        // Validate that the primary connection is an actual connection.
        Assert::keyExists(
            $data['connections'],
            $data['primary'],
            'Primary connection is not present in connections list.',
        );

        if (isset($data['persistent'])) {
            Assert::boolean($data['persistent'], 'Persistent connection flag is not a boolean.');
        }

        /**
         * @var array{
         *     primary: non-empty-string,
         *     connections: array<non-empty-string, array<string, mixed>>,
         *     persistent?: bool,
         * } $data
         */

        return new self(
            $data['primary'],
            array_map(static function ($connection) {
                return ConnectionConfig::fromArray($connection);
            }, $data['connections']),
            $data['persistent'] ?? false,
        );
    }

    /**
     * @param string                          $primary
     * @param array<string, ConnectionConfig> $connections
     * @param bool                            $persistent
     */
    private function __construct(
        public string $primary,
        public array $connections,
        public bool $persistent,
    ) {
    }
}
