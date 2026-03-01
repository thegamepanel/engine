<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;

final readonly class ColumnNotEqualTo implements Expression
{
    public static function make(string $column, mixed $value): self
    {
        return new self($column, $value);
    }

    private function __construct(
        private string $column,
        private mixed  $value
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
        return "{$this->column} != ?";
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return [$this->value];
    }
}
