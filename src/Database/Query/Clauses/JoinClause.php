<?php
declare(strict_types=1);

namespace Engine\Database\Query\Clauses;

use Engine\Database\Contracts\Expression;

final class JoinClause implements Expression
{
    /**
     * @var list<array{conjunction: 'AND'|'OR', sql: string, bindings: array<int|string, mixed>}>
     */
    private array $conditions = [];

    /**
     * Add an ON condition to the join.
     *
     * @param string $left
     * @param string $operator
     * @param string $right
     *
     * @return static
     */
    public function on(string $left, string $operator, string $right): self
    {
        $this->conditions[] = [
            'conjunction' => 'AND',
            'sql'         => "{$left} {$operator} {$right}",
            'bindings'    => [],
        ];

        return $this;
    }

    /**
     * Add an OR ON condition to the join.
     *
     * @param string $left
     * @param string $operator
     * @param string $right
     *
     * @return static
     */
    public function orOn(string $left, string $operator, string $right): self
    {
        $this->conditions[] = [
            'conjunction' => 'OR',
            'sql'         => "{$left} {$operator} {$right}",
            'bindings'    => [],
        ];

        return $this;
    }

    /**
     * Add a WHERE condition to the join (bound value, not column reference).
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function where(string $column, string $operator, mixed $value): self
    {
        $this->conditions[] = [
            'conjunction' => 'AND',
            'sql'         => "{$column} {$operator} ?",
            'bindings'    => [$value],
        ];

        return $this;
    }

    /**
     * Add an OR WHERE condition to the join.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->conditions[] = [
            'conjunction' => 'OR',
            'sql'         => "{$column} {$operator} ?",
            'bindings'    => [$value],
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
            $prefix  = $i === 0 ? '' : " {$condition['conjunction']} ";
            $parts[] = $prefix . $condition['sql'];
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
            $bindings[] = $condition['bindings'];
        }

        return array_merge(...$bindings ?: [[]]);
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }
}
