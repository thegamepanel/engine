<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Column;
use Engine\Database\Schema\Create\CreateDatabase;
use Engine\Database\Schema\Create\CreateTable;

/**
 * Create
 * ------
 *
 * Static factory for creating DDL create operations.
 */
final readonly class Create
{
    /**
     * Create a new table.
     *
     * @param string        $table
     * @param array<Column> $columns
     *
     * @return CreateTable
     */
    public static function table(string $table, array $columns): CreateTable
    {
        return new CreateTable($table, $columns);
    }

    /**
     * Create a new database.
     *
     * @param string $database
     *
     * @return CreateDatabase
     */
    public static function database(string $database): CreateDatabase
    {
        return new CreateDatabase($database);
    }
}
