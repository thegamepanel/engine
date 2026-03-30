<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Expressions\RawExpression;
use Engine\Database\Query\Update;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('update')]
class UpdateTest extends TestCase
{
    // -------------------------------------------------------------------------
    // set()
    // -------------------------------------------------------------------------

    /**
     * - set() with plain values produces correct SET SQL and bindings.
     */
    #[Test]
    public function setWithPlainValuesProducesCorrectSql(): void
    {
        $query = Update::table('users')->set(['name' => 'John', 'age' => 30]);

        $this->assertSame('UPDATE users SET name = ?, age = ?', $query->toSql());
        $this->assertSame(['John', 30], $query->getBindings());
    }

    /**
     * - set() with Expression values renders SQL inline.
     */
    #[Test]
    public function setWithExpressionRendersInlineSql(): void
    {
        $query = Update::table('counters')
            ->set(['hits' => RawExpression::make('hits + ?', [1])])
        ;

        $this->assertSame('UPDATE counters SET hits = hits + ?', $query->toSql());
        $this->assertSame([1], $query->getBindings());
    }

    /**
     * - set() with mixed plain and Expression values produces correct SQL and bindings.
     */
    #[Test]
    public function setWithMixedValuesProducesCorrectSql(): void
    {
        $query = Update::table('users')
            ->set([
                'name' => 'John',
                'hits' => RawExpression::make('hits + ?', [1]),
                'age'  => 30,
            ])
        ;

        $this->assertSame('UPDATE users SET name = ?, hits = hits + ?, age = ?', $query->toSql());
        $this->assertSame(['John', 1, 30], $query->getBindings());
    }

    /**
     * - Multiple set() calls merge values.
     */
    #[Test]
    public function multipleSetCallsMergeValues(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'John'])
            ->set(['age' => 30])
        ;

        $this->assertSame('UPDATE users SET name = ?, age = ?', $query->toSql());
        $this->assertSame(['John', 30], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // where
    // -------------------------------------------------------------------------

    /**
     * - set() with where clause produces correct SQL and bindings in order.
     */
    #[Test]
    public function setWithWhereProducesCorrectSql(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'John'])
            ->where('id', '=', 1)
        ;

        $this->assertSame('UPDATE users SET name = ? WHERE id = ?', $query->toSql());
        $this->assertSame(['John', 1], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // orderBy()
    // -------------------------------------------------------------------------

    /**
     * - orderBy() appends ORDER BY clause.
     */
    #[Test]
    public function orderByAppendsOrderByClause(): void
    {
        $query = Update::table('users')
            ->set(['status' => 'inactive'])
            ->orderBy('created_at', 'asc')
        ;

        $this->assertSame('UPDATE users SET status = ? ORDER BY created_at ASC', $query->toSql());
        $this->assertSame(['inactive'], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // limit()
    // -------------------------------------------------------------------------

    /**
     * - limit() appends LIMIT clause.
     */
    #[Test]
    public function limitAppendsLimitClause(): void
    {
        $query = Update::table('users')
            ->set(['status' => 'inactive'])
            ->limit(10)
        ;

        $this->assertSame('UPDATE users SET status = ? LIMIT 10', $query->toSql());
        $this->assertSame(['inactive'], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Full combination
    // -------------------------------------------------------------------------

    /**
     * - Full combination of set, where, order by, and limit produces correct SQL.
     */
    #[Test]
    public function fullCombinationProducesCorrectSql(): void
    {
        $query = Update::table('users')
            ->set(['status' => 'inactive'])
            ->where('active', '=', false)
            ->orderBy('created_at', 'asc')
            ->limit(100)
        ;

        $this->assertSame(
            'UPDATE users SET status = ? WHERE active = ? ORDER BY created_at ASC LIMIT 100',
            $query->toSql(),
        );
        $this->assertSame(['inactive', false], $query->getBindings());
    }

    /**
     * - Expression in set() combined with where produces correct binding order.
     */
    #[Test]
    public function expressionInSetWithWhereProducesCorrectBindingOrder(): void
    {
        $query = Update::table('counters')
            ->set(['hits' => RawExpression::make('hits + ?', [1])])
            ->where('name', '=', 'visits')
        ;

        $this->assertSame('UPDATE counters SET hits = hits + ? WHERE name = ?', $query->toSql());
        $this->assertSame([1, 'visits'], $query->getBindings());
    }
}
