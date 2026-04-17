<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Indexes;

use Engine\Database\Contracts\Index;

/**
 * Named Index
 * -----------
 *
 * Represents a named index definition. Supports UNIQUE, INDEX,
 * and FULLTEXT index types.
 *
 * @template TType of self::UNIQUE|self::INDEX|self::FULLTEXT
 *
 * @internal Only ever returned by {@see Index} factory
 */
final class NamedIndex implements Index
{
    private const string UNIQUE = 'UNIQUE INDEX';

    private const string INDEX = 'INDEX';

    private const string FULLTEXT = 'FULLTEXT INDEX';

    /**
     * Create a unique index.
     *
     * @param string        $name
     * @param array<string> $columns
     *
     * @return self<self::UNIQUE>
     */
    public static function unique(string $name, array $columns): self
    {
        return new self($name, self::UNIQUE, $columns);
    }

    /**
     * Create a normal index.
     *
     * @param string        $name
     * @param array<string> $columns
     *
     * @return self<self::INDEX>
     */
    public static function index(string $name, array $columns): self
    {
        return new self($name, self::INDEX, $columns);
    }

    /**
     * Create a fulltext index.
     *
     * @param string        $name
     * @param array<string> $columns
     *
     * @return self<self::FULLTEXT>
     */
    public static function fulltext(string $name, array $columns): self
    {
        return new self($name, self::FULLTEXT, $columns);
    }

    /**
     * @var TType
     */
    private string $type;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string[]
     */
    private array $columns;

    /**
     * @param string        $name
     * @param TType         $type
     * @param array<string> $columns
     */
    private function __construct(string $name, string $type, array $columns)
    {
        assert(in_array($type, [self::UNIQUE, self::INDEX, self::FULLTEXT]), 'Invalid index type');

        $this->name    = $name;
        $this->type    = $type;
        $this->columns = $columns;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->type . ' `' . $this->name . '` (`' . implode('`, `', $this->columns) . '`)';
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
