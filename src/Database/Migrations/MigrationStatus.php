<?php
declare(strict_types=1);

namespace Engine\Database\Migrations;

use Engine\Database\Query\Row;

final class MigrationStatus
{
    /**
     * Create a MigrationStatus from a database Row.
     *
     * @param Row $row
     *
     * @return self
     */
    public static function fromRow(Row $row): self
    {
        return new self(
            $row->string('migration'),
            $row->string('module'),
            $row->int('batch'),
            $row->bool('schema'),
            $row->bool('alter'),
            $row->bool('data'),
            $row->bool('errored'),
        );
    }

    public function __construct(
        public readonly string $migration,
        public readonly string $module,
        public readonly int    $batch,
        public bool            $schema = false,
        public bool            $alter = false,
        public bool            $data = false,
        public bool            $errored = false,
    ) {
    }

    /**
     * Check whether a named phase has completed.
     *
     * @param string $phase
     *
     * @return bool
     */
    public function phaseCompleted(string $phase): bool
    {
        return match ($phase) {
            'schema' => $this->schema,
            'alter'  => $this->alter,
            'data'   => $this->data,
            default  => false,
        };
    }

    /**
     * Mark a named phase as completed.
     *
     * @param string $phase
     */
    public function markPhase(string $phase): void
    {
        match ($phase) {
            'schema' => $this->schema = true,
            'alter'  => $this->alter  = true,
            'data'   => $this->data   = true,
            default  => null,
        };
    }

    /**
     * Mark the migration as errored.
     */
    public function markErrored(): void
    {
        $this->errored = true;
    }

    /**
     * Clear the errored flag.
     */
    public function clearError(): void
    {
        $this->errored = false;
    }
}
