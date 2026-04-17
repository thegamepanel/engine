<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Config;

use Engine\Database\Config\ConnectionConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('connection-config')]
class ConnectionConfigTest extends TestCase
{
    // -------------------------------------------------------------------------
    // __set_state()
    // -------------------------------------------------------------------------

    /**
     * - __set_state() restores a ConnectionConfig from its exported array form.
     */
    #[Test]
    public function setStateRestoresConfigFromExportedArray(): void
    {
        $options = [\PDO::ATTR_TIMEOUT => 5];

        $config = ConnectionConfig::__set_state([
            'host'     => '127.0.0.1',
            'port'     => 3306,
            'socket'   => null,
            'database' => 'engine_test',
            'username' => 'engine',
            'password' => 'secret',
            'options'  => $options,
        ]);

        $this->assertSame('127.0.0.1', $config->host);
        $this->assertSame(3306, $config->port);
        $this->assertNull($config->socket);
        $this->assertSame('engine_test', $config->database);
        $this->assertSame('engine', $config->username);
        $this->assertSame('secret', $config->password);
        $this->assertSame($options, $config->options);
        $this->assertSame('mysql', $config->driver);
    }
}
