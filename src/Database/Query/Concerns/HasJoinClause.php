<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Closure;
use Engine\Database\Query\Clauses\JoinClause;

trait HasJoinClause
{
    /**
     * @var list<array{type: string, table: string, clause: \Engine\Database\Query\Clauses\JoinClause}>
     */
    private array $joins = [];

    /**
     * Add an inner join to the query.
     *
     * @param string          $table
     * @param string|\Closure $first
     * @param string|null     $operator
     * @param string|null     $second
     *
     * @return $this
     */
    public function join(string $table, string|Closure $first, ?string $operator = null, ?string $second = null): static
    {
        return $this->addJoin('INNER', $table, $first, $operator, $second);
    }

    /**
     * Add a left join to the query.
     *
     * @param string          $table
     * @param string|\Closure $first
     * @param string|null     $operator
     * @param string|null     $second
     *
     * @return $this
     */
    public function leftJoin(string $table, string|Closure $first, ?string $operator = null, ?string $second = null): static
    {
        return $this->addJoin('LEFT', $table, $first, $operator, $second);
    }

    /**
     * Add a right join to the query.
     *
     * @param string          $table
     * @param string|\Closure $first
     * @param string|null     $operator
     * @param string|null     $second
     *
     * @return $this
     */
    public function rightJoin(string $table, string|Closure $first, ?string $operator = null, ?string $second = null): static
    {
        return $this->addJoin('RIGHT', $table, $first, $operator, $second);
    }

    /**
     * Add a cross join to the query.
     *
     * @param string $table
     *
     * @return $this
     */
    public function crossJoin(string $table): static
    {
        $this->joins[] = ['type' => 'CROSS', 'table' => $table, 'clause' => new JoinClause()];

        return $this;
    }

    private function addJoin(string $type, string $table, string|Closure $first, ?string $operator, ?string $second): static
    {
        $clause = new JoinClause();

        if ($first instanceof Closure) {
            $first($clause);
        } else {
            /** @var string $operator */
            $clause->on($first, $operator, $second);
        }

        $this->joins[] = ['type' => $type, 'table' => $table, 'clause' => $clause];

        return $this;
    }

    private function buildJoinClause(): string
    {
        if (empty($this->joins)) {
            return '';
        }

        $parts = [];

        foreach ($this->joins as $join) {
            $sql = " {$join['type']} JOIN {$join['table']}";

            if (! $join['clause']->isEmpty()) {
                $sql .= ' ON ' . $join['clause']->toSql();
            }

            $parts[] = $sql;
        }

        return implode('', $parts);
    }

    /**
     * @return array<int, mixed>
     */
    private function getJoinBindings(): array
    {
        $bindings = [];

        foreach ($this->joins as $join) {
            $bindings = array_merge($bindings, $join['clause']->getBindings());
        }

        return $bindings;
    }
}
