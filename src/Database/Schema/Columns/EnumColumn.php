<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

use BackedEnum;
use Engine\Database\Schema\Concerns\HasCharsetAndCollation;

/**
 * Enum Column
 * -----------
 *
 * Represents the definition of a MySQL ENUM or SET column type. Values can
 * be provided as an array or a class name of a {@see BackedEnum} type.
 *
 * @template TType of self::ENUM|self::SET
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class EnumColumn extends BaseColumn
{
    use HasCharsetAndCollation;

    private const string ENUM = 'ENUM';

    private const string SET = 'SET';

    /**
     * Create a new ENUM column.
     *
     * @param string                                 $name
     * @param array<string>|class-string<BackedEnum> $values
     *
     * @return self<self::ENUM>
     */
    public static function enum(string $name, array|string $values): self
    {
        return new self($name, self::ENUM, $values);
    }

    /**
     * Create a new SET column.
     *
     * @param string                                 $name
     * @param array<string>|class-string<BackedEnum> $values
     *
     * @return self<self::SET>
     */
    public static function set(string $name, array|string $values): self
    {
        return new self($name, self::SET, $values);
    }

    /**
     * The column type.
     *
     * @var TType
     */
    private string $type;

    /**
     * The values the column can take.
     *
     * @var array<string|BackedEnum>
     */
    private array $values;

    /**
     * @param string                                 $name
     * @param TType                                  $type
     * @param array<string>|class-string<BackedEnum> $values
     */
    private function __construct(string $name, string $type, array|string $values)
    {
        assert(in_array($type, [self::ENUM, self::SET]), 'Invalid enum column type');

        parent::__construct($name);

        $this->type = $type;

        if (is_string($values)) {
            assert(enum_exists($values) && is_subclass_of($values, BackedEnum::class), 'Invalid enum class: ' . $values);

            $values = $values::cases();
        }

        assert(count($values) > 0, 'Enum class must have at least one case');

        $this->values = $values;
    }

    /**
     * Get the definition of the column.
     *
     * @return string
     */
    protected function getDefinition(): string
    {
        $definition = $this->type;

        $definition .= '(';

        foreach ($this->values as $value) {
            if ($value instanceof BackedEnum) {
                $value = (string) $value->value;
            }

            $definition .= "'" . addslashes($value) . "', ";
        }

        $definition = rtrim($definition, ', ') . ')';

        if ($this->hasCharset()) {
            $definition .= ' CHARACTER SET ' . $this->getCharset();
        }

        if ($this->hasCollation()) {
            $definition .= ' COLLATE ' . $this->getCollation();
        }

        return $definition;
    }
}
