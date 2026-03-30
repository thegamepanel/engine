<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Concerns;

use Engine\Database\Query\Clauses\WhereClause;
use Engine\Database\Query\Concerns\HasHavingClause;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('having')]
class HasHavingClauseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // having()
    // -------------------------------------------------------------------------

    /**
     * - having() with column, operator, and value produces correct SQL.
     */
    #[Test]
    public function havingWithColumnOperatorValueProducesCorrectSql(): void
    {
        $query = $this->makeQuery();
        $query->having('count', '>', 5);

        $this->assertSame('count > ?', $query->havingClause->toSql());
        $this->assertSame([5], $query->havingClause->getBindings());
    }

    /**
     * - having() with a closure produces grouped SQL.
     */
    #[Test]
    public function havingWithClosureProducesGroupedSql(): void
    {
        $query = $this->makeQuery();
        $query->having(function (WhereClause $q) {
            $q->where('total', '>', 100);
            $q->where('count', '>', 1);
        });

        $this->assertSame('(total > ? AND count > ?)', $query->havingClause->toSql());
        $this->assertSame([100, 1], $query->havingClause->getBindings());
    }

    // -------------------------------------------------------------------------
    // orHaving()
    // -------------------------------------------------------------------------

    /**
     * - orHaving() uses OR conjunction.
     */
    #[Test]
    public function orHavingUsesOrConjunction(): void
    {
        $query = $this->makeQuery();
        $query->having('count', '>', 5);
        $query->orHaving('total', '>', 1000);

        $this->assertSame('count > ? OR total > ?', $query->havingClause->toSql());
        $this->assertSame([5, 1000], $query->havingClause->getBindings());
    }

    // -------------------------------------------------------------------------
    // havingRaw() / orHavingRaw()
    // -------------------------------------------------------------------------

    /**
     * - havingRaw() produces exact SQL with AND conjunction.
     */
    #[Test]
    public function havingRawProducesExactSql(): void
    {
        $query = $this->makeQuery();
        $query->havingRaw('SUM(total) > ?', [500]);

        $this->assertSame('SUM(total) > ?', $query->havingClause->toSql());
        $this->assertSame([500], $query->havingClause->getBindings());
    }

    /**
     * - orHavingRaw() produces exact SQL with OR conjunction.
     */
    #[Test]
    public function orHavingRawUsesOrConjunction(): void
    {
        $query = $this->makeQuery();
        $query->having('count', '>', 5);
        $query->orHavingRaw('AVG(score) > ?', [80]);

        $this->assertSame('count > ? OR AVG(score) > ?', $query->havingClause->toSql());
        $this->assertSame([5, 80], $query->havingClause->getBindings());
    }

    // -------------------------------------------------------------------------
    // isEmpty
    // -------------------------------------------------------------------------

    /**
     * - The having clause is initially empty.
     */
    #[Test]
    public function havingClauseIsInitiallyEmpty(): void
    {
        $query = $this->makeQuery();

        $this->assertTrue($query->havingClause->isEmpty());
    }

    /**
     * - The having clause is not empty after adding a condition.
     */
    #[Test]
    public function havingClauseIsNotEmptyAfterCondition(): void
    {
        $query = $this->makeQuery();
        $query->having('count', '>', 5);

        $this->assertFalse($query->havingClause->isEmpty());
    }

    private function makeQuery(): object
    {
        return new class {
            use HasHavingClause;
        };
    }
}
