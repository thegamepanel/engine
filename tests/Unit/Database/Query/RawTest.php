<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Contracts\Expression;
use Engine\Database\Query\Raw;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RawTest extends TestCase
{
    #[Test]
    public function it_implements_expression(): void
    {
        $raw = new Raw('1 = 1');

        $this->assertInstanceOf(Expression::class, $raw);
    }

    #[Test]
    public function it_returns_the_sql_string(): void
    {
        $raw = new Raw('COUNT(*) as total');

        $this->assertSame('COUNT(*) as total', $raw->toSql());
    }

    #[Test]
    public function it_returns_empty_bindings_by_default(): void
    {
        $raw = new Raw('NOW()');

        $this->assertSame([], $raw->getBindings());
    }

    #[Test]
    public function it_returns_provided_bindings(): void
    {
        $raw = new Raw('created_at > NOW() - INTERVAL ? DAY', [30]);

        $this->assertSame([30], $raw->getBindings());
    }
}
