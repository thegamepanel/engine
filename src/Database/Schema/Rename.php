<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Schema;

/**
 * Rename
 * ------
 *
 * Schema builder for renaming a table.
 */
final class Rename implements Schema
{
    /**
     * Create a new rename table operation.
     *
     * @param string $table
     * @param string $newName
     *
     * @return self
     */
    public static function table(string $table, string $newName): self
    {
        return new self($table, $newName);
    }

    private string $table;

    private string $newName;

    private function __construct(string $table, string $newName)
    {
        $this->table   = $table;
        $this->newName = $newName;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return "RENAME TABLE `{$this->table}` TO `{$this->newName}`";
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
