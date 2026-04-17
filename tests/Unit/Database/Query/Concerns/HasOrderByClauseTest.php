<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Concerns;

use Engine\Database\Query\Concerns\HasOrderByClause;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('order-by')]
class HasOrderByClauseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // orderBy()
    // -------------------------------------------------------------------------

    /**
     * - orderBy() with a string column and default direction produces ASC.
     */
    #[Test]
    public function orderByDefaultsToAsc(): void
    {
        $query = $this->makeQuery();
        $query->orderBy('name');

        $this->assertSame('ORDER BY name ASC', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    /**
     * - orderBy() with lowercase 'desc' produces DESC.
     */
    #[Test]
    public function orderByWithLowercaseDescProducesDesc(): void
    {
        $query = $this->makeQuery();
        $query->orderBy('name', 'desc');

        $this->assertSame('ORDER BY name DESC', $query->sql());
    }

    /**
     * - orderBy() with uppercase 'DESC' produces DESC.
     */
    #[Test]
    public function orderByWithUppercaseDescProducesDesc(): void
    {
        $query = $this->makeQuery();
        $query->orderBy('name', 'DESC');

        $this->assertSame('ORDER BY name DESC', $query->sql());
    }

    /**
     * - orderBy() with mixed case 'Desc' produces DESC.
     */
    #[Test]
    public function orderByWithMixedCaseDescProducesDesc(): void
    {
        $query = $this->makeQuery();
        $query->orderBy('name', 'Desc');

        $this->assertSame('ORDER BY name DESC', $query->sql());
    }

    /**
     * - Multiple orderBy() calls produce comma-separated columns.
     */
    #[Test]
    public function multipleOrderByCallsProduceCommaSeparatedColumns(): void
    {
        $query = $this->makeQuery();
        $query->orderBy('name', 'asc');
        $query->orderBy('age', 'desc');

        $this->assertSame('ORDER BY name ASC, age DESC', $query->sql());
    }

    /**
     * - orderBy() with an Expression column embeds its SQL.
     */
    #[Test]
    public function orderByWithExpressionEmbedsSql(): void
    {
        $expr  = RawExpression::make('FIELD(status, ?)', ['active']);
        $query = $this->makeQuery();
        $query->orderBy($expr);

        $this->assertSame('ORDER BY FIELD(status, ?) ASC', $query->sql());
        $this->assertSame(['active'], $query->bindings());
    }

    /**
     * - Multiple Expression orderBy calls accumulate all bindings.
     */
    #[Test]
    public function multipleExpressionOrderByCallsAccumulateBindings(): void
    {
        $expr1 = RawExpression::make('FIELD(status, ?)', ['active']);
        $expr2 = RawExpression::make('FIELD(role, ?)', ['admin']);
        $query = $this->makeQuery();
        $query->orderBy($expr1);
        $query->orderBy($expr2);

        $this->assertSame('ORDER BY FIELD(status, ?) ASC, FIELD(role, ?) ASC', $query->sql());
        $this->assertSame(['active', 'admin'], $query->bindings());
    }

    /**
     * - No orderBy() calls produces an empty string.
     */
    #[Test]
    public function noOrderByProducesEmptyString(): void
    {
        $query = $this->makeQuery();

        $this->assertSame('', $query->sql());
        $this->assertSame([], $query->bindings());
    }

    private function makeQuery(): object
    {
        return new class {
            use HasOrderByClause;

            public function sql(): string
            {
                return ltrim($this->buildOrderByClause());
            }

            public function bindings(): array
            {
                return $this->getOrderByBindings();
            }
        };
    }
}
