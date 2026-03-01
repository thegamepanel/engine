<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Query\Concerns\HasJoinClause;
use Engine\Database\Query\Concerns\HasLimitClause;
use Engine\Database\Query\Concerns\HasOrderByClause;
use Engine\Database\Query\Concerns\HasWhereClause;
use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Contracts\Query;

/**
 *
 */
final class Select implements Query
{
    use HasWhereClause;
    use HasJoinClause;
    use HasOrderByClause;
    use HasLimitClause;

    public static function from(string|Expression $table): self
    {
        return new self($table);
    }

    private bool $distinct = false;

    /**
     * @var array<string|\Engine\Database\Query\Contracts\Expression>
     */
    private array $columns = [];

    private function __construct(
        private string|Expression $table,
    )
    {
    }

    /**
     * Set the columns to select.
     *
     * @param string|\Engine\Database\Query\Contracts\Expression ...$columns
     *
     * @return $this
     */
    public function columns(string|Expression ...$columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Add a column to select.
     *
     * @param string|\Engine\Database\Query\Contracts\Expression $column
     *
     * @return $this
     */
    public function addColumn(string|Expression $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * Set the query to select distinct rows.
     *
     * @return $this
     */
    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $columns  = empty($this->columns) ? '*' : implode(', ', array_map(
            fn (string|Expression $col) => $col instanceof Expression ? $col->toSql() : $col,
            $this->columns,
        ));
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $table    = $this->table instanceof Expression ? '(' . $this->table->toSql() . ')' : $this->table;
        $where    = $this->hasWhereClause() ? ' WHERE ' . $this->whereClause->toSql() : '';

        return "SELECT {$distinct}{$columns} FROM {$table}"
             . $this->buildJoinClause()
             . $where
             . $this->buildOrderByClause()
             . $this->buildLimitClause();
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        $bindings = [];

        // Table subquery bindings
        if ($this->table instanceof Expression) {
            $bindings = $this->table->getBindings();
        }

        // Column expression bindings
        foreach ($this->columns as $column) {
            if ($column instanceof Expression) {
                $bindings = array_merge($bindings, $column->getBindings());
            }
        }

        return array_merge(
            $bindings,
            $this->getJoinBindings(),
            $this->whereClause->getBindings(),
            $this->getOrderByBindings(),
        );
    }
}
