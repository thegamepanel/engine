<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Database\Config\ConnectionConfig;
use Engine\Database\Config\DatabaseConfig;
use Engine\Database\Exceptions\ConnectionException;
use Engine\Database\Exceptions\DatabaseException;
use PDO;
use PDOException;
use Webmozart\Assert\Assert;

/**
 * Connection factory
 * ------------------
 *
 * Responsible for creating database connections and storing them in a cache.
 */
final class ConnectionFactory
{
    /**
     * Default PDO options.
     *
     * @var array<int, int|bool>
     */
    private static array $defaultOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    /**
     * Cached database connections.
     *
     * @var array<string, Connection>
     */
    private array $connections = [];

    /**
     * @param DatabaseConfig $config
     */
    public function __construct(
        private readonly DatabaseConfig $config,
    ) {
    }

    /**
     * Get a database connection.
     *
     * Retrieves a cached connection if it exists, otherwise creates a new one.
     * If no name is provided, the primary connection is returned.
     *
     * @param string|null $name
     *
     * @return Connection
     */
    public function make(?string $name = null): Connection
    {
        $name ??= $this->config->primary;

        return $this->connections[$name] ?? ($this->connections[$name] = $this->createConnection($name));
    }

    /**
     * Create a new database connection.
     *
     * @param string $name
     *
     * @return Connection
     */
    private function createConnection(string $name): Connection
    {
        // Grab the config.
        $config = $this->config->connections[$name] ?? null;

        // In theory, this should never happen, but it's here just in case.
        if ($config === null) {
            throw ConnectionException::noConfig($name);
        }

        try {
            // Create the connection.
            return new Connection(
                $name,
                $this->createPdo($config),
            );
        } catch (PDOException $e) {
            // If there was a PDO problem, throw an exception.
            throw ConnectionException::cannotConnect($name, $e);
        }
    }

    /**
     * Create a PDO instance.
     *
     * @param ConnectionConfig $config
     *
     * @return PDO
     */
    private function createPdo(ConnectionConfig $config): PDO
    {
        $options = $config->options + self::$defaultOptions;

        // This should only ever create a MySQL instance because the driver is
        // hardcoded, but this is here for the future.
        return new PDO(match ($config->driver) {
            'mysql' => $this->createMysqlPdoDsn($config),
            default => throw new DatabaseException(
                sprintf(
                    'Unsupported database driver: %s.',
                    $config->driver,
                ),
            ),
        }, $config->username, $config->password, $options);
    }

    /**
     * Create a PDO DSN string for MySQL.
     *
     * @param ConnectionConfig $config
     *
     * @return string
     */
    private function createMysqlPdoDsn(ConnectionConfig $config): string
    {
        if ($config->socket !== null) {
            return sprintf(
                'mysql:unix_socket=%s;dbname=%s',
                $config->socket,
                $config->database,
            );
        }

        // ConnectionConfig's constructor invariants guarantee these are
        // non-null when socket is null. The asserts encode that invariant
        // for both PHPStan and any future maintainer.
        Assert::notNull($config->host, 'Host must be set when socket is null.');
        Assert::notNull($config->port, 'Port must be set when socket is null.');

        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s',
            $config->host,
            $config->port,
            $config->database,
        );
    }
}
