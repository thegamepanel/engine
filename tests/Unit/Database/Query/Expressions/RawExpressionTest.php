<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\RawExpression;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RawExpressionTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = RawExpression::make('1 = 1', []);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_returns_raw_sql(): void
    {
        $expr = RawExpression::make('COUNT(*) as total', []);

        $this->assertSame('COUNT(*) as total', $expr->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings(): void
    {
        $expr = RawExpression::make('1 = 1', []);

        $this->assertSame([], $expr->getBindings());
    }

    #[Test]
    public function it_returns_provided_bindings(): void
    {
        $expr = RawExpression::make('created_at > ?', ['2025-01-01']);

        $this->assertSame(['2025-01-01'], $expr->getBindings());
    }
}
