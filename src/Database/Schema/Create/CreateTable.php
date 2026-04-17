<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Create;

use Engine\Database\Contracts\Column;
use Engine\Database\Contracts\Expression;
use Engine\Database\Contracts\Index;
use Engine\Database\Contracts\Schema;
use Engine\Database\Schema\Concerns\HasCharsetAndCollation;
use Engine\Database\Schema\Concerns\HasComment;

/**
 * Create Table
 * ------------
 *
 * Schema builder for creating a table. Compiles into a single
 * "CREATE TABLE" statement.
 */
final class CreateTable implements Schema
{
    use HasCharsetAndCollation;
    use HasComment;

    public readonly string $table;

    /**
     * @var array<Column>
     */
    private array $columns;

    /**
     * @var array<Index>
     */
    private array $indexes = [];

    private bool $ifNotExists = false;

    private bool $temporary = false;

    private ?string $engine = null;

    private ?int $autoIncrement = null;

    /**
     * @var array<Expression>
     */
    private array $options = [];

    /**
     * @param string        $table
     * @param array<Column> $columns
     */
    public function __construct(string $table, array $columns)
    {
        $this->columns = $columns;
        $this->table   = $table;
    }

    /**
     * Only create the table if it does not already exist.
     *
     * @return static
     */
    public function ifNotExists(): self
    {
        $this->ifNotExists = true;

        return $this;
    }

    /**
     * Mark the table as temporary.
     *
     * @return static
     */
    public function temporary(): self
    {
        $this->temporary = true;

        return $this;
    }

    /**
     * Set the storage engine for the table.
     *
     * @param string $engine
     *
     * @return static
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Set the starting auto-increment value.
     *
     * @param int $autoIncrement
     *
     * @return static
     */
    public function autoIncrement(int $autoIncrement): self
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    /**
     * Set additional table options as raw expressions.
     *
     * @param Expression ...$options
     *
     * @return static
     */
    public function options(Expression ...$options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set the indexes for the table.
     *
     * @param Index ...$indexes
     *
     * @return static
     */
    public function indexes(Index ...$indexes): self
    {
        $this->indexes = $indexes;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = 'CREATE ' . ($this->temporary ? 'TEMPORARY ' : '') . ' TABLE ';
        $sql .= ($this->ifNotExists ? 'IF NOT EXISTS ' : '');
        $sql .= "`{$this->table}` (\n";
        $sql .= implode(",\n", array_map(static fn (Column $column) => $column->toSql(), $this->columns)) . ",\n";

        if (! empty($this->indexes)) {
            $sql .= implode(",\n", array_map(static fn (Index $index) => $index->toSql(), $this->indexes)) . ",\n";
        }

        $sql = rtrim($sql, ",\n") . "\n)";

        if ($this->engine !== null) {
            $sql .= ' ENGINE ' . $this->engine . ', ';
        }

        if ($this->hasCharset()) {
            $sql .= ' CHARACTER SET ' . $this->getCharset() . ', ';
        }

        if ($this->hasCollation()) {
            $sql .= ' COLLATE ' . $this->getCollation() . ', ';
        }

        if ($this->autoIncrement !== null) {
            $sql .= ' AUTO_INCREMENT ' . $this->autoIncrement . ', ';
        }

        if ($this->hasComment()) {
            $sql .= ' COMMENT \'' . $this->getComment() . '\', ';
        }

        if (! empty($this->options)) {
            $sql .= implode(', ', array_map(static fn (Expression $option) => $option->toSql(), $this->options)) . ', ';
        }

        return rtrim($sql, ', ');
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
