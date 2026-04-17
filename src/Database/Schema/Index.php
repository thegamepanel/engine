<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Schema\Indexes\ForeignKey;
use Engine\Database\Schema\Indexes\NamedIndex;
use Engine\Database\Schema\Indexes\PrimaryIndex;

/**
 * Index
 * -----
 *
 * Static factory for creating index definitions. Delegates to the
 * concrete index classes.
 */
final readonly class Index
{
    /**
     * Create a foreign key index.
     *
     * @param string $name
     * @param string ...$columns
     *
     * @return ForeignKey
     */
    public static function foreign(string $name, string ...$columns): ForeignKey
    {
        return ForeignKey::make($name, $columns);
    }

    /**
     * Create a unique index.
     *
     * @param string $name
     * @param string ...$columns
     *
     * @return NamedIndex<NamedIndex::UNIQUE>
     */
    public static function unique(string $name, string ...$columns): NamedIndex
    {
        return NamedIndex::unique($name, $columns);
    }

    /**
     * Create a normal index.
     *
     * @param string $name
     * @param string ...$columns
     *
     * @return NamedIndex<NamedIndex::INDEX>
     */
    public static function index(string $name, string ...$columns): NamedIndex
    {
        return NamedIndex::index($name, $columns);
    }

    /**
     * Create a fulltext index.
     *
     * @param string $name
     * @param string ...$columns
     *
     * @return NamedIndex<NamedIndex::FULLTEXT>
     */
    public static function fulltext(string $name, string ...$columns): NamedIndex
    {
        return NamedIndex::fulltext($name, $columns);
    }

    /**
     * Create a primary key index.
     *
     * @param string ...$columns
     *
     * @return PrimaryIndex
     */
    public static function primary(string ...$columns): PrimaryIndex
    {
        return PrimaryIndex::make($columns);
    }
}
