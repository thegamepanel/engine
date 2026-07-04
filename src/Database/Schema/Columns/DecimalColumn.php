<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use Engine\Database\Schema\Concerns\CanBeUnsigned;
use Engine\Database\Schema\Concerns\HasLength;

/**
 * Decimal Column
 * --------------
 *
 * Represents the definition of a MySQL decimal column type. Includes:
 *
 * - DECIMAL
 * - FLOAT
 * - DOUBLE
 *
 * @template TType of self::DECIMAL|self::FLOAT|self::DOUBLE
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class DecimalColumn extends BaseColumn
{
    use HasLength;
    use CanBeUnsigned;

    private const string DECIMAL = 'DECIMAL';

    private const string FLOAT = 'FLOAT';

    private const string DOUBLE = 'DOUBLE';

    /**
     * Create a new DECIMAL column.
     *
     * @param string   $name
     * @param int|null $length
     * @param int|null $decimals
     *
     * @return self<self::DECIMAL>
     */
    public static function decimal(string $name, ?int $length = null, ?int $decimals = null): self
    {
        return new self($name, self::DECIMAL, $length, $decimals);
    }

    /**
     * Create a new FLOAT column.
     *
     * @param string   $name
     * @param int|null $length
     * @param int|null $decimals
     *
     * @return self<self::FLOAT>
     */
    public static function float(string $name, ?int $length = null, ?int $decimals = null): self
    {
        return new self($name, self::FLOAT, $length, $decimals);
    }

    /**
     * Create a new DOUBLE column.
     *
     * @param string   $name
     * @param int|null $length
     * @param int|null $decimals
     *
     * @return self<self::DOUBLE>
     */
    public static function double(string $name, ?int $length = null, ?int $decimals = null): self
    {
        return new self($name, self::DOUBLE, $length, $decimals);
    }

    /**
     * The column type.
     *
     * @var TType
     */
    private string $type;

    /**
     * The number of decimal places.
     *
     * @var int|null
     */
    private ?int $decimals;

    /**
     * @param string   $name
     * @param TType    $type
     * @param int|null $length
     * @param int|null $decimals
     */
    private function __construct(
        string $name,
        string $type,
        ?int   $length = null,
        ?int   $decimals = null,
    ) {
        assert(in_array($type, [self::DECIMAL, self::FLOAT, self::DOUBLE]), 'Invalid column type: ' . $type);

        parent::__construct($name);

        $this->type = $type;

        if ($length) {
            $this->length($length);
        }

        $this->decimals = $decimals;
    }

    /**
     * Get the definition of the column.
     *
     * @return string
     */
    protected function getDefinition(): string
    {
        $definition = $this->type;

        if ($this->decimals !== null || $this->hasLength()) {
            $definition .= '(';

            if ($this->hasLength()) {
                $definition .= $this->getLength();

                if ($this->decimals !== null) {
                    $definition .= ',' . $this->decimals;
                }
            } else {
                $definition .= $this->decimals;
            }

            $definition .= ')';
        }

        if ($this->isUnsigned()) {
            $definition .= ' UNSIGNED';
        }

        return $definition;
    }
}
