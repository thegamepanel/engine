<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Closure;
use Engine\Database\Query\Clauses\WhereClause;
use Engine\Database\Query\Contracts\Expression;

trait HasWhereClause
{
    private(set) protected WhereClause $whereClause {
        get => $this->whereClause ?? $this->whereClause = new WhereClause();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param string|\Closure $column
     * @param mixed|null      $operatorOrValue
     * @param mixed|null      $value
     *
     * @return $this
     */
    public function where(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): static
    {
        $this->whereClause->where(...func_get_args());

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param string|\Closure $column
     * @param mixed|null      $operatorOrValue
     * @param mixed|null      $value
     *
     * @return $this
     */
    public function orWhere(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): static
    {
        $this->whereClause->orWhere(...func_get_args());

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function whereNull(string $column): static
    {
        $this->whereClause->whereNull($column);

        return $this;
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function orWhereNull(string $column): static
    {
        $this->whereClause->orWhereNull($column);

        return $this;
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function whereNotNull(string $column): static
    {
        $this->whereClause->whereNotNull($column);

        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string                                                   $column
     * @param array<mixed>|\Engine\Database\Query\Contracts\Expression $values
     *
     * @return $this
     */
    public function whereIn(string $column, array|Expression $values): static
    {
        $this->whereClause->whereIn($column, $values);

        return $this;
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string                                                   $column
     * @param array<mixed>|\Engine\Database\Query\Contracts\Expression $values
     *
     * @return $this
     */
    public function whereNotIn(string $column, array|Expression $values): static
    {
        $this->whereClause->whereNotIn($column, $values);

        return $this;
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return $this
     */
    public function whereRaw(string $sql, array $bindings = []): static
    {
        $this->whereClause->whereRaw($sql, $bindings);

        return $this;
    }

    protected function hasWhereClause(): bool
    {
        return $this->whereClause->isEmpty() === false;
    }
}
