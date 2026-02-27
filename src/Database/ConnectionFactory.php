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
     * @var array<string, \Engine\Database\Connection>
     */
    private array $connections;

    /**
     * @var array<string, \Engine\Database\ConnectionConfig>
     */
    private array $config;

    /**
     * @param array<string, \Engine\Database\ConnectionConfig> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     *
     * @return \Engine\Database\Connection
     */
    public function make(string $name): Connection
    {
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
        return new PDO(match ($config->driver) {
            'mysql' => $this->makeMysqlDsn($config),
            'pgsql' => $this->makePgsqlDsn($config),
            default => throw new DatabaseException(
                sprintf(
                    'Unsupported database driver: %s.',
                    $config->driver
                )
            )
        });
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
