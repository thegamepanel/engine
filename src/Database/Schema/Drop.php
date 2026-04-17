<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Expression;
use Engine\Database\Exceptions\InvalidSchemaException;

/**
 * Drop
 * ----
 *
 * Schema builder for DROP operations. Supports dropping tables, columns,
 * indexes, primary keys, foreign keys, and databases.
 *
 * @template TType of self::TABLE|self::COLUMN|self::INDEX|self::PRIMARY_KEY|self::FOREIGN_KEY|self::DATABASE
 */
final class Drop implements Expression
{
    private const string TABLE = 'TABLE';

    private const string COLUMN = 'COLUMN';

    private const string INDEX = 'INDEX';

    private const string PRIMARY_KEY = 'PRIMARY KEY';

    private const string FOREIGN_KEY = 'FOREIGN KEY';

    private const string DATABASE = 'DATABASE';

    /**
     * @param string $name
     *
     * @return self<self::TABLE>
     */
    public static function table(string $name): self
    {
        return new self($name, self::TABLE);
    }

    /**
     * @param string $name
     *
     * @return self<self::COLUMN>
     */
    public static function column(string $name): self
    {
        return new self($name, self::COLUMN);
    }

    /**
     * @param string $name
     *
     * @return self<self::INDEX>
     */
    public static function index(string $name): self
    {
        return new self($name, self::INDEX);
    }

    /**
     * @param string $name
     *
     * @return self<self::PRIMARY_KEY>
     */
    public static function primaryKey(string $name): self
    {
        return new self($name, self::PRIMARY_KEY);
    }

    /**
     * @param string $name
     *
     * @return self<self::FOREIGN_KEY>
     */
    public static function foreignKey(string $name): self
    {
        return new self($name, self::FOREIGN_KEY);
    }

    /**
     * @param string $name
     *
     * @return self<self::DATABASE>
     */
    public static function database(string $name): self
    {
        return new self($name, self::DATABASE);
    }

    private string $name;

    /**
     * @var TType
     */
    private string $type;

    private bool $ifExists = false;

    private bool $temporary = false;

    /**
     * @param string $name
     * @param TType  $type
     */
    private function __construct(string $name, string $type)
    {
        assert(
            in_array($type, [self::TABLE, self::COLUMN, self::INDEX, self::PRIMARY_KEY, self::FOREIGN_KEY, self::DATABASE]),
            'Invalid drop type',
        );

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Add the IF EXISTS clause.
     *
     * Only valid when dropping a DATABASE or TABLE.
     *
     * @return static
     */
    public function ifExists(): static
    {
        if ($this->type !== self::DATABASE && $this->type !== self::TABLE) {
            throw InvalidSchemaException::incompatibleModifier('IF EXISTS', $this->type);
        }

        $this->ifExists = true;

        return $this;
    }

    /**
     * Mark the drop as targeting a temporary table.
     *
     * Only valid when dropping a TABLE.
     *
     * @return static
     */
    public function temporary(): static
    {
        if ($this->type !== self::TABLE) {
            throw InvalidSchemaException::incompatibleModifier('TEMPORARY', $this->type);
        }

        $this->temporary = true;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return 'DROP '
               . ($this->temporary ? 'TEMPORARY ' : '')
               . ($this->type . ' ')
               . ($this->ifExists ? 'IF EXISTS ' : '')
               . "`{$this->name}`";
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

    /**
     * Check if this drop targets an index, primary key, or foreign key.
     *
     * @return bool
     */
    public function isDroppingAnIndex(): bool
    {
        return $this->type === self::INDEX
            || $this->type === self::PRIMARY_KEY
            || $this->type === self::FOREIGN_KEY;
    }

    /**
     * Check if this drop targets the primary key.
     *
     * @return bool
     */
    public function isDroppingThePrimaryKey(): bool
    {
        return $this->type === self::PRIMARY_KEY;
    }

    /**
     * Check if this drop targets a column.
     *
     * @return bool
     */
    public function isDroppingAColumn(): bool
    {
        return $this->type === self::COLUMN;
    }
}
