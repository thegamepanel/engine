<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Closure;
use Engine\Database\Query\Clauses\WhereClause;

trait HasHavingClause
{
    private(set) public WhereClause $havingClause {
        get => $this->havingClause ?? $this->havingClause = new WhereClause();
    }

    /**
     * Add a having clause to the query.
     *
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed|null     $value
     *
     * @return static
     */
    public function having(Closure|string $column, ?string $operator = null, mixed $value = null): static
    {
        $this->havingClause->where($column, $operator, $value);

        return $this;
    }

    /**
     * Add an "or having" clause to the query.
     *
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed|null     $value
     *
     * @return static
     */
    public function orHaving(Closure|string $column, ?string $operator = null, mixed $value = null): static
    {
        $this->havingClause->orWhere($column, $operator, $value);

        return $this;
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return static
     */
    public function havingRaw(string $sql, array $bindings = []): static
    {
        $this->havingClause->whereRaw($sql, $bindings);

        return $this;
    }

    /**
     * Add a raw "or having" clause to the query.
     *
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return static
     */
    public function orHavingRaw(string $sql, array $bindings = []): static
    {
        $this->havingClause->orWhereRaw($sql, $bindings);

        return $this;
    }

    protected function hasHavingClause(): bool
    {
        return $this->havingClause->isEmpty() === false;
    }
}
