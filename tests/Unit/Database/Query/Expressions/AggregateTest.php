<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Expressions\Aggregate;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('aggregate')]
class AggregateTest extends TestCase
{
    // -------------------------------------------------------------------------
    // COUNT
    // -------------------------------------------------------------------------

    /**
     * - COUNT(*) produces correct SQL with no bindings.
     */
    #[Test]
    public function countStarProducesCorrectSql(): void
    {
        $expr = Aggregate::make('COUNT', '*');

        $this->assertSame('COUNT(*)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    /**
     * - COUNT(column) produces correct SQL with no bindings.
     */
    #[Test]
    public function countColumnProducesCorrectSql(): void
    {
        $expr = Aggregate::make('COUNT', 'id');

        $this->assertSame('COUNT(id)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    // -------------------------------------------------------------------------
    // SUM / MIN / MAX / AVG
    // -------------------------------------------------------------------------

    /**
     * - SUM(column) produces correct SQL.
     */
    #[Test]
    public function sumProducesCorrectSql(): void
    {
        $expr = Aggregate::make('SUM', 'total');

        $this->assertSame('SUM(total)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    /**
     * - MIN(column) produces correct SQL.
     */
    #[Test]
    public function minProducesCorrectSql(): void
    {
        $expr = Aggregate::make('MIN', 'price');

        $this->assertSame('MIN(price)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    /**
     * - MAX(column) produces correct SQL.
     */
    #[Test]
    public function maxProducesCorrectSql(): void
    {
        $expr = Aggregate::make('MAX', 'price');

        $this->assertSame('MAX(price)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    /**
     * - AVG(column) produces correct SQL.
     */
    #[Test]
    public function avgProducesCorrectSql(): void
    {
        $expr = Aggregate::make('AVG', 'score');

        $this->assertSame('AVG(score)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    // -------------------------------------------------------------------------
    // Expression column
    // -------------------------------------------------------------------------

    /**
     * - An aggregate with an Expression column embeds its SQL and collects its bindings.
     */
    #[Test]
    public function aggregateWithExpressionColumnEmbedsSubquerySql(): void
    {
        $subExpr = RawExpression::make('DISTINCT status', []);

        $expr = Aggregate::make('COUNT', $subExpr);

        $this->assertSame('COUNT(DISTINCT status)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    /**
     * - An aggregate with an Expression column collects bindings from the expression.
     */
    #[Test]
    public function aggregateWithExpressionColumnCollectsBindings(): void
    {
        $subExpr = RawExpression::make('CASE WHEN status = ? THEN 1 END', ['active']);

        $expr = Aggregate::make('SUM', $subExpr);

        $this->assertSame('SUM(CASE WHEN status = ? THEN 1 END)', $expr->toSql());
        $this->assertSame(['active'], $expr->getBindings());
    }
}
