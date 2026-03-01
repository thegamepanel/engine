<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;

final readonly class ColumnIn implements Expression
{
    /**
     * @param string       $column
     * @param array<mixed> $values
     *
     * @return self
     */
    public static function make(string $column, array $values): self
    {
        return new self($column, $values);
    }

    /**
     * @param string       $column
     * @param array<mixed> $values
     */
    private function __construct(
        private string $column,
        private array  $values
    )
    {
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $params = array_fill(0, count($this->values), '?');
        $params = implode(', ', $params);

        return "{$this->column} IN ({$params})";
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return $this->values;
    }
}
