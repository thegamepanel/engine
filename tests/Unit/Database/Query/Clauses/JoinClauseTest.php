<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Clauses;

use Engine\Database\Query\Clauses\JoinClause;
use Engine\Database\Query\Contracts\Expression;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JoinClauseTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $clause = new JoinClause();

        $this->assertInstanceOf(Expression::class, $clause);
    }

    // ── isEmpty ──────────────────────────────────────────────

    #[Test]
    public function it_is_empty_by_default(): void
    {
        $clause = new JoinClause();

        $this->assertTrue($clause->isEmpty());
    }

    #[Test]
    public function it_is_not_empty_after_adding_a_condition(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'profiles.user_id');

        $this->assertFalse($clause->isEmpty());
    }

    // ── toSql / getBindings — empty ─────────────────────────

    #[Test]
    public function it_returns_empty_string_when_no_conditions(): void
    {
        $clause = new JoinClause();

        $this->assertSame('', $clause->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings_when_no_conditions(): void
    {
        $clause = new JoinClause();

        $this->assertSame([], $clause->getBindings());
    }

    // ── on() ─────────────────────────────────────────────────

    #[Test]
    public function it_handles_simple_on_condition(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'profiles.user_id');

        $this->assertSame('users.id = profiles.user_id', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // ── orOn() ───────────────────────────────────────────────

    #[Test]
    public function it_handles_or_on_condition(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'profiles.user_id');
        $clause->orOn('users.email', '=', 'profiles.email');

        $this->assertSame('users.id = profiles.user_id OR users.email = profiles.email', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // ── where() — 2-arg ─────────────────────────────────────

    #[Test]
    public function it_handles_two_arg_where(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'orders.user_id');
        $clause->where('orders.status', 'completed');

        $this->assertSame('users.id = orders.user_id AND orders.status = ?', $clause->toSql());
        $this->assertSame(['completed'], $clause->getBindings());
    }

    // ── where() — 3-arg ─────────────────────────────────────

    #[Test]
    public function it_handles_three_arg_where(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'orders.user_id');
        $clause->where('orders.total', '>', 100);

        $this->assertSame('users.id = orders.user_id AND orders.total > ?', $clause->toSql());
        $this->assertSame([100], $clause->getBindings());
    }

    // ── orWhere() — 2-arg ───────────────────────────────────

    #[Test]
    public function it_handles_two_arg_or_where(): void
    {
        $clause = new JoinClause();
        $clause->where('status', 'active');
        $clause->orWhere('status', 'pending');

        $this->assertSame('status = ? OR status = ?', $clause->toSql());
        $this->assertSame(['active', 'pending'], $clause->getBindings());
    }

    // ── orWhere() — 3-arg ───────────────────────────────────

    #[Test]
    public function it_handles_three_arg_or_where(): void
    {
        $clause = new JoinClause();
        $clause->where('total', '>', 100);
        $clause->orWhere('total', '<', 10);

        $this->assertSame('total > ? OR total < ?', $clause->toSql());
        $this->assertSame([100, 10], $clause->getBindings());
    }

    // ── Mixed on + where ────────────────────────────────────

    #[Test]
    public function it_combines_on_and_where_bindings(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'orders.user_id');
        $clause->where('orders.status', 'completed');
        $clause->where('orders.total', '>', 50);

        $this->assertSame(
            'users.id = orders.user_id AND orders.status = ? AND orders.total > ?',
            $clause->toSql(),
        );
        $this->assertSame(['completed', 50], $clause->getBindings());
    }

    // ── where() / orWhere() — falsy values ──────────────────

    #[Test]
    public function it_handles_where_with_falsy_values(): void
    {
        $clause = new JoinClause();
        $clause->on('users.id', '=', 'orders.user_id');
        $clause->where('active', false);
        $clause->where('count', 0);

        $this->assertSame(
            'users.id = orders.user_id AND active = ? AND count = ?',
            $clause->toSql(),
        );
        $this->assertSame([false, 0], $clause->getBindings());
    }

    #[Test]
    public function it_handles_or_where_with_falsy_values(): void
    {
        $clause = new JoinClause();
        $clause->where('active', false);
        $clause->orWhere('count', 0);

        $this->assertSame('active = ? OR count = ?', $clause->toSql());
        $this->assertSame([false, 0], $clause->getBindings());
    }

    // ── Fluent returns ──────────────────────────────────────

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $clause = new JoinClause();

        $this->assertSame($clause, $clause->on('a', '=', 'b'));
        $this->assertSame($clause, $clause->orOn('c', '=', 'd'));
        $this->assertSame($clause, $clause->where('col', 1));
        $this->assertSame($clause, $clause->orWhere('col', 2));
    }
}
