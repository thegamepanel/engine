<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Config;

use Engine\Database\Config\ConnectionConfig;
use Engine\Database\Config\DatabaseConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('database-config')]
class DatabaseConfigTest extends TestCase
{
    // -------------------------------------------------------------------------
    // make()
    // -------------------------------------------------------------------------

    /**
     * - persistent defaults to false when not provided.
     */
    #[Test]
    public function persistentDefaultsToFalse(): void
    {
        $config = DatabaseConfig::make('default', [
            'default' => ConnectionConfig::make(
                host: '127.0.0.1',
                port: 3306,
                socket: null,
                database: 'engine_test',
                username: 'engine',
                password: 'secret',
            ),
        ]);

        $this->assertFalse($config->persistent);
    }

    // -------------------------------------------------------------------------
    // fromArray()
    // -------------------------------------------------------------------------

    /**
     * - fromArray() hydrates from a valid array, defaulting persistent to false.
     */
    #[Test]
    public function fromArrayBuildsConfigFromValidArray(): void
    {
        $config = DatabaseConfig::fromArray([
            'primary'     => 'default',
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);

        $this->assertSame('default', $config->primary);
        $this->assertArrayHasKey('default', $config->connections);
        $this->assertInstanceOf(ConnectionConfig::class, $config->connections['default']);
        $this->assertFalse($config->persistent);
    }

    /**
     * - fromArray() preserves persistent=true when present.
     */
    #[Test]
    public function fromArrayPreservesPersistentTrue(): void
    {
        $config = DatabaseConfig::fromArray([
            'primary'     => 'default',
            'persistent'  => true,
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);

        $this->assertTrue($config->persistent);
    }

    /**
     * - fromArray() throws when 'primary' is missing.
     */
    #[Test]
    public function fromArrayThrowsWhenPrimaryMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary connection is not defined.');

        DatabaseConfig::fromArray([
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);
    }

    /**
     * - fromArray() throws when 'primary' is an empty string.
     */
    #[Test]
    public function fromArrayThrowsWhenPrimaryEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary connection is not defined.');

        DatabaseConfig::fromArray([
            'primary'     => '',
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);
    }

    /**
     * - fromArray() throws when 'connections' is missing.
     */
    #[Test]
    public function fromArrayThrowsWhenConnectionsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No connections defined.');

        DatabaseConfig::fromArray([
            'primary' => 'default',
        ]);
    }

    /**
     * - fromArray() throws when 'connections' is not an array.
     */
    #[Test]
    public function fromArrayThrowsWhenConnectionsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No connections defined.');

        DatabaseConfig::fromArray([
            'primary'     => 'default',
            'connections' => 'not-an-array',
        ]);
    }

    /**
     * - fromArray() throws when 'connections' is empty.
     */
    #[Test]
    public function fromArrayThrowsWhenConnectionsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No connections defined.');

        DatabaseConfig::fromArray([
            'primary'     => 'default',
            'connections' => [],
        ]);
    }

    /**
     * - fromArray() throws when the primary connection key is not present in connections.
     */
    #[Test]
    public function fromArrayThrowsWhenPrimaryNotInConnections(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary connection is not present in connections list.');

        DatabaseConfig::fromArray([
            'primary'     => 'missing',
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);
    }

    /**
     * - fromArray() throws when 'persistent' is provided but not a boolean.
     */
    #[Test]
    public function fromArrayThrowsWhenPersistentNotBoolean(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Persistent connection flag is not a boolean.');

        DatabaseConfig::fromArray([
            'primary'     => 'default',
            'persistent'  => 'yes',
            'connections' => [
                'default' => [
                    'host'     => 'localhost',
                    'port'     => 3306,
                    'database' => 'app',
                    'username' => 'app',
                    'password' => 'secret',
                ],
            ],
        ]);
    }
}
