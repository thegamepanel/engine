<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Engine\Database\Contracts\Expression;

trait HasGroupByClause
{
    /**
     * @var list<string|Expression>
     */
    private array $groups = [];

    /**
     * Add columns to the group by clause.
     *
     * @param string|Expression ...$columns
     *
     * @return static
     */
    public function groupBy(Expression|string ...$columns): static
    {
        array_push($this->groups, ...$columns);

        return $this;
    }

    private function buildGroupByClause(): string
    {
        if (empty($this->groups)) {
            return '';
        }

        $clauses = array_map(
            fn (Expression|string $col) => $col instanceof Expression ? $col->toSql() : $col,
            $this->groups,
        );

        return ' GROUP BY ' . implode(', ', $clauses);
    }

    /**
     * @return array<int, mixed>
     */
    private function getGroupByBindings(): array
    {
        $bindings = [];

        foreach ($this->groups as $group) {
            if ($group instanceof Expression) {
                $bindings[] = $group->getBindings();
            }
        }

        /** @var array<int, mixed> */
        return array_merge(...$bindings);
    }
}
