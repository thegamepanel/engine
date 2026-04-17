<?php
declare(strict_types=1);

namespace Engine\Database\Query\Expressions;

use Engine\Database\Contracts\Expression;

final readonly class MatchAgainst implements Expression
{
    /**
     * @param array<string> $columns
     * @param string        $value
     * @param string        $mode
     *
     * @return self
     */
    public static function make(array $columns, string $value, string $mode = 'natural'): self
    {
        return new self($columns, $value, $mode);
    }

    /**
     * @param array<string> $columns
     * @param string        $value
     * @param string        $mode
     */
    private function __construct(
        private array  $columns,
        private string $value,
        private string $mode,
    ) {
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        $mode    = match ($this->mode) {
            'boolean' => ' IN BOOLEAN MODE',
            default   => ' IN NATURAL LANGUAGE MODE',
        };

        return "MATCH({$columns}) AGAINST(?{$mode})";
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
