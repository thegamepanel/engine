<?php
declare(strict_types=1);

namespace Tests\Integration\Database;

use Engine\Database\Connection;
use Engine\Database\Migrations\MigrationLedger;
use Engine\Database\Migrations\MigrationPhase;
use Engine\Database\Migrations\MigrationRunner;
use Engine\Database\Query\Select;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('integration'), Group('database'), Group('migration-runner')]
class MigrationRunnerTest extends TestCase
{
    private static Connection $connection;

    public static function setUpBeforeClass(): void
    {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                (string) (getenv('DB_HOST') ?: '127.0.0.1'),
                (int) (getenv('DB_PORT') ?: 3306),
                (string) (getenv('DB_DATABASE') ?: 'engine_test'),
            ),
            (string) (getenv('DB_USERNAME') ?: 'engine'),
            (string) (getenv('DB_PASSWORD') ?: 'secret'),
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );

        self::$connection = new Connection('test', $pdo);
    }

    public static function tearDownAfterClass(): void
    {
        self::$connection->execute('DROP TABLE IF EXISTS migration_test');
        self::$connection->execute('DROP TABLE IF EXISTS migrations');
    }

    protected function setUp(): void
    {
        self::$connection->execute('DROP TABLE IF EXISTS migration_test');
        self::$connection->execute('DROP TABLE IF EXISTS migrations');
    }

    // -------------------------------------------------------------------------
    // migrate
    // -------------------------------------------------------------------------

    /**
     * - migrate executes all phases, creating the table and seeding data.
     */
    #[Test]
    public function migrateExecutesAllPhases(): void
    {
        $runner = $this->buildRunner();
        $runner->migrate();

        // Table exists and has a seeded row
        $rows = self::$connection->query(Select::from('migration_test'))->all();

        $this->assertCount(1, $rows);
        $this->assertSame('seeded', $rows[0]->string('name'));

        // Ledger has records for both migrations
        $ledger = new MigrationLedger(self::$connection);

        $status001 = $ledger->getMigrationStatus('001_create_test_table');
        $this->assertNotNull($status001);
        $this->assertTrue($status001->schema);
        $this->assertSame('test', $status001->module);

        $status002 = $ledger->getMigrationStatus('002_seed_test_data');
        $this->assertNotNull($status002);
        $this->assertTrue($status002->data);
        $this->assertSame('test', $status002->module);
    }

    /**
     * - migrate is idempotent and does not duplicate data when run twice.
     */
    #[Test]
    public function migrateIsIdempotent(): void
    {
        $this->buildRunner()->migrate();
        $this->buildRunner()->migrate();

        $rows = self::$connection->query(Select::from('migration_test'))->all();

        $this->assertCount(1, $rows);
    }

    /**
     * - migrate with onlyPhases=[Schema] creates the table but inserts no data.
     */
    #[Test]
    public function migrateWithOnlySchemaPhaseCreatesTableButNoData(): void
    {
        $runner = $this->buildRunner();
        $runner->migrate(onlyPhases: [MigrationPhase::Schema]);

        // Table exists
        $tableResult = self::$connection->query('SHOW TABLES LIKE \'migration_test\'');
        $this->assertNotNull($tableResult->first());

        // No data rows
        $rows = self::$connection->query(Select::from('migration_test'))->all();
        $this->assertCount(0, $rows);

        // Ledger: 001 has schema=true but data=false
        $ledger    = new MigrationLedger(self::$connection);
        $status001 = $ledger->getMigrationStatus('001_create_test_table');

        $this->assertNotNull($status001);
        $this->assertTrue($status001->schema);
        $this->assertFalse($status001->data);
    }

    // -------------------------------------------------------------------------
    // rollbackBatch
    // -------------------------------------------------------------------------

    /**
     * - rollbackBatch reverses the last batch, dropping the table and clearing the ledger.
     */
    #[Test]
    public function rollbackBatchReversesLastBatch(): void
    {
        $this->buildRunner()->migrate();
        $this->buildRunner()->rollbackBatch();

        // Table no longer exists
        $tableResult = self::$connection->query('SHOW TABLES LIKE \'migration_test\'');
        $this->assertNull($tableResult->first());

        // Ledger has no records for either migration
        $ledger = new MigrationLedger(self::$connection);

        $this->assertNull($ledger->getMigrationStatus('001_create_test_table'));
        $this->assertNull($ledger->getMigrationStatus('002_seed_test_data'));
    }

    // -------------------------------------------------------------------------
    // rollbackMigration
    // -------------------------------------------------------------------------

    /**
     * - rollbackMigration reverses a specific migration, leaving others intact.
     */
    #[Test]
    public function rollbackMigrationReversesSpecificMigration(): void
    {
        $this->buildRunner()->migrate();
        $this->buildRunner()->rollbackMigration('002_seed_test_data');

        // Table still exists
        $tableResult = self::$connection->query('SHOW TABLES LIKE \'migration_test\'');
        $this->assertNotNull($tableResult->first());

        // Seeded row is gone
        $rows = self::$connection->query(Select::from('migration_test'))->all();
        $this->assertCount(0, $rows);

        // Ledger: 001 still recorded, 002 removed
        $ledger = new MigrationLedger(self::$connection);

        $this->assertNotNull($ledger->getMigrationStatus('001_create_test_table'));
        $this->assertNull($ledger->getMigrationStatus('002_seed_test_data'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildRunner(): MigrationRunner
    {
        $runner = new MigrationRunner(self::$connection);

        $runner->addMigration('test', '001_create_test_table', require __DIR__ . '/Migrations/001_create_test_table.php');
        $runner->addMigration('test', '002_seed_test_data', require __DIR__ . '/Migrations/002_seed_test_data.php');

        return $runner;
    }
}
