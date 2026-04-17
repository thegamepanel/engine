<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use Engine\Database\Exceptions\InvalidSchemaException;
use Engine\Database\Schema\Concerns\HasCharsetAndCollation;
use Engine\Database\Schema\Concerns\HasLength;

/**
 * String Column
 * -------------
 *
 * Represents the definition of a MySQL string column type. Includes:
 *
 * - CHAR
 * - VARCHAR
 * - TINYTEXT
 * - TEXT
 * - MEDIUMTEXT
 * - LONGTEXT
 *
 * @template TType of self::CHAR|self::VARCHAR|self::TINYTEXT|self::TEXT|self::MEDIUMTEXT|self::LONGTEXT
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class StringColumn extends BaseColumn
{
    use HasCharsetAndCollation, HasLength {
        length as traitLength;
    }

    private const string CHAR = 'CHAR';

    private const string VARCHAR = 'VARCHAR';

    private const string TINYTEXT = 'TINYTEXT';

    private const string TEXT = 'TEXT';

    private const string MEDIUMTEXT = 'MEDIUMTEXT';

    private const string LONGTEXT = 'LONGTEXT';

    /**
     * Create a new CHAR column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::CHAR>
     */
    public static function char(string $name, ?int $length = null): self
    {
        $column = new self($name, self::CHAR);

        if ($length !== null) {
            $column->length($length);
        }

        return $column;
    }

    /**
     * Create a new VARCHAR column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::VARCHAR>
     */
    public static function varchar(string $name, ?int $length = null): self
    {
        $column = new self($name, self::VARCHAR);

        if ($length !== null) {
            $column->length($length);
        }

        return $column;
    }

    /**
     * Create a new TINYTEXT column.
     *
     * @param string $name
     *
     * @return self<self::TINYTEXT>
     */
    public static function tinytext(string $name): self
    {
        return new self($name, self::TINYTEXT);
    }

    /**
     * Create a new TEXT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::TEXT>
     */
    public static function text(string $name, ?int $length = null): self
    {
        $column = new self($name, self::TEXT);

        if ($length !== null) {
            $column->length($length);
        }

        return $column;
    }

    /**
     * Create a new MEDIUMTEXT column.
     *
     * @param string $name
     *
     * @return self<self::MEDIUMTEXT>
     */
    public static function mediumtext(string $name): self
    {
        return new self($name, self::MEDIUMTEXT);
    }

    /**
     * Create a new LONGTEXT column.
     *
     * @param string $name
     *
     * @return self<self::LONGTEXT>
     */
    public static function longtext(string $name): self
    {
        return new self($name, self::LONGTEXT);
    }

    /**
     * The column type.
     *
     * @var TType
     */
    private string $type;

    /**
     * @param string $name
     * @param TType  $type
     */
    private function __construct(string $name, string $type)
    {
        assert(in_array($type, [self::CHAR, self::VARCHAR, self::TEXT, self::TINYTEXT, self::MEDIUMTEXT, self::LONGTEXT]), 'Invalid string column type');

        parent::__construct($name);

        $this->type = $type;
    }

    /**
     * Set the length of the column.
     *
     * Only applies to CHAR, VARCHAR and TEXT columns.
     *
     * @param int $length
     *
     * @return static
     */
    public function length(int $length): static
    {
        if ($this->type !== self::VARCHAR && $this->type !== self::CHAR && $this->type !== self::TEXT) {
            throw InvalidSchemaException::incompatibleModifier('length', $this->type);
        }

        return $this->traitLength($length);
    }

    /**
     * Get the definition of the column.
     *
     * @return string
     */
    protected function getDefinition(): string
    {
        $definition = $this->type;

        if ($this->hasLength()) {
            $definition .= '(' . $this->getLength() . ')';
        }

        if ($this->hasCharset()) {
            $definition .= ' CHARACTER SET ' . $this->getCharset();
        }

        if ($this->hasCollation()) {
            $definition .= ' COLLATE ' . $this->getCollation();
        }

        return $definition;
    }
}
