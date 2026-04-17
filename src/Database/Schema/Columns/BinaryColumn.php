<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use Engine\Database\Exceptions\InvalidSchemaException;
use Engine\Database\Schema\Concerns\HasLength;

/**
 * Binary Column
 * -------------
 *
 * Represents the definition of a MySQL binary column type. Includes:
 *
 * - BINARY
 * - VARBINARY
 * - TINYBLOB
 * - BLOB
 * - MEDIUMBLOB
 * - LONGBLOB
 *
 * @template TType of self::BINARY|self::VARBINARY|self::TINYBLOB|self::BLOB|self::MEDIUMBLOB|self::LONGBLOB
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class BinaryColumn extends BaseColumn
{
    use HasLength {
        length as traitLength;
    }

    private const string BINARY = 'BINARY';

    private const string VARBINARY = 'VARBINARY';

    private const string TINYBLOB = 'TINYBLOB';

    private const string BLOB = 'BLOB';

    private const string MEDIUMBLOB = 'MEDIUMBLOB';

    private const string LONGBLOB = 'LONGBLOB';

    /**
     * Create a new BINARY column.
     *
     * @param string $name
     *
     * @return self<self::BINARY>
     */
    public static function binary(string $name): self
    {
        return new self($name, self::BINARY);
    }

    /**
     * Create a new VARBINARY column.
     *
     * @param string $name
     *
     * @return self<self::VARBINARY>
     */
    public static function varbinary(string $name): self
    {
        return new self($name, self::VARBINARY);
    }

    /**
     * Create a new TINYBLOB column.
     *
     * @param string $name
     *
     * @return self<self::TINYBLOB>
     */
    public static function tinyblob(string $name): self
    {
        return new self($name, self::TINYBLOB);
    }

    /**
     * Create a new BLOB column.
     *
     * @param string $name
     *
     * @return self<self::BLOB>
     */
    public static function blob(string $name): self
    {
        return new self($name, self::BLOB);
    }

    /**
     * Create a new MEDIUMBLOB column.
     *
     * @param string $name
     *
     * @return self<self::MEDIUMBLOB>
     */
    public static function mediumblob(string $name): self
    {
        return new self($name, self::MEDIUMBLOB);
    }

    /**
     * Create a new LONGBLOB column.
     *
     * @param string $name
     *
     * @return self<self::LONGBLOB>
     */
    public static function longblob(string $name): self
    {
        return new self($name, self::LONGBLOB);
    }

    /**
     * The type of the column.
     *
     * @var string
     */
    private string $type;

    /**
     * @param string $name
     * @param TType  $type
     */
    private function __construct(string $name, string $type)
    {
        assert(in_array($type, [self::BINARY, self::VARBINARY, self::TINYBLOB, self::BLOB, self::MEDIUMBLOB, self::LONGBLOB]), 'Invalid binary column type');

        parent::__construct($name);

        $this->type = $type;
    }

    /**
     * Set the length of the column.
     *
     * Only applies to BINARY, VARBINARY and BLOB columns.
     *
     * @param int $length
     *
     * @return static
     */
    public function length(int $length): static
    {
        if ($this->type !== self::BINARY && $this->type !== self::VARBINARY && $this->type !== self::BLOB) {
            throw InvalidSchemaException::incompatibleModifier('length', $this->type);
        }

        $this->traitLength($length);

        return $this;
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

        return $definition;
    }
}
