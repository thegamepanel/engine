<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Engine\Database\Config\ConnectionConfig;
use Engine\Database\Config\DatabaseConfig;
use Engine\Database\ConnectionFactory;
use Engine\Database\Exceptions\ConnectionException;
use Engine\Database\Exceptions\DatabaseException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('connection-factory')]
class ConnectionFactoryTest extends TestCase
{
    private ConnectionConfig $validConfig;

    private ConnectionConfig $noHostConfig;

    protected function setUp(): void
    {
        $this->validConfig = ConnectionConfig::make(
            host: '127.0.0.1',
            port: 3307,
            socket: null,
            database: 'engine_test',
            username: 'engine',
            password: 'secret',
        );

        $this->noHostConfig = ConnectionConfig::make(
            host: null,
            port: null,
            socket: null,
            database: 'engine_test',
            username: 'engine',
            password: 'secret',
        );
    }

    // -------------------------------------------------------------------------
    // make() - missing config
    // -------------------------------------------------------------------------

    /**
     * - make() throws ConnectionException when the connection name has no config entry.
     */
    #[Test]
    public function makeThrowsConnectionExceptionForUnknownConnectionName(): void
    {
        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $this->validConfig]),
        );

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('No configuration found for the database connection "nonexistent"');

        $factory->make('nonexistent');
    }

    // -------------------------------------------------------------------------
    // make() - primary fallback
    // -------------------------------------------------------------------------

    /**
     * - make() with no name falls back to the primary connection and proceeds
     *   past the config-lookup step (evidenced by DatabaseException rather than
     *   ConnectionException::noConfig).
     */
    #[Test]
    public function makeWithNoNameUsesPrimaryConnection(): void
    {
        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $this->noHostConfig]),
        );

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No host or socket specified.');

        $factory->make();
    }

    // -------------------------------------------------------------------------
    // make() - DSN errors
    // -------------------------------------------------------------------------

    /**
     * - make() throws DatabaseException when the MySQL config has neither a
     *   host nor a socket.
     */
    #[Test]
    public function makeThrowsDatabaseExceptionWhenMysqlHasNoHostOrSocket(): void
    {
        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $this->noHostConfig]),
        );

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No host or socket specified.');

        $factory->make('default');
    }

    /**
     * - make() throws ConnectionException when a socket path is provided but
     *   unreachable, exercising the socket DSN branch in createMysqlPdoDsn().
     */
    #[Test]
    public function makeThrowsConnectionExceptionForUnreachableSocket(): void
    {
        $config = ConnectionConfig::make(
            host: null,
            port: null,
            socket: '/nonexistent/mysql.sock',
            database: 'engine_test',
            username: 'engine',
            password: 'secret',
        );

        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $config]),
        );

        $this->expectException(ConnectionException::class);

        $factory->make('default');
    }
}
