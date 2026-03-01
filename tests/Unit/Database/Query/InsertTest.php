<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Contracts\Query;
use Engine\Database\Query\Insert;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsertTest extends TestCase
{
    #[Test]
    public function it_implements_query(): void
    {
        $query = Insert::into('users')->values(['name' => 'John']);

        $this->assertInstanceOf(Query::class, $query);
    }

    #[Test]
    public function it_generates_insert_sql(): void
    {
        $query = Insert::into('users')->values([
            'name'  => 'John',
            'email' => 'john@example.com',
        ]);

        $this->assertSame('INSERT INTO users (name, email) VALUES (?, ?)', $query->toSql());
    }

    #[Test]
    public function it_returns_values_as_bindings(): void
    {
        $query = Insert::into('users')->values([
            'name'  => 'John',
            'email' => 'john@example.com',
        ]);

        $this->assertSame(['John', 'john@example.com'], $query->getBindings());
    }

    #[Test]
    public function it_handles_single_value(): void
    {
        $query = Insert::into('settings')->values(['key' => 'theme']);

        $this->assertSame('INSERT INTO settings (key) VALUES (?)', $query->toSql());
        $this->assertSame(['theme'], $query->getBindings());
    }

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $query = Insert::into('users');

        $this->assertSame($query, $query->values(['name' => 'John']));
    }
}
