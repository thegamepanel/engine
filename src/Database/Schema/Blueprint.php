<?php
declare(strict_types=1);

namespace Engine\Database\Schema;

use Engine\Database\Contracts\Expression;

final class Blueprint implements Expression
{
    /**
     * @var list<array{action: 'add'|'modify'|'drop'|'rename', expression: Column|Index, details: array<string, string>}>
     */
    private array $operations = [];

    /**
     * Add a column or index to the table.
     *
     * @return static
     */
    public function add(Column|Index $expression): self
    {
        $this->operations[] = [
            'action'     => 'add',
            'expression' => $expression,
            'details'    => [],
        ];

        return $this;
    }

    /**
     * Modify a column in the table.
     *
     * @return static
     */
    public function modify(Column $column): self
    {
        $this->operations[] = [
            'action'     => 'modify',
            'expression' => $column,
            'details'    => [],
        ];

        return $this;
    }

    /**
     * Drop a column or index from the table.
     *
     * @return static
     */
    public function drop(Column|Index $reference): self
    {
        $this->operations[] = [
            'action'     => 'drop',
            'expression' => $reference,
            'details'    => [],
        ];

        return $this;
    }

    /**
     * Rename a column in the table.
     *
     * @return static
     */
    public function rename(Column $reference, string $newName): self
    {
        $this->operations[] = [
            'action'     => 'rename',
            'expression' => $reference,
            'details'    => ['to' => $newName],
        ];

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        return implode(', ', array_map($this->renderOperation(...), $this->operations));
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

    /**
     * @param array{action: 'add'|'modify'|'drop'|'rename', expression: Column|Index, details: array<string, string>} $operation
     */
    private function renderOperation(array $operation): string
    {
        $expression = $operation['expression'];

        return match ($operation['action']) {
            'add'    => ($expression instanceof Column ? 'ADD COLUMN ' : 'ADD ') . $expression->toSql(),
            'modify' => 'MODIFY COLUMN ' . $expression->toSql(),
            'drop'   => ($expression instanceof Column ? 'DROP COLUMN ' : 'DROP INDEX ') . $expression->toSql(),
            'rename' => "RENAME COLUMN {$expression->toSql()} TO `{$operation['details']['to']}`",
        };
    }
}
