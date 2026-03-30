<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Contracts\Expression;
use Engine\Database\Contracts\Query;
use Engine\Database\Query\Concerns\HasGroupByClause;
use Engine\Database\Query\Concerns\HasHavingClause;
use Engine\Database\Query\Concerns\HasJoinClause;
use Engine\Database\Query\Concerns\HasLimitClause;
use Engine\Database\Query\Concerns\HasOrderByClause;
use Engine\Database\Query\Concerns\HasWhereClause;

final class Select implements Query
{
    use HasWhereClause;
    use HasJoinClause;
    use HasOrderByClause;
    use HasLimitClause;
    use HasGroupByClause;
    use HasHavingClause;

    public static function from(Expression|string $table): self
    {
        return new self($table);
    }

    private bool $distinct = false;

    /**
     * @var array<string|Expression>
     */
    private array $columns = [];

    private function __construct(
        private Expression|string $table,
    ) {
    }

    /**
     * Set the columns to select.
     *
     * @param string|Expression ...$columns
     *
     * @return $this
     */
    public function columns(Expression|string ...$columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Add a column to select.
     *
     * @param string|Expression $column
     *
     * @return $this
     */
    public function addColumn(Expression|string $column): self
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
        $columns = empty($this->columns) ? '*' : implode(', ', array_map(
            fn (Expression|string $col) => $col instanceof Expression ? $col->toSql() : $col,
            $this->columns,
        ));
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $table    = $this->table instanceof Expression ? '(' . $this->table->toSql() . ')' : $this->table;
        $where    = $this->hasWhereClause() ? ' WHERE ' . $this->whereClause->toSql() : '';
        $having   = $this->hasHavingClause() ? ' HAVING ' . $this->havingClause->toSql() : '';

        return "SELECT {$distinct}{$columns} FROM {$table}"
             . $this->buildJoinClause()
             . $where
             . $this->buildGroupByClause()
             . $having
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
            $this->getGroupByBindings(),
            $this->havingClause->getBindings(),
            $this->getOrderByBindings(),
        );
    }
}
