<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Indexes;

use Engine\Database\Contracts\Index;

/**
 * Primary Index
 * -------------
 *
 * Represents a primary key index definition.
 *
 * @internal Only ever returned by {@see Index} factory
 */
final class PrimaryIndex implements Index
{
    /**
     * Create a new primary key instance.
     *
     * @param array<string> $columns
     *
     * @return self
     */
    public static function make(array $columns)
    {
        return new self($columns);
    }

    /**
     * @var string[]
     */
    private array $columns;

    /**
     * @param array<string> $columns
     */
    private function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return 'PRIMARY KEY (`' . implode('`, `', $this->columns) . '`)';
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
