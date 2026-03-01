<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Contracts\Query;
use Engine\Database\Query\Update;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{
    #[Test]
    public function it_implements_query(): void
    {
        $query = Update::table('users')->set(['name' => 'Jane']);

        $this->assertInstanceOf(Query::class, $query);
    }

    #[Test]
    public function it_generates_update_sql_with_where(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'Jane'])
            ->where('id', 1);

        $this->assertSame('UPDATE users SET name = ? WHERE id = ?', $query->toSql());
    }

    #[Test]
    public function it_returns_bindings_in_correct_order(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'Jane', 'email' => 'jane@example.com'])
            ->where('id', 1);

        // SET bindings first, then WHERE bindings
        $this->assertSame(['Jane', 'jane@example.com', 1], $query->getBindings());
    }

    #[Test]
    public function it_generates_update_without_where(): void
    {
        $query = Update::table('users')->set(['active' => false]);

        $this->assertSame('UPDATE users SET active = ?', $query->toSql());
        $this->assertSame([false], $query->getBindings());
    }

    #[Test]
    public function it_merges_multiple_set_calls(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'Jane'])
            ->set(['email' => 'jane@example.com']);

        $this->assertSame('UPDATE users SET name = ?, email = ?', $query->toSql());
        $this->assertSame(['Jane', 'jane@example.com'], $query->getBindings());
    }

    #[Test]
    public function it_overrides_duplicate_keys_in_set(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'Jane'])
            ->set(['name' => 'John']);

        $this->assertSame('UPDATE users SET name = ?', $query->toSql());
        $this->assertSame(['John'], $query->getBindings());
    }

    #[Test]
    public function it_returns_bindings_in_correct_order_with_multiple_set_and_where(): void
    {
        $query = Update::table('users')
            ->set(['name' => 'Jane'])
            ->set(['email' => 'jane@example.com'])
            ->where('active', true)
            ->where('role', 'admin');

        $this->assertSame(
            'UPDATE users SET name = ?, email = ? WHERE active = ? AND role = ?',
            $query->toSql(),
        );
        // SET bindings first, then WHERE bindings
        $this->assertSame(['Jane', 'jane@example.com', true, 'admin'], $query->getBindings());
    }

    #[Test]
    public function it_returns_fluent_self(): void
    {
        $query = Update::table('users');

        $this->assertSame($query, $query->set(['name' => 'Jane']));
        $this->assertSame($query, $query->where('id', 1));
    }
}
