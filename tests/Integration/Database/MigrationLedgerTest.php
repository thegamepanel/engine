<?php
declare(strict_types=1);

namespace Tests\Integration\Database;

use Engine\Database\Connection;
use Engine\Database\Migrations\MigrationLedger;
use Engine\Database\Migrations\MigrationPhase;
use Engine\Database\Migrations\MigrationStatus;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[Group('integration'), Group('database'), Group('migration-ledger')]
class MigrationLedgerTest extends TestCase
{
    private static Connection $connection;

    private MigrationLedger $ledger;

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
        self::$connection->execute('DROP TABLE IF EXISTS migrations');
    }

    protected function setUp(): void
    {
        self::$connection->execute('DROP TABLE IF EXISTS migrations');
        $this->ledger = new MigrationLedger(self::$connection);
        $this->ledger->ensureTable();
    }

    // -------------------------------------------------------------------------
    // ensureTable
    // -------------------------------------------------------------------------

    /**
     * - ensureTable creates the migrations table.
     */
    #[Test]
    public function ensureTableCreatesTheMigrationsTable(): void
    {
        $result = self::$connection->query('SHOW TABLES LIKE \'migrations\'');

        $this->assertNotNull($result->first());
    }

    /**
     * - ensureTable is idempotent and can be called twice without error.
     */
    #[Test]
    public function ensureTableIsIdempotent(): void
    {
        $this->ledger->ensureTable();

        $result = self::$connection->query('SHOW TABLES LIKE \'migrations\'');

        $this->assertNotNull($result->first());
    }

    // -------------------------------------------------------------------------
    // record and getMigrationStatus
    // -------------------------------------------------------------------------

    /**
     * - record inserts a row and getMigrationStatus retrieves it with correct values.
     */
    #[Test]
    public function recordAndGetMigrationStatusRetrievesCorrectValues(): void
    {
        $this->ledger->record('2024_01_01_000000_create_users_table', 'core', 1);

        $status = $this->ledger->getMigrationStatus('2024_01_01_000000_create_users_table');

        $this->assertNotNull($status);
        $this->assertInstanceOf(MigrationStatus::class, $status);
        $this->assertSame('2024_01_01_000000_create_users_table', $status->migration);
        $this->assertSame('core', $status->module);
        $this->assertSame(1, $status->batch);
        $this->assertFalse($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
        $this->assertFalse($status->errored);
    }

    /**
     * - getMigrationStatus returns null for a non-existent migration.
     */
    #[Test]
    public function getMigrationStatusReturnsNullForNonExistentMigration(): void
    {
        $row = $this->ledger->getMigrationStatus('non_existent_migration');

        $this->assertNull($row);
    }

    // -------------------------------------------------------------------------
    // nextBatch
    // -------------------------------------------------------------------------

    /**
     * - nextBatch returns 1 when no migrations exist.
     */
    #[Test]
    public function nextBatchReturnsOneWhenNoMigrationsExist(): void
    {
        $this->assertSame(1, $this->ledger->nextBatch());
    }

    /**
     * - nextBatch returns max batch plus one after records exist.
     */
    #[Test]
    public function nextBatchReturnsMaxBatchPlusOneAfterRecordsExist(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->record('migration_b', 'core', 2);
        $this->ledger->record('migration_c', 'core', 3);

        $this->assertSame(4, $this->ledger->nextBatch());
    }

    // -------------------------------------------------------------------------
    // markPhase
    // -------------------------------------------------------------------------

    /**
     * - markPhase sets the schema flag to true while other flags remain false.
     */
    #[Test]
    public function markPhaseSetsSchemaFlagToTrue(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->markPhase('migration_a', MigrationPhase::Schema);

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertTrue($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
    }

    /**
     * - markPhase can mark all three phases independently.
     */
    #[Test]
    public function markPhaseCanMarkAllThreePhasesIndependently(): void
    {
        $this->ledger->record('migration_a', 'core', 1);

        $this->ledger->markPhase('migration_a', MigrationPhase::Schema);
        $this->ledger->markPhase('migration_a', MigrationPhase::Alter);
        $this->ledger->markPhase('migration_a', MigrationPhase::Data);

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertTrue($status->schema);
        $this->assertTrue($status->alter);
        $this->assertTrue($status->data);
    }

    // -------------------------------------------------------------------------
    // markErrored and clearError
    // -------------------------------------------------------------------------

    /**
     * - markErrored sets the errored flag to true.
     */
    #[Test]
    public function markErroredSetsErroredFlag(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->markErrored('migration_a');

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertTrue($status->errored);
    }

    /**
     * - clearError resets the errored flag to false.
     */
    #[Test]
    public function clearErrorResetsErroredFlag(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->markErrored('migration_a');
        $this->ledger->clearError('migration_a');

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertFalse($status->errored);
    }

    // -------------------------------------------------------------------------
    // getByBatch
    // -------------------------------------------------------------------------

    /**
     * - getByBatch returns all migrations in the given batch.
     */
    #[Test]
    public function getByBatchReturnsAllMigrationsInGivenBatch(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->record('migration_b', 'core', 1);
        $this->ledger->record('migration_c', 'auth', 2);

        $batch1 = $this->ledger->getByBatch(1);
        $batch2 = $this->ledger->getByBatch(2);

        $this->assertCount(2, $batch1);
        $this->assertCount(1, $batch2);

        $this->assertContainsOnlyInstancesOf(MigrationStatus::class, $batch1);
        $this->assertContainsOnlyInstancesOf(MigrationStatus::class, $batch2);
    }

    /**
     * - getByBatch returns an empty result for a non-existent batch.
     */
    #[Test]
    public function getByBatchReturnsEmptyResultForNonExistentBatch(): void
    {
        $result = $this->ledger->getByBatch(999);

        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // remove
    // -------------------------------------------------------------------------

    /**
     * - remove deletes the migration record so getMigrationStatus returns null.
     */
    #[Test]
    public function removeDeletesMigrationRecord(): void
    {
        $this->ledger->record('migration_a', 'core', 1);

        $this->assertNotNull($this->ledger->getMigrationStatus('migration_a'));

        $this->ledger->remove('migration_a');

        $this->assertNull($this->ledger->getMigrationStatus('migration_a'));
    }

    // -------------------------------------------------------------------------
    // hasRun
    // -------------------------------------------------------------------------

    /**
     * - hasRun returns true for a recorded migration.
     */
    #[Test]
    public function hasRunReturnsTrueForRecordedMigration(): void
    {
        $this->ledger->record('migration_a', 'core', 1);

        $this->assertTrue($this->ledger->hasRun('migration_a'));
    }

    /**
     * - hasRun returns false for a non-existent migration.
     */
    #[Test]
    public function hasRunReturnsFalseForNonExistentMigration(): void
    {
        $this->assertFalse($this->ledger->hasRun('non_existent_migration'));
    }

    // -------------------------------------------------------------------------
    // record() exception paths
    // -------------------------------------------------------------------------

    /**
     * - record throws RuntimeException for a duplicate migration name.
     */
    #[Test]
    public function recordThrowsRuntimeExceptionForDuplicateMigration(): void
    {
        $this->ledger->record('migration_a', 'core', 1);

        $this->expectException(RuntimeException::class);

        $this->ledger->record('migration_a', 'core', 2);
    }

    // -------------------------------------------------------------------------
    // remove() exception paths
    // -------------------------------------------------------------------------

    /**
     * - remove throws RuntimeException for a non-existent migration.
     */
    #[Test]
    public function removeThrowsRuntimeExceptionForNonExistentMigration(): void
    {
        $this->expectException(RuntimeException::class);

        $this->ledger->remove('non_existent_migration');
    }

    // -------------------------------------------------------------------------
    // Cache consistency
    // -------------------------------------------------------------------------

    /**
     * - Cache is consistent after record then getMigrationStatus.
     */
    #[Test]
    public function cacheIsConsistentAfterRecordThenGet(): void
    {
        $this->ledger->record('migration_a', 'core', 1);

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertInstanceOf(MigrationStatus::class, $status);
        $this->assertSame('migration_a', $status->migration);
        $this->assertSame('core', $status->module);
        $this->assertSame(1, $status->batch);
        $this->assertFalse($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
        $this->assertFalse($status->errored);
    }

    /**
     * - Cache is consistent after markPhase then getMigrationStatus.
     */
    #[Test]
    public function cacheIsConsistentAfterMarkPhaseThenGet(): void
    {
        $this->ledger->record('migration_a', 'core', 1);
        $this->ledger->markPhase('migration_a', MigrationPhase::Schema);

        $status = $this->ledger->getMigrationStatus('migration_a');

        $this->assertNotNull($status);
        $this->assertTrue($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
        $this->assertFalse($status->errored);
    }
}
