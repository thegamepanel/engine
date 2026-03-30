<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Contracts\Expression;
use Engine\Database\Contracts\Query;

final class Insert implements Query
{
    public static function into(string $table): self
    {
        return new self($table);
    }

    /**
     * @var list<array<string, mixed>>
     */
    private array $rows = [];

    private bool $ignore = false;

    private bool $replace = false;

    /**
     * @var array<string, mixed|Expression>
     */
    private array $upsertValues = [];

    private function __construct(
        private string $table,
    ) {
    }

    /**
     * Add a row of values to insert.
     *
     * @param array<string, mixed> $values
     *
     * @return static
     */
    public function values(array $values): self
    {
        $this->rows[] = $values;

        return $this;
    }

    /**
     * Set the query to use INSERT IGNORE.
     *
     * @return static
     */
    public function ignore(): self
    {
        $this->ignore = true;

        return $this;
    }

    /**
     * Set the query to use REPLACE INTO instead of INSERT INTO.
     *
     * @return static
     */
    public function replace(): self
    {
        $this->replace = true;

        return $this;
    }

    /**
     * Set the ON DUPLICATE KEY UPDATE values.
     *
     * @param array<string, mixed|Expression> $values
     *
     * @return static
     */
    public function upsert(array $values): self
    {
        $this->upsertValues = $values;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $columns      = implode(', ', array_keys($this->rows[0]));
        $placeholders = implode(', ', array_fill(0, count($this->rows[0]), '?'));
        $tuples       = implode(', ', array_fill(0, count($this->rows), "({$placeholders})"));

        if ($this->replace) {
            $prefix = 'REPLACE INTO';
        } else if ($this->ignore) {
            $prefix = 'INSERT IGNORE INTO';
        } else {
            $prefix = 'INSERT INTO';
        }

        $sql = "{$prefix} {$this->table} ({$columns}) VALUES {$tuples}";

        if (! empty($this->upsertValues)) {
            $updates = [];

            foreach ($this->upsertValues as $column => $value) {
                if ($value instanceof Expression) {
                    $updates[] = "{$column} = {$value->toSql()}";
                } else {
                    $updates[] = "{$column} = ?";
                }
            }

            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);
        }

        return $sql;
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        $bindings = [];

        foreach ($this->rows as $row) {
            array_push($bindings, ...array_values($row));
        }

        foreach ($this->upsertValues as $value) {
            if ($value instanceof Expression) {
                array_push($bindings, ...$value->getBindings());
            } else {
                $bindings[] = $value;
            }
        }

        return $bindings;
    }
}
