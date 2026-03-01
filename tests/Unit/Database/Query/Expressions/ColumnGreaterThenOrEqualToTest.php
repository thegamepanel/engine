<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnGreaterThenOrEqualTo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnGreaterThenOrEqualToTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnGreaterThenOrEqualTo::make('age', 18);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnGreaterThenOrEqualTo::make('age', 18);

        $this->assertSame('age >= ?', $expr->toSql());
    }

    #[Test]
    public function it_returns_value_as_binding(): void
    {
        $expr = ColumnGreaterThenOrEqualTo::make('age', 18);

        $this->assertSame([18], $expr->getBindings());
    }
}
