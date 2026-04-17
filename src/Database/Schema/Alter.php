<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Schema\Alter\AlterDatabase;
use Engine\Database\Schema\Alter\AlterTable;

/**
 * Alter
 * -----
 *
 * Static factory for creating DDL alter operations.
 */
final readonly class Alter
{
    /**
     * Alter an existing table.
     *
     * @param string $table
     *
     * @return AlterTable
     */
    public static function table(string $table): AlterTable
    {
        return new AlterTable($table);
    }

    /**
     * Alter an existing database.
     *
     * @param string $database
     *
     * @return AlterDatabase
     */
    public static function database(string $database): AlterDatabase
    {
        return new AlterDatabase($database);
    }
}
