<?php
declare(strict_types=1);

namespace Engine\Database\Query\Clauses;

use Closure;
use Engine\Database\Contracts\Expression;
use Engine\Database\Exceptions\InvalidExpressionException;
use Engine\Database\Query\Expressions;
use Engine\Database\Query\Expressions\MatchAgainst;

final class WhereClause implements Expression
{
    /**
     * @var list<array{conjunction: 'AND'|'OR', expression: Expression, grouped: bool}>
     */
    private array $conditions = [];

    /**
     * Add a basic where clause to the query.
     *
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed|null     $value
     *
     * @return static
     */
    public function where(Closure|string $column, ?string $operator = null, mixed $value = null): self
    {
        if ($column instanceof Closure) {
            $this->condition('AND', $column, null, null);
        } else {
            $this->condition('AND', $column, $operator, $value);
        }

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
    public function orWhere(Closure|string $column, ?string $operator = null, mixed $value = null): self
    {
        if ($column instanceof Closure) {
            $this->condition('OR', $column, null, null);
        } else {
            $this->condition('OR', $column, $operator, $value);
        }

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function whereNull(string $column): self
    {
        $this->condition('AND', $column, 'IS NULL', null);

        return $this;
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function orWhereNull(string $column): self
    {
        $this->condition('OR', $column, 'IS NULL', null);

        return $this;
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function whereNotNull(string $column): self
    {
        $this->condition('AND', $column, 'IS NOT NULL', null);

        return $this;
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function orWhereNotNull(string $column): self
    {
        $this->condition('OR', $column, 'IS NOT NULL', null);

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
    public function whereIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('AND', $column, 'IN', $values);
        } else {
            $this->whereRaw("{$column} IN (" . $values->toSql() . ')', $values->getBindings());
        }

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
    public function whereNotIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('AND', $column, 'NOT IN', $values);
        } else {
            $this->whereRaw("{$column} NOT IN (" . $values->toSql() . ')', $values->getBindings());
        }

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
    public function orWhereIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('OR', $column, 'IN', $values);
        } else {
            $this->orWhereRaw("{$column} IN (" . $values->toSql() . ')', $values->getBindings());
        }

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
    public function orWhereNotIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('OR', $column, 'NOT IN', $values);
        } else {
            $this->orWhereRaw("{$column} NOT IN (" . $values->toSql() . ')', $values->getBindings());
        }

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
    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->conditions[] = [
            'conjunction' => 'AND',
            'expression'  => Expressions::raw($sql, $bindings),
            'grouped'     => false,
        ];

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
    public function orWhereRaw(string $sql, array $bindings = []): self
    {
        $this->conditions[] = [
            'conjunction' => 'OR',
            'expression'  => Expressions::raw($sql, $bindings),
            'grouped'     => false,
        ];

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
    public function whereFullText(array $columns, string $value, string $mode = 'natural'): self
    {
        $this->conditions[] = [
            'conjunction' => 'AND',
            'expression'  => MatchAgainst::make($columns, $value, $mode),
            'grouped'     => false,
        ];

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
    public function orWhereFullText(array $columns, string $value, string $mode = 'natural'): self
    {
        $this->conditions[] = [
            'conjunction' => 'OR',
            'expression'  => MatchAgainst::make($columns, $value, $mode),
            'grouped'     => false,
        ];

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $parts = [];

        foreach ($this->conditions as $i => $condition) {
            $prefix = $i === 0 ? '' : " {$condition['conjunction']} ";
            $sql    = $condition['expression']->toSql();

            $parts[] = $prefix . ($condition['grouped'] ? "({$sql})" : $sql);
        }

        return implode('', $parts);
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        $bindings = [];

        foreach ($this->conditions as $condition) {
            $bindings[] = $condition['expression']->getBindings();
        }

        return array_merge(...$bindings);
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }

    /**
     * Add a condition to the query.
     *
     * @param 'AND'|'OR'     $conjunction
     * @param string|Closure $column
     * @param string|null    $operator
     * @param mixed          $value
     */
    private function condition(
        string         $conjunction,
        Closure|string $column,
        ?string        $operator,
        mixed          $value,
    ): void {
        if ($column instanceof Closure) {
            $clause = new self();

            $column($clause);

            if ($clause->isEmpty()) {
                throw InvalidExpressionException::emptyGroupedCondition();
            }

            $this->conditions[] = [
                'conjunction' => $conjunction,
                'expression'  => $clause,
                'grouped'     => true,
            ];
        } else {
            /** @var string $operator */
            $this->conditions[] = [
                'conjunction' => $conjunction,
                'expression'  => Expressions::whereColumn($operator, $column, $value),
                'grouped'     => false,
            ];
        }
    }
}
