<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnEqualTo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnEqualToTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnEqualTo::make('id', 1);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnEqualTo::make('name', 'John');

        $this->assertSame('name = ?', $expr->toSql());
    }

    #[Test]
    public function it_returns_value_as_binding(): void
    {
        $expr = ColumnEqualTo::make('id', 42);

        $this->assertSame([42], $expr->getBindings());
    }
}
