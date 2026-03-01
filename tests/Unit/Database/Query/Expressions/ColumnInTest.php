<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnIn;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnInTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnIn::make('role', ['admin']);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_sql_for_single_value(): void
    {
        $expr = ColumnIn::make('role', ['admin']);

        $this->assertSame('role IN (?)', $expr->toSql());
    }

    #[Test]
    public function it_generates_sql_for_multiple_values(): void
    {
        $expr = ColumnIn::make('role', ['admin', 'moderator', 'user']);

        $this->assertSame('role IN (?, ?, ?)', $expr->toSql());
    }

    #[Test]
    public function it_returns_values_as_bindings(): void
    {
        $expr = ColumnIn::make('id', [1, 2, 3]);

        $this->assertSame([1, 2, 3], $expr->getBindings());
    }
}
