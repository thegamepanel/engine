<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnNotEqualTo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnNotEqualToTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnNotEqualTo::make('status', 'deleted');

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnNotEqualTo::make('status', 'deleted');

        $this->assertSame('status != ?', $expr->toSql());
    }

    #[Test]
    public function it_returns_value_as_binding(): void
    {
        $expr = ColumnNotEqualTo::make('status', 'deleted');

        $this->assertSame(['deleted'], $expr->getBindings());
    }
}
