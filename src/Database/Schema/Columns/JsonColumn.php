<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Columns;

/**
 * JSON Column
 * -----------
 *
 * Represents the definition of a MySQL JSON column type.
 *
 * @internal Only ever returned by {@see Column} factory
 */
final class JsonColumn extends BaseColumn
{
    /**
     * Create a new JSON column instance.
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
        return 'JSON';
    }
}
