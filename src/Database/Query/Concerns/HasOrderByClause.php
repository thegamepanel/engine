<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

use Engine\Database\Contracts\Expression;

trait HasOrderByClause
{
    /**
     * @var list<array{column: string|Expression, direction: string}>
     */
    private array $orders = [];

    /**
     * Add an order by clause to the query.
     *
     * @param string|Expression $column
     * @param string            $direction
     *
     * @return static
     */
    public function orderBy(Expression|string $column, string $direction = 'asc'): static
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

        $clauses = array_map(static function (array $order): string {
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
                $bindings[] = $order['column']->getBindings();
            }
        }

        /** @var array<int, mixed> */
        return array_merge(...$bindings);
    }
}
