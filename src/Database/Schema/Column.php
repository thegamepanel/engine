<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Expression;

final class Column implements Expression
{
    /**
     * Create a BIGINT column.
     */
    public static function bigInt(string $name): self
    {
        return new self($name, 'BIGINT');
    }

    /**
     * Create an INT column.
     */
    public static function int(string $name): self
    {
        return new self($name, 'INT');
    }

    /**
     * Create a SMALLINT column.
     */
    public static function smallInt(string $name): self
    {
        return new self($name, 'SMALLINT');
    }

    /**
     * Create a TINYINT column.
     */
    public static function tinyInt(string $name): self
    {
        return new self($name, 'TINYINT');
    }

    /**
     * Create a DECIMAL column.
     */
    public static function decimal(string $name, int $precision, int $scale): self
    {
        return new self($name, "DECIMAL({$precision}, {$scale})");
    }

    /**
     * Create a FLOAT column.
     */
    public static function float(string $name): self
    {
        return new self($name, 'FLOAT');
    }

    /**
     * Create a DOUBLE column.
     */
    public static function double(string $name): self
    {
        return new self($name, 'DOUBLE');
    }

    /**
     * Create a VARCHAR column.
     */
    public static function string(string $name, int $length): self
    {
        return new self($name, "VARCHAR({$length})");
    }

    /**
     * Create a TEXT column.
     */
    public static function text(string $name): self
    {
        return new self($name, 'TEXT');
    }

    /**
     * Create a MEDIUMTEXT column.
     */
    public static function mediumText(string $name): self
    {
        return new self($name, 'MEDIUMTEXT');
    }

    /**
     * Create a LONGTEXT column.
     */
    public static function longText(string $name): self
    {
        return new self($name, 'LONGTEXT');
    }

    /**
     * Create a BOOLEAN column.
     */
    public static function boolean(string $name): self
    {
        return new self($name, 'BOOLEAN');
    }

    /**
     * Create a DATE column.
     */
    public static function date(string $name): self
    {
        return new self($name, 'DATE');
    }

    /**
     * Create a DATETIME column.
     */
    public static function datetime(string $name): self
    {
        return new self($name, 'DATETIME');
    }

    /**
     * Create a TIMESTAMP column.
     */
    public static function timestamp(string $name): self
    {
        return new self($name, 'TIMESTAMP');
    }

    /**
     * Create a TIME column.
     */
    public static function time(string $name): self
    {
        return new self($name, 'TIME');
    }

    /**
     * Create a BINARY column.
     */
    public static function binary(string $name): self
    {
        return new self($name, 'BINARY');
    }

    /**
     * Create a BLOB column.
     */
    public static function blob(string $name): self
    {
        return new self($name, 'BLOB');
    }

    /**
     * Create a JSON column.
     */
    public static function json(string $name): self
    {
        return new self($name, 'JSON');
    }

    /**
     * Create an ENUM column.
     *
     * @param list<string> $values
     */
    public static function enum(string $name, array $values): self
    {
        $quoted = implode(', ', array_map(
            fn (string $v) => "'" . str_replace("'", "''", $v) . "'",
            $values,
        ));

        return new self($name, "ENUM({$quoted})");
    }

    /**
     * Create a named reference to an existing column, for use in
     * Blueprint drop and rename operations. Produces only the
     * backtick-quoted name, with no type or modifiers.
     */
    public static function named(string $name): self
    {
        $column             = new self($name, '');
        $column->isNamedRef = true;

        return $column;
    }

    private bool $isNullable = false;

    private bool $hasDefault = false;

    private bool|Expression|float|int|string|null $defaultValue = null;

    private bool $isUnsigned = false;

    private bool $isAutoInc = false;

    private ?string $comment = null;

    private ?string $charset = null;

    private ?string $collation = null;

    private ?string $afterColumn = null;

    private bool $isFirst = false;

    private bool $isNamedRef = false;

    private function __construct(
        private readonly string $name,
        private readonly string $type,
    ) {
    }

    /**
     * Mark the column as nullable.
     *
     * @return static
     */
    public function nullable(): self
    {
        $this->isNullable = true;

        return $this;
    }

    /**
     * Set the default value for the column.
     *
     * Accepts scalar values for literal defaults, or an Expression
     * for raw SQL defaults like CURRENT_TIMESTAMP.
     *
     * @return static
     */
    public function default(bool|Expression|float|int|string|null $value): self
    {
        $this->hasDefault   = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Mark the column as unsigned.
     *
     * @return static
     */
    public function unsigned(): self
    {
        $this->isUnsigned = true;

        return $this;
    }

    /**
     * Mark the column as auto-incrementing.
     *
     * @return static
     */
    public function autoIncrement(): self
    {
        $this->isAutoInc = true;

        return $this;
    }

    /**
     * Set a comment on the column.
     *
     * @return static
     */
    public function comment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Set the character set for the column.
     *
     * @return static
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set the collation for the column.
     *
     * @return static
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * Position the column after an existing column.
     *
     * @return static
     */
    public function after(string $column): self
    {
        $this->afterColumn = $column;

        return $this;
    }

    /**
     * Position the column first in the table.
     *
     * @return static
     */
    public function first(): self
    {
        $this->isFirst = true;

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        if ($this->isNamedRef) {
            return "`{$this->name}`";
        }

        $parts = ["`{$this->name}`", $this->type];

        if ($this->isUnsigned) {
            $parts[] = 'UNSIGNED';
        }

        $parts[] = $this->isNullable ? 'NULL' : 'NOT NULL';

        if ($this->hasDefault) {
            $parts[] = 'DEFAULT ' . $this->renderDefault();
        }

        if ($this->isAutoInc) {
            $parts[] = 'AUTO_INCREMENT';
        }

        if ($this->comment !== null) {
            $parts[] = "COMMENT '" . str_replace("'", "''", $this->comment) . "'";
        }

        if ($this->charset !== null) {
            $parts[] = "CHARACTER SET {$this->charset}";
        }

        if ($this->collation !== null) {
            $parts[] = "COLLATE {$this->collation}";
        }

        if ($this->isFirst) {
            $parts[] = 'FIRST';
        }

        if ($this->afterColumn !== null) {
            $parts[] = "AFTER `{$this->afterColumn}`";
        }

        return implode(' ', $parts);
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

    private function renderDefault(): string
    {
        if ($this->defaultValue instanceof Expression) {
            return $this->defaultValue->toSql();
        }

        if ($this->defaultValue === null) {
            return 'NULL';
        }

        if (is_bool($this->defaultValue)) {
            return $this->defaultValue ? 'TRUE' : 'FALSE';
        }

        if (is_int($this->defaultValue) || is_float($this->defaultValue)) {
            return (string) $this->defaultValue;
        }

        return "'" . str_replace("'", "''", $this->defaultValue) . "'";
    }
}
