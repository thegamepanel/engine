<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use Engine\Database\Exceptions\InvalidSchemaException;
use Engine\Database\Query\Raw;

/**
 * Temporal Column
 * ---------------
 *
 * Represents the definition of a MySQL temporal column type. Includes:
 *
 * - DATE
 * - DATETIME
 * - TIMESTAMP
 * - TIME
 * - YEAR
 *
 * @template TType of self::DATE|self::DATETIME|self::TIMESTAMP|self::TIME|self::YEAR
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class TemporalColumn extends BaseColumn
{
    private const string DATE = 'date';

    private const string DATETIME = 'datetime';

    private const string TIMESTAMP = 'timestamp';

    private const string TIME = 'time';

    private const string YEAR = 'year';

    /**
     * Create a new DATE column.
     *
     * @param string $name
     *
     * @return self<self::DATE>
     */
    public static function date(string $name): self
    {
        return new self($name, self::DATE);
    }

    /**
     * Create a new DATETIME column.
     *
     * @param string $name
     *
     * @return self<self::DATETIME>
     */
    public static function datetime(string $name): self
    {
        return new self($name, self::DATETIME);
    }

    /**
     * Create a new TIMESTAMP column.
     *
     * @param string $name
     *
     * @return self<self::TIMESTAMP>
     */
    public static function timestamp(string $name): self
    {
        return new self($name, self::TIMESTAMP);
    }

    /**
     * Create a new TIME column.
     *
     * @param string $name
     *
     * @return self<self::TIME>
     */
    public static function time(string $name): self
    {
        return new self($name, self::TIME);
    }

    /**
     * Create a new YEAR column.
     *
     * @param string $name
     *
     * @return self<self::YEAR>
     */
    public static function year(string $name): self
    {
        return new self($name, self::YEAR);
    }

    /**
     * The column type.
     *
     * @var TType
     */
    private string $type;

    /**
     * The fractional seconds precision.
     *
     * @var int|null
     */
    private ?int $precision = null;

    /**
     * Whether the column should update to CURRENT_TIMESTAMP on row update.
     *
     * @var bool
     */
    private bool $onUpdate = false;

    /**
     * @param string $name
     * @param TType  $type
     */
    private function __construct(string $name, string $type)
    {
        assert(in_array($type, [self::DATE, self::DATETIME, self::TIMESTAMP, self::TIME, self::YEAR]), 'Invalid temporal column type');

        parent::__construct($name);

        $this->type = $type;
    }

    /**
     * Set the fractional seconds precision.
     *
     * Only applies to DATETIME, TIMESTAMP and TIME columns.
     *
     * @param int $precision
     *
     * @return static
     */
    public function precision(int $precision): self
    {
        if ($this->type !== self::DATETIME && $this->type !== self::TIMESTAMP && $this->type !== self::TIME) {
            throw InvalidSchemaException::incompatibleModifier('precision', $this->type);
        }

        $this->precision = $precision;

        return $this;
    }

    /**
     * Set the default value to CURRENT_TIMESTAMP.
     *
     * Only applies to TIMESTAMP and DATETIME columns.
     *
     * @return static
     */
    public function defaultCurrentTimestamp(): self
    {
        if ($this->type !== self::TIMESTAMP && $this->type !== self::DATETIME) {
            throw InvalidSchemaException::incompatibleModifier('default CURRENT_TIMESTAMP', $this->type);
        }

        $this->default(Raw::from('CURRENT_TIMESTAMP'));

        return $this;
    }

    /**
     * Automatically update the column to CURRENT_TIMESTAMP on row update.
     *
     * Only applies to TIMESTAMP and DATETIME columns.
     *
     * @return static
     */
    public function onUpdateCurrentTimestamp(): self
    {
        if ($this->type !== self::TIMESTAMP && $this->type !== self::DATETIME) {
            throw InvalidSchemaException::incompatibleModifier('ON UPDATE CURRENT_TIMESTAMP', $this->type);
        }

        $this->onUpdate = true;

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

        if ($this->precision !== null) {
            $definition .= '(' . $this->precision . ')';
        }

        if ($this->onUpdate) {
            $definition .= ' ON UPDATE CURRENT_TIMESTAMP';
        }

        return $definition;
    }
}
