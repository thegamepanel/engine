<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Query\Contracts\Query;

/**
 *
 */
final class Insert implements Query
{
    public static function into(string $table): self
    {
        return new self($table);
    }

    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    private function __construct(
        private string $table,
    )
    {
    }

    /**
     * Set the values to insert.
     *
     * @param array<string, mixed> $values
     *
     * @return $this
     */
    public function values(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $columns      = implode(', ', array_keys($this->values));
        $placeholders = implode(', ', array_fill(0, count($this->values), '?'));

        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return array_values($this->values);
    }
}
