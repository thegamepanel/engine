<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Schema;

/**
 * Truncate
 * --------
 *
 * Schema builder for truncating a table.
 */
final class Truncate implements Schema
{
    /**
     * Create a new truncate table operation.
     *
     * @param string $table
     *
     * @return self
     */
    public static function table(string $table): self
    {
        return new self($table);
    }

    private string $table;

    private function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return "TRUNCATE TABLE `{$this->table}`";
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
