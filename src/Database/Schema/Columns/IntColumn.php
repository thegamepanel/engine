<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use Engine\Database\Schema\Concerns\CanBeUnsigned;
use Engine\Database\Schema\Concerns\HasLength;

/**
 * Int Column
 * ----------
 *
 * Represents the definition of a MySQL integer column type. Includes:
 *
 * - TINYINT
 * - SMALLINT
 * - MEDIUMINT
 * - INT
 * - BIGINT
 *
 * @template TType of self::TINY|self::SMALL|self::MEDIUM|self::DEFAULT|self::BIG
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class IntColumn extends BaseColumn
{
    use HasLength;
    use CanBeUnsigned;

    private const string TINY = 'TINYINT';

    private const string SMALL = 'SMALLINT';

    private const string MEDIUM = 'MEDIUMINT';

    private const string DEFAULT = 'INT';

    private const string BIG = 'BIGINT';

    /**
     * Create a new TINYINT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::TINY>
     */
    public static function tiny(string $name, ?int $length = null): self
    {
        return new self($name, self::TINY, $length);
    }

    /**
     * Create a new SMALLINT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::SMALL>
     */
    public static function small(string $name, ?int $length = null): self
    {
        return new self($name, self::SMALL, $length);
    }

    /**
     * Create a new MEDIUMINT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::MEDIUM>
     */
    public static function medium(string $name, ?int $length = null): self
    {
        return new self($name, self::MEDIUM, $length);
    }

    /**
     * Create a new INT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::DEFAULT>
     */
    public static function int(string $name, ?int $length = null): self
    {
        return new self($name, self::DEFAULT, $length);
    }

    /**
     * Create a new BIGINT column.
     *
     * @param string   $name
     * @param int|null $length
     *
     * @return self<self::BIG>
     */
    public static function big(string $name, ?int $length = null): self
    {
        return new self($name, self::BIG, $length);
    }

    /**
     * The column type.
     *
     * @var TType
     */
    private string $type;

    /**
     * Whether the column should auto-increment.
     *
     * @var bool
     */
    private bool $autoIncrement = false;

    /**
     * @param string   $name
     * @param TType    $type
     * @param int|null $length
     */
    private function __construct(string $name, string $type = self::DEFAULT, ?int $length = null)
    {
        assert(in_array($type, [self::TINY, self::SMALL, self::MEDIUM, self::DEFAULT, self::BIG]), 'Invalid column type: ' . $type);

        parent::__construct($name);

        $this->type = $type;

        if ($length !== null) {
            $this->length($length);
        }
    }

    /**
     * Set the column to auto-increment.
     *
     * @return static
     */
    public function autoIncrement(): static
    {
        $this->autoIncrement = true;

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

        if ($this->isUnsigned()) {
            $definition .= ' UNSIGNED';
        }

        if ($this->autoIncrement) {
            $definition .= ' AUTO_INCREMENT';
        }

        return $definition;
    }
}
