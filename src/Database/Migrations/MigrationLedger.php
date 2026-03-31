<?php
declare(strict_types=1);

namespace Engine\Database\Migrations;

use Engine\Database\Connection;
use Engine\Database\Exceptions\QueryException;
use Engine\Database\Query\Delete;
use Engine\Database\Query\Insert;
use Engine\Database\Query\Raw;
use Engine\Database\Query\Select;
use Engine\Database\Query\Update;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;
use RuntimeException;

final class MigrationLedger
{
    private Connection $connection;

    /** @var array<string, MigrationStatus>|null */
    private ?array $statuses = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create the migrations tracking table if it does not already exist.
     *
     * @throws QueryException
     */
    public function ensureTable(): void
    {
        $this->connection->execute(Table::create('migrations', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::string('migration', 255),
            Column::int('batch')->unsigned(),
            Column::string('module', 255),
            Column::timestamp('migrated_at')->default(Raw::from('CURRENT_TIMESTAMP')),
            Column::boolean('schema')->default(false),
            Column::boolean('alter')->default(false),
            Column::boolean('data')->default(false),
            Column::boolean('errored')->default(false),
            Index::primary('id'),
            Index::unique('migrations_migration_unique', 'migration'),
        ])->ifNotExists());

        $this->loadStatuses();
    }

    /**
     * Get the next batch number.
     *
     * @return int
     *
     * @throws QueryException
     */
    public function nextBatch(): int
    {
        $row = $this->connection->query(
            Select::from('migrations')
                ->columns(Raw::from('COALESCE(MAX(batch), 0) as max_batch')),
        )->first();

        if ($row === null) {
            return 1;
        }

        return $row->int('max_batch') + 1;
    }

    /**
     * Record a migration in the ledger.
     *
     * @param string $migration
     * @param string $module
     * @param int    $batch
     *
     * @throws RuntimeException if the migration already exists in the ledger
     * @throws QueryException
     */
    public function record(string $migration, string $module, int $batch): void
    {
        try {
            $this->connection->execute(
                Insert::into('migrations')->values([
                    'migration' => $migration,
                    'module'    => $module,
                    'batch'     => $batch,
                ]),
            );
        } catch (QueryException $e) {
            throw new RuntimeException(sprintf(
                'Cannot record migration \'%s\': a record already exists in the ledger.',
                $migration,
            ), 0, $e);
        }

        $status = new MigrationStatus($migration, $module, $batch);

        if ($this->statuses !== null) {
            $this->statuses[$migration] = $status;
        }
    }

    /**
     * Mark a migration phase as completed.
     *
     * @param string         $migration
     * @param MigrationPhase $phase
     *
     * @throws RuntimeException if the migration does not exist in the ledger
     * @throws QueryException
     */
    public function markPhase(string $migration, MigrationPhase $phase): void
    {
        $result = $this->connection->execute(
            Update::table('migrations')
                ->set([$phase->value => true])
                ->where('migration', '=', $migration),
        );

        if (! $result->wasSuccessful()) {
            throw new RuntimeException(sprintf(
                'Cannot mark phase \'%s\' for migration \'%s\': migration not found in ledger.',
                $phase->value,
                $migration,
            ));
        }

        if (isset($this->statuses[$migration])) {
            $this->statuses[$migration]->markPhase($phase->value);
        }
    }

    /**
     * Mark a migration as errored.
     *
     * @param string $migration
     *
     * @throws RuntimeException if the migration does not exist in the ledger
     * @throws QueryException
     */
    public function markErrored(string $migration): void
    {
        $result = $this->connection->execute(
            Update::table('migrations')
                ->set(['errored' => true])
                ->where('migration', '=', $migration),
        );

        if (! $result->wasSuccessful()) {
            throw new RuntimeException(sprintf(
                'Cannot mark migration \'%s\' as errored: migration not found in ledger.',
                $migration,
            ));
        }

        if (isset($this->statuses[$migration])) {
            $this->statuses[$migration]->markErrored();
        }
    }

    /**
     * Clear the error flag for a migration.
     *
     * @param string $migration
     *
     * @throws RuntimeException if the migration does not exist in the ledger
     * @throws QueryException
     */
    public function clearError(string $migration): void
    {
        $result = $this->connection->execute(
            Update::table('migrations')
                ->set(['errored' => 0])
                ->where('migration', '=', $migration),
        );

        if (! $result->wasSuccessful()) {
            throw new RuntimeException(sprintf(
                'Cannot clear error for migration \'%s\': migration not found in ledger.',
                $migration,
            ));
        }

        if (isset($this->statuses[$migration])) {
            $this->statuses[$migration]->clearError();
        }
    }

    /**
     * Get the status for a specific migration.
     *
     * @param string $migration
     *
     * @return MigrationStatus|null
     *
     * @throws QueryException
     */
    public function getMigrationStatus(string $migration): ?MigrationStatus
    {
        if ($this->statuses !== null) {
            return $this->statuses[$migration] ?? null;
        }

        $row = $this->connection->query(
            Select::from('migrations')
                ->where('migration', '=', $migration),
        )->first();

        if ($row === null) {
            return null;
        }

        return MigrationStatus::fromRow($row);
    }

    /**
     * Get all migrations for a specific batch.
     *
     * @param int $batch
     *
     * @return list<MigrationStatus>
     *
     * @throws QueryException
     */
    public function getByBatch(int $batch): array
    {
        if ($this->statuses !== null) {
            return array_values(array_filter(
                $this->statuses,
                static fn (MigrationStatus $status): bool => $status->batch === $batch,
            ));
        }

        $statuses = [];

        foreach ($this->connection->query(
            Select::from('migrations')
                ->where('batch', '=', $batch),
        )->all() as $row) {
            $statuses[] = MigrationStatus::fromRow($row);
        }

        return $statuses;
    }

    /**
     * Remove a migration record from the ledger.
     *
     * @param string $migration
     *
     * @throws RuntimeException if the migration does not exist in the ledger
     * @throws QueryException
     */
    public function remove(string $migration): void
    {
        $result = $this->connection->execute(
            Delete::from('migrations')
                ->where('migration', '=', $migration),
        );

        if (! $result->wasSuccessful()) {
            throw new RuntimeException(sprintf(
                'Cannot remove migration \'%s\': migration not found in ledger.',
                $migration,
            ));
        }

        if ($this->statuses !== null) {
            unset($this->statuses[$migration]);
        }
    }

    /**
     * Get all recorded migration statuses, keyed by migration name.
     *
     * @return array<string, MigrationStatus>
     *
     * @throws QueryException
     */
    public function getAllStatuses(): array
    {
        if ($this->statuses !== null) {
            return $this->statuses;
        }

        return $this->loadStatuses();
    }

    /**
     * Determine if a migration has been run.
     *
     * @param string $migration
     *
     * @return bool
     *
     * @throws QueryException
     */
    public function hasRun(string $migration): bool
    {
        return $this->getMigrationStatus($migration) !== null;
    }

    /**
     * Load all migration statuses from the database into the cache.
     *
     * @return array<string, MigrationStatus>
     *
     * @throws QueryException
     */
    private function loadStatuses(): array
    {
        $this->statuses = [];

        foreach ($this->connection->query(Select::from('migrations'))->all() as $row) {
            $this->statuses[$row->string('migration')] = MigrationStatus::fromRow($row);
        }

        return $this->statuses;
    }
}
