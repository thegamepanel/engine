<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Expressions;
use Engine\Database\Query\Expressions\ColumnEqualTo;
use Engine\Database\Query\Expressions\ColumnGreaterThen;
use Engine\Database\Query\Expressions\ColumnGreaterThenOrEqualTo;
use Engine\Database\Query\Expressions\ColumnIn;
use Engine\Database\Query\Expressions\ColumnIs;
use Engine\Database\Query\Expressions\ColumnIsNotNull;
use Engine\Database\Query\Expressions\ColumnIsNull;
use Engine\Database\Query\Expressions\ColumnLessThan;
use Engine\Database\Query\Expressions\ColumnLessThanOrEqualTo;
use Engine\Database\Query\Expressions\ColumnNotEqualTo;
use Engine\Database\Query\Expressions\ColumnNotIn;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnhandledMatchError;

final class ExpressionsFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_column_equal_to(): void
    {
        $expr = Expressions::whereColumn('=', 'id', 1);

        $this->assertInstanceOf(ColumnEqualTo::class, $expr);
    }

    #[Test]
    public function it_creates_column_less_than(): void
    {
        $expr = Expressions::whereColumn('<', 'age', 18);

        $this->assertInstanceOf(ColumnLessThan::class, $expr);
    }

    #[Test]
    public function it_creates_column_greater_then(): void
    {
        $expr = Expressions::whereColumn('>', 'age', 18);

        $this->assertInstanceOf(ColumnGreaterThen::class, $expr);
    }

    #[Test]
    public function it_creates_column_less_than_or_equal_to(): void
    {
        $expr = Expressions::whereColumn('<=', 'age', 18);

        $this->assertInstanceOf(ColumnLessThanOrEqualTo::class, $expr);
    }

    #[Test]
    public function it_creates_column_greater_then_or_equal_to(): void
    {
        $expr = Expressions::whereColumn('>=', 'age', 18);

        $this->assertInstanceOf(ColumnGreaterThenOrEqualTo::class, $expr);
    }

    #[Test]
    public function it_creates_column_is(): void
    {
        $expr = Expressions::whereColumn('is', 'active', true);

        $this->assertInstanceOf(ColumnIs::class, $expr);
    }

    #[Test]
    public function it_creates_column_is_null(): void
    {
        $expr = Expressions::whereColumn('is null', 'deleted_at', null);

        $this->assertInstanceOf(ColumnIsNull::class, $expr);
    }

    #[Test]
    public function it_creates_column_is_not_null(): void
    {
        $expr = Expressions::whereColumn('is not null', 'email', null);

        $this->assertInstanceOf(ColumnIsNotNull::class, $expr);
    }

    #[Test]
    public function it_creates_column_not_equal_to(): void
    {
        $expr = Expressions::whereColumn('!=', 'status', 'deleted');

        $this->assertInstanceOf(ColumnNotEqualTo::class, $expr);
    }

    #[Test]
    public function it_creates_column_in(): void
    {
        $expr = Expressions::whereColumn('in', 'role', ['admin', 'mod']);

        $this->assertInstanceOf(ColumnIn::class, $expr);
    }

    #[Test]
    public function it_creates_column_not_in(): void
    {
        $expr = Expressions::whereColumn('not in', 'status', ['deleted', 'banned']);

        $this->assertInstanceOf(ColumnNotIn::class, $expr);
    }

    #[Test]
    public function it_handles_is_uppercase(): void
    {
        $expr = Expressions::whereColumn('IS', 'active', true);

        $this->assertInstanceOf(ColumnIs::class, $expr);
    }

    #[Test]
    public function it_handles_is_null_uppercase(): void
    {
        $expr = Expressions::whereColumn('IS NULL', 'deleted_at', null);

        $this->assertInstanceOf(ColumnIsNull::class, $expr);
    }

    #[Test]
    public function it_handles_is_not_null_uppercase(): void
    {
        $expr = Expressions::whereColumn('IS NOT NULL', 'email', null);

        $this->assertInstanceOf(ColumnIsNotNull::class, $expr);
    }

    #[Test]
    public function it_handles_in_uppercase(): void
    {
        $expr = Expressions::whereColumn('IN', 'role', ['admin']);

        $this->assertInstanceOf(ColumnIn::class, $expr);
    }

    #[Test]
    public function it_handles_not_in_uppercase(): void
    {
        $expr = Expressions::whereColumn('NOT IN', 'status', ['deleted']);

        $this->assertInstanceOf(ColumnNotIn::class, $expr);
    }

    #[Test]
    public function it_throws_on_invalid_operator(): void
    {
        $this->expectException(UnhandledMatchError::class);

        Expressions::whereColumn('LIKE', 'name', '%test%');
    }

    #[Test]
    public function it_creates_raw_expression(): void
    {
        $expr = Expressions::raw('COUNT(*)', []);

        $this->assertInstanceOf(RawExpression::class, $expr);
        $this->assertSame('COUNT(*)', $expr->toSql());
        $this->assertSame([], $expr->getBindings());
    }

    #[Test]
    public function it_creates_raw_expression_with_bindings(): void
    {
        $expr = Expressions::raw('created_at > ?', ['2025-01-01']);

        $this->assertSame(['2025-01-01'], $expr->getBindings());
    }
}
