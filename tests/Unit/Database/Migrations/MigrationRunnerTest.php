<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations;

use Engine\Database\Connection;
use Engine\Database\Contracts\Migration;
use Engine\Database\Migrations\MigrationRunner;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Tests\Unit\Database\Migrations\Fixtures\CreatePostsTable;
use Tests\Unit\Database\Migrations\Fixtures\CreateUsersTable;
use Tests\Unit\Database\Migrations\Fixtures\DataOnlyMigration;

#[Group('unit'), Group('database'), Group('migration-runner')]
class MigrationRunnerTest extends TestCase
{
    private Connection $connection;

    private MigrationRunner $runner;

    protected function setUp(): void
    {
        $pdo              = new PDO('sqlite::memory:');
        $this->connection = new Connection('test', $pdo);
        $this->runner     = new MigrationRunner($this->connection);
    }

    // -------------------------------------------------------------------------
    // scope() and collect()
    // -------------------------------------------------------------------------

    /**
     * - scope() sets the current module and migration name.
     */
    #[Test]
    public function scopeSetsModuleAndMigrationProperties(): void
    {
        $this->runner->scope('core', '001_create_users');

        $reflection = new ReflectionClass($this->runner);

        $module    = $reflection->getProperty('module')->getValue($this->runner);
        $migration = $reflection->getProperty('migration')->getValue($this->runner);

        $this->assertSame('core', $module);
        $this->assertSame('001_create_users', $migration);
    }

    /**
     * - collect() with CreateUsersTable populates schema and data buckets.
     */
    #[Test]
    public function collectWithCreateUsersTablePopulatesSchemaAndDataBuckets(): void
    {
        $this->runner->scope('core', '001_create_users');
        $this->runner->collect(new CreateUsersTable());

        $reflection = new ReflectionClass($this->runner);
        $schemas    = $reflection->getProperty('schema')->getValue($this->runner);
        $data       = $reflection->getProperty('data')->getValue($this->runner);

        $this->assertArrayHasKey('core', $schemas);
        $this->assertArrayHasKey('001_create_users', $schemas['core']);
        $this->assertCount(1, $schemas['core']['001_create_users']);

        $this->assertArrayHasKey('core', $data);
        $this->assertArrayHasKey('001_create_users', $data['core']);
        $this->assertCount(1, $data['core']['001_create_users']);
    }

    /**
     * - collect() with CreatePostsTable populates schema and alter buckets.
     */
    #[Test]
    public function collectWithCreatePostsTablePopulatesSchemaAndAlterBuckets(): void
    {
        $this->runner->scope('core', '002_create_posts');
        $this->runner->collect(new CreatePostsTable());

        $reflection = new ReflectionClass($this->runner);
        $schemas    = $reflection->getProperty('schema')->getValue($this->runner);
        $alters     = $reflection->getProperty('alter')->getValue($this->runner);

        $this->assertArrayHasKey('core', $schemas);
        $this->assertArrayHasKey('002_create_posts', $schemas['core']);
        $this->assertCount(1, $schemas['core']['002_create_posts']);

        $this->assertArrayHasKey('core', $alters);
        $this->assertArrayHasKey('002_create_posts', $alters['core']);
        $this->assertCount(1, $alters['core']['002_create_posts']);
    }

    /**
     * - collect() with DataOnlyMigration populates only the data bucket.
     */
    #[Test]
    public function collectWithDataOnlyMigrationPopulatesOnlyDataBucket(): void
    {
        $this->runner->scope('core', '003_seed_data');
        $this->runner->collect(new DataOnlyMigration());

        $reflection = new ReflectionClass($this->runner);
        $schemas    = $reflection->getProperty('schema')->getValue($this->runner);
        $alters     = $reflection->getProperty('alter')->getValue($this->runner);
        $data       = $reflection->getProperty('data')->getValue($this->runner);

        $this->assertEmpty($schemas);
        $this->assertEmpty($alters);

        $this->assertArrayHasKey('core', $data);
        $this->assertArrayHasKey('003_seed_data', $data['core']);
        $this->assertCount(1, $data['core']['003_seed_data']);
    }

    /**
     * - Expressions from different modules are stored under separate module keys.
     */
    #[Test]
    public function expressionsFromDifferentModulesAreStoredSeparately(): void
    {
        $this->runner->scope('core', '001_create_users');
        $this->runner->collect(new CreateUsersTable());

        $this->runner->scope('blog', '001_create_posts');
        $this->runner->collect(new CreatePostsTable());

        $reflection = new ReflectionClass($this->runner);
        $schemas    = $reflection->getProperty('schema')->getValue($this->runner);

        $this->assertArrayHasKey('core', $schemas);
        $this->assertArrayHasKey('001_create_users', $schemas['core']);

        $this->assertArrayHasKey('blog', $schemas);
        $this->assertArrayHasKey('001_create_posts', $schemas['blog']);
    }

    // -------------------------------------------------------------------------
    // addMigration()
    // -------------------------------------------------------------------------

    /**
     * - addMigration() stores the migration instance keyed by module and name.
     */
    #[Test]
    public function addMigrationStoresInstanceByModuleAndName(): void
    {
        $migration = new CreateUsersTable();
        $this->runner->addMigration('core', '001_create_users', $migration);

        $reflection = new ReflectionClass($this->runner);
        $migrations = $reflection->getProperty('migrations')->getValue($this->runner);

        $this->assertArrayHasKey('core', $migrations);
        $this->assertArrayHasKey('001_create_users', $migrations['core']);
        $this->assertSame($migration, $migrations['core']['001_create_users']);
    }

    /**
     * - addMigration() can register migrations across multiple modules.
     */
    #[Test]
    public function addMigrationRegistersAcrossMultipleModules(): void
    {
        $this->runner->addMigration('core', '001_create_users', new CreateUsersTable());
        $this->runner->addMigration('blog', '001_create_posts', new CreatePostsTable());

        $reflection = new ReflectionClass($this->runner);
        $migrations = $reflection->getProperty('migrations')->getValue($this->runner);

        $this->assertArrayHasKey('core', $migrations);
        $this->assertArrayHasKey('blog', $migrations);
        $this->assertCount(1, $migrations['core']);
        $this->assertCount(1, $migrations['blog']);
    }

    /**
     * - addMigration() returns self for fluent chaining.
     */
    #[Test]
    public function addMigrationReturnsSelfForChaining(): void
    {
        $result = $this->runner->addMigration('core', '001_create_users', new CreateUsersTable());

        $this->assertSame($this->runner, $result);
    }

    // -------------------------------------------------------------------------
    // Rollback error paths
    // -------------------------------------------------------------------------

    /**
     * - rollbackMigration() throws RuntimeException for a non-reversible migration.
     */
    #[Test]
    public function rollbackMigrationThrowsForNonReversibleMigration(): void
    {
        $this->runner->addMigration('core', '001_create_posts', new CreatePostsTable());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not implement ReversibleMigration');

        $this->runner->rollbackMigration('001_create_posts');
    }

    /**
     * - rollbackMigration() throws RuntimeException for an unknown migration name.
     */
    #[Test]
    public function rollbackMigrationThrowsForUnknownMigrationName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Migration 'unknown_migration' not found.");

        $this->runner->rollbackMigration('unknown_migration');
    }
}
