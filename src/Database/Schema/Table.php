<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Closure;
use Engine\Database\Contracts\Expression;
use Engine\Database\Contracts\Schema;

final class Table implements Schema
{
    private const string MODE_CREATE = 'create';

    private const string MODE_ALTER = 'alter';

    private const string MODE_DROP = 'drop';

    /**
     * Create a new table with the given definitions.
     *
     * @param list<Expression> $definitions
     */
    public static function create(string $table, array $definitions): self
    {
        return new self(self::MODE_CREATE, $table, $definitions);
    }

    /**
     * Alter an existing table using a blueprint closure.
     *
     * @param Closure(Blueprint): void $callback
     */
    public static function alter(string $table, Closure $callback): self
    {
        $blueprint = new Blueprint();
        $callback($blueprint);

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    /**
     * Drop a table.
     */
    public static function drop(string $table): self
    {
        return new self(self::MODE_DROP, $table);
    }

    /**
     * Add columns to an existing table.
     *
     * @param list<Column> $columns
     */
    public static function addColumns(string $table, array $columns): self
    {
        $blueprint = new Blueprint();

        foreach ($columns as $column) {
            $blueprint->add($column);
        }

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    /**
     * Drop columns from an existing table.
     *
     * @param list<string> $names
     */
    public static function dropColumns(string $table, array $names): self
    {
        $blueprint = new Blueprint();

        foreach ($names as $name) {
            $blueprint->drop(Column::named($name));
        }

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    /**
     * Add indexes to an existing table.
     *
     * @param list<Index> $indexes
     */
    public static function addIndexes(string $table, array $indexes): self
    {
        $blueprint = new Blueprint();

        foreach ($indexes as $index) {
            $blueprint->add($index);
        }

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    /**
     * Drop indexes from an existing table.
     *
     * @param list<string> $names
     */
    public static function dropIndexes(string $table, array $names): self
    {
        $blueprint = new Blueprint();

        foreach ($names as $name) {
            $blueprint->drop(Index::named($name));
        }

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    /**
     * Rename a column in an existing table.
     */
    public static function renameColumn(string $table, string $from, string $to): self
    {
        $blueprint = new Blueprint();
        $blueprint->rename(Column::named($from), $to);

        return new self(self::MODE_ALTER, $table, blueprint: $blueprint);
    }

    private bool $ifNotExists = false;

    private bool $ifExists = false;

    private ?string $engine = null;

    private ?string $charset = null;

    private ?string $collation = null;

    /**
     * @param self::MODE_*     $mode
     * @param list<Expression> $definitions
     */
    private function __construct(
        private readonly string     $mode,
        private readonly string     $table,
        private readonly array      $definitions = [],
        private readonly ?Blueprint $blueprint = null,
    ) {
    }

    /**
     * Add the IF NOT EXISTS modifier (create mode).
     *
     * @return static
     */
    public function ifNotExists(): self
    {
        $this->ifNotExists = true;

        return $this;
    }

    /**
     * Add the IF EXISTS modifier (drop mode).
     *
     * @return static
     */
    public function ifExists(): self
    {
        $this->ifExists = true;

        return $this;
    }

    /**
     * Set the table engine.
     *
     * @return static
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Set the default character set.
     *
     * @return static
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set the default collation.
     *
     * @return static
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return match ($this->mode) {
            self::MODE_CREATE => $this->buildCreate(),
            self::MODE_ALTER  => $this->buildAlter(),
            self::MODE_DROP   => $this->buildDrop(),
        };
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

    private function buildCreate(): string
    {
        $prefix = 'CREATE TABLE';

        if ($this->ifNotExists) {
            $prefix .= ' IF NOT EXISTS';
        }

        $definitionSql = implode(",\n    ", array_map(
            fn (Expression $def) => $def->toSql(),
            $this->definitions,
        ));

        $sql = "{$prefix} `{$this->table}` (\n    {$definitionSql}\n)";

        if ($this->engine !== null) {
            $sql .= " ENGINE = {$this->engine}";
        }

        if ($this->charset !== null) {
            $sql .= " DEFAULT CHARACTER SET {$this->charset}";
        }

        if ($this->collation !== null) {
            $sql .= " DEFAULT COLLATE {$this->collation}";
        }

        return $sql;
    }

    private function buildAlter(): string
    {
        assert($this->blueprint !== null);

        return "ALTER TABLE `{$this->table}` {$this->blueprint->toSql()}";
    }

    private function buildDrop(): string
    {
        $prefix = 'DROP TABLE';

        if ($this->ifExists) {
            $prefix .= ' IF EXISTS';
        }

        return "{$prefix} `{$this->table}`";
    }
}
