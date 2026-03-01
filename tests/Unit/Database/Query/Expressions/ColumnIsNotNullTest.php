<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnIsNotNull;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnIsNotNullTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnIsNotNull::make('email');

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnIsNotNull::make('email');

        $this->assertSame('email IS NOT NULL', $expr->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings(): void
    {
        $expr = ColumnIsNotNull::make('email');

        $this->assertSame([], $expr->getBindings());
    }
}
