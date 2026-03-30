<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Clauses;

use Engine\Database\Exceptions\InvalidExpressionException;
use Engine\Database\Query\Clauses\WhereClause;
use Engine\Database\Query\Expressions\RawExpression;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('where-clause')]
class WhereClauseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // where()
    // -------------------------------------------------------------------------

    /**
     * - where() with a column, operator, and value produces correct SQL and bindings.
     */
    #[Test]
    public function whereWithColumnOperatorAndValueProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('name', '=', 'John');

        $this->assertSame('name = ?', $clause->toSql());
        $this->assertSame(['John'], $clause->getBindings());
    }

    /**
     * - where() with a closure produces grouped SQL wrapped in parentheses.
     */
    #[Test]
    public function whereWithClosureProducesGroupedSql(): void
    {
        $clause = new WhereClause();
        $clause->where(function (WhereClause $query) {
            $query->where('a', '=', 1);
            $query->where('b', '=', 2);
        });

        $this->assertSame('(a = ? AND b = ?)', $clause->toSql());
        $this->assertSame([1, 2], $clause->getBindings());
    }

    /**
     * - where() with an empty closure throws InvalidExpressionException.
     */
    #[Test]
    public function whereWithEmptyClosureThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->where(function (WhereClause $query) {
            // intentionally empty
        });
    }

    /**
     * - where() with the < operator produces correct SQL.
     */
    #[Test]
    public function whereWithLessThanOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '<', 18);

        $this->assertSame('age < ?', $clause->toSql());
        $this->assertSame([18], $clause->getBindings());
    }

    /**
     * - where() with the <= operator produces correct SQL.
     */
    #[Test]
    public function whereWithLessThanOrEqualOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '<=', 18);

        $this->assertSame('age <= ?', $clause->toSql());
        $this->assertSame([18], $clause->getBindings());
    }

    /**
     * - where() with the >= operator produces correct SQL.
     */
    #[Test]
    public function whereWithGreaterThanOrEqualOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '>=', 18);

        $this->assertSame('age >= ?', $clause->toSql());
        $this->assertSame([18], $clause->getBindings());
    }

    /**
     * - where() with the > operator produces correct SQL.
     */
    #[Test]
    public function whereWithGreaterThanOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('age', '>', 18);

        $this->assertSame('age > ?', $clause->toSql());
        $this->assertSame([18], $clause->getBindings());
    }

    /**
     * - where() with the IS operator produces correct SQL.
     */
    #[Test]
    public function whereWithIsOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('active', 'is', true);

        $this->assertSame('active IS ?', $clause->toSql());
        $this->assertSame([true], $clause->getBindings());
    }

    /**
     * - where() with the != operator produces correct SQL.
     */
    #[Test]
    public function whereWithNotEqualOperatorProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '!=', 'banned');

        $this->assertSame('status != ?', $clause->toSql());
        $this->assertSame(['banned'], $clause->getBindings());
    }

    /**
     * - where() with an invalid operator throws InvalidArgumentException.
     */
    #[Test]
    public function whereWithInvalidOperatorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $clause = new WhereClause();
        $clause->where('col', 'INVALID', 'value');
    }

    // -------------------------------------------------------------------------
    // orWhere()
    // -------------------------------------------------------------------------

    /**
     * - orWhere() uses OR conjunction between conditions.
     */
    #[Test]
    public function orWhereUsesOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('name', '=', 'John');
        $clause->orWhere('name', '=', 'Jane');

        $this->assertSame('name = ? OR name = ?', $clause->toSql());
        $this->assertSame(['John', 'Jane'], $clause->getBindings());
    }

    /**
     * - orWhere() with a closure produces grouped SQL with OR conjunction.
     */
    #[Test]
    public function orWhereWithClosureProducesGroupedSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhere(function (WhereClause $query) {
            $query->where('role', '=', 'admin');
            $query->where('verified', '=', true);
        });

        $this->assertSame('status = ? OR (role = ? AND verified = ?)', $clause->toSql());
        $this->assertSame(['active', 'admin', true], $clause->getBindings());
    }

    /**
     * - orWhere() with an empty closure throws InvalidExpressionException.
     */
    #[Test]
    public function orWhereWithEmptyClosureThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->orWhere(function (WhereClause $query) {
            // intentionally empty
        });
    }

    // -------------------------------------------------------------------------
    // whereNull() / orWhereNull() / whereNotNull()
    // -------------------------------------------------------------------------

    /**
     * - whereNull() produces IS NULL SQL with no bindings.
     */
    #[Test]
    public function whereNullProducesIsNullSql(): void
    {
        $clause = new WhereClause();
        $clause->whereNull('deleted_at');

        $this->assertSame('deleted_at IS NULL', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    /**
     * - orWhereNull() produces IS NULL SQL with OR conjunction.
     */
    #[Test]
    public function orWhereNullProducesIsNullSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereNull('deleted_at');

        $this->assertSame('status = ? OR deleted_at IS NULL', $clause->toSql());
        $this->assertSame(['active'], $clause->getBindings());
    }

    /**
     * - whereNotNull() produces IS NOT NULL SQL with no bindings.
     */
    #[Test]
    public function whereNotNullProducesIsNotNullSql(): void
    {
        $clause = new WhereClause();
        $clause->whereNotNull('email');

        $this->assertSame('email IS NOT NULL', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    // -------------------------------------------------------------------------
    // orWhereNotNull()
    // -------------------------------------------------------------------------

    /**
     * - orWhereNotNull() produces IS NOT NULL SQL with OR conjunction.
     */
    #[Test]
    public function orWhereNotNullProducesIsNotNullSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereNotNull('email');

        $this->assertSame('status = ? OR email IS NOT NULL', $clause->toSql());
        $this->assertSame(['active'], $clause->getBindings());
    }

    // -------------------------------------------------------------------------
    // whereIn() / whereNotIn()
    // -------------------------------------------------------------------------

    /**
     * - whereIn() with an array produces IN SQL with placeholders.
     */
    #[Test]
    public function whereInWithArrayProducesInSql(): void
    {
        $clause = new WhereClause();
        $clause->whereIn('id', [1, 2, 3]);

        $this->assertSame('id IN (?, ?, ?)', $clause->toSql());
        $this->assertSame([1, 2, 3], $clause->getBindings());
    }

    /**
     * - whereIn() with an Expression embeds the subquery SQL.
     */
    #[Test]
    public function whereInWithExpressionEmbedsSubquerySql(): void
    {
        $subquery = RawExpression::make('SELECT id FROM other WHERE active = ?', [true]);

        $clause = new WhereClause();
        $clause->whereIn('id', $subquery);

        $this->assertSame('id IN (SELECT id FROM other WHERE active = ?)', $clause->toSql());
        $this->assertSame([true], $clause->getBindings());
    }

    /**
     * - whereIn() with an empty array throws InvalidExpressionException.
     */
    #[Test]
    public function whereInWithEmptyArrayThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->whereIn('id', []);
    }

    /**
     * - whereNotIn() with an array produces NOT IN SQL with placeholders.
     */
    #[Test]
    public function whereNotInWithArrayProducesNotInSql(): void
    {
        $clause = new WhereClause();
        $clause->whereNotIn('id', [4, 5]);

        $this->assertSame('id NOT IN (?, ?)', $clause->toSql());
        $this->assertSame([4, 5], $clause->getBindings());
    }

    /**
     * - whereNotIn() with an Expression embeds the subquery SQL.
     */
    #[Test]
    public function whereNotInWithExpressionEmbedsSubquerySql(): void
    {
        $subquery = RawExpression::make('SELECT id FROM banned', []);

        $clause = new WhereClause();
        $clause->whereNotIn('user_id', $subquery);

        $this->assertSame('user_id NOT IN (SELECT id FROM banned)', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    /**
     * - whereNotIn() with an empty array throws InvalidExpressionException.
     */
    #[Test]
    public function whereNotInWithEmptyArrayThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->whereNotIn('id', []);
    }

    // -------------------------------------------------------------------------
    // orWhereIn()
    // -------------------------------------------------------------------------

    /**
     * - orWhereIn() with an array produces IN SQL with OR conjunction.
     */
    #[Test]
    public function orWhereInWithArrayProducesInSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereIn('id', [1, 2, 3]);

        $this->assertSame('status = ? OR id IN (?, ?, ?)', $clause->toSql());
        $this->assertSame(['active', 1, 2, 3], $clause->getBindings());
    }

    /**
     * - orWhereIn() with an Expression embeds the subquery SQL with OR conjunction.
     */
    #[Test]
    public function orWhereInWithExpressionEmbedsSubquerySqlWithOrConjunction(): void
    {
        $subquery = RawExpression::make('SELECT id FROM other WHERE active = ?', [true]);

        $clause = new WhereClause();
        $clause->where('status', '=', 'banned');
        $clause->orWhereIn('id', $subquery);

        $this->assertSame('status = ? OR id IN (SELECT id FROM other WHERE active = ?)', $clause->toSql());
        $this->assertSame(['banned', true], $clause->getBindings());
    }

    /**
     * - orWhereIn() with an empty array throws InvalidExpressionException.
     */
    #[Test]
    public function orWhereInWithEmptyArrayThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->orWhereIn('id', []);
    }

    // -------------------------------------------------------------------------
    // orWhereNotIn()
    // -------------------------------------------------------------------------

    /**
     * - orWhereNotIn() with an array produces NOT IN SQL with OR conjunction.
     */
    #[Test]
    public function orWhereNotInWithArrayProducesNotInSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereNotIn('id', [4, 5]);

        $this->assertSame('status = ? OR id NOT IN (?, ?)', $clause->toSql());
        $this->assertSame(['active', 4, 5], $clause->getBindings());
    }

    /**
     * - orWhereNotIn() with an Expression embeds the subquery SQL with OR conjunction.
     */
    #[Test]
    public function orWhereNotInWithExpressionEmbedsSubquerySqlWithOrConjunction(): void
    {
        $subquery = RawExpression::make('SELECT id FROM banned', []);

        $clause = new WhereClause();
        $clause->where('active', '=', true);
        $clause->orWhereNotIn('user_id', $subquery);

        $this->assertSame('active = ? OR user_id NOT IN (SELECT id FROM banned)', $clause->toSql());
        $this->assertSame([true], $clause->getBindings());
    }

    /**
     * - orWhereNotIn() with an empty array throws InvalidExpressionException.
     */
    #[Test]
    public function orWhereNotInWithEmptyArrayThrowsException(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $clause = new WhereClause();
        $clause->orWhereNotIn('id', []);
    }

    // -------------------------------------------------------------------------
    // whereRaw()
    // -------------------------------------------------------------------------

    /**
     * - whereRaw() produces the exact SQL string with the given bindings.
     */
    #[Test]
    public function whereRawProducesExactSqlWithBindings(): void
    {
        $clause = new WhereClause();
        $clause->whereRaw('LOWER(name) = ?', ['john']);

        $this->assertSame('LOWER(name) = ?', $clause->toSql());
        $this->assertSame(['john'], $clause->getBindings());
    }

    /**
     * - whereRaw() with no bindings produces SQL with an empty bindings array.
     */
    #[Test]
    public function whereRawWithNoBindingsProducesSqlWithEmptyBindings(): void
    {
        $clause = new WhereClause();
        $clause->whereRaw('1 = 1');

        $this->assertSame('1 = 1', $clause->toSql());
        $this->assertSame([], $clause->getBindings());
    }

    /**
     * - whereRaw() combined with other clauses uses AND conjunction.
     */
    #[Test]
    public function whereRawCombinedWithOtherClausesUsesAndConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->whereRaw('LOWER(name) = ?', ['john']);

        $this->assertSame('status = ? AND LOWER(name) = ?', $clause->toSql());
        $this->assertSame(['active', 'john'], $clause->getBindings());
    }

    // -------------------------------------------------------------------------
    // orWhereRaw()
    // -------------------------------------------------------------------------

    /**
     * - orWhereRaw() produces the exact SQL string with OR conjunction.
     */
    #[Test]
    public function orWhereRawProducesExactSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereRaw('LOWER(name) = ?', ['john']);

        $this->assertSame('status = ? OR LOWER(name) = ?', $clause->toSql());
        $this->assertSame(['active', 'john'], $clause->getBindings());
    }

    /**
     * - orWhereRaw() with no bindings produces SQL with an empty bindings array.
     */
    #[Test]
    public function orWhereRawWithNoBindingsProducesSqlWithOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('id', '=', 1);
        $clause->orWhereRaw('1 = 1');

        $this->assertSame('id = ? OR 1 = 1', $clause->toSql());
        $this->assertSame([1], $clause->getBindings());
    }

    // -------------------------------------------------------------------------
    // whereFullText() / orWhereFullText()
    // -------------------------------------------------------------------------

    /**
     * - whereFullText() in natural language mode produces correct SQL.
     */
    #[Test]
    public function whereFullTextNaturalModeProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->whereFullText(['title', 'body'], 'search term');

        $this->assertSame('MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)', $clause->toSql());
        $this->assertSame(['search term'], $clause->getBindings());
    }

    /**
     * - whereFullText() in boolean mode produces correct SQL.
     */
    #[Test]
    public function whereFullTextBooleanModeProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->whereFullText(['title'], '+required -excluded', 'boolean');

        $this->assertSame('MATCH(title) AGAINST(? IN BOOLEAN MODE)', $clause->toSql());
        $this->assertSame(['+required -excluded'], $clause->getBindings());
    }

    /**
     * - whereFullText() uses AND conjunction with other conditions.
     */
    #[Test]
    public function whereFullTextUsesAndConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->whereFullText(['title'], 'search');

        $this->assertSame('status = ? AND MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)', $clause->toSql());
        $this->assertSame(['active', 'search'], $clause->getBindings());
    }

    /**
     * - orWhereFullText() uses OR conjunction with other conditions.
     */
    #[Test]
    public function orWhereFullTextUsesOrConjunction(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->orWhereFullText(['title', 'body'], 'search term');

        $this->assertSame('status = ? OR MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)', $clause->toSql());
        $this->assertSame(['active', 'search term'], $clause->getBindings());
    }

    /**
     * - orWhereFullText() in boolean mode produces correct SQL.
     */
    #[Test]
    public function orWhereFullTextBooleanModeProducesCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('id', '=', 1);
        $clause->orWhereFullText(['title'], '+php', 'boolean');

        $this->assertSame('id = ? OR MATCH(title) AGAINST(? IN BOOLEAN MODE)', $clause->toSql());
        $this->assertSame([1, '+php'], $clause->getBindings());
    }

    // -------------------------------------------------------------------------
    // isEmpty()
    // -------------------------------------------------------------------------

    /**
     * - isEmpty() returns true when no conditions have been added.
     */
    #[Test]
    public function isEmptyReturnsTrueWhenNoConditions(): void
    {
        $clause = new WhereClause();

        $this->assertTrue($clause->isEmpty());
    }

    /**
     * - isEmpty() returns false after a condition has been added.
     */
    #[Test]
    public function isEmptyReturnsFalseAfterConditionAdded(): void
    {
        $clause = new WhereClause();
        $clause->where('id', '=', 1);

        $this->assertFalse($clause->isEmpty());
    }

    // -------------------------------------------------------------------------
    // Chained combinations
    // -------------------------------------------------------------------------

    /**
     * - Multiple chained where clauses produce correct combined SQL with AND conjunctions.
     */
    #[Test]
    public function chainedWhereClausesProduceCorrectCombinedSql(): void
    {
        $clause = new WhereClause();
        $clause->where('status', '=', 'active');
        $clause->whereNotNull('email');
        $clause->whereIn('role', ['admin', 'editor']);

        $this->assertSame('status = ? AND email IS NOT NULL AND role IN (?, ?)', $clause->toSql());
        $this->assertSame(['active', 'admin', 'editor'], $clause->getBindings());
    }

    /**
     * - Mixed AND and OR conditions produce correct SQL with proper conjunctions.
     */
    #[Test]
    public function mixedAndOrConditionsProduceCorrectSql(): void
    {
        $clause = new WhereClause();
        $clause->where('active', '=', true);
        $clause->orWhere('role', '=', 'admin');
        $clause->where('verified', '=', true);

        $this->assertSame('active = ? OR role = ? AND verified = ?', $clause->toSql());
        $this->assertSame([true, 'admin', true], $clause->getBindings());
    }
}
