<?php
declare(strict_types=1);

namespace Engine\Database\Config;

use Engine\Config\Contracts\ConfigObject;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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
     * @param array<array-key, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        // Array-shape assertions only. Value-rule assertions live in the
        // constructor so that make() cannot bypass them.
        Assert::keyExists($data, 'primary', 'Primary connection is not defined.');
        Assert::keyExists($data, 'connections', 'No connections defined.');
        Assert::isArray($data['connections'], 'No connections defined.');

        if (isset($data['persistent'])) {
            Assert::boolean($data['persistent'], 'Persistent connection flag is not a boolean.');
        }

        /**
         * @var array{
         *     primary: string,
         *     connections: array<string, array<string, mixed>>,
         *     persistent?: bool,
         * } $data
         */

        return new self(
            $data['primary'],
            self::hydrateConnections($data['connections']),
            $data['persistent'] ?? false,
        );
    }

    /**
     * Hydrate an array of raw connection arrays into ConnectionConfig instances.
     *
     * @param array<string, mixed> $connections
     *
     * @return array<string, ConnectionConfig>
     */
    private static function hydrateConnections(array $connections): array
    {
        $hydrated = [];

        foreach ($connections as $name => $connection) {
            $message = 'Connection \'' . $name . '\' is invalid: ';

            // isMap narrows mixed → array<string, mixed>; notEmpty then rules
            // out the empty array case.
            Assert::isMap($connection, $message . 'Config is not an array');
            Assert::notEmpty($connection, $message . 'Config is not an array');

            try {
                $hydrated[$name] = ConnectionConfig::fromArray($connection);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException($message . $e->getMessage(), $e->getCode(), $e);
            }
        }

        return $hydrated;
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
        Assert::stringNotEmpty($primary, 'Primary connection is not defined.');
        Assert::notEmpty($connections, 'No connections defined.');
        Assert::keyExists($connections, $primary, 'Primary connection is not present in connections list.');
        Assert::allIsInstanceOf($connections, ConnectionConfig::class, 'Connections must be ConnectionConfig instances.');
    }
}
