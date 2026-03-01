<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnIsNull;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnIsNullTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnIsNull::make('deleted_at');

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_correct_sql(): void
    {
        $expr = ColumnIsNull::make('deleted_at');

        $this->assertSame('deleted_at IS NULL', $expr->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings(): void
    {
        $expr = ColumnIsNull::make('deleted_at');

        $this->assertSame([], $expr->getBindings());
    }
}
