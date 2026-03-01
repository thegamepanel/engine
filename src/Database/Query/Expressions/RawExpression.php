<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;

final readonly class RawExpression implements Expression
{
    /**
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return self
     */
    public static function make(string $sql, array $bindings): self
    {
        return new self($sql, $bindings);
    }

    /**
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     */
    private function __construct(
        private string $sql,
        private array  $bindings
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
        return $this->sql;
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
