<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Clauses;

use Engine\Database\Query\Clauses\WhereClause;
use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Exceptions\InvalidExpressionException;
use Engine\Database\Query\Raw;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WhereClauseTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $clause = new WhereClause();

        $this->assertInstanceOf(Expression::class, $clause);
    }

    // ── isEmpty ──────────────────────────────────────────────

    #[Test]
    public function it_is_empty_by_default(): void
    {
        $clause = new WhereClause();

        $this->assertTrue($clause->isEmpty());
    }

    #[Test]
    public function it_is_not_empty_after_adding_a_condition(): void
    {
        $clause = new WhereClause();
        $clause->where('id', 1);

        $this->assertFalse($clause->isEmpty());
    }

    // ── toSql / getBindings — empty ─────────────────────────

    #[Test]
    public function it_returns_empty_string_when_no_conditions(): void
    {
        $clause = new WhereClause();

        $this->assertSame('', $clause->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings_when_no_conditions(): void
    {
        $clause = new WhereClause();

        $this->assertSame([], $clause->getBindings());
    }

    // ── where() — 2-arg shorthand ───────────────────────────

    #[Test]
    public function it_handles_two_arg_where_as_equals(): void
    {
        $clause = new WhereClause();
        $clause->where('id', 42);

        $this->assertSame('id = ?', $clause->toSql());
        $this->assertSame([42], $clause->getBindings());
    }

    #[Test]
    public function it_handles_two_arg_where_with_falsy_value(): void
    {
        $clause = new WhereClause();
        $clause->where('active', false);

        $this->assertSame('active = ?', $clause->toSql());
        $this->assertSame([false], $clause->getBindings());
    }

    #[Test]
    public function it_handles_two_arg_where_with_zero(): void
    {
        $clause = new WhereClause();
        $clause->where('count', 0);

        $this->assertSame('count = ?', $clause->toSql());
        $this->assertSame([0], $clause->getBindings());
    }

    #[Test]
    public function it_handles_two_arg_where_with_null(): void
    {
        $clause = new WhereClause();
        $clause->where('value', null);

        $this->assertSame('value = ?', $clause->toSql());
        $this->assertSame([null], $clause->getBindings());
    }

    // ── where() — 3-arg with operator ───────────────────────

    #[Test]
    public function it_handles_three_arg_where_with_operator(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '>', 18);

        $this->assertSame('age > ?', $clause->toSql());
        $this->assertSame([18], $clause->getBindings());
    }

    // ── where() — closure (grouped) ─────────────────────────

    #[Test]
    public function it_handles_closure_as_grouped_condition(): void
    {
        $clause = new WhereClause();
        $clause->where('active', true);
        $clause->where(function (WhereClause $w) {
            $w->where('role', 'admin')
              ->orWhere('role', 'moderator');
        });

        $this->assertSame('active = ? AND (role = ? OR role = ?)', $clause->toSql());
        $this->assertSame([true, 'admin', 'moderator'], $clause->getBindings());
    }

    // ── orWhere() — 2-arg ───────────────────────────────────

    #[Test]
    public function it_handles_two_arg_or_where(): void
    {
        $clause = new WhereClause();
        $clause->where('status', 'active');
        $clause->orWhere('status', 'pending');

        $this->assertSame('status = ? OR status = ?', $clause->toSql());
        $this->assertSame(['active', 'pending'], $clause->getBindings());
    }

    // ── orWhere() — 3-arg ───────────────────────────────────

    #[Test]
    public function it_handles_three_arg_or_where(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '>', 18);
        $clause->orWhere('age', '<', 5);

        $this->assertSame('age > ? OR age < ?', $clause->toSql());
        $this->assertSame([18, 5], $clause->getBindings());
    }

    // ── orWhere() — closure ─────────────────────────────────

    #[Test]
    public function it_handles_or_where_with_closure(): void
    {
        $clause = new WhereClause();
        $clause->where('active', true);
        $clause->orWhere(function (WhereClause $w) {
            $w->where('role', 'admin')
              ->where('verified', true);
        });

        $this->assertSame('active = ? OR (role = ? AND verified = ?)', $clause->toSql());
        $this->assertSame([true, 'admin', true], $clause->getBindings());
    }

    // ── whereNull / orWhereNull / whereNotNull ──────────────

    #[Test]
    public function it_handles_where_null(): void
    {
        $clause = new WhereClause();
        $clause->whereNull('deleted_at');

        $this->assertSame('deleted_at IS NULL', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    #[Test]
    public function it_handles_or_where_null(): void
    {
        $clause = new WhereClause();
        $clause->where('active', false);
        $clause->orWhereNull('deleted_at');

        $this->assertSame('active = ? OR deleted_at IS NULL', $clause->toSql());
        $this->assertSame([false], $clause->getBindings());
    }

    #[Test]
    public function it_handles_where_not_null(): void
    {
        $clause = new WhereClause();
        $clause->whereNotNull('email');

        $this->assertSame('email IS NOT NULL', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // ── whereIn / whereNotIn — array ────────────────────────

    #[Test]
    public function it_handles_where_in_with_array(): void
    {
        $clause = new WhereClause();
        $clause->whereIn('role', ['admin', 'moderator']);

        $this->assertSame('role IN (?, ?)', $clause->toSql());
        $this->assertSame(['admin', 'moderator'], $clause->getBindings());
    }

    #[Test]
    public function it_handles_where_not_in_with_array(): void
    {
        $clause = new WhereClause();
        $clause->whereNotIn('status', ['deleted', 'banned']);

        $this->assertSame('status NOT IN (?, ?)', $clause->toSql());
        $this->assertSame(['deleted', 'banned'], $clause->getBindings());
    }

    // ── whereIn / whereNotIn — Expression (subquery) ────────

    #[Test]
    public function it_handles_where_in_with_expression(): void
    {
        $subquery = new Raw('SELECT user_id FROM orders WHERE total > ?', [100]);

        $clause = new WhereClause();
        $clause->whereIn('id', $subquery);

        $this->assertSame('id IN (SELECT user_id FROM orders WHERE total > ?)', $clause->toSql());
        $this->assertSame([100], $clause->getBindings());
    }

    #[Test]
    public function it_handles_where_not_in_with_expression(): void
    {
        $subquery = new Raw('SELECT id FROM blacklist', []);

        $clause = new WhereClause();
        $clause->whereNotIn('user_id', $subquery);

        $this->assertSame('user_id NOT IN (SELECT id FROM blacklist)', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // ── whereRaw ────────────────────────────────────────────

    #[Test]
    public function it_handles_where_raw(): void
    {
        $clause = new WhereClause();
        $clause->whereRaw('YEAR(created_at) = ?', [2025]);

        $this->assertSame('YEAR(created_at) = ?', $clause->toSql());
        $this->assertSame([2025], $clause->getBindings());
    }

    #[Test]
    public function it_handles_where_raw_without_bindings(): void
    {
        $clause = new WhereClause();
        $clause->whereRaw('1 = 1');

        $this->assertSame('1 = 1', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // ── Multiple conditions ─────────────────────────────────

    #[Test]
    public function it_chains_multiple_and_conditions(): void
    {
        $clause = new WhereClause();
        $clause->where('id', 42)
               ->where('age', '>', 18)
               ->whereNull('deleted_at');

        $this->assertSame('id = ? AND age > ? AND deleted_at IS NULL', $clause->toSql());
        $this->assertSame([42, 18], $clause->getBindings());
    }

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $clause = new WhereClause();

        $this->assertSame($clause, $clause->where('id', 1));
        $this->assertSame($clause, $clause->orWhere('id', 2));
        $this->assertSame($clause, $clause->whereNull('col'));
        $this->assertSame($clause, $clause->orWhereNull('col'));
        $this->assertSame($clause, $clause->whereNotNull('col'));
        $this->assertSame($clause, $clause->whereIn('col', [1]));
        $this->assertSame($clause, $clause->whereNotIn('col', [1]));
        $this->assertSame($clause, $clause->whereRaw('1 = 1'));
    }

    // ── Single condition — no conjunction prefix ────────────

    #[Test]
    public function it_omits_conjunction_for_first_condition(): void
    {
        $clause = new WhereClause();
        $clause->orWhere('status', 'active');

        // Even though orWhere uses 'OR', the first condition has no prefix
        $this->assertSame('status = ?', $clause->toSql());
    }

    // ── Empty closure throws ──────────────────────────────

    #[Test]
    public function it_throws_on_empty_where_closure(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->where(function (WhereClause $w) {
            // intentionally empty
        });
    }

    #[Test]
    public function it_throws_on_empty_or_where_closure(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->orWhere(function (WhereClause $w) {
            // intentionally empty
        });
    }

    // ── Nested closures ───────────────────────────────────

    #[Test]
    public function it_handles_nested_closures(): void
    {
        $clause = new WhereClause();
        $clause->where('a', 1);
        $clause->where(function (WhereClause $w) {
            $w->where('b', 2)
              ->orWhere(function (WhereClause $w2) {
                  $w2->where('c', 3)
                     ->where('d', 4);
              });
        });

        $this->assertSame('a = ? AND (b = ? OR (c = ? AND d = ?))', $clause->toSql());
        $this->assertSame([1, 2, 3, 4], $clause->getBindings());
    }

    // ── Empty array throws ────────────────────────────────

    #[Test]
    public function it_throws_on_where_in_with_empty_array(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->whereIn('id', []);
    }

    #[Test]
    public function it_throws_on_where_not_in_with_empty_array(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->whereNotIn('id', []);
    }
}
