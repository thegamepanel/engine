<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Contracts\Query;
use Engine\Database\Query\Delete;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteTest extends TestCase
{
    #[Test]
    public function it_implements_query(): void
    {
        $query = Delete::from('users');

        $this->assertInstanceOf(Query::class, $query);
    }

    #[Test]
    public function it_generates_delete_sql_with_where(): void
    {
        $query = Delete::from('users')->where('id', 1);

        $this->assertSame('DELETE FROM users WHERE id = ?', $query->toSql());
        $this->assertSame([1], $query->getBindings());
    }

    #[Test]
    public function it_generates_delete_without_where(): void
    {
        $query = Delete::from('users');

        $this->assertSame('DELETE FROM users', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $query = Delete::from('users');

        $this->assertSame($query, $query->where('id', 1));
    }
}
