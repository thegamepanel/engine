<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Expression;

final class Index implements Expression
{
    /**
     * Create a PRIMARY KEY index.
     *
     * @param list<string> $columns
     */
    public static function primary(array|string $columns): self
    {
        return new self('primary', null, (array) $columns);
    }

    /**
     * Create a UNIQUE INDEX.
     *
     * @param list<string> $columns
     */
    public static function unique(string $name, array|string $columns): self
    {
        return new self('unique', $name, (array) $columns);
    }

    /**
     * Create an INDEX.
     *
     * @param list<string> $columns
     */
    public static function index(string $name, array|string $columns): self
    {
        return new self('index', $name, (array) $columns);
    }

    /**
     * Create a FULLTEXT INDEX.
     *
     * @param list<string> $columns
     */
    public static function fulltext(string $name, array|string $columns): self
    {
        return new self('fulltext', $name, (array) $columns);
    }

    /**
     * Create a FOREIGN KEY constraint.
     *
     * @param list<string> $columns
     */
    public static function foreign(string $name, array|string $columns): self
    {
        return new self('foreign', $name, (array) $columns);
    }

    /**
     * Create a named reference to an existing index, for use in
     * Blueprint drop operations. Produces only the backtick-quoted name.
     */
    public static function named(string $name): self
    {
        return new self('named', $name, []);
    }

    private ?string $referenceTable = null;

    /**
     * @var list<string>
     */
    private array $referenceColumns = [];

    private ?string $deleteAction = null;

    private ?string $updateAction = null;

    /**
     * @param 'primary'|'unique'|'index'|'fulltext'|'foreign'|'named' $type
     * @param list<string>                                            $columns
     */
    private function __construct(
        private readonly string  $type,
        private readonly ?string $name,
        private readonly array   $columns,
    ) {
    }

    /**
     * Set the referenced table and columns for a foreign key.
     *
     * @param string|list<string> $columns
     *
     * @return static
     */
    public function references(string $table, array|string $columns): self
    {
        $this->referenceTable   = $table;
        $this->referenceColumns = (array) $columns;

        return $this;
    }

    /**
     * Set the ON DELETE action for a foreign key. The action
     * string is automatically uppercased.
     *
     * @return static
     */
    public function onDelete(string $action): self
    {
        $this->deleteAction = strtoupper($action);

        return $this;
    }

    /**
     * Set the ON UPDATE action for a foreign key. The action
     * string is automatically uppercased.
     *
     * @return static
     */
    public function onUpdate(string $action): self
    {
        $this->updateAction = strtoupper($action);

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        if ($this->type === 'named') {
            return "`{$this->name}`";
        }

        if ($this->type === 'foreign') {
            return $this->buildForeignKeySql();
        }

        $quotedColumns = $this->quoteColumns($this->columns);

        return match ($this->type) {
            'primary'  => "PRIMARY KEY ({$quotedColumns})",
            'unique'   => "UNIQUE INDEX `{$this->name}` ({$quotedColumns})",
            'index'    => "INDEX `{$this->name}` ({$quotedColumns})",
            'fulltext' => "FULLTEXT INDEX `{$this->name}` ({$quotedColumns})",
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

    private function buildForeignKeySql(): string
    {
        $quotedColumns = $this->quoteColumns($this->columns);

        $sql = "CONSTRAINT `{$this->name}` FOREIGN KEY ({$quotedColumns})";

        if ($this->referenceTable !== null) {
            $refColumns = $this->quoteColumns($this->referenceColumns);
            $sql .= " REFERENCES `{$this->referenceTable}` ({$refColumns})";
        }

        if ($this->deleteAction !== null) {
            $sql .= " ON DELETE {$this->deleteAction}";
        }

        if ($this->updateAction !== null) {
            $sql .= " ON UPDATE {$this->updateAction}";
        }

        return $sql;
    }

    /**
     * @param list<string> $columns
     */
    private function quoteColumns(array $columns): string
    {
        return implode(', ', array_map(
            fn (string $column) => "`{$column}`",
            $columns,
        ));
    }
}
