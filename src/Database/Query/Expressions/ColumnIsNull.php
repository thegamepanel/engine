<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;

final readonly class ColumnIsNull implements Expression
{
    public static function make(string $column): self
    {
        return new self($column);
    }

    private function __construct(
        private string $column
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
        return "{$this->column} IS NULL";
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return [];
    }
}
