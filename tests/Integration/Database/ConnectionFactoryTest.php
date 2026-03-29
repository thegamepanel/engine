<?php
declare(strict_types=1);

namespace Tests\Integration\Database;

use Engine\Database\Config\ConnectionConfig;
use Engine\Database\Config\DatabaseConfig;
use Engine\Database\Connection;
use Engine\Database\ConnectionFactory;
use Engine\Database\Exceptions\ConnectionException;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[Group('integration'), Group('database'), Group('connection-factory')]
class ConnectionFactoryTest extends TestCase
{
    private DatabaseConfig $config;

    protected function setUp(): void
    {
        $connection = ConnectionConfig::make(
            host: (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            port: (int) (getenv('DB_PORT') ?: 3306),
            socket: null,
            database: (string) (getenv('DB_DATABASE') ?: 'engine_test'),
            username: (string) (getenv('DB_USERNAME') ?: 'engine'),
            password: (string) (getenv('DB_PASSWORD') ?: 'secret'),
        );

        $this->config = DatabaseConfig::make('primary', [
            'primary'   => $connection,
            'secondary' => $connection,
        ]);
    }

    // -------------------------------------------------------------------------
    // make() - connection creation
    // -------------------------------------------------------------------------

    /**
     * - make() returns a Connection instance for a named connection.
     */
    #[Test]
    public function makeReturnsConnectionForNamedConnection(): void
    {
        $factory = new ConnectionFactory($this->config);

        $this->assertInstanceOf(Connection::class, $factory->make('primary'));
    }

    /**
     * - make() with no name falls back to the primary connection.
     */
    #[Test]
    public function makeWithNoNameReturnsPrimaryConnection(): void
    {
        $factory = new ConnectionFactory($this->config);

        $connection = $factory->make();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame('primary', $connection->name);
    }

    /**
     * - make() uses the explicit port from config, not the default fallback.
     *
     * The secondary container has engine_test_port (not engine_test), so the
     * coalesce mutation — which ignores the explicit port and always uses 3306 —
     * will fail to find engine_test_port on the primary, killing the mutant.
     */
    #[Test]
    public function makeUsesExplicitPortOverDefault(): void
    {
        $config = ConnectionConfig::make(
            host: (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            port: (int) (getenv('DB_PORT_SECONDARY') ?: 3307),
            socket: null,
            database: (string) (getenv('DB_DATABASE_SECONDARY') ?: 'engine_test_port'),
            username: (string) (getenv('DB_USERNAME') ?: 'engine'),
            password: (string) (getenv('DB_PASSWORD') ?: 'secret'),
        );

        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $config]),
        );

        $this->assertInstanceOf(Connection::class, $factory->make('default'));
    }

    /**
     * - make() connects successfully when port is null, using the default port.
     */
    #[Test]
    public function makeConnectsWithNullPort(): void
    {
        $config = ConnectionConfig::make(
            host: (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            port: null,
            socket: null,
            database: (string) (getenv('DB_DATABASE') ?: 'engine_test'),
            username: (string) (getenv('DB_USERNAME') ?: 'engine'),
            password: (string) (getenv('DB_PASSWORD') ?: 'secret'),
        );

        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $config]),
        );

        $this->assertInstanceOf(Connection::class, $factory->make('default'));
    }

    // -------------------------------------------------------------------------
    // make() - PDO options
    // -------------------------------------------------------------------------

    /**
     * - make() applies options from $config->options on the PDO instance.
     *
     * Sets FETCH_BOTH via config options, overriding the default FETCH_ASSOC.
     * A fetched row will include numeric keys, confirming the override was applied.
     */
    #[Test]
    public function makeAppliesConnectionConfigOptions(): void
    {
        $config = ConnectionConfig::make(
            host: (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            port: (int) (getenv('DB_PORT') ?: 3306),
            socket: null,
            database: (string) (getenv('DB_DATABASE') ?: 'engine_test'),
            username: (string) (getenv('DB_USERNAME') ?: 'engine'),
            password: (string) (getenv('DB_PASSWORD') ?: 'secret'),
            options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH],
        );

        $factory    = new ConnectionFactory(DatabaseConfig::make('default', ['default' => $config]));
        $connection = $factory->make('default');

        $pdo    = new ReflectionProperty(Connection::class, 'pdo')->getValue($connection);
        $result = $pdo->query('SELECT 1 AS num')->fetch();

        $this->assertArrayHasKey(0, $result);
    }

    /**
     * - make() applies default PDO options even when $config->options is empty.
     *
     * Default options include FETCH_ASSOC, so a fetched row has only named keys.
     */
    #[Test]
    public function makeAppliesDefaultPdoOptions(): void
    {
        $factory    = new ConnectionFactory($this->config);
        $connection = $factory->make('primary');

        $pdo    = new ReflectionProperty(Connection::class, 'pdo')->getValue($connection);
        $result = $pdo->query('SELECT 1 AS num')->fetch();

        $this->assertArrayHasKey('num', $result);
        $this->assertArrayNotHasKey(0, $result);
    }

    // -------------------------------------------------------------------------
    // make() - connection caching
    // -------------------------------------------------------------------------

    /**
     * - make() called twice with the same name returns the same instance.
     */
    #[Test]
    public function makeReturnsSameInstanceOnSubsequentCallsForSameName(): void
    {
        $factory = new ConnectionFactory($this->config);

        $this->assertSame($factory->make('primary'), $factory->make('primary'));
    }

    /**
     * - make() with different names returns different instances.
     */
    #[Test]
    public function makeReturnsDifferentInstancesForDifferentNames(): void
    {
        $factory = new ConnectionFactory($this->config);

        $this->assertNotSame($factory->make('primary'), $factory->make('secondary'));
    }

    // -------------------------------------------------------------------------
    // make() - socket connection
    // -------------------------------------------------------------------------

    /**
     * - make() connects via a Unix socket when socket is set and host is null.
     */
    #[Test]
    public function makeConnectsViaSocket(): void
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Unix socket connections are only testable on Linux (Docker Desktop on macOS does not proxy sockets to the host).');
        }

        $socketPath = (string) (getenv('DB_SOCKET') ?: '/tmp/mysql_socket/mysqld.sock');

        $config = ConnectionConfig::make(
            host: null,
            port: null,
            socket: $socketPath,
            database: (string) (getenv('DB_DATABASE_SOCKET') ?: 'engine_test_socket'),
            username: (string) (getenv('DB_USERNAME') ?: 'engine'),
            password: (string) (getenv('DB_PASSWORD') ?: 'secret'),
        );

        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $config]),
        );

        $this->assertInstanceOf(Connection::class, $factory->make('default'));
    }

    // -------------------------------------------------------------------------
    // make() - connection failure
    // -------------------------------------------------------------------------

    /**
     * - make() throws ConnectionException when the credentials are invalid.
     */
    #[Test]
    public function makeThrowsConnectionExceptionOnBadCredentials(): void
    {
        $badConfig = ConnectionConfig::make(
            host: (string) (getenv('DB_HOST') ?: '127.0.0.1'),
            port: (int) (getenv('DB_PORT') ?: 3306),
            socket: null,
            database: 'engine_test',
            username: 'invalid_user',
            password: 'invalid_password',
        );

        $factory = new ConnectionFactory(
            DatabaseConfig::make('default', ['default' => $badConfig]),
        );

        $this->expectException(ConnectionException::class);

        $factory->make('default');
    }
}
