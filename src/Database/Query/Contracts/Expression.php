<?php

namespace Engine\Database\Query\Contracts;

interface Expression
{
    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string;

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array;
}
