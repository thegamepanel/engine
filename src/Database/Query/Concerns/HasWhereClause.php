<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Closure;
use Engine\Database\Contracts\Expression;
use Engine\Database\Query\Clauses\WhereClause;

trait HasWhereClause
{
    private(set) protected WhereClause $whereClause {
        get => $this->whereClause ?? $this->whereClause = new WhereClause();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed|null     $value
     *
     * @return static
     */
    public function where(Closure|string $column, ?string $operator = null, mixed $value = null): static
    {
        $this->whereClause->where($column, $operator, $value);

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed|null     $value
     *
     * @return static
     */
    public function orWhere(Closure|string $column, ?string $operator = null, mixed $value = null): static
    {
        $this->whereClause->orWhere($column, $operator, $value);

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     *
     * @return static
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
     * @return static
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
     * @return static
     */
    public function whereNotNull(string $column): static
    {
        $this->whereClause->whereNotNull($column);

        return $this;
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function orWhereNotNull(string $column): static
    {
        $this->whereClause->orWhereNotNull($column);

        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string                  $column
     * @param array<mixed>|Expression $values
     *
     * @return static
     */
    public function whereIn(string $column, array|Expression $values): static
    {
        $this->whereClause->whereIn($column, $values);

        return $this;
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string                  $column
     * @param array<mixed>|Expression $values
     *
     * @return static
     */
    public function whereNotIn(string $column, array|Expression $values): static
    {
        $this->whereClause->whereNotIn($column, $values);

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string                  $column
     * @param array<mixed>|Expression $values
     *
     * @return static
     */
    public function orWhereIn(string $column, array|Expression $values): static
    {
        $this->whereClause->orWhereIn($column, $values);

        return $this;
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string                  $column
     * @param array<mixed>|Expression $values
     *
     * @return static
     */
    public function orWhereNotIn(string $column, array|Expression $values): static
    {
        $this->whereClause->orWhereNotIn($column, $values);

        return $this;
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return static
     */
    public function whereRaw(string $sql, array $bindings = []): static
    {
        $this->whereClause->whereRaw($sql, $bindings);

        return $this;
    }

    /**
     * Add a raw "or where" clause to the query.
     *
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return static
     */
    public function orWhereRaw(string $sql, array $bindings = []): static
    {
        $this->whereClause->orWhereRaw($sql, $bindings);

        return $this;
    }

    /**
     * Add a full-text search where clause to the query.
     *
     * @param array<string> $columns
     * @param string        $value
     * @param string        $mode
     *
     * @return static
     */
    public function whereFullText(array $columns, string $value, string $mode = 'natural'): static
    {
        $this->whereClause->whereFullText($columns, $value, $mode);

        return $this;
    }

    /**
     * Add an "or" full-text search where clause to the query.
     *
     * @param array<string> $columns
     * @param string        $value
     * @param string        $mode
     *
     * @return static
     */
    public function orWhereFullText(array $columns, string $value, string $mode = 'natural'): static
    {
        $this->whereClause->orWhereFullText($columns, $value, $mode);

        return $this;
    }

    protected function hasWhereClause(): bool
    {
        return $this->whereClause->isEmpty() === false;
    }
}
