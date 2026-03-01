<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Database\Exceptions\ConnectionException;
use Engine\Database\Exceptions\DatabaseException;
use PDO;
use PDOException;

final class ConnectionFactory
{
    /**
     * @var array<int, int|bool>
     */
    private static array $defaultOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    /**
     * @var array<string, \Engine\Database\Connection>
     */
    private array $connections;

    /**
     * @var array<string, \Engine\Database\ConnectionConfig>
     */
    private array $config;

    public readonly string $default;

    public readonly bool $persistent;

    /**
     * @param array<string, \Engine\Database\ConnectionConfig> $connectionConfig
     * @param string                                           $defaultConnection
     * @param bool                                             $persistentConnections
     */
    public function __construct(
        array  $connectionConfig,
        string $defaultConnection,
        bool   $persistentConnections = true
    )
    {
        $this->config     = $connectionConfig;
        $this->default    = $defaultConnection;
        $this->persistent = $persistentConnections;
    }

    /**
     * @param string $name
     *
     * @return \Engine\Database\Connection
     */
    public function make(?string $name = null): Connection
    {
        $name ??= $this->default;

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $config = $this->config[$name] ?? null;

        if ($config === null) {
            throw new DatabaseException(
                sprintf(
                    'No database connection configuration found for %s.',
                    $name
                )
            );
        }

        try {
            return $this->connections[$name] = new Connection(
                $name,
                $this->makePdoForConfig($config)
            );
        } catch (PDOException $e) {
            throw new ConnectionException($name, previous: $e);
        }
    }

    /**
     * @param \Engine\Database\ConnectionConfig $config
     *
     * @return \PDO
     */
    private function makePdoForConfig(ConnectionConfig $config): PDO
    {
        $options = array_merge(
            self::$defaultOptions,
            $config->options
        );

        return new PDO(match ($config->driver) {
            'mysql' => $this->makeMysqlDsn($config),
            'pgsql' => $this->makePgsqlDsn($config),
            default => throw new DatabaseException(
                sprintf(
                    'Unsupported database driver: %s.',
                    $config->driver
                )
            )
        }, $config->username, $config->password, $options);
    }

    private function makeMysqlDsn(ConnectionConfig $config): string
    {
        if ($config->socket) {
            return sprintf(
                'mysql:unix_socket=%s;dbname=%s',
                $config->socket,
                $config->database
            );
        }

        if ($config->host) {
            return sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $config->host,
                $config->port ?? 3307,
                $config->database
            );
        }

        throw new DatabaseException('No host or socket specified.');
    }

    private function makePgsqlDsn(ConnectionConfig $config): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s;sslmode=%s',
            $config->socket ?? $config->host,
            $config->port ?? 5432,
            $config->database,
            $config->username,
            $config->password,
            $config->options['sslmode'] ?? 'prefer' // @phpstan-ignore-line
        );
    }
}
