<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Create;

use Engine\Database\Contracts\Schema;
use Engine\Database\Schema\Concerns\HasCharsetAndCollation;

/**
 * Create Database
 * ---------------
 *
 * Schema builder for creating a database. Compiles into a single
 * "CREATE DATABASE" statement.
 */
final class CreateDatabase implements Schema
{
    use HasCharsetAndCollation;

    public readonly string $database;

    private bool $ifNotExists = false;

    /**
     * @param string $database
     */
    public function __construct(string $database)
    {
        $this->database = $database;
    }

    /**
     * Only create the database if it does not already exist.
     *
     * @return static
     */
    public function ifNotExists(): self
    {
        $this->ifNotExists = true;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = 'CREATE DATABASE ';
        $sql .= ($this->ifNotExists ? 'IF NOT EXISTS ' : '');
        $sql .= "`{$this->database}`";

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
