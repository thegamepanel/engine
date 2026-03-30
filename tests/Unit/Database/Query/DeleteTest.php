<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Delete;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('delete')]
class DeleteTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Basic delete
    // -------------------------------------------------------------------------

    /**
     * - A basic delete produces correct SQL.
     */
    #[Test]
    public function basicDeleteProducesCorrectSql(): void
    {
        $query = Delete::from('users');

        $this->assertSame('DELETE FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // where
    // -------------------------------------------------------------------------

    /**
     * - Delete with where clause produces correct SQL and bindings.
     */
    #[Test]
    public function deleteWithWhereProducesCorrectSql(): void
    {
        $query = Delete::from('users')
            ->where('status', '=', 'inactive')
        ;

        $this->assertSame('DELETE FROM users WHERE status = ?', $query->toSql());
        $this->assertSame(['inactive'], $query->getBindings());
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
        $query = Delete::from('logs')
            ->orderBy('created_at', 'asc')
        ;

        $this->assertSame('DELETE FROM logs ORDER BY created_at ASC', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - orderBy() with an Expression column collects its bindings.
     */
    #[Test]
    public function orderByWithExpressionCollectsBindings(): void
    {
        $expr  = RawExpression::make('FIELD(status, ?)', ['active']);
        $query = Delete::from('users')
            ->orderBy($expr)
        ;

        $this->assertSame('DELETE FROM users ORDER BY FIELD(status, ?) ASC', $query->toSql());
        $this->assertSame(['active'], $query->getBindings());
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
        $query = Delete::from('logs')
            ->limit(1000)
        ;

        $this->assertSame('DELETE FROM logs LIMIT 1000', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Full combination
    // -------------------------------------------------------------------------

    /**
     * - Full combination of where, order by, and limit produces correct SQL.
     */
    #[Test]
    public function fullCombinationProducesCorrectSql(): void
    {
        $query = Delete::from('logs')
            ->where('level', '=', 'debug')
            ->orderBy('created_at', 'asc')
            ->limit(1000)
        ;

        $this->assertSame(
            'DELETE FROM logs WHERE level = ? ORDER BY created_at ASC LIMIT 1000',
            $query->toSql(),
        );
        $this->assertSame(['debug'], $query->getBindings());
    }
}
