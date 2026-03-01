<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Expressions\ColumnNotIn;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ColumnNotInTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $expr = ColumnNotIn::make('role', ['banned']);

        $this->assertInstanceOf(Expression::class, $expr);
    }

    #[Test]
    public function it_generates_sql_for_single_value(): void
    {
        $expr = ColumnNotIn::make('status', ['deleted']);

        $this->assertSame('status NOT IN (?)', $expr->toSql());
    }

    #[Test]
    public function it_generates_sql_for_multiple_values(): void
    {
        $expr = ColumnNotIn::make('status', ['deleted', 'banned', 'suspended']);

        $this->assertSame('status NOT IN (?, ?, ?)', $expr->toSql());
    }

    #[Test]
    public function it_returns_values_as_bindings(): void
    {
        $expr = ColumnNotIn::make('id', [4, 5, 6]);

        $this->assertSame([4, 5, 6], $expr->getBindings());
    }
}
