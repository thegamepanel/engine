<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

/**
 * Boolean Column
 * --------------
 *
 * Represents the definition of a MySQL BOOLEAN column type. MySQL treats
 * BOOLEAN as an alias for TINYINT(1).
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class BooleanColumn extends BaseColumn
{
    /**
     * Create a new BOOLEAN column instance.
     *
     * @param string $name
     *
     * @return self
     */
    public static function make(string $name): self
    {
        return new self($name);
    }

    /**
     * @param string $name
     */
    private function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * Get the definition of the column.
     *
     * @return string
     */
    protected function getDefinition(): string
    {
        return 'BOOLEAN';
    }
}
