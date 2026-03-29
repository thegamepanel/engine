<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Config;

use Engine\Database\Config\ConnectionConfig;
use Engine\Database\Config\DatabaseConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('database-config')]
class DatabaseConfigTest extends TestCase
{
    // -------------------------------------------------------------------------
    // persistent
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
}
