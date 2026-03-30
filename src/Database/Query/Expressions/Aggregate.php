<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Contracts\Expression;

final readonly class Aggregate implements Expression
{
    /**
     * @param string            $function
     * @param string|Expression $column
     *
     * @return self
     */
    public static function make(string $function, Expression|string $column): self
    {
        return new self($function, $column);
    }

    /**
     * @param string            $function
     * @param string|Expression $column
     */
    private function __construct(
        private string            $function,
        private Expression|string $column,
    ) {
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $col = $this->column instanceof Expression ? $this->column->toSql() : $this->column;

        return "{$this->function}({$col})";
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return $this->column instanceof Expression ? $this->column->getBindings() : [];
    }
}
