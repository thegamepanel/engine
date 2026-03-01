<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnIs;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnIsTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnIs::make('active', true);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnIs::make('active', true);

        $this->assertSame('active IS ?', $expr->toSql());
    }

    #[Test]
    public function it_returns_value_as_binding(): void
    {
        $expr = ColumnIs::make('active', true);

        $this->assertSame([true], $expr->getBindings());
    }
}
