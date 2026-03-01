<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Engine\Database\Query\Contracts\Expression;

trait HasOrderByClause
{
    /**
     * @var list<array{column: string|\Engine\Database\Query\Contracts\Expression, direction: string}>
     */
    private array $orders = [];

    /**
     * Add an order by clause to the query.
     *
     * @param string|\Engine\Database\Query\Contracts\Expression $column
     * @param string                                              $direction
     *
     * @return $this
     */
    public function orderBy(string|Expression $column, string $direction = 'asc'): static
    {
        $this->orders[] = [
            'column'    => $column,
            'direction' => strtolower($direction) === 'desc' ? 'DESC' : 'ASC',
        ];

        return $this;
    }

    private function buildOrderByClause(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $clauses = array_map(function (array $order): string {
            $col = $order['column'] instanceof Expression
                ? $order['column']->toSql()
                : $order['column'];

            return "{$col} {$order['direction']}";
        }, $this->orders);

        return ' ORDER BY ' . implode(', ', $clauses);
    }

    /**
     * @return array<int, mixed>
     */
    private function getOrderByBindings(): array
    {
        $bindings = [];

        foreach ($this->orders as $order) {
            if ($order['column'] instanceof Expression) {
                $bindings = array_merge($bindings, $order['column']->getBindings());
            }
        }

        return $bindings;
    }
}
