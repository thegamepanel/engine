<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations;

use Engine\Database\Connection;
use Engine\Database\Migrations\MigrationRunner;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Query\Insert;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[Group('unit'), Group('database'), Group('migrations'), Group('migrator')]
class MigratorTest extends TestCase
{
    private MigrationRunner $runner;

    private Migrator $migrator;

    protected function setUp(): void
    {
        $pdo          = new PDO('sqlite::memory:');
        $connection   = new Connection('test', $pdo);
        $this->runner = new MigrationRunner($connection);

        $this->runner->scope('core', 'test_migration');

        $reflection     = new ReflectionClass($this->runner);
        $this->migrator = $reflection->getProperty('migrator')->getValue($this->runner);
    }

    // -------------------------------------------------------------------------
    // Proxying to runner
    // -------------------------------------------------------------------------

    /**
     * - schema() proxies the Schema to the runner's schema bucket.
     */
    #[Test]
    public function schemaProxiesToRunnerSchemaBucket(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Index::primary('id'),
        ]);

        $this->migrator->schema($table);

        $reflection = new ReflectionClass($this->runner);
        $schemas    = $reflection->getProperty('schema')->getValue($this->runner);

        $this->assertArrayHasKey('core', $schemas);
        $this->assertArrayHasKey('test_migration', $schemas['core']);
        $this->assertCount(1, $schemas['core']['test_migration']);
        $this->assertSame($table, $schemas['core']['test_migration'][0]);
    }

    /**
     * - alter() proxies the Schema to the runner's alter bucket.
     */
    #[Test]
    public function alterProxiesToRunnerAlterBucket(): void
    {
        $table = Table::create('users', [
            Column::string('email', 255),
        ]);

        $this->migrator->alter($table);

        $reflection = new ReflectionClass($this->runner);
        $alters     = $reflection->getProperty('alter')->getValue($this->runner);

        $this->assertArrayHasKey('core', $alters);
        $this->assertArrayHasKey('test_migration', $alters['core']);
        $this->assertCount(1, $alters['core']['test_migration']);
        $this->assertSame($table, $alters['core']['test_migration'][0]);
    }

    /**
     * - data() proxies the Query to the runner's data bucket.
     */
    #[Test]
    public function dataProxiesToRunnerDataBucket(): void
    {
        $query = Insert::into('users')->values([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->migrator->data($query);

        $reflection = new ReflectionClass($this->runner);
        $data       = $reflection->getProperty('data')->getValue($this->runner);

        $this->assertArrayHasKey('core', $data);
        $this->assertArrayHasKey('test_migration', $data['core']);
        $this->assertCount(1, $data['core']['test_migration']);
        $this->assertSame($query, $data['core']['test_migration'][0]);
    }

    // -------------------------------------------------------------------------
    // Fluent chaining
    // -------------------------------------------------------------------------

    /**
     * - schema() returns self for fluent chaining.
     */
    #[Test]
    public function schemaReturnsSelfForFluentChaining(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id'),
        ]);

        $result = $this->migrator->schema($table);

        $this->assertSame($this->migrator, $result);
    }

    /**
     * - alter() returns self for fluent chaining.
     */
    #[Test]
    public function alterReturnsSelfForFluentChaining(): void
    {
        $table = Table::create('users', [
            Column::string('name', 255),
        ]);

        $result = $this->migrator->alter($table);

        $this->assertSame($this->migrator, $result);
    }

    /**
     * - data() returns self for fluent chaining.
     */
    #[Test]
    public function dataReturnsSelfForFluentChaining(): void
    {
        $query = Insert::into('users')->values([
            'name' => 'Test',
        ]);

        $result = $this->migrator->data($query);

        $this->assertSame($this->migrator, $result);
    }
}
