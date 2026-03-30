<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Clauses;

use Engine\Database\Query\Clauses\JoinClause;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('join-clause')]
class JoinClauseTest extends TestCase
{
    /**
     * - on() produces column comparison SQL with no bindings.
     */
    #[Test]
    public function onProducesColumnComparisonSql(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'posts.user_id');
        $this->assertSame('users.id = posts.user_id', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    /**
     * - orOn() uses OR conjunction between conditions.
     */
    #[Test]
    public function orOnUsesOrConjunction(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'posts.user_id');
        $clause->orOn('users.id', '=', 'posts.editor_id');
        $this->assertSame('users.id = posts.user_id OR users.id = posts.editor_id', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    /**
     * - where() produces bound value condition SQL.
     */
    #[Test]
    public function whereProducesBoundValueSql(): void
    {
        $clause = new JoinClause();
        $clause->where('posts.status', '=', 'published');
        $this->assertSame('posts.status = ?', $clause->toSql());
        $this->assertSame(['published'], $clause->getBindings());
    }

    /**
     * - orWhere() uses OR conjunction with bound value.
     */
    #[Test]
    public function orWhereUsesOrConjunction(): void
    {
        $clause = new JoinClause();
        $clause->where('posts.status', '=', 'published');
        $clause->orWhere('posts.status', '=', 'draft');
        $this->assertSame('posts.status = ? OR posts.status = ?', $clause->toSql());
        $this->assertSame(['published', 'draft'], $clause->getBindings());
    }

    /**
     * - where() followed by on() uses AND conjunction from the on() call.
     */
    #[Test]
    public function whereFollowedByOnUsesAndConjunction(): void
    {
        $clause = new JoinClause();
        $clause->where('posts.status', '=', 'published');
        $clause->on('users.id', '=', 'posts.user_id');

        $this->assertSame('posts.status = ? AND users.id = posts.user_id', $clause->toSql());
        $this->assertSame(['published'], $clause->getBindings());
    }

    /**
     * - on() and where() can be combined in a single join clause.
     */
    #[Test]
    public function onAndWhereCanBeCombined(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'posts.user_id');
        $clause->where('posts.status', '=', 'published');
        $this->assertSame('users.id = posts.user_id AND posts.status = ?', $clause->toSql());
        $this->assertSame(['published'], $clause->getBindings());
    }

    /**
     * - isEmpty() returns true when no conditions added.
     */
    #[Test]
    public function isEmptyReturnsTrueWhenNoConditions(): void
    {
        $this->assertTrue(new JoinClause()->isEmpty());
    }

    /**
     * - getBindings() returns an empty array when no conditions are added.
     */
    #[Test]
    public function getBindingsReturnsEmptyArrayWhenNoConditions(): void
    {
        $this->assertSame([], new JoinClause()->getBindings());
    }

    /**
     * - isEmpty() returns false after a condition is added.
     */
    #[Test]
    public function isEmptyReturnsFalseAfterCondition(): void
    {
        $clause = new JoinClause();
        $clause->on('a.id', '=', 'b.a_id');
        $this->assertFalse($clause->isEmpty());
    }
}
