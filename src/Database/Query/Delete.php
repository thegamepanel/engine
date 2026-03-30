<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Contracts\Query;
use Engine\Database\Query\Concerns\HasLimitClause;
use Engine\Database\Query\Concerns\HasOrderByClause;
use Engine\Database\Query\Concerns\HasWhereClause;

final class Delete implements Query
{
    use HasWhereClause;
    use HasOrderByClause;
    use HasLimitClause;

    public static function from(string $table): self
    {
        return new self($table);
    }

    private function __construct(
        private string $table,
    ) {
    }

    /**
     * Get the SQL representation of the expression.
     *
     * @return string
     */
    public function toSql(): string
    {
        $where = $this->hasWhereClause() ? ' WHERE ' . $this->whereClause->toSql() : '';

        return "DELETE FROM {$this->table}"
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
        return array_merge(
            $this->whereClause->getBindings(),
            $this->getOrderByBindings(),
        );
    }
}
