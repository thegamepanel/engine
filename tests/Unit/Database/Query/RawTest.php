<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Raw;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('raw')]
class RawTest extends TestCase
{
    /**
     * - A raw query returns the SQL string and bindings as provided.
     */
    #[Test]
    public function rawQueryReturnsSqlAndBindings(): void
    {
        $raw = Raw::from('SELECT * FROM users WHERE id = ?', [1]);

        $this->assertSame('SELECT * FROM users WHERE id = ?', $raw->toSql());
        $this->assertSame([1], $raw->getBindings());
    }

    /**
     * - A raw query with no bindings defaults to an empty array.
     */
    #[Test]
    public function rawQueryDefaultsToEmptyBindings(): void
    {
        $raw = Raw::from('SELECT 1');

        $this->assertSame('SELECT 1', $raw->toSql());
        $this->assertSame([], $raw->getBindings());
    }
}
