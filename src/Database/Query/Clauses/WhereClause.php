<?php
declare(strict_types=1);

namespace Engine\Database\Query\Clauses;

use Closure;
use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Exceptions\InvalidExpressionException;
use Engine\Database\Query\Expressions;

/**
 *
 */
final class WhereClause implements Expression
{
    /**
     * @var list<array{conjunction: 'AND'|'OR', expression: \Engine\Database\Query\Contracts\Expression, grouped: bool}>
     */
    private array $conditions = [];

    /**
     * Add a condition to the query.
     *
     * @param 'AND'|'OR'      $conjunction
     * @param string|\Closure $column
     * @param string|null     $operator
     * @param mixed           $value
     *
     * @return void
     */
    private function condition(
        string         $conjunction,
        string|Closure $column,
        ?string        $operator,
        mixed          $value,
    ): void
    {
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

    /**
     * Add a basic where clause to the query.
     *
     * @param string|\Closure $column
     * @param mixed|null      $operatorOrValue
     * @param mixed|null      $value
     *
     * @return $this
     */
    public function where(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): self
    {
        if ($column instanceof Closure) {
            $this->condition('AND', $column, null, null);
        } else {
            if (func_num_args() === 2) {
                $value    = $operatorOrValue;
                $operator = '=';
            } else {
                /** @var string $operator */
                $operator = $operatorOrValue;
            }

            $this->condition('AND', $column, $operator, $value);
        }

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
    public function orWhere(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): self
    {
        if ($column instanceof Closure) {
            $this->condition('OR', $column, null, null);
        } else {
            if (func_num_args() === 2) {
                $value    = $operatorOrValue;
                $operator = '=';
            } else {
                /** @var string $operator */
                $operator = $operatorOrValue;
            }

            $this->condition('OR', $column, $operator, $value);
        }

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     *
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function whereNotNull(string $column): self
    {
        $this->condition('AND', $column, 'IS NOT NULL', null);

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
    public function whereIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('AND', $column, 'IN', $values);
        } else {
            $this->whereRaw("{$column} IN (" . $values->toSql() . ")", $values->getBindings());
        }

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
    public function whereNotIn(string $column, array|Expression $values): self
    {
        if (is_array($values) && empty($values)) {
            throw InvalidExpressionException::emptyInClause($column);
        }

        if (is_array($values)) {
            $this->condition('AND', $column, 'NOT IN', $values);
        } else {
            $this->whereRaw("{$column} NOT IN (" . $values->toSql() . ")", $values->getBindings());
        }

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
}
