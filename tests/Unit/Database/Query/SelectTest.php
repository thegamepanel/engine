<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Clauses\JoinClause;
use Engine\Database\Query\Clauses\WhereClause;
use Engine\Database\Query\Contracts\Query;
use Engine\Database\Query\Exceptions\InvalidExpressionException;
use Engine\Database\Query\Raw;
use Engine\Database\Query\Select;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SelectTest extends TestCase
{
    #[Test]
    public function it_implements_query(): void
    {
        $query = Select::from('users');

        $this->assertInstanceOf(Query::class, $query);
    }

    // ── Basic SELECT ────────────────────────────────────────

    #[Test]
    public function it_generates_select_all_by_default(): void
    {
        $query = Select::from('users');

        $this->assertSame('SELECT * FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // ── Columns ─────────────────────────────────────────────

    #[Test]
    public function it_selects_specific_columns(): void
    {
        $query = Select::from('users')->columns('id', 'name', 'email');

        $this->assertSame('SELECT id, name, email FROM users', $query->toSql());
    }

    #[Test]
    public function it_selects_expression_columns(): void
    {
        $query = Select::from('users')->columns(
            'id',
            new Raw('COUNT(*) as total'),
        );

        $this->assertSame('SELECT id, COUNT(*) as total FROM users', $query->toSql());
    }

    #[Test]
    public function it_includes_expression_column_bindings(): void
    {
        $query = Select::from('users')->columns(
            new Raw('SUM(CASE WHEN age > ? THEN 1 ELSE 0 END) as adults', [18]),
        );

        $this->assertSame([18], $query->getBindings());
    }

    #[Test]
    public function it_adds_columns_incrementally(): void
    {
        $query = Select::from('users')
            ->columns('id')
            ->addColumn('name')
            ->addColumn('email');

        $this->assertSame('SELECT id, name, email FROM users', $query->toSql());
    }

    // ── DISTINCT ────────────────────────────────────────────

    #[Test]
    public function it_generates_distinct(): void
    {
        $query = Select::from('users')->distinct()->columns('email');

        $this->assertSame('SELECT DISTINCT email FROM users', $query->toSql());
    }

    // ── Table as Expression (subquery) ──────────────────────

    #[Test]
    public function it_handles_expression_as_table(): void
    {
        $subquery = Select::from('orders')->columns('user_id')->where('total', '>', 100);
        $query    = Select::from($subquery)->columns('user_id');

        $this->assertSame(
            'SELECT user_id FROM (SELECT user_id FROM orders WHERE total > ?)',
            $query->toSql(),
        );
        $this->assertSame([100], $query->getBindings());
    }

    // ── WHERE (via HasWhereClause trait) ─────────────────────

    #[Test]
    public function it_generates_where_clause(): void
    {
        $query = Select::from('users')->where('id', 1);

        $this->assertSame('SELECT * FROM users WHERE id = ?', $query->toSql());
        $this->assertSame([1], $query->getBindings());
    }

    #[Test]
    public function it_handles_two_arg_where_through_trait(): void
    {
        $query = Select::from('users')->where('active', false);

        $this->assertSame('SELECT * FROM users WHERE active = ?', $query->toSql());
        $this->assertSame([false], $query->getBindings());
    }

    #[Test]
    public function it_handles_three_arg_where_through_trait(): void
    {
        $query = Select::from('users')->where('age', '>', 18);

        $this->assertSame('SELECT * FROM users WHERE age > ?', $query->toSql());
        $this->assertSame([18], $query->getBindings());
    }

    #[Test]
    public function it_handles_or_where_through_trait(): void
    {
        $query = Select::from('users')
            ->where('status', 'active')
            ->orWhere('status', 'pending');

        $this->assertSame('SELECT * FROM users WHERE status = ? OR status = ?', $query->toSql());
        $this->assertSame(['active', 'pending'], $query->getBindings());
    }

    #[Test]
    public function it_handles_where_null_through_trait(): void
    {
        $query = Select::from('users')->whereNull('deleted_at');

        $this->assertSame('SELECT * FROM users WHERE deleted_at IS NULL', $query->toSql());
    }

    #[Test]
    public function it_handles_or_where_null_through_trait(): void
    {
        $query = Select::from('users')
            ->where('active', false)
            ->orWhereNull('deleted_at');

        $this->assertSame('SELECT * FROM users WHERE active = ? OR deleted_at IS NULL', $query->toSql());
    }

    #[Test]
    public function it_handles_where_not_null_through_trait(): void
    {
        $query = Select::from('users')->whereNotNull('email');

        $this->assertSame('SELECT * FROM users WHERE email IS NOT NULL', $query->toSql());
    }

    #[Test]
    public function it_handles_where_in_through_trait(): void
    {
        $query = Select::from('users')->whereIn('role', ['admin', 'mod']);

        $this->assertSame('SELECT * FROM users WHERE role IN (?, ?)', $query->toSql());
        $this->assertSame(['admin', 'mod'], $query->getBindings());
    }

    #[Test]
    public function it_handles_where_not_in_through_trait(): void
    {
        $query = Select::from('users')->whereNotIn('status', ['deleted', 'banned']);

        $this->assertSame('SELECT * FROM users WHERE status NOT IN (?, ?)', $query->toSql());
        $this->assertSame(['deleted', 'banned'], $query->getBindings());
    }

    #[Test]
    public function it_handles_where_raw_through_trait(): void
    {
        $query = Select::from('users')->whereRaw('YEAR(created_at) = ?', [2025]);

        $this->assertSame('SELECT * FROM users WHERE YEAR(created_at) = ?', $query->toSql());
        $this->assertSame([2025], $query->getBindings());
    }

    #[Test]
    public function it_handles_closure_where_through_trait(): void
    {
        $query = Select::from('users')
            ->where('active', true)
            ->where(function (WhereClause $w) {
                $w->where('role', 'admin')
                  ->orWhere('role', 'moderator');
            });

        $this->assertSame(
            'SELECT * FROM users WHERE active = ? AND (role = ? OR role = ?)',
            $query->toSql(),
        );
        $this->assertSame([true, 'admin', 'moderator'], $query->getBindings());
    }

    // ── JOIN (via HasJoinClause trait) ───────────────────────

    #[Test]
    public function it_generates_inner_join(): void
    {
        $query = Select::from('users')
            ->join('profiles', 'users.id', '=', 'profiles.user_id');

        $this->assertSame(
            'SELECT * FROM users INNER JOIN profiles ON users.id = profiles.user_id',
            $query->toSql(),
        );
    }

    #[Test]
    public function it_generates_left_join(): void
    {
        $query = Select::from('users')
            ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id');

        $this->assertSame(
            'SELECT * FROM users LEFT JOIN profiles ON users.id = profiles.user_id',
            $query->toSql(),
        );
    }

    #[Test]
    public function it_generates_right_join(): void
    {
        $query = Select::from('users')
            ->rightJoin('profiles', 'users.id', '=', 'profiles.user_id');

        $this->assertSame(
            'SELECT * FROM users RIGHT JOIN profiles ON users.id = profiles.user_id',
            $query->toSql(),
        );
    }

    #[Test]
    public function it_generates_cross_join(): void
    {
        $query = Select::from('users')->crossJoin('roles');

        $this->assertSame('SELECT * FROM users CROSS JOIN roles', $query->toSql());
    }

    #[Test]
    public function it_generates_closure_join(): void
    {
        $query = Select::from('users')
            ->leftJoin('orders', function (JoinClause $join) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->where('orders.status', 'completed');
            });

        $this->assertSame(
            'SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id AND orders.status = ?',
            $query->toSql(),
        );
        $this->assertSame(['completed'], $query->getBindings());
    }

    #[Test]
    public function it_generates_multiple_joins(): void
    {
        $query = Select::from('users')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->leftJoin('avatars', 'users.id', '=', 'avatars.user_id');

        $this->assertSame(
            'SELECT * FROM users INNER JOIN profiles ON users.id = profiles.user_id LEFT JOIN avatars ON users.id = avatars.user_id',
            $query->toSql(),
        );
    }

    // ── ORDER BY (via HasOrderByClause trait) ────────────────

    #[Test]
    public function it_generates_order_by_asc_by_default(): void
    {
        $query = Select::from('users')->orderBy('name');

        $this->assertSame('SELECT * FROM users ORDER BY name ASC', $query->toSql());
    }

    #[Test]
    public function it_generates_order_by_desc(): void
    {
        $query = Select::from('users')->orderBy('created_at', 'desc');

        $this->assertSame('SELECT * FROM users ORDER BY created_at DESC', $query->toSql());
    }

    #[Test]
    public function it_handles_case_insensitive_direction(): void
    {
        $query = Select::from('users')->orderBy('name', 'DESC');

        $this->assertSame('SELECT * FROM users ORDER BY name DESC', $query->toSql());
    }

    #[Test]
    public function it_generates_multiple_order_by(): void
    {
        $query = Select::from('users')
            ->orderBy('name')
            ->orderBy('id', 'desc');

        $this->assertSame('SELECT * FROM users ORDER BY name ASC, id DESC', $query->toSql());
    }

    #[Test]
    public function it_generates_order_by_with_expression(): void
    {
        $query = Select::from('users')->orderBy(new Raw('FIELD(status, ?, ?, ?)', ['active', 'pending', 'inactive']));

        $this->assertSame('SELECT * FROM users ORDER BY FIELD(status, ?, ?, ?) ASC', $query->toSql());
        $this->assertSame(['active', 'pending', 'inactive'], $query->getBindings());
    }

    // ── LIMIT / OFFSET (via HasLimitClause trait) ────────────

    #[Test]
    public function it_generates_limit(): void
    {
        $query = Select::from('users')->limit(10);

        $this->assertSame('SELECT * FROM users LIMIT 10', $query->toSql());
    }

    #[Test]
    public function it_generates_offset(): void
    {
        $query = Select::from('users')->offset(20);

        $this->assertSame('SELECT * FROM users OFFSET 20', $query->toSql());
    }

    #[Test]
    public function it_generates_limit_and_offset(): void
    {
        $query = Select::from('users')->limit(10)->offset(20);

        $this->assertSame('SELECT * FROM users LIMIT 10 OFFSET 20', $query->toSql());
    }

    // ── Full query ──────────────────────────────────────────

    #[Test]
    public function it_generates_full_query(): void
    {
        $query = Select::from('users')
            ->distinct()
            ->columns('users.id', 'users.name', 'profiles.bio')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('users.active', true)
            ->whereNotNull('users.email')
            ->orderBy('users.name')
            ->limit(10)
            ->offset(20);

        $this->assertSame(
            'SELECT DISTINCT users.id, users.name, profiles.bio FROM users'
            . ' INNER JOIN profiles ON users.id = profiles.user_id'
            . ' WHERE users.active = ? AND users.email IS NOT NULL'
            . ' ORDER BY users.name ASC'
            . ' LIMIT 10 OFFSET 20',
            $query->toSql(),
        );
        $this->assertSame([true], $query->getBindings());
    }

    // ── Binding order ───────────────────────────────────────

    #[Test]
    public function it_returns_bindings_in_sql_order(): void
    {
        $query = Select::from('users')
            ->columns(new Raw('SUM(CASE WHEN age > ? THEN 1 ELSE 0 END)', [18]))
            ->leftJoin('orders', function (JoinClause $join) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->where('orders.status', 'completed');
            })
            ->where('users.active', true)
            ->orderBy(new Raw('FIELD(role, ?)', ['admin']));

        // Binding order: column expressions → join → where → order by
        $this->assertSame([18, 'completed', true, 'admin'], $query->getBindings());
    }

    // ── orWhere 3-arg through trait ──────────────────────────

    #[Test]
    public function it_handles_or_where_three_arg_through_trait(): void
    {
        $query = Select::from('users')
            ->where('age', '>', 18)
            ->orWhere('age', '<', 5);

        $this->assertSame('SELECT * FROM users WHERE age > ? OR age < ?', $query->toSql());
        $this->assertSame([18, 5], $query->getBindings());
    }

    // ── whereIn / whereNotIn with Expression through trait ──

    #[Test]
    public function it_handles_where_in_with_expression_through_trait(): void
    {
        $subquery = new Raw('SELECT user_id FROM orders WHERE total > ?', [100]);
        $query    = Select::from('users')->whereIn('id', $subquery);

        $this->assertSame(
            'SELECT * FROM users WHERE id IN (SELECT user_id FROM orders WHERE total > ?)',
            $query->toSql(),
        );
        $this->assertSame([100], $query->getBindings());
    }

    #[Test]
    public function it_handles_where_not_in_with_expression_through_trait(): void
    {
        $subquery = new Raw('SELECT id FROM blacklist');
        $query    = Select::from('users')->whereNotIn('user_id', $subquery);

        $this->assertSame(
            'SELECT * FROM users WHERE user_id NOT IN (SELECT id FROM blacklist)',
            $query->toSql(),
        );
        $this->assertSame([], $query->getBindings());
    }

    // ── Empty array through trait throws ────────────────────

    #[Test]
    public function it_throws_on_where_in_empty_array_through_trait(): void
    {
        $this->expectException(InvalidExpressionException::class);

        Select::from('users')->whereIn('id', []);
    }

    // ── LIMIT 0 / OFFSET 0 ─────────────────────────────────

    #[Test]
    public function it_generates_limit_zero(): void
    {
        $query = Select::from('users')->limit(0);

        $this->assertSame('SELECT * FROM users LIMIT 0', $query->toSql());
    }

    #[Test]
    public function it_generates_offset_zero(): void
    {
        $query = Select::from('users')->offset(0);

        $this->assertSame('SELECT * FROM users OFFSET 0', $query->toSql());
    }

    // ── Invalid direction defaults to ASC ───────────────────

    #[Test]
    public function it_defaults_invalid_direction_to_asc(): void
    {
        $query = Select::from('users')->orderBy('name', 'invalid');

        $this->assertSame('SELECT * FROM users ORDER BY name ASC', $query->toSql());
    }

    // ── Fluent returns ──────────────────────────────────────

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $query = Select::from('users');

        $this->assertSame($query, $query->columns('id'));
        $this->assertSame($query, $query->addColumn('name'));
        $this->assertSame($query, $query->distinct());
        $this->assertSame($query, $query->where('id', 1));
        $this->assertSame($query, $query->join('t', 'a', '=', 'b'));
        $this->assertSame($query, $query->orderBy('id'));
        $this->assertSame($query, $query->limit(10));
        $this->assertSame($query, $query->offset(0));
    }
}
