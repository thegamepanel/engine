<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Contracts\Expression;
use Engine\Database\Contracts\Query;
use Engine\Database\Query\Concerns\HasLimitClause;
use Engine\Database\Query\Concerns\HasOrderByClause;
use Engine\Database\Query\Concerns\HasWhereClause;

final class Update implements Query
{
    use HasWhereClause;
    use HasOrderByClause;
    use HasLimitClause;

    public static function table(string $table): self
    {
        return new self($table);
    }

    /**
     * @var array<string, mixed|Expression>
     */
    private array $sets = [];

    private function __construct(
        private string $table,
    ) {
    }

    /**
     * Set the column values to update.
     *
     * @param array<string, mixed|Expression> $values
     *
     * @return static
     */
    public function set(array $values): self
    {
        $this->sets = array_merge($this->sets, $values);

        return $this;
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $setClauses = [];

        foreach ($this->sets as $column => $value) {
            if ($value instanceof Expression) {
                $setClauses[] = "{$column} = {$value->toSql()}";
            } else {
                $setClauses[] = "{$column} = ?";
            }
        }

        $where = $this->hasWhereClause() ? ' WHERE ' . $this->whereClause->toSql() : '';

        return "UPDATE {$this->table} SET " . implode(', ', $setClauses)
             . $where
             . $this->buildOrderByClause()
             . $this->buildLimitClause();
    }

    /**
     * Get the bindings for the expression.
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array
    {
        $setBindings = [];

        foreach ($this->sets as $value) {
            if ($value instanceof Expression) {
                array_push($setBindings, ...$value->getBindings());
            } else {
                $setBindings[] = $value;
            }
        }

        return array_merge(
            $setBindings,
            $this->whereClause->getBindings(),
            $this->getOrderByBindings(),
        );
    }
}
