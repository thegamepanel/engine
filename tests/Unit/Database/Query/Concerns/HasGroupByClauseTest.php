<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Concerns;

use Engine\Database\Query\Concerns\HasGroupByClause;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('group-by')]
class HasGroupByClauseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // groupBy()
    // -------------------------------------------------------------------------

    /**
     * - groupBy() with a single string column produces correct SQL.
     */
    #[Test]
    public function groupBySingleColumnProducesCorrectSql(): void
    {
        $query = $this->makeQuery();
        $query->groupBy('status');

        $this->assertSame('GROUP BY status', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    /**
     * - groupBy() with multiple columns produces comma-separated SQL.
     */
    #[Test]
    public function groupByMultipleColumnsProducesCommaSeparatedSql(): void
    {
        $query = $this->makeQuery();
        $query->groupBy('status', 'role');

        $this->assertSame('GROUP BY status, role', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    /**
     * - groupBy() with an Expression column embeds its SQL and collects bindings.
     */
    #[Test]
    public function groupByWithExpressionEmbedsSubquerySql(): void
    {
        $expr  = RawExpression::make('YEAR(created_at)', []);
        $query = $this->makeQuery();
        $query->groupBy($expr);

        $this->assertSame('GROUP BY YEAR(created_at)', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    /**
     * - groupBy() with an Expression collects its bindings.
     */
    #[Test]
    public function groupByWithExpressionCollectsBindings(): void
    {
        $expr  = RawExpression::make('IF(status = ?, 1, 0)', ['active']);
        $query = $this->makeQuery();
        $query->groupBy($expr);

        $this->assertSame('GROUP BY IF(status = ?, 1, 0)', $query->sql());
        $this->assertSame(['active'], $query->bindings());
    }

    /**
     * - Chained groupBy() calls accumulate columns.
     */
    #[Test]
    public function chainedGroupByCallsAccumulateColumns(): void
    {
        $query = $this->makeQuery();
        $query->groupBy('status');
        $query->groupBy('role');

        $this->assertSame('GROUP BY status, role', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    /**
     * - groupBy() with multiple Expressions accumulates all bindings.
     */
    #[Test]
    public function groupByWithMultipleExpressionsAccumulatesBindings(): void
    {
        $expr1 = RawExpression::make('IF(status = ?, 1, 0)', ['active']);
        $expr2 = RawExpression::make('IF(role = ?, 1, 0)', ['admin']);
        $query = $this->makeQuery();
        $query->groupBy($expr1, $expr2);

        $this->assertSame('GROUP BY IF(status = ?, 1, 0), IF(role = ?, 1, 0)', $query->sql());
        $this->assertSame(['active', 'admin'], $query->bindings());
    }

    /**
     * - No groupBy() calls produces an empty string.
     */
    #[Test]
    public function noGroupByProducesEmptyString(): void
    {
        $query = $this->makeQuery();

        $this->assertSame('', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    private function makeQuery(): object
    {
        return new class {
            use HasGroupByClause;

            public function sql(): string
            {
                return ltrim($this->buildGroupByClause());
            }

            public function bindings(): array
            {
                return $this->getGroupByBindings();
            }
        };
    }
}
