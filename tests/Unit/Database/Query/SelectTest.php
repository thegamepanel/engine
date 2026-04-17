<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Clauses\JoinClause;
use Engine\Database\Query\Expressions;
use Engine\Database\Query\Expressions\RawExpression;
use Engine\Database\Query\Select;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('select')]
class SelectTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Basic select
    // -------------------------------------------------------------------------

    /**
     * - Select::from('users') produces SELECT * FROM users.
     */
    #[Test]
    public function basicSelectProducesSelectStarSql(): void
    {
        $query = Select::from('users');

        $this->assertSame('SELECT * FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // columns() / addColumn()
    // -------------------------------------------------------------------------

    /**
     * - columns() produces a SELECT with the named columns.
     */
    #[Test]
    public function columnsProducesSelectWithSpecificColumns(): void
    {
        $query = Select::from('users')->columns('name', 'email');

        $this->assertSame('SELECT name, email FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - An Expression column embeds its SQL inline.
     */
    #[Test]
    public function columnsWithExpressionEmbedsSql(): void
    {
        $query = Select::from('users')->columns('name', Expressions::count('*'));

        $this->assertSame('SELECT name, COUNT(*) FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - addColumn() appends a column to an existing columns list.
     */
    #[Test]
    public function addColumnAppendsColumn(): void
    {
        $query = Select::from('users')->columns('name')->addColumn('email');

        $this->assertSame('SELECT name, email FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // distinct()
    // -------------------------------------------------------------------------

    /**
     * - distinct() inserts DISTINCT keyword after SELECT.
     */
    #[Test]
    public function distinctAddsDistinctKeyword(): void
    {
        $query = Select::from('users')->distinct()->columns('status');

        $this->assertSame('SELECT DISTINCT status FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - Expressions::match() factory produces MATCH AGAINST as a column expression.
     */
    #[Test]
    public function expressionsMatchFactoryProducesMatchAgainstColumn(): void
    {
        $query = Select::from('posts')->columns(Expressions::match(['title', 'body'], 'search'));

        $this->assertSame(
            'SELECT MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE) FROM posts',
            $query->toSql(),
        );
        $this->assertSame(['search'], $query->getBindings());
    }

    /**
     * - Expressions::matchBoolean() factory produces MATCH AGAINST in boolean mode.
     */
    #[Test]
    public function expressionsMatchBooleanFactoryProducesBooleanMode(): void
    {
        $query = Select::from('posts')->columns(Expressions::matchBoolean(['title'], '+php'));

        $this->assertSame(
            'SELECT MATCH(title) AGAINST(? IN BOOLEAN MODE) FROM posts',
            $query->toSql(),
        );
        $this->assertSame(['+php'], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Subquery FROM
    // -------------------------------------------------------------------------

    /**
     * - An Expression passed as the table wraps it in a subquery.
     */
    #[Test]
    public function expressionAsTableProducesSubquery(): void
    {
        $subquery = RawExpression::make('SELECT id FROM users WHERE active = ?', [true]);
        $query    = Select::from($subquery)->columns('id');

        $this->assertSame('SELECT id FROM (SELECT id FROM users WHERE active = ?)', $query->toSql());
        $this->assertSame([true], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // where variants
    // -------------------------------------------------------------------------

    /**
     * - where() produces a WHERE clause with a bound placeholder.
     */
    #[Test]
    public function whereProducesWhereClause(): void
    {
        $query = Select::from('users')->where('status', '=', 'active');

        $this->assertSame('SELECT * FROM users WHERE status = ?', $query->toSql());
        $this->assertSame(['active'], $query->getBindings());
    }

    /**
     * - orWhere() uses OR conjunction for subsequent conditions.
     */
    #[Test]
    public function orWhereProducesOrConjunction(): void
    {
        $query = Select::from('users')
            ->where('status', '=', 'active')
            ->orWhere('role', '=', 'admin')
        ;

        $this->assertSame('SELECT * FROM users WHERE status = ? OR role = ?', $query->toSql());
        $this->assertSame(['active', 'admin'], $query->getBindings());
    }

    /**
     * - whereNull() produces an IS NULL clause.
     */
    #[Test]
    public function whereNullProducesIsNullClause(): void
    {
        $query = Select::from('users')->whereNull('deleted_at');

        $this->assertSame('SELECT * FROM users WHERE deleted_at IS NULL', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - orWhereNull() produces an OR IS NULL clause.
     */
    #[Test]
    public function orWhereNullProducesOrIsNullClause(): void
    {
        $query = Select::from('users')
            ->whereNull('deleted_at')
            ->orWhereNull('suspended_at')
        ;

        $this->assertSame('SELECT * FROM users WHERE deleted_at IS NULL OR suspended_at IS NULL', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - whereNotNull() produces an IS NOT NULL clause.
     */
    #[Test]
    public function whereNotNullProducesIsNotNullClause(): void
    {
        $query = Select::from('users')->whereNotNull('email');

        $this->assertSame('SELECT * FROM users WHERE email IS NOT NULL', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - orWhereNotNull() produces an OR IS NOT NULL clause.
     */
    #[Test]
    public function orWhereNotNullProducesOrIsNotNullClause(): void
    {
        $query = Select::from('users')
            ->whereNotNull('email')
            ->orWhereNotNull('phone')
        ;

        $this->assertSame('SELECT * FROM users WHERE email IS NOT NULL OR phone IS NOT NULL', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - whereIn() produces an IN clause with bound placeholders.
     */
    #[Test]
    public function whereInProducesInClause(): void
    {
        $query = Select::from('users')->whereIn('role', ['admin', 'moderator']);

        $this->assertSame('SELECT * FROM users WHERE role IN (?, ?)', $query->toSql());
        $this->assertSame(['admin', 'moderator'], $query->getBindings());
    }

    /**
     * - orWhereIn() produces an OR IN clause.
     */
    #[Test]
    public function orWhereInProducesOrInClause(): void
    {
        $query = Select::from('users')
            ->where('active', '=', true)
            ->orWhereIn('role', ['admin', 'moderator'])
        ;

        $this->assertSame('SELECT * FROM users WHERE active = ? OR role IN (?, ?)', $query->toSql());
        $this->assertSame([true, 'admin', 'moderator'], $query->getBindings());
    }

    /**
     * - whereNotIn() produces a NOT IN clause with bound placeholders.
     */
    #[Test]
    public function whereNotInProducesNotInClause(): void
    {
        $query = Select::from('users')->whereNotIn('status', ['banned', 'suspended']);

        $this->assertSame('SELECT * FROM users WHERE status NOT IN (?, ?)', $query->toSql());
        $this->assertSame(['banned', 'suspended'], $query->getBindings());
    }

    /**
     * - orWhereNotIn() produces an OR NOT IN clause.
     */
    #[Test]
    public function orWhereNotInProducesOrNotInClause(): void
    {
        $query = Select::from('users')
            ->where('active', '=', true)
            ->orWhereNotIn('status', ['banned', 'suspended'])
        ;

        $this->assertSame('SELECT * FROM users WHERE active = ? OR status NOT IN (?, ?)', $query->toSql());
        $this->assertSame([true, 'banned', 'suspended'], $query->getBindings());
    }

    /**
     * - whereRaw() embeds raw SQL with its bindings.
     */
    #[Test]
    public function whereRawProducesRawWhereClause(): void
    {
        $query = Select::from('users')->whereRaw('YEAR(created_at) = ?', [2024]);

        $this->assertSame('SELECT * FROM users WHERE YEAR(created_at) = ?', $query->toSql());
        $this->assertSame([2024], $query->getBindings());
    }

    /**
     * - orWhereRaw() embeds raw SQL with OR conjunction.
     */
    #[Test]
    public function orWhereRawProducesOrRawWhereClause(): void
    {
        $query = Select::from('users')
            ->where('active', '=', true)
            ->orWhereRaw('YEAR(created_at) = ?', [2024])
        ;

        $this->assertSame('SELECT * FROM users WHERE active = ? OR YEAR(created_at) = ?', $query->toSql());
        $this->assertSame([true, 2024], $query->getBindings());
    }

    /**
     * - whereFullText() produces a MATCH ... AGAINST clause in natural language mode.
     */
    #[Test]
    public function whereFullTextProducesMatchAgainstClause(): void
    {
        $query = Select::from('posts')->whereFullText(['title', 'body'], 'search term');

        $this->assertSame(
            'SELECT * FROM posts WHERE MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)',
            $query->toSql(),
        );
        $this->assertSame(['search term'], $query->getBindings());
    }

    /**
     * - orWhereFullText() produces an OR MATCH ... AGAINST clause.
     */
    #[Test]
    public function orWhereFullTextProducesOrMatchAgainstClause(): void
    {
        $query = Select::from('posts')
            ->where('active', '=', true)
            ->orWhereFullText(['title', 'body'], 'search term')
        ;

        $this->assertSame(
            'SELECT * FROM posts WHERE active = ? OR MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)',
            $query->toSql(),
        );
        $this->assertSame([true, 'search term'], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // join variants
    // -------------------------------------------------------------------------

    /**
     * - join() produces an INNER JOIN ... ON clause.
     */
    #[Test]
    public function joinProducesInnerJoinClause(): void
    {
        $query = Select::from('users')->join('posts', 'users.id', '=', 'posts.user_id');

        $this->assertSame('SELECT * FROM users INNER JOIN posts ON users.id = posts.user_id', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - leftJoin() produces a LEFT JOIN ... ON clause.
     */
    #[Test]
    public function leftJoinProducesLeftJoinClause(): void
    {
        $query = Select::from('users')->leftJoin('profiles', 'users.id', '=', 'profiles.user_id');

        $this->assertSame('SELECT * FROM users LEFT JOIN profiles ON users.id = profiles.user_id', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - rightJoin() produces a RIGHT JOIN ... ON clause.
     */
    #[Test]
    public function rightJoinProducesRightJoinClause(): void
    {
        $query = Select::from('orders')->rightJoin('users', 'orders.user_id', '=', 'users.id');

        $this->assertSame('SELECT * FROM orders RIGHT JOIN users ON orders.user_id = users.id', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - crossJoin() produces a CROSS JOIN clause with no ON condition.
     */
    #[Test]
    public function crossJoinProducesCrossJoinClause(): void
    {
        $query = Select::from('users')->crossJoin('roles');

        $this->assertSame('SELECT * FROM users CROSS JOIN roles', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - A join closure can combine on() and where() conditions.
     */
    #[Test]
    public function joinWithClosureProducesComplexConditions(): void
    {
        $query = Select::from('users')->join('posts', function (JoinClause $join) {
            $join->on('users.id', '=', 'posts.user_id');
            $join->where('posts.published', '=', true);
        });

        $this->assertSame(
            'SELECT * FROM users INNER JOIN posts ON users.id = posts.user_id AND posts.published = ?',
            $query->toSql(),
        );
        $this->assertSame([true], $query->getBindings());
    }

    /**
     * - Multiple joins with bound values accumulate all bindings.
     */
    #[Test]
    public function multipleJoinsWithBoundValuesAccumulateBindings(): void
    {
        $query = Select::from('users')
            ->join('posts', function (JoinClause $join) {
                $join->on('users.id', '=', 'posts.user_id');
                $join->where('posts.status', '=', 'published');
            })
            ->join('comments', function (JoinClause $join) {
                $join->on('posts.id', '=', 'comments.post_id');
                $join->where('comments.approved', '=', true);
            })
        ;

        $this->assertSame(
            'SELECT * FROM users'
            . ' INNER JOIN posts ON users.id = posts.user_id AND posts.status = ?'
            . ' INNER JOIN comments ON posts.id = comments.post_id AND comments.approved = ?',
            $query->toSql(),
        );
        $this->assertSame(['published', true], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // groupBy() / having()
    // -------------------------------------------------------------------------

    /**
     * - groupBy() appends a GROUP BY clause.
     */
    #[Test]
    public function groupByProducesGroupByClause(): void
    {
        $query = Select::from('orders')->columns('status')->groupBy('status');

        $this->assertSame('SELECT status FROM orders GROUP BY status', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - havingRaw() appends a HAVING clause with a raw expression.
     */
    #[Test]
    public function havingProducesHavingClause(): void
    {
        $query = Select::from('orders')
            ->columns('status')
            ->groupBy('status')
            ->havingRaw('COUNT(*) > ?', [5])
        ;

        $this->assertSame('SELECT status FROM orders GROUP BY status HAVING COUNT(*) > ?', $query->toSql());
        $this->assertSame([5], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // orderBy() / limit() / offset()
    // -------------------------------------------------------------------------

    /**
     * - orderBy() appends an ORDER BY clause.
     */
    #[Test]
    public function orderByProducesOrderByClause(): void
    {
        $query = Select::from('users')->orderBy('name');

        $this->assertSame('SELECT * FROM users ORDER BY name ASC', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    /**
     * - limit() and offset() append LIMIT and OFFSET clauses.
     */
    #[Test]
    public function limitAndOffsetProduceLimitOffsetClause(): void
    {
        $query = Select::from('users')->limit(10)->offset(20);

        $this->assertSame('SELECT * FROM users LIMIT 10 OFFSET 20', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Full combination
    // -------------------------------------------------------------------------

    /**
     * - All clauses combined produce SQL in the correct clause order.
     */
    #[Test]
    public function fullCombinationProducesCorrectSqlOrder(): void
    {
        $query = Select::from('orders')
            ->columns('orders.id', 'users.name')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.status', '=', 'completed')
            ->groupBy('orders.id', 'users.name')
            ->havingRaw('COUNT(*) > ?', [1])
            ->orderBy('orders.id', 'desc')
            ->limit(25)
            ->offset(50)
        ;

        $this->assertSame(
            'SELECT orders.id, users.name FROM orders'
            . ' INNER JOIN users ON orders.user_id = users.id'
            . ' WHERE orders.status = ?'
            . ' GROUP BY orders.id, users.name'
            . ' HAVING COUNT(*) > ?'
            . ' ORDER BY orders.id DESC'
            . ' LIMIT 25 OFFSET 50',
            $query->toSql(),
        );
        $this->assertSame(['completed', 1], $query->getBindings());
    }

    /**
     * - Bindings are collected in the correct order: table subquery, expression columns, joins, where, group by, having, order by.
     */
    #[Test]
    public function bindingsAreCollectedInCorrectOrder(): void
    {
        $tableSubquery  = RawExpression::make('SELECT id FROM base WHERE flag = ?', ['table-flag']);
        $expressionCol  = RawExpression::make('COALESCE(a, ?)', ['col-default']);
        $joinWhereValue = 'join-val';
        $whereValue     = 'where-val';
        $groupByExpr    = RawExpression::make('FIELD(status, ?)', ['group-val']);
        $havingValue    = 99;
        $orderByExpr    = RawExpression::make('FIELD(priority, ?)', ['order-val']);

        $query = Select::from($tableSubquery)
            ->columns('id', $expressionCol)
            ->join('posts', function (JoinClause $join) use ($joinWhereValue) {
                $join->on('base.id', '=', 'posts.base_id');
                $join->where('posts.active', '=', $joinWhereValue);
            })
            ->where('status', '=', $whereValue)
            ->groupBy($groupByExpr)
            ->havingRaw('COUNT(*) > ?', [$havingValue])
            ->orderBy($orderByExpr)
        ;

        $this->assertSame(
            ['table-flag', 'col-default', $joinWhereValue, $whereValue, 'group-val', $havingValue, 'order-val'],
            $query->getBindings(),
        );
    }
}
