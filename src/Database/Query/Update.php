<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Query\Concerns\HasWhereClause;
use Engine\Database\Query\Contracts\Query;

/**
 *
 */
final class Update implements Query
{
    use HasWhereClause;

    public static function table(string $table): self
    {
        return new self($table);
    }

    /**
     * @var array<string, mixed>
     */
    private array $sets = [];

    private function __construct(
        private string $table,
    )
    {
    }

    /**
     * Set the column values to update.
     *
     * @param array<string, mixed> $values
     *
     * @return $this
     */
    public function set(array $values): self
    {
        $this->sets = array_merge($this->sets, $values);

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $setClauses = [];

        foreach (array_keys($this->sets) as $column) {
            $setClauses[] = "{$column} = ?";
        }

        $where = $this->hasWhereClause() ? ' WHERE ' . $this->whereClause->toSql() : '';

        return "UPDATE {$this->table} SET " . implode(', ', $setClauses) . $where;
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return array_merge(
            array_values($this->sets),
            $this->whereClause->getBindings(),
        );
    }
}
