<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Indexes;

use Engine\Database\Contracts\Index;
use Engine\Database\Exceptions\InvalidSchemaException;

/**
 * Foreign Key
 * -----------
 *
 * Represents a foreign key constraint definition, including the referenced
 * table, columns, and ON DELETE / ON UPDATE referential actions.
 *
 * @internal Only ever returned by {@see Index} factory
 */
final class ForeignKey implements Index
{
    /**
     * Create a new foreign key instance.
     *
     * @param string        $name
     * @param array<string> $columns
     *
     * @return self
     */
    public static function make(string $name, array $columns): self
    {
        return new self($name, $columns);
    }

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string[]
     */
    private array $columns;

    /**
     * The table referenced by the foreign key.
     *
     * @var string
     */
    private string $on;

    /**
     * @var array<string>
     */
    private array $references = [];

    /**
     * The ON DELETE referential action.
     *
     * @var string|null
     */
    private ?string $onDelete = null;

    /**
     * The ON UPDATE referential action.
     *
     * @var string|null
     */
    private ?string $onUpdate = null;

    /**
     * @param array<string> $columns
     */
    private function __construct(string $name, array $columns)
    {
        $this->name    = $name;
        $this->columns = $columns;
    }

    /**
     * Set the columns referenced by the foreign key.
     *
     * @param string ...$references
     *
     * @return static
     */
    public function references(string ...$references): self
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Set the table referenced by the foreign key.
     *
     * @param string $table
     *
     * @return static
     */
    public function on(string $table): self
    {
        $this->on = $table;

        return $this;
    }

    /**
     * Set the ON DELETE action to CASCADE.
     *
     * @return static
     */
    public function onDeleteCascade(): self
    {
        $this->onDelete = 'CASCADE';

        return $this;
    }

    /**
     * Set the ON DELETE action to SET NULL.
     *
     * @return static
     */
    public function onDeleteSetNull(): self
    {
        $this->onDelete = 'SET NULL';

        return $this;
    }

    /**
     * Set the ON DELETE action to RESTRICT.
     *
     * @return static
     */
    public function onDeleteRestrict(): self
    {
        $this->onDelete = 'RESTRICT';

        return $this;
    }

    /**
     * Set the ON DELETE action to SET DEFAULT.
     *
     * @return static
     */
    public function onDeleteSetDefault(): self
    {
        $this->onDelete = 'SET DEFAULT';

        return $this;
    }

    /**
     * Set the ON DELETE action to NO ACTION.
     *
     * @return static
     */
    public function onDeleteNoAction(): self
    {
        $this->onDelete = 'NO ACTION';

        return $this;
    }

    /**
     * Set the ON UPDATE action to CASCADE.
     *
     * @return static
     */
    public function onUpdateCascade(): self
    {
        $this->onUpdate = 'CASCADE';

        return $this;
    }

    /**
     * Set the ON UPDATE action to SET NULL.
     *
     * @return static
     */
    public function onUpdateSetNull(): self
    {
        $this->onUpdate = 'SET NULL';

        return $this;
    }

    /**
     * Set the ON UPDATE action to RESTRICT.
     *
     * @return static
     */
    public function onUpdateRestrict(): self
    {
        $this->onUpdate = 'RESTRICT';

        return $this;
    }

    /**
     * Set the ON UPDATE action to SET DEFAULT.
     *
     * @return static
     */
    public function onUpdateSetDefault(): self
    {
        $this->onUpdate = 'SET DEFAULT';

        return $this;
    }

    /**
     * Set the ON UPDATE action to NO ACTION.
     *
     * @return static
     */
    public function onUpdateNoAction(): self
    {
        $this->onUpdate = 'NO ACTION';

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        if (! isset($this->on) || $this->on === '') {
            throw InvalidSchemaException::incompleteForeignKey('on');
        }

        if (empty($this->references)) {
            throw InvalidSchemaException::incompleteForeignKey('references');
        }

        return 'CONSTRAINT `' . $this->name . '` '
            . ' FOREIGN KEY (`' . implode('`, `', $this->columns) . '`)'
            . ' REFERENCES `' . $this->on . '` (`' . implode('`, `', $this->references) . '`)'
            . ($this->onDelete ? ' ON DELETE ' . $this->onDelete : '')
            . ($this->onUpdate ? ' ON UPDATE ' . $this->onUpdate : '');
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
