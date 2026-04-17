<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Alter;

use Engine\Database\Contracts\Schema;
use Engine\Database\Schema\Concerns\HasCharsetAndCollation;

/**
 * Alter Database
 * --------------
 *
 * Schema builder for altering a database. Compiles into a single
 * "ALTER DATABASE" statement.
 */
final class AlterDatabase implements Schema
{
    use HasCharsetAndCollation;

    public readonly string $database;

    /**
     * @param string $database
     */
    public function __construct(string $database)
    {
        $this->database = $database;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "ALTER DATABASE `{$this->database}`";

        if ($this->hasCharset()) {
            $sql .= ' CHARACTER SET ' . $this->getCharset();
        }

        if ($this->hasCollation()) {
            $sql .= ' COLLATE ' . $this->getCollation();
        }

        return $sql;
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
