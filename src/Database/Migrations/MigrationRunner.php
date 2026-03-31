<?php
declare(strict_types=1);

namespace Engine\Database\Migrations;

use Engine\Database\Attributes\Database;
use Engine\Database\Connection;
use Engine\Database\Contracts\Migration;
use Engine\Database\Contracts\Query;
use Engine\Database\Contracts\ReversibleMigration;
use Engine\Database\Contracts\Schema;
use RuntimeException;
use Throwable;

final class MigrationRunner
{
    /**
     * @var array<string, array<string, array<Schema>>>
     */
    private array $schema = [];

    /**
     * @var array<string, array<string, array<Schema>>>
     */
    private array $alter = [];

    /**
     * @var array<string, array<string, array<Query>>>
     */
    private array $data = [];

    private Connection $connection;

    private Migrator $migrator;

    private MigrationLedger $ledger;

    /**
     * @var array<string, array<string, Migration>>
     */
    private array $migrations = [];

    private string $module;

    private string $migration;

    public function __construct(#[Database] Connection $connection)
    {
        $this->connection = $connection;
        $this->migrator   = new Migrator($this);
        $this->ledger     = new MigrationLedger($connection);
    }

    /**
     * Register a migration with the runner.
     *
     * @param string    $module
     * @param string    $name
     * @param Migration $migration
     *
     * @return static
     */
    public function addMigration(string $module, string $name, Migration $migration): self
    {
        $this->migrations[$module][$name] = $migration;

        return $this;
    }

    /**
     * Set the current module and migration context.
     *
     * @param string $module
     * @param string $migration
     *
     * @return static
     */
    public function scope(string $module, string $migration): self
    {
        $this->module    = $module;
        $this->migration = $migration;

        return $this;
    }

    /**
     * Collect migration expressions by calling the migration's up method.
     *
     * @param Migration $migration
     *
     * @return static
     */
    public function collect(Migration $migration): self
    {
        $migration->up($this->migrator);

        return $this;
    }

    /**
     * Append a schema expression for the current scope.
     *
     * @param Schema $schema
     *
     * @return static
     */
    public function schema(Schema $schema): self
    {
        $this->schema[$this->module][$this->migration][] = $schema;

        return $this;
    }

    /**
     * Append an alter expression for the current scope.
     *
     * @param Schema $schema
     *
     * @return static
     */
    public function alter(Schema $schema): self
    {
        $this->alter[$this->module][$this->migration][] = $schema;

        return $this;
    }

    /**
     * Append a data expression for the current scope.
     *
     * @param Query $query
     *
     * @return static
     */
    public function data(Query $query): self
    {
        $this->data[$this->module][$this->migration][] = $query;

        return $this;
    }

    /**
     * Run all pending migrations.
     *
     * @param MigrationPhase|null  $fromPhase
     * @param list<MigrationPhase> $onlyPhases
     *
     * @throws Throwable
     */
    public function migrate(?MigrationPhase $fromPhase = null, array $onlyPhases = []): void
    {
        $this->ledger->ensureTable();

        $batch  = $this->ledger->nextBatch();
        $phases = $this->resolvePhases($fromPhase, $onlyPhases);

        foreach ($this->migrations as $module => $migrations) {
            foreach ($migrations as $name => $migration) {
                if (! $this->shouldRun($name, $fromPhase)) {
                    continue;
                }

                $this->scope($module, $name);
                $migration->up($this->migrator);

                if (! $this->ledger->hasRun($name)) {
                    $this->ledger->record($name, $module, $batch);
                }
            }
        }

        foreach ($phases as $phase) {
            $this->executePhase($phase);
        }
    }

    /**
     * Rollback migrations for a specific batch.
     *
     * @param int|null $batch
     *
     * @throws RuntimeException
     */
    public function rollbackBatch(?int $batch = null): void
    {
        $this->ledger->ensureTable();

        if ($batch === null) {
            $batch = $this->ledger->nextBatch() - 1;
        }

        if ($batch < 1) {
            return;
        }

        $statuses = array_reverse($this->ledger->getByBatch($batch));

        foreach ($statuses as $status) {
            $migration = $this->findMigration($status->migration);

            if (! $migration instanceof ReversibleMigration) {
                throw new RuntimeException(sprintf(
                    'Migration \'%s\' does not implement ReversibleMigration and cannot be rolled back.',
                    $status->migration,
                ));
            }

            $this->scope($this->findModule($status->migration), $status->migration);
            $migration->down($this->migrator);
        }

        $reversePhases = [MigrationPhase::Data, MigrationPhase::Alter, MigrationPhase::Schema];

        foreach ($reversePhases as $phase) {
            $this->executePhase($phase, force: true);
        }

        foreach ($statuses as $status) {
            $this->ledger->remove($status->migration);
            $this->clearCollected($this->findModule($status->migration), $status->migration);
        }
    }

    /**
     * Rollback a single migration by name.
     *
     * @param string $migrationName
     *
     * @throws RuntimeException
     */
    public function rollbackMigration(string $migrationName): void
    {
        $migration = $this->findMigration($migrationName);

        if (! $migration instanceof ReversibleMigration) {
            throw new RuntimeException(
                "Migration '{$migrationName}' does not implement ReversibleMigration and cannot be rolled back.",
            );
        }

        $module = $this->findModule($migrationName);

        $this->scope($module, $migrationName);
        $migration->down($this->migrator);

        $reversePhases = [MigrationPhase::Data, MigrationPhase::Alter, MigrationPhase::Schema];

        foreach ($reversePhases as $phase) {
            $this->executePhase($phase, force: true);
        }

        $this->ledger->remove($migrationName);
        $this->clearCollected($module, $migrationName);
    }

    /**
     * Resolve which phases to execute.
     *
     * @param MigrationPhase|null  $fromPhase
     * @param list<MigrationPhase> $onlyPhases
     *
     * @return list<MigrationPhase>
     */
    private function resolvePhases(?MigrationPhase $fromPhase, array $onlyPhases): array
    {
        $allPhases = [MigrationPhase::Schema, MigrationPhase::Alter, MigrationPhase::Data];

        if (! empty($onlyPhases)) {
            return $onlyPhases;
        }

        if ($fromPhase !== null) {
            $index = array_search($fromPhase, $allPhases, true);

            if ($index === false) {
                return $allPhases;
            }

            return array_slice($allPhases, $index);
        }

        return $allPhases;
    }

    /**
     * Determine if a migration should be run.
     *
     * @param string              $migration
     * @param MigrationPhase|null $fromPhase
     *
     * @return bool
     */
    private function shouldRun(string $migration, ?MigrationPhase $fromPhase): bool
    {
        $status = $this->ledger->getMigrationStatus($migration);

        if ($status === null) {
            return true;
        }

        if ($status->errored) {
            return true;
        }

        if ($fromPhase !== null) {
            return true;
        }

        return false;
    }

    /**
     * Execute all expressions for a given phase.
     *
     * @param MigrationPhase $phase
     * @param bool           $force skip phase-completion checks (used for rollback)
     *
     * @throws Throwable
     */
    private function executePhase(MigrationPhase $phase, bool $force = false): void
    {
        $bucket = match ($phase) {
            MigrationPhase::Schema => $this->schema,
            MigrationPhase::Alter  => $this->alter,
            MigrationPhase::Data   => $this->data,
        };

        foreach ($bucket as $module => $migrations) {
            foreach ($migrations as $migrationName => $expressions) {
                $status = $this->ledger->getMigrationStatus($migrationName);

                if (! $force && $status !== null && $status->phaseCompleted($phase->value)) {
                    continue;
                }

                $isDataPhase = $phase === MigrationPhase::Data;

                if ($isDataPhase) {
                    $this->connection->beginTransaction();
                }

                try {
                    foreach ($expressions as $expression) {
                        $this->connection->execute($expression);
                    }

                    if (! $force) {
                        $this->ledger->markPhase($migrationName, $phase);
                    }

                    if ($isDataPhase) {
                        $this->connection->commit();
                    }
                } catch (Throwable $e) {
                    if ($isDataPhase) {
                        $this->connection->rollback();
                    }

                    if (! $force) {
                        $this->ledger->markErrored($migrationName);
                    }

                    throw $e;
                }
            }
        }
    }

    /**
     * Find a migration instance by its name.
     *
     * @param string $name
     *
     * @return Migration
     *
     * @throws RuntimeException
     */
    private function findMigration(string $name): Migration
    {
        foreach ($this->migrations as $migrations) {
            if (isset($migrations[$name])) {
                return $migrations[$name];
            }
        }

        throw new RuntimeException("Migration '{$name}' not found.");
    }

    /**
     * Find the module name for a given migration.
     *
     * @param string $name
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function findModule(string $name): string
    {
        foreach ($this->migrations as $module => $migrations) {
            if (isset($migrations[$name])) {
                return $module;
            }
        }

        throw new RuntimeException("Migration '{$name}' not found.");
    }

    /**
     * Clear collected expressions for a specific module and migration.
     *
     * @param string $module
     * @param string $migration
     */
    private function clearCollected(string $module, string $migration): void
    {
        unset(
            $this->schema[$module][$migration],
            $this->alter[$module][$migration],
            $this->data[$module][$migration],
        );
    }
}
